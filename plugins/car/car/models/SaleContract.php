<?php namespace Car\Car\Models;

use Model;

use Jacob\Logbook\Traits\LogChanges;
use Ramsey\Uuid\Guid\Guid;

class SaleContract extends Model
{
    use \Winter\Storm\Database\Traits\Validation;
    use \Winter\Storm\Database\Traits\Nullable;


      use LogChanges;
      protected $nullable = ['contract_place' , 'seller_id_issuer' , 'seller_address' , 'seller_phone' , 'buyer_id_issuer' , 'buyer_address' , 'buyer_phone' , 'payment_terms' , 'general_terms' ];

  public $logBookModelName = 'car.car::lang.plugin.sale_contracts';
  public static function changeLogBookDisplayColumn($column)
  {
    return 'car.car::lang.model.salecontract.' . $column;
  }

    public $table = 'car_car_sale_contracts';

    public $rules = [
        'contract_number' => 'required|unique:car_car_sale_contracts,contract_number',
        'contract_date'   => 'required|date',
        'seller_name'     => 'required',
        'buyer_name'      => 'required',
    ];

    // علاقة متعدد لمتعدد مع السيارات (عبر جدول Pivot)
    public $belongsToMany = [
        'cars' => [
            'Car\Car\Models\Car',
            'table'    => 'car_car_cars_sale_contracts',
            'key'      => 'sale_contract_id',
            'otherKey' => 'car_id',
        ],
    ];
    
    /**
     * @var array Attribute names to encode and decode using JSON.
     */
    public $jsonable = [];

    // احتساب المبلغ المتبقي تلقائياً
    public function beforeSave()
    {
        $this->remaining_amount = $this->total_price - $this->paid_amount;
    }

      public function beforeValidate()
  {
    if (empty($this->contract_number)) {
      $this->contract_number = Guid::uuid4()->toString();
    }
  }

