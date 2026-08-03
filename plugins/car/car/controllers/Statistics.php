<?php namespace Car\Car\Controllers;

use Backend\Classes\Controller;
use BackendMenu;
use Car\Car\Models\Brand;
use Car\Car\Models\Car;
use Car\Car\Models\Customer;
use Car\Car\Models\Rent;
use Car\Car\Models\Country;
use Car\Car\Models\Modelnew;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class Statistics extends Controller
{
    public $implement = [];

    public function __construct()
    {
        parent::__construct();
        BackendMenu::setContext('Car.Car', 'main-menu-item', 'side-menu-statistics');
    }



    /**
     * الصفحة الرئيسية للإحصائيات
     */
    public function index()
    {
        $this->pageTitle = 'لوحة الإحصائيات الشاملة';

        // جلب جميع البيانات المطلوبة
        $stats = $this->collectStatistics();

        // تمرير البيانات إلى الـ View
        $this->vars['stats'] = $stats;
        $this->vars['chartData'] = $this->prepareChartData($stats);
    }

    /**
     * جمع جميع الإحصائيات في مصفوفة واحدة
     */
    protected function collectStatistics()
    {
        return [
            // الإحصائيات الأساسية
            'total_cars'             => Car::count(),
            'total_customers'        => Customer::count(),
            'total_rents'            => Rent::count(),

            // السيارات حسب الماركة
            'cars_by_brand'          => $this->getCarsByBrand(),

            // السيارات حسب الوقود
            'cars_by_fuel'           => $this->getCarsByFuel(),

            // السيارات حسب سنة الصنع (توزيع)
            'cars_by_year'           => $this->getCarsByYear(),

            // السيارات حسب بلد اللوحة (الجديد والقديم)
            'cars_by_country'        => $this->getCarsByCountry(),

            // السيارات حسب نوع الملكية
            'cars_by_ownership'      => $this->getCarsByOwnership(),

            // السيارات المؤجرة حالياً (نشطة)
            'active_rents'           => $this->getActiveRentsCount(),

            // الإيرادات الإجمالية
            'total_revenue'          => $this->getTotalRevenue(),

            // الإيرادات الشهرية (آخر 12 شهر)
            'revenue_by_month'       => $this->getRevenueByMonth(),

            // أكثر السيارات تأجيراً (top 5)
            'top_rented_cars'        => $this->getTopRentedCars(5),

            // أكثر العملاء تأجيراً (كمستأجرين) top 5
            'top_tenants'            => $this->getTopTenants(5),

            // توزيع أنواع رخص القيادة
            'license_types'          => $this->getLicenseTypes(),

            // متوسط مدة الإيجار (بالأيام)
            'avg_rent_duration'      => $this->getAverageRentDuration(),

            // عدد العقود حسب الشهر (آخر 12 شهر)
            'rents_by_month'         => $this->getRentsByMonth(),
        ];
    }

    /**
     * إعداد البيانات بصيغة مناسبة للرسوم البيانية (Chart.js)
     */
    protected function prepareChartData($stats)
    {
        // 1. الماركات
        $brandLabels = [];
        $brandCounts = [];
        foreach ($stats['cars_by_brand'] as $item) {
            $brandLabels[] = $item['brand_name'];
            $brandCounts[] = $item['count'];
        }

        // 2. أنواع الوقود
        $fuelLabels = [
            'petrol' => 'بنزين',
            'diesel' => 'ديزل',
            'electric' => 'كهربائي',
            'hybrid' => 'هايبرد',
            'plug-in_hybrid' => 'هايبرد قابل للشحن',
            'hydrogen' => 'هيدروجين',
            'lpg' => 'غاز LPG',
            'cng' => 'غاز CNG',
        ];
        $fuelCounts = [];
        $fuelLabelsDisplay = [];
        foreach ($fuelLabels as $key => $label) {
            $count = $stats['cars_by_fuel'][$key] ?? 0;
            if ($count > 0) {
                $fuelLabelsDisplay[] = $label;
                $fuelCounts[] = $count;
            }
        }

        // 3. السنوات
        $yearLabels = [];
        $yearCounts = [];
        foreach ($stats['cars_by_year'] as $item) {
            $yearLabels[] = $item['year'] ?? 'غير محدد';
            $yearCounts[] = $item['count'];
        }

        // 4. البلدان (اللوحات)
        $countryLabels = [];
        $countryCounts = [];
        foreach ($stats['cars_by_country'] as $item) {
            $countryLabels[] = $item['country_name'] ?? 'بدون بلد';
            $countryCounts[] = $item['count'];
        }

        // 5. الإيرادات الشهرية
        $revenueLabels = [];
        $revenueValues = [];
        foreach ($stats['revenue_by_month'] as $item) {
            $revenueLabels[] = $item['month'];
            $revenueValues[] = $item['revenue'];
        }

        // 6. العقود الشهرية
        $rentsMonthLabels = [];
        $rentsMonthValues = [];
        foreach ($stats['rents_by_month'] as $item) {
            $rentsMonthLabels[] = $item['month'];
            $rentsMonthValues[] = $item['count'];
        }

        return [
            'brand' => [
                'labels' => $brandLabels,
                'counts' => $brandCounts,
            ],
            'fuel' => [
                'labels' => $fuelLabelsDisplay,
                'counts' => $fuelCounts,
            ],
            'year' => [
                'labels' => $yearLabels,
                'counts' => $yearCounts,
            ],
            'country' => [
                'labels' => $countryLabels,
                'counts' => $countryCounts,
            ],
            'revenue' => [
                'labels' => $revenueLabels,
                'values' => $revenueValues,
            ],
            'rents_month' => [
                'labels' => $rentsMonthLabels,
                'values' => $rentsMonthValues,
            ],
        ];
    }

    // ============= دوال الإحصائيات =============

    protected function getCarsByBrand()
    {
        return Car::select('brand_id', DB::raw('count(*) as count'))
            ->with('brand')
            ->groupBy('brand_id')
            ->get()
            ->map(function ($item) {
                return [
                    'brand_name' => $item->brand ? $item->brand->name : 'غير معروف',
                    'count' => $item->count,
                ];
            })
            ->toArray();
    }

    protected function getCarsByFuel()
    {
        return Car::select('fuel_type', DB::raw('count(*) as count'))
            ->groupBy('fuel_type')
            ->pluck('count', 'fuel_type')
            ->toArray();
    }

protected function getCarsByYear()
{
    $driver = DB::connection()->getDriverName();
    if ($driver === 'pgsql') {
        $yearExpression = DB::raw('EXTRACT(YEAR FROM year_of_manufacturing_date) as year');
    } else {
        $yearExpression = DB::raw('YEAR(year_of_manufacturing_date) as year');
    }

    return Car::select($yearExpression, DB::raw('count(*) as count'))
        ->groupBy('year')
        ->orderBy('year', 'desc')
        ->get()
        ->map(function ($item) {
            return [
                'year' => $item->year,
                'count' => $item->count,
            ];
        })
        ->toArray();
}

   protected function getCarsByCountry()
{
    // الجلب للـ country_id
    $old = Car::select('country_id', DB::raw('count(*) as count'))
        ->whereNotNull('country_id')
        ->with('country')  // العلاقة الصحيحة هي 'country'
        ->groupBy('country_id')
        ->get()
        ->map(function ($item) {
            return [
                'country_name' => $item->country ? $item->country->name : 'غير معروف',
                'count' => $item->count,
            ];
        });

    // الجلب للـ country_new_id
    $new = Car::select('country_new_id', DB::raw('count(*) as count'))
        ->whereNotNull('country_new_id')
        ->with('country_new')  // التصحيح: استخدم 'country_new' بدلاً من 'countryNew'
        ->groupBy('country_new_id')
        ->get()
        ->map(function ($item) {
            return [
                'country_name' => $item->country_new ? $item->country_new->name : 'غير معروف',
                'count' => $item->count,
            ];
        });

    // دمج النتائج
    $merged = [];
    foreach ($old as $o) {
        $name = $o['country_name'];
        if (!isset($merged[$name])) {
            $merged[$name] = 0;
        }
        $merged[$name] += $o['count'];
    }
    foreach ($new as $n) {
        $name = $n['country_name'];
        if (!isset($merged[$name])) {
            $merged[$name] = 0;
        }
        $merged[$name] += $n['count'];
    }

    $result = [];
    foreach ($merged as $name => $count) {
        $result[] = ['country_name' => $name, 'count' => $count];
    }
    return $result;
}

    protected function getCarsByOwnership()
    {
        return Car::select('ownership', DB::raw('count(*) as count'))
            ->groupBy('ownership')
            ->pluck('count', 'ownership')
            ->toArray();
    }

    protected function getActiveRentsCount()
    {
        $now = Carbon::now();
        return Rent::where('start_date', '<=', $now)
            ->where('end_date', '>=', $now)
            ->count();
    }

    protected function getTotalRevenue()
    {
        // نفترض أن rent_allowance + additional_rent_amount هو المبلغ المستحق
        return Rent::sum(DB::raw('rent_allowance + additional_rent_amount'));
    }

    protected function getRevenueByMonth()
    {
        // آخر 12 شهراً
        $months = collect();
        for ($i = 11; $i >= 0; $i--) {
            $months->push(Carbon::now()->subMonths($i)->format('Y-m'));
        }

        $revenues = [];
        foreach ($months as $month) {
            $start = Carbon::parse($month)->startOfMonth();
            $end = Carbon::parse($month)->endOfMonth();
            $total = Rent::whereBetween('created_at', [$start, $end])
                ->sum(DB::raw('rent_allowance + additional_rent_amount'));
            $revenues[] = [
                'month' => Carbon::parse($month)->format('M Y'),
                'revenue' => (float) $total,
            ];
        }
        return $revenues;
    }

    protected function getRentsByMonth()
    {
        $months = collect();
        for ($i = 11; $i >= 0; $i--) {
            $months->push(Carbon::now()->subMonths($i)->format('Y-m'));
        }

        $counts = [];
        foreach ($months as $month) {
            $start = Carbon::parse($month)->startOfMonth();
            $end = Carbon::parse($month)->endOfMonth();
            $count = Rent::whereBetween('created_at', [$start, $end])->count();
            $counts[] = [
                'month' => Carbon::parse($month)->format('M Y'),
                'count' => $count,
            ];
        }
        return $counts;
    }

    protected function getTopRentedCars($limit = 5)
    {
        return Rent::select('car_id', DB::raw('count(*) as rent_count'))
            ->with('car')
            ->groupBy('car_id')
            ->orderBy('rent_count', 'desc')
            ->limit($limit)
            ->get()
            ->map(function ($item) {
                return [
                    'car_name' => $item->car ? $item->car->brand->name . ' ' . $item->car->model->name . ' (' . $item->car->license_plate_number . ')' : 'غير معروف',
                    'rent_count' => $item->rent_count,
                ];
            })
            ->toArray();
    }

protected function getTopTenants($limit = 5)
{
    return Rent::select('customer_tenant_id', DB::raw('count(*) as rent_count'))
        ->with('customer_tenant')  // التصحيح: customer_tenant
        ->groupBy('customer_tenant_id')
        ->orderBy('rent_count', 'desc')
        ->limit($limit)
        ->get()
        ->map(function ($item) {
            return [
                'customer_name' => $item->customer_tenant ? $item->customer_tenant->full_name : 'غير معروف',
                'rent_count' => $item->rent_count,
            ];
        })
        ->toArray();
}

    protected function getLicenseTypes()
    {
        $data = Customer::select('type_of_drivers_license', DB::raw('count(*) as count'))
            ->whereNotNull('type_of_drivers_license')
            ->groupBy('type_of_drivers_license')
            ->pluck('count', 'type_of_drivers_license')
            ->toArray();

        // ترجمة المفاتيح
        $trans = [
            'private' => 'خاصة',
            'public' => 'عامة',
        ];
        $result = [];
        foreach ($data as $key => $value) {
            $result[$trans[$key] ?? $key] = $value;
        }
        return $result;
    }

protected function getAverageRentDuration()
{
    $driver = DB::connection()->getDriverName();
    if ($driver === 'pgsql') {
        // في PostgreSQL، طرح تاريخين يعطي عدد الأيام كـ integer
        $avg = Rent::select(DB::raw("AVG(CAST(end_date - start_date AS DOUBLE PRECISION)) as avg_days"))
            ->value('avg_days');
    } else {
        $avg = Rent::select(DB::raw("AVG(DATEDIFF(end_date, start_date)) as avg_days"))
            ->value('avg_days');
    }
    return round($avg, 1) ?? 0;
}
}