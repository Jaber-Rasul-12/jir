<?php namespace Finance\Finance\Controllers;

use Backend\Classes\Controller;
use BackendMenu;
use Finance\Finance\Models\Invoice;
use Finance\Finance\Models\Month;
use Finance\Finance\Models\Year;
use Flash;
use Carbon\Carbon;
use Finance\Finance\Models\ModelType;
use Jacob\Logbook\ReportWidgets\LogBookModelChanges;
use Db;

class Invoices extends Controller
{
    public $implement = [        'Backend\Behaviors\ListController',        'Backend\Behaviors\FormController' ,  \Backend\Behaviors\RelationController::class,    ];
    
    public $listConfig = 'config_list.yaml';
    public $formConfig = 'config_form.yaml';

    public $requiredPermissions = [
        'invoices' 
    ];

    public $relationConfig = 'relation_config.yaml';

    public function __construct()
    {
        parent::__construct();
        BackendMenu::setContext('Finance.Finance', 'finance_menu', 'invoices');
        $this->addCss('/plugins/finance/finance/assets/css/style_button.css', 'finance.finance');
    }

           public function formGetRedirectUrl($context = null, $model = null)
    {
               $url = post('url');
        if (($url == 'create') && !empty($url)) {
            return "finance/finance/invoices/create";
        }else if (($url == 'preview') && !empty($url)) {
            return "finance/finance/invoices/$url/$model->id";
        }else {
            if ((post("close") == 1) && !empty(post("close"))) {
                return "finance/finance/invoices";
            } else {
                return "finance/finance/invoices/update/$model->id";
            }
        }
    }

    public function logchanges(){

    $this->pageTitle = trans('finance.finance::lang.plugin.log_changes_finance');


     $widget = new LogBookModelChanges($this, [
            'limitPerPage' => 20 
        ]);
        
        // Pass to view
        $this->vars['logbookWidget'] = $widget;

    }

// public function onDailyFundMovement()
// {
//     $invoices = \Finance\Finance\Models\Invoice::all();
    
//     return [
//         '#Lists' => $this->makePartial('daily_fund_movement', [
//             'invoices' => $invoices
//         ]),
//         '#Filter-listFilter' => ' ',
//     ];
// }


 public function onDailyFundMovement()
    {
        return [
            '#Lists' => $this->makePartial('daily_fund_movement', $this->buildStatistics()),
            '#Filter-listFilter' => ' ',
        ];
    }