 public function getSaleContractHtml()
{
    $contract = $this;

    // جلب جميع السيارات المرتبطة بالعقد
    $cars = $contract->cars;

    // دالة مساعدة لتنسيق التواريخ بأمان
    $formatDate = function ($dateValue, $format = 'Y-m-d') {
        if (empty($dateValue)) {
            return '...........................';
        }
        try {
            return \Carbon\Carbon::parse($dateValue)->format($format);
        } catch (\Exception $e) {
            return '...........................';
        }
    };

    // تعبئة بيانات البائع
    $sellerName     = $contract->seller_name ?? '...........................';
    $sellerId       = $contract->seller_id_number ?? '...........................';
    $sellerIdIssuer = $contract->seller_id_issuer ?? '...........................';
    $sellerAddress  = $contract->seller_address ?? '...........................';
    $sellerPhone    = $contract->seller_phone ?? '...........................';

    // تعبئة بيانات المشتري
    $buyerName      = $contract->buyer_name ?? '...........................';
    $buyerId        = $contract->buyer_id_number ?? '...........................';
    $buyerIdIssuer  = $contract->buyer_id_issuer ?? '...........................';
    $buyerAddress   = $contract->buyer_address ?? '...........................';
    $buyerPhone     = $contract->buyer_phone ?? '...........................';

    // بيانات الثمن
    $totalPrice      = number_format($contract->total_price ?? 0, 0, '.', ',');
    $paidAmount      = number_format($contract->paid_amount ?? 0, 0, '.', ',');
    $remainingAmount = number_format($contract->remaining_amount ?? 0, 0, '.', ',');

    // بيانات إضافية
    $contractNumber = $contract->contract_number ?? '...........................';
    $contractDate   = $formatDate($contract->contract_date);
    $contractPlace  = $contract->contract_place ?? '...........................';
    $today          = now()->format('Y-m-d');
    $imageUrl       = e(\Backend\Models\BrandSetting::getFavicon());

    // ===== بناء بطاقات السيارات ديناميكياً =====
    $carsCount = $cars->count();
    $carsHtml  = '';

    if ($carsCount > 0) {
        foreach ($cars as $index => $car) {
            $carNumber       = $index + 1;
            $carLicensePlate = $car->license_plate_number ?? '...........................';
            $carChassis      = $car->chassis_number ?? '...........................';
            $carEngine       = $car->engine_number ?? '...........................';
            $carBrand        = $car->brand ? $car->brand->name : '...........................';
            $carModel        = $car->model ? $car->model->name : '...........................';
            $carColor        = $car->color ?? '...........................';
            $carYear         = $formatDate($car->year_of_manufacturing_date, 'Y');
            $carType         = $car->type ?? '...........................';
            $carFuel         = $car->fuel_type
                ? trans('car.car::lang.model.car.' . $car->fuel_type)
                : '...........................';

            $carsHtml .= <<<CAR
            <div class="car-card">
                <div class="car-card-header">
                    <h4 class="car-card-title">
                        <span class="car-number">{$carNumber}</span>
                        السيارة رقم {$carNumber}
                    </h4>
                    <span class="car-card-brand">الماركة: <strong>{$carBrand}</strong></span>
                </div>
                <div class="car-card-body">
                    <span><strong>رقم اللوحة:</strong> {$carLicensePlate}</span>
                    <span><strong>الطراز:</strong> {$carModel}</span>
                    <span><strong>الفئة:</strong> {$carType}</span>
                    <span><strong>اللون:</strong> {$carColor}</span>
                    <span><strong>سنة الصنع:</strong> {$carYear}</span>
                    <span><strong>نوع الوقود:</strong> {$carFuel}</span>
                    <span class="full-width"><strong>رقم الهيكل:</strong> {$carChassis}</span>
                    <span class="full-width"><strong>رقم المحرك:</strong> {$carEngine}</span>
                </div>
            </div>
CAR;
        }
    } else {
        $carsHtml = '<div class="empty-cars">لم يتم ربط أي سيارة بهذا العقد بعد.</div>';
    }

    // نص وصفي لعدد السيارات
    $carsCountText = $carsCount == 1
        ? 'السيارة المبينة أوصافها'
        : "السيارات ({$carsCount}) المبينة أوصافها";

    // بناء HTML
    $html = <<<HTML
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Amiri:ital,wght@0,400;0,700;1,400;1,700&family=Cairo:wght@300;400;600;700;900&display=swap" rel="stylesheet">

<style>
    .contract-wrapper {
        direction: rtl;
        font-family: 'Amiri', 'Traditional Arabic', 'Times New Roman', serif;
        font-size: 16px;
        line-height: 1.95;
        max-width: 1100px;
        margin: 0 auto;
        background: #fafafa;
        padding: 30px;
        border-radius: 12px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.15);
        position: relative;
        overflow: hidden;
        color: #1a1a1a;
    }

    .contract-wrapper h1,
    .contract-wrapper h2,
    .contract-wrapper h3,
    .contract-wrapper h4 {
        font-family: 'Cairo', 'Tahoma', sans-serif;
        font-weight: 700;
        letter-spacing: -0.3px;
    }

    .contract-wrapper strong {
        font-family: 'Cairo', 'Tahoma', sans-serif;
        font-weight: 600;
        color: #2c3e50;
    }

    .contract-inner {
        background: #fff;
        padding: 40px 50px;
        border-radius: 10px;
        box-shadow: 0 4px 8px rgba(0,0,0,0.05);
        position: relative;
        z-index: 1;
    }

    .contract-title {
        font-family: 'Amiri', serif;
        font-weight: 700;
        font-size: 28px;
        color: #2c3e50;
        letter-spacing: 0.5px;
    }

