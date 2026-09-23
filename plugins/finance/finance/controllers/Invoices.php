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

    /**
     * بناء كل الإحصائيات
     */
    protected function buildStatistics()
    {
        $now = Carbon::now();
        $currentYear = $now->year;
        $currentMonth = $now->month;

        // ===== 1. الإحصائيات العامة =====
        $all = Invoice::all();
        $totalAmount     = $all->sum('amount');
        $totalPayments   = $all->where('type', 'payment')->sum('amount');
        $totalReceipts   = $all->where('type', 'receipt')->sum('amount');
        $paymentCount    = $all->where('type', 'payment')->count();
        $receiptCount    = $all->where('type', 'receipt')->count();
        $balance         = $totalReceipts - $totalPayments;

        // ===== 2. إحصائيات هذا الشهر =====
        $monthInvoices = Invoice::whereHas('month', function ($q) use ($currentMonth) {
            $q->where('name', 'like', '%' . $currentMonth . '%');
        })->get();

        $monthPayments = $monthInvoices->where('type', 'payment')->sum('amount');
        $monthReceipts = $monthInvoices->where('type', 'receipt')->sum('amount');

        // ===== 3. إحصائيات هذه السنة =====
        $yearInvoices = Invoice::whereHas('year', function ($q) use ($currentYear) {
            $q->where('name', 'like', '%' . $currentYear . '%');
        })->get();

        $yearPayments = $yearInvoices->where('type', 'payment')->sum('amount');
        $yearReceipts = $yearInvoices->where('type', 'receipt')->sum('amount');

        // ===== 4. إحصائيات حسب model_type =====
        $byModelType = ModelType::with(['invoices' => function ($q) {
            // علاقة معكوسة - نحتاج لتعريفها
        }])->get();

        // إذا لم تكن العلاقة معرّفة، نستخدم استعلام مباشر
        $modelTypeStats = Db::table('finance_finance_invoices')
            ->join('finance_finance_types', 'finance_finance_invoices.type_id', '=', 'finance_finance_types.id')
            ->select(
                'finance_finance_types.id',
                'finance_finance_types.name',
                'finance_finance_types.type',
                Db::raw("SUM(CASE WHEN finance_finance_invoices.type = 'payment' THEN finance_finance_invoices.amount ELSE 0 END) as total_payment"),
                Db::raw("SUM(CASE WHEN finance_finance_invoices.type = 'receipt' THEN finance_finance_invoices.amount ELSE 0 END) as total_receipt"),
                Db::raw("COUNT(finance_finance_invoices.id) as total_count")
            )
            ->groupBy(
                'finance_finance_types.id',
                'finance_finance_types.name',
                'finance_finance_types.type'
            )
            ->get();

        // ===== 5. توزيع الشهري (12 شهرًا) =====
        $monthlyStats = Db::table('finance_finance_invoices')
            ->join('finance_finance_months', 'finance_finance_invoices.month_id', '=', 'finance_finance_months.id')
            ->select(
                'finance_finance_months.name as month_name',
                Db::raw("SUM(CASE WHEN finance_finance_invoices.type = 'payment' THEN finance_finance_invoices.amount ELSE 0 END) as payment"),
                Db::raw("SUM(CASE WHEN finance_finance_invoices.type = 'receipt' THEN finance_finance_invoices.amount ELSE 0 END) as receipt")
            )
            ->groupBy('finance_finance_months.id', 'finance_finance_months.name')
            ->orderBy('finance_finance_months.id')
            ->get();

        // ===== 6. أعلى 5 معاملات =====
        $topInvoices = Invoice::orderBy('amount', 'desc')->take(5)->get();

        // ===== 7. آخر المعاملات =====
        $recentInvoices = Invoice::orderBy('created_at', 'desc')->take(10)->get();

        return [
            'invoices'         => $all,
            'totalAmount'      => $totalAmount,
            'totalPayments'    => $totalPayments,
            'totalReceipts'    => $totalReceipts,
            'paymentCount'     => $paymentCount,
            'receiptCount'     => $receiptCount,
            'balance'          => $balance,
            'monthPayments'    => $monthPayments,
            'monthReceipts'    => $monthReceipts,
            'yearPayments'     => $yearPayments,
            'yearReceipts'     => $yearReceipts,
            'modelTypeStats'   => $modelTypeStats,
            'monthlyStats'     => $monthlyStats,
            'topInvoices'      => $topInvoices,
            'recentInvoices'   => $recentInvoices,
            'currentYear'      => $currentYear,
            'currentMonth'     => $currentMonth,
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