    protected function buildStatistics()
    {
        $now = Carbon::now();
        $currentYear = $now->year;

        // ============ 1. إحصائيات عامة حسب العملة ============
        $general = Db::table('finance_finance_invoices')
            ->selectRaw("
                currency,
                COUNT(*) as total_count,
                SUM(amount) as total_amount,
                SUM(CASE WHEN type = 'payment' THEN amount ELSE 0 END) as total_payment,
                SUM(CASE WHEN type = 'receipt' THEN amount ELSE 0 END) as total_receipt,
                SUM(CASE WHEN type = 'payment' THEN 1 ELSE 0 END) as payment_count,
                SUM(CASE WHEN type = 'receipt' THEN 1 ELSE 0 END) as receipt_count
            ")
            ->groupBy('currency')
            ->get()
            ->keyBy('currency');

        // استخراج كل عملة على حدة
        $dollar = $general->get('dollar', (object) [
            'total_count' => 0, 'total_amount' => 0,
            'total_payment' => 0, 'total_receipt' => 0,
            'payment_count' => 0, 'receipt_count' => 0,
        ]);

        $syrian = $general->get('syrian', (object) [
            'total_count' => 0, 'total_amount' => 0,
            'total_payment' => 0, 'total_receipt' => 0,
            'payment_count' => 0, 'receipt_count' => 0,
        ]);

        // ============ 2. إحصائيات هذا الشهر ============
        $monthly = Db::table('finance_finance_invoices')
            ->join('finance_finance_months', 'finance_finance_invoices.month_id', '=', 'finance_finance_months.id')
            ->where('finance_finance_months.id', $now->month)
            ->selectRaw("
                currency,
                SUM(CASE WHEN type = 'payment' THEN amount ELSE 0 END) as total_payment,
                SUM(CASE WHEN type = 'receipt' THEN amount ELSE 0 END) as total_receipt
            ")
            ->groupBy('currency')
            ->get()
            ->keyBy('currency');

        $monthDollar = $monthly->get('dollar', (object) ['total_payment' => 0, 'total_receipt' => 0]);
        $monthSyrian = $monthly->get('syrian', (object) ['total_payment' => 0, 'total_receipt' => 0]);

        // ============ 3. إحصائيات هذه السنة ============
        $yearly = Db::table('finance_finance_invoices')
            ->join('finance_finance_years', 'finance_finance_invoices.year_id', '=', 'finance_finance_years.id')
            ->where('finance_finance_years.id', $currentYear)
            ->selectRaw("
                currency,
                SUM(CASE WHEN type = 'payment' THEN amount ELSE 0 END) as total_payment,
                SUM(CASE WHEN type = 'receipt' THEN amount ELSE 0 END) as total_receipt
            ")
            ->groupBy('currency')
            ->get()
            ->keyBy('currency');

        $yearDollar = $yearly->get('dollar', (object) ['total_payment' => 0, 'total_receipt' => 0]);
        $yearSyrian = $yearly->get('syrian', (object) ['total_payment' => 0, 'total_receipt' => 0]);

        // ============ 4. إحصائيات حسب model_type + العملة ============
        $modelTypeStats = Db::table('finance_finance_invoices')
            ->join('finance_finance_types', 'finance_finance_invoices.type_id', '=', 'finance_finance_types.id')
            ->selectRaw("
                finance_finance_types.id,
                finance_finance_types.name,
                finance_finance_types.type,
                finance_finance_invoices.currency,
                COUNT(*) as total_count,
                SUM(CASE WHEN finance_finance_invoices.type = 'payment' THEN finance_finance_invoices.amount ELSE 0 END) as total_payment,
                SUM(CASE WHEN finance_finance_invoices.type = 'receipt' THEN finance_finance_invoices.amount ELSE 0 END) as total_receipt
            ")
            ->groupBy(
                'finance_finance_types.id',
                'finance_finance_types.name',
                'finance_finance_types.type',
                'finance_finance_invoices.currency'
            )
            ->get();

        // تجميع حسب model_type مع فصل العملات
        $modelTypeGrouped = [];
        foreach ($modelTypeStats as $stat) {
            $id = $stat->id;
            if (!isset($modelTypeGrouped[$id])) {
                $modelTypeGrouped[$id] = [
                    'id'             => $stat->id,
                    'name'           => $stat->name,
                    'type'           => $stat->type,
                    'dollar_payment' => 0,
                    'dollar_receipt' => 0,
                    'syrian_payment' => 0,
                    'syrian_receipt' => 0,
                    'dollar_count'   => 0,
                    'syrian_count'   => 0,
                ];
            }
            if ($stat->currency === 'dollar') {
                $modelTypeGrouped[$id]['dollar_payment'] = $stat->total_payment;
                $modelTypeGrouped[$id]['dollar_receipt'] = $stat->total_receipt;
                $modelTypeGrouped[$id]['dollar_count']   = $stat->total_count;
            } elseif ($stat->currency === 'syrian') {
                $modelTypeGrouped[$id]['syrian_payment'] = $stat->total_payment;
                $modelTypeGrouped[$id]['syrian_receipt'] = $stat->total_receipt;
                $modelTypeGrouped[$id]['syrian_count']   = $stat->total_count;
            }
        }
        $modelTypeGrouped = array_values($modelTypeGrouped);

        // ============ 5. التوزيع الشهري (لكل عملة) ============
        $monthlyStats = Db::table('finance_finance_invoices')
            ->join('finance_finance_months', 'finance_finance_invoices.month_id', '=', 'finance_finance_months.id')
            ->selectRaw("
                finance_finance_months.id as month_id,
                finance_finance_months.name as month_name,
                finance_finance_invoices.currency,
                SUM(CASE WHEN finance_finance_invoices.type = 'payment' THEN finance_finance_invoices.amount ELSE 0 END) as payment,
                SUM(CASE WHEN finance_finance_invoices.type = 'receipt' THEN finance_finance_invoices.amount ELSE 0 END) as receipt
            ")
            ->groupBy(
                'finance_finance_months.id',
                'finance_finance_months.name',
                'finance_finance_invoices.currency'
            )
            ->orderBy('finance_finance_months.id')
            ->get();

        // ترتيب شهري لكل عملة
        $months     = $monthlyStats->pluck('month_name', 'month_id')->unique()->values()->toArray();
        $monthIds   = $monthlyStats->pluck('month_id')->unique()->sort()->values()->toArray();

        $dollarMonthlyPayments = [];
        $dollarMonthlyReceipts = [];
        $syrianMonthlyPayments = [];
        $syrianMonthlyReceipts = [];

        foreach ($monthIds as $mid) {
            $dRow = $monthlyStats->firstWhere(fn($r) => $r->month_id == $mid && $r->currency === 'dollar');
            $sRow = $monthlyStats->firstWhere(fn($r) => $r->month_id == $mid && $r->currency === 'syrian');

            $dollarMonthlyPayments[] = $dRow ? (float) $dRow->payment : 0;
            $dollarMonthlyReceipts[] = $dRow ? (float) $dRow->receipt : 0;
            $syrianMonthlyPayments[] = $sRow ? (float) $sRow->payment : 0;
            $syrianMonthlyReceipts[] = $sRow ? (float) $sRow->receipt : 0;
        }

        // ============ 6. أعلى المعاملات ============
        $topDollar = Invoice::where('currency', 'dollar')->orderBy('amount', 'desc')->take(5)->get();
        $topSyrian = Invoice::where('currency', 'syrian')->orderBy('amount', 'desc')->take(5)->get();

        // ============ 7. آخر المعاملات ============
        $recentInvoices = Invoice::orderBy('created_at', 'desc')->take(10)->get();

        return [
            // عام
            'dollar'        => $dollar,
            'syrian'        => $syrian,
            'totalAmount'   => $dollar->total_amount + $syrian->total_amount,
            'totalPayments' => $dollar->total_payment + $syrian->total_payment,
            'totalReceipts' => $dollar->total_receipt + $syrian->total_receipt,
            'paymentCount'  => $dollar->payment_count + $syrian->payment_count,
            'receiptCount'  => $dollar->receipt_count + $syrian->receipt_count,
            'balance'       => ($dollar->total_receipt + $syrian->total_receipt)
                             - ($dollar->total_payment + $syrian->total_payment),

            // شهري
            'monthDollar'   => $monthDollar,
            'monthSyrian'   => $monthSyrian,

            // سنوي
            'yearDollar'    => $yearDollar,
            'yearSyrian'    => $yearSyrian,

            // model_type
            'modelTypeStats' => $modelTypeGrouped,

            // chart
            'months'                 => $months,
            'dollarMonthlyPayments'  => $dollarMonthlyPayments,
            'dollarMonthlyReceipts'  => $dollarMonthlyReceipts,
            'syrianMonthlyPayments'  => $syrianMonthlyPayments,
            'syrianMonthlyReceipts'  => $syrianMonthlyReceipts,

            // top
            'topDollar'      => $topDollar,
            'topSyrian'      => $topSyrian,
            'recentInvoices' => $recentInvoices,

            'currentYear'    => $currentYear,
        ];
    }

public function reports(){
    $this->pageTitle = trans('finance.finance::lang.plugin.print_tables');
    $this->vars['yearsOptions'] = Year::get();
}



    public function onGetMonths()
    {
        $yearId = post('year_id');
        $months = Month::where('year_id', $yearId)->get();

        return [
            '#monthSelect' => $this->makePartial('monthoptions', ['months' => $months]),
        ];
    }
public function onFilterReports()
{
    $year_id = post('year_id');
    $month_id = post('month_id');
    $currency = post('currency');

    if(empty($year_id) || empty($month_id) || empty($currency)){
        Flash::error('تحديد السنة والشهر والعملة مطلوبين');
        return;
    }

    $query = Invoice::with(['model_type', 'month', 'year']);
    $invoices = $query->where('year_id', $year_id)
                      ->where('month_id', $month_id)
                      ->where('currency', $currency)
                      ->get();

    $this->vars['invoices'] = $invoices;

    // ====== حساب الإحصائيات ======
    $statistics = [
        'total_invoices' => $invoices->count(),
        'total_payments' => 0,
        'total_receipts' => 0,
        'balance' => 0,
        'model_types' => []
    ];

    // تجميع الإحصائيات حسب model_type
    $groupedByModelType = [];

    foreach($invoices as $invoice) {
        $typeName = $invoice->model_type->name ?? 'غير محدد';
        $typeId = $invoice->model_type->id ?? 0;
        $type = $invoice->type; // 'payment' or 'receipt'
        $amount = (float) $invoice->amount;

        // تجميع المبالغ حسب النوع العام
        if ($type == 'payment') {
            $statistics['total_payments'] += $amount;
        } else if ($type == 'receipt') {
            $statistics['total_receipts'] += $amount;
        }

        // تجميع المبالغ حسب model_type
        $key = $typeId . '_' . $typeName;
        if (!isset($groupedByModelType[$key])) {
            $groupedByModelType[$key] = [
                'id' => $typeId,
                'name' => $typeName,
                'type' => $type,
                'count' => 0,
                'total' => 0
            ];
        }
        $groupedByModelType[$key]['count']++;
        $groupedByModelType[$key]['total'] += $amount;
    }

    // ترتيب النتائج
    $statistics['model_types'] = array_values($groupedByModelType);
    
    // ترتيب حسب النوع (الدفع أولاً ثم القبض)
    usort($statistics['model_types'], function($a, $b) {
        if ($a['type'] == $b['type']) {
            return $a['name'] <=> $b['name'];
        }
        return $a['type'] == 'payment' ? -1 : 1;
    });

    // حساب الرصيد = المقبوضات - المدفوعات
    $statistics['balance'] = $statistics['total_receipts'] - $statistics['total_payments'];

    $this->vars['statistics'] = $statistics;
    // ====== نهاية حساب الإحصائيات ======

    return [
        '#body_table' => $this->makePartial('table', ['invoices' => $invoices , 'currency' => $currency == 'dollar' ? '$' : 'ل.س']),
        '#statistics-container' => $this->makePartial('statistics', ['statistics' => $statistics , 'currency' => $currency == 'dollar' ? '$' : 'ل.س']),
    ];
}


}