    .contract-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-bottom: 3px double #2c3e50;
        padding-bottom: 15px;
        margin-bottom: 25px;
    }

    .contract-header-meta {
        text-align: left;
        font-size: 14px;
        color: #7f8c8d;
        font-family: 'Cairo', sans-serif;
        line-height: 1.6;
    }

    .section-intro {
        margin: 25px 0;
        padding: 15px;
        background: #fdfaf0;
        border-radius: 8px;
        border: 1px solid #f1c40f;
    }

    .section-intro h3 {
        color: #8e44ad;
        margin-top: 0;
    }

    .party-box {
        margin-bottom: 20px;
        background: #f0f4f8;
        padding: 15px;
        border-radius: 8px;
    }

    .party-box.seller {
        border-right: 6px solid #2980b9;
    }

    .party-box.buyer {
        border-right: 6px solid #27ae60;
    }

    .party-box h3 {
        margin: 0 0 8px 0;
        font-size: 18px;
    }

    .party-box.seller h3 { color: #2980b9; }
    .party-box.buyer h3 { color: #27ae60; }

    .party-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 5px 20px;
    }

    .article {
        margin-bottom: 25px;
    }

    .article h3 {
        color: #2c3e50;
        border-bottom: 2px solid #3498db;
        padding-bottom: 5px;
        font-size: 19px;
    }

    .article-body {
        background: #f9f9f9;
        padding: 12px 20px;
        border-radius: 6px;
        line-height: 1.9;
    }

    .article-body p {
        margin: 8px 0;
    }

    .article-body ol,
    .article-body ul {
        padding-right: 20px;
        margin: 0;
    }

    .article-body li {
        margin-bottom: 6px;
    }

    .car-card {
        background: #ffffff;
        border: 1px solid #dfe6ec;
        border-radius: 8px;
        padding: 18px 22px;
        margin-bottom: 15px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.03);
    }

    .car-card-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 12px;
        border-bottom: 1px dashed #bdc3c7;
        padding-bottom: 8px;
    }

    .car-card-title {
        margin: 0;
        color: #2c3e50;
        font-size: 16px;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .car-number {
        display: inline-block;
        background: #3498db;
        color: #fff;
        width: 28px;
        height: 28px;
        line-height: 28px;
        text-align: center;
        border-radius: 50%;
        font-size: 14px;
    }

    .car-card-brand {
        font-size: 13px;
        color: #7f8c8d;
        font-family: 'Cairo', sans-serif;
    }

    .car-card-body {
        display: grid;
        grid-template-columns: 1fr 1fr 1fr;
        gap: 8px 20px;
        font-size: 15px;
    }

    .full-width {
        grid-column: span 3;
    }

    .empty-cars {
        background: #fdf2f2;
        border: 1px solid #f5c6cb;
        color: #721c24;
        padding: 15px;
        border-radius: 6px;
        text-align: center;
    }

    .price-highlight {
        color: #c0392b;
        font-weight: 700;
        font-family: 'Cairo', sans-serif;
    }

    .signatures {
        border-top: 2px solid #2c3e50;
        padding-top: 25px;
        margin-top: 10px;
    }

    .signatures-row {
        display: flex;
        flex-wrap: wrap;
        justify-content: space-between;
    }

    .signature-box {
        width: 45%;
    }

    .signature-box h4 {
        margin: 0 0 10px 0;
        font-size: 16px;
    }

    .signature-box.seller h4 { color: #2980b9; }
    .signature-box.buyer h4 { color: #27ae60; }

    .signature-box p {
        margin: 6px 0;
    }

    .contract-footer {
        text-align: left;
        margin-top: 20px;
        color: #7f8c8d;
        font-family: 'Cairo', sans-serif;
        font-size: 14px;
    }

    @media print {
        .contract-wrapper {
            box-shadow: none !important;
            padding: 0 !important;
            background: #fff !important;
            font-size: 14px;
            line-height: 1.8;
        }
        .contract-inner {
            box-shadow: none !important;
            padding: 20px !important;
        }
        .contract-title { font-size: 24px !important; }
        .article h3 { font-size: 16px; }
        .car-card {
            page-break-inside: avoid;
            break-inside: avoid;
        }
        .article {
            page-break-inside: avoid;
            break-inside: avoid;
        }
        .contract-background-image {
            display: block !important;
            position: fixed !important;
            top: 0 !important;
            left: 0 !important;
            width: 100% !important;
            height: 100% !important;
            object-fit: cover !important;
            opacity: 0.08 !important;
            z-index: -1 !important;
            pointer-events: none !important;
        }
        @page {
            margin: 15mm !important;
        }
        * {
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }
    }
</style>

<div class="contract-wrapper">
    <img src="{$imageUrl}" class="contract-background-image"
         style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; object-fit: cover; opacity: 0.05; z-index: 0; pointer-events: none;" alt="خلفية العقد">
    <div class="contract-inner">

        <!-- رأس الوثيقة -->
        <div class="contract-header">
            <div class="contract-title">عقد بيع سيارات قطعي</div>
            <div class="contract-header-meta">
                رقم العقد: {$contractNumber}<br>
                التاريخ: {$contractDate}<br>
                عدد السيارات: {$carsCount}
            </div>
        </div>

        <!-- مقدمة -->
        <div class="section-intro">
            <h3>مقدمة</h3>
            <p style="line-height: 1.9; text-align: justify; margin: 0;">
                لما كان الفريق الأول (البائع) يملك ويتصرف {$carsCountText} في هذا العقد،
                ولما كان الفريق الثاني (المشتري) يرغب بشرائها،
                فقد اتفق الفريقان وهما بكامل الأهلية المعتبرة شرعاً وقانوناً على ما يلي:
            </p>
        </div>

        <!-- الفريق الأول: البائع -->
        <div class="party-box seller">
            <h3>الفريق الأول (البائع)</h3>
            <div class="party-grid">
                <span><strong>الاسم الرباعي:</strong> {$sellerName}</span>
                <span><strong>رقم الهوية:</strong> {$sellerId}</span>
                <span><strong>جهة الإصدار:</strong> {$sellerIdIssuer}</span>
                <span><strong>العنوان:</strong> {$sellerAddress}</span>
                <span><strong>الهاتف:</strong> {$sellerPhone}</span>
            </div>
        </div>

        <!-- الفريق الثاني: المشتري -->
        <div class="party-box buyer">
            <h3>الفريق الثاني (المشتري)</h3>
            <div class="party-grid">
                <span><strong>الاسم الرباعي:</strong> {$buyerName}</span>
                <span><strong>رقم الهوية:</strong> {$buyerId}</span>
                <span><strong>جهة الإصدار:</strong> {$buyerIdIssuer}</span>
                <span><strong>العنوان:</strong> {$buyerAddress}</span>
                <span><strong>الهاتف:</strong> {$buyerPhone}</span>
            </div>
        </div>

        <!-- المادة 1 -->
        <div class="article">
            <h3>المادة 1: موضوع البيع ووصف السيارات</h3>
            <p style="background: #fcfcfc; padding: 12px 20px; border-radius: 6px; margin-bottom: 12px; line-height: 1.8;">
                باع الفريق الأول (البائع) إلى الفريق الثاني (المشتري) {$carsCountText} التالية:
            </p>
            {$carsHtml}
        </div>

        <!-- المادة 2 -->
        <div class="article">
            <h3>المادة 2: ضمان البائع</h3>
            <div class="article-body">
                <p>يضمن الفريق الأول (البائع) أن {$carsCountText} مملوكة له ملكية خالصة، وأنه يضع اليد عليها بصفة ظاهرة وهادئة ومستمرة، ولا ينازعه أحد في ملكيتها، وأنها خالية من أي حقوق أو ديون أو رهون أو حجوزات أو قيود تحول دون التصرف فيها، ولم تقم بشأنها أي دعوى، وهي خالية من الضرائب والرسوم كافة.</p>
            </div>
        </div>

        <!-- المادة 3 -->
        <div class="article">
            <h3>المادة 3: المعاينة والقبول</h3>
            <div class="article-body">
                <p>يقر الفريق الثاني (المشتري) بأنه عاين {$carsCountText} المعاينة التامة النافية للجهالة شرعاً وقانوناً، وأنه قبل شراءها بحالتها الراهنة بعد اختبارها، وأسقط حقه في الرجوع على الفريق الأول بأي عيب أو نقص، ما عدا العيوب الخفية التي لا يمكن اكتشافها بالمعاينة العادية.</p>
            </div>
        </div>

        <!-- المادة 4 -->
        <div class="article">
            <h3>المادة 4: الثمن وطريقة الدفع</h3>
            <div class="article-body">
                <p>تم هذا البيع وقبل بثمن إجمالي قدره <span class="price-highlight">{$totalPrice} دولار</span> عن كامل السيارات المذكورة.</p>
                <p>دفع الفريق الثاني (المشتري) للفريق الأول (البائع) مبلغاً وقدره <span class="price-highlight">{$paidAmount} دولار</span> عند التوقيع على هذا العقد.</p>
                <p>يتعهد الفريق الثاني بدفع المبلغ المتبقي وقدره <span class="price-highlight">{$remainingAmount} دولار</span> عند نقل ملكية السيارات من اسم الفريق الأول إلى اسم الفريق الثاني لدى مديريات النقل المختصة.</p>
            </div>
        </div>

        <!-- المادة 5 -->
        <div class="article">
            <h3>المادة 5: قطعية البيع</h3>
            <div class="article-body">
                <p>يعتبر هذا البيع قطعياً نهائياً لا يحق لأي من الفريقين الرجوع فيه لأي سبب من الأسباب، ويعتبر التوقيع على هذا العقد ملزماً للطرفين بمجرد إتمامه.</p>
            </div>
        </div>

        <!-- المادة 6 -->
        <div class="article">
            <h3>المادة 6: تسليم السيارات</h3>
            <div class="article-body">
                <p>يقر الفريق الثاني (المشتري) بأنه تسلم {$carsCountText} تسليماً فعلياً على سبيل الأمانة لحين انتهاء نقل ملكيتها، وأنه أصبح وحده المسؤول عنها وعن جميع الحوادث والمخالفات التي تحدث بسببها اعتباراً من لحظة تحرير هذا العقد، أما تلك السابقة على البيع فيسأل عنها الفريق الأول (البائع).</p>
            </div>
        </div>

        <!-- المادة 7 -->
        <div class="article">
            <h3>المادة 7: النكول</h3>
            <div class="article-body">
                <p>من ينكل من الفريقين عن تنفيذ أي بند من بنود هذا العقد يلزم بأداء مبلغ قدره .................... دولار إلى الفريق الآخر، دون حاجة إلى حكم قضائي أو إنذار، وذلك بمثابة تعويض عن ذلك النكول، مع ثبوت حق الفريق المتضرر غير الناكل في تثبيت العقد أو فسخه حسب رغبته.</p>
            </div>
        </div>

        <!-- المادة 8 -->
        <div class="article">
            <h3>المادة 8: المصروفات والرسوم</h3>
            <div class="article-body">
                <p>يتحمل الفريق الثاني (المشتري) وحده جميع مصروفات ورسوم هذا العقد ونقل ترخيص السيارات المبيعة ووثائق تأمينها، ويتولى أيضاً إتمام الإجراءات اللازمة لذلك دون أدنى مسؤولية أو رجوع على الفريق الأول.</p>
            </div>
        </div>

        <!-- التوقيعات -->
        <div class="signatures">
            <div class="signatures-row">
                <div class="signature-box seller">
                    <h4>الفريق الأول (البائع)</h4>
                    <p><strong>الاسم:</strong> {$sellerName}</p>
                    <p><strong>التوقيع:</strong> ........................</p>
                    <p><strong>البصمة:</strong> ........................</p>
                </div>
                <div class="signature-box buyer">
                    <h4>الفريق الثاني (المشتري)</h4>
                    <p><strong>الاسم:</strong> {$buyerName}</p>
                    <p><strong>التوقيع:</strong> ........................</p>
                    <p><strong>البصمة:</strong> ........................</p>
                </div>
            </div>
            <p class="contract-footer">
                <strong>حرر في:</strong> {$contractPlace} &nbsp;&nbsp; <strong>بتاريخ:</strong> {$today}
            </p>
        </div>

    </div>
</div>
HTML;

    return $html;
}
}