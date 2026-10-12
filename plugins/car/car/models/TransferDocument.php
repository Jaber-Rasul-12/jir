<?php namespace Car\Car\Models;

use Model;
use Jacob\Logbook\Traits\LogChanges;
use Ramsey\Uuid\Guid\Guid;

class TransferDocument extends Model
{
    use \Winter\Storm\Database\Traits\Validation;
    use \Winter\Storm\Database\Traits\Nullable;


      use LogChanges;
      protected $nullable = ['old_owner_id_issuer' , 'old_owner_address' , 'old_owner_phone' , 'new_owner_id_issuer' , 'new_owner_address' , 'new_owner_phone' , 'car_license_plate' , 'car_chassis_number' , 'car_engine_number' , 'car_brand' , 'car_model' , 'car_color' , 'transfer_place' , 'is_full_transfer' , 'transfer_percentage' , 'notes' ];

  public $logBookModelName = 'car.car::lang.plugin.transfer_documents';
  public static function changeLogBookDisplayColumn($column)
  {
    return 'car.car::lang.model.transferdocument.' . $column;
  }

    public $table = 'car_car_transfer_documents';

    public $rules = [
        'document_number' => 'required|unique:car_car_transfer_documents,document_number',
        'car_id'          => 'required|exists:car_car_cars,id',
        'old_owner_name'  => 'required',
        'new_owner_name'  => 'required',
        'transfer_date'   => 'required|date',
    ];

    public $belongsTo = [
        'car' => ['Car\Car\Models\Car', 'key' => 'car_id'],
    ];
    
    /**
     * @var array Attribute names to encode and decode using JSON.
     */
    public $jsonable = [];

    // نسخ بيانات السيارة تلقائياً
    public function beforeSave()
    {
        if ($this->car) {
            $this->car_license_plate  = $this->car->license_plate_number;
            $this->car_chassis_number = $this->car->chassis_number;
            $this->car_brand          = optional($this->car->brand)->name;
            $this->car_model          = optional($this->car->model)->name;
            $this->car_color          = $this->car->color;
        }
    }

    public function filterFields($fields, $context = null)
{
    
        // التحقق من وجود القيم وعدم كونها فارغة
        if (isset($fields->car->value) && !empty($fields->car->value) ) {
          $car = Car::find($fields->car->value);
            
            // جلب القيم وتحويلها إلى أرقام عشرية (float)
            $fields->car_license_plate->value = $car->license_plate_number ? $car->license_plate_number : 'لا يوجد';
             $fields->car_chassis_number->value = $car->chassis_number ? $car->chassis_number : 'لا يوجد';
             $fields->car_engine_number->value = $car->engine_number ? $car->engine_number : 'لا يوجد';
             $fields->car_brand->value = $car->brand->name ? $car->brand->name : 'لا يوجد';
             $fields->car_model->value = $car->model->name ? $car->model->name : 'لا يوجد';
             $fields->car_color->value = $car->color ? $car->color : 'لا يوجد';

        }else{
          
            $fields->car_license_plate->value = 'لا يوجد';
             $fields->car_chassis_number->value = 'لا يوجد';
             $fields->car_engine_number->value = 'لا يوجد';
             $fields->car_brand->value = 'لا يوجد';
             $fields->car_model->value = 'لا يوجد';
             $fields->car_color->value = 'لا يوجد';
        }
}

      public function beforeValidate()
  {
    if (empty($this->document_number)) {
      $this->document_number = Guid::uuid4()->toString();
    }
  }
public function getTransferDocumentHtml()
{
    $doc = $this;

    // دالة مساعدة لتنسيق التواريخ
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

    // بيانات المالك القديم (المتنازل)
    $oldOwnerName     = $doc->old_owner_name ?? '...........................';
    $oldOwnerId       = $doc->old_owner_id_number ?? '...........................';
    $oldOwnerIdIssuer = $doc->old_owner_id_issuer ?? '...........................';
    $oldOwnerAddress  = $doc->old_owner_address ?? '...........................';
    $oldOwnerPhone    = $doc->old_owner_phone ?? '...........................';

    // بيانات المالك الجديد (المتنازل إليه)
    $newOwnerName     = $doc->new_owner_name ?? '...........................';
    $newOwnerId       = $doc->new_owner_id_number ?? '...........................';
    $newOwnerIdIssuer = $doc->new_owner_id_issuer ?? '...........................';
    $newOwnerAddress  = $doc->new_owner_address ?? '...........................';
    $newOwnerPhone    = $doc->new_owner_phone ?? '...........................';

    // بيانات السيارة
    $carLicensePlate = $doc->car_license_plate ?? '...........................';
    $carChassis      = $doc->car_chassis_number ?? '...........................';
    $carEngine       = $doc->car_engine_number ?? '...........................';
    $carBrand        = $doc->car_brand ?? '...........................';
    $carModel        = $doc->car_model ?? '...........................';
    $carColor        = $doc->car_color ?? '...........................';

    // بيانات الوثيقة
    $documentNumber = $doc->document_number ?? '...........................';
    $transferDate   = $formatDate($doc->transfer_date);
    $transferPlace  = $doc->transfer_place ?? '...........................';
    $isFullTransfer = $doc->is_full_transfer ? 'كامل' : 'جزئي';
    $transferPct    = $doc->transfer_percentage ?? '...........................';

    // ترجمة الحالة
    $status     = $doc->status ?? 'pending';
    $statusText = trans('car.car::lang.model.transferdocument.status_' . $status);

    // حالة الوثائق
    $docsCheck = function ($value) {
        return $value ? '✅ موجودة' : '❌ غير موجودة';
    };

    $today    = now()->format('Y-m-d');
    $imageUrl = e(\Backend\Models\BrandSetting::getFavicon());

    // بناء HTML
    $html = <<<HTML
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Carlito:ital,wght@0,400;0,700;1,400;1,700&display=swap" rel="stylesheet">

<style>
    .contract-wrapper {
        direction: rtl;
        font-family: 'Calibri', 'Carlito', 'Segoe UI', 'Tahoma', sans-serif;
        font-size: 16px;
        line-height: 1.85;
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
        font-family: 'Calibri', 'Carlito', 'Segoe UI', 'Tahoma', sans-serif;
        font-weight: 700;
        letter-spacing: -0.3px;
    }

    .contract-wrapper strong {
        font-family: 'Calibri', 'Carlito', 'Segoe UI', 'Tahoma', sans-serif;
        font-weight: 700;
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
        font-family: 'Calibri', 'Carlito', 'Segoe UI', sans-serif;
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
        font-family: 'Calibri', 'Carlito', sans-serif;
        line-height: 1.6;
    }

    .status-badge {
        display: inline-block;
        padding: 3px 10px;
        border-radius: 12px;
        font-size: 12px;
        font-family: 'Calibri', 'Carlito', sans-serif;
        font-weight: 700;
        background: #ecf0f1;
        color: #2c3e50;
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

    .party-box.old-owner {
        border-right: 6px solid #e67e22;
    }

    .party-box.new-owner {
        border-right: 6px solid #27ae60;
    }

    .party-box h3 {
        margin: 0 0 8px 0;
        font-size: 18px;
    }

    .party-box.old-owner h3 { color: #e67e22; }
    .party-box.new-owner h3 { color: #27ae60; }

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

    .car-data-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 8px 30px;
        background: #fcfcfc;
        padding: 15px;
        border-radius: 6px;
    }

    .docs-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 8px 20px;
        background: #fcfcfc;
        padding: 15px;
        border-radius: 6px;
    }

    .notes-box {
        background: #fff8e1;
        padding: 12px 20px;
        border-radius: 6px;
        border-right: 4px solid #f39c12;
        line-height: 1.8;
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

    .signature-box.old-owner h4 { color: #e67e22; }
    .signature-box.new-owner h4 { color: #27ae60; }

    .signature-box p {
        margin: 6px 0;
    }

    .contract-footer {
        text-align: left;
        margin-top: 20px;
        color: #7f8c8d;
        font-family: 'Calibri', 'Carlito', sans-serif;
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
        .article {
            page-break-inside: avoid;
            break-inside: avoid;
        }
        .party-box {
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
         style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; object-fit: cover; opacity: 0.05; z-index: 0; pointer-events: none;" alt="خلفية الوثيقة">
    <div class="contract-inner">

        <!-- رأس الوثيقة -->
        <div class="contract-header" style="background: #000000db; color: white!important; border-radius: 10px; padding: 15px;">
            <div class="contract-title" style="color: white!important;">وثيقة تنازل عن سيارة</div>
            <div class="contract-header-meta" style="color: white!important;">
                رقم الوثيقة: {$documentNumber}<br>
                رقم الهيكل: {$carChassis}<br>
                رقم اللوحة: {$carLicensePlate}<br>
                التاريخ: {$transferDate}<br>
                الحالة: <span class="status-badge">{$statusText}</span>
            </div>
        </div>

        <!-- مقدمة -->
        <div class="section-intro">
            <h3>مقدمة</h3>
            <p style="line-height: 1.9; text-align: justify; margin: 0;">
                أنا الموقع أدناه (المالك القديم / المتنازل)، أقر بكامل الأهلية المعتبرة شرعاً وقانوناً،
                بأنني قد تنازلت عن ملكية السيارة المبينة أوصافها أدناه إلى السيد (المالك الجديد / المتنازل إليه)،
                وقد تم هذا التنازل بموجب هذا السند الرسمي وفقاً لأحكام القانون السوري النافذ،
                وبعد أن تم تسليم السيارة بكامل ملحقاتها ووثائقها، وأصبح المتنازل إليه هو المالك الشرعي الوحيد للسيارة.
            </p>
        </div>

        <!-- المالك القديم (المتنازل) -->
        <div class="party-box old-owner">
            <h3>المالك القديم (المتنازل)</h3>
            <div class="party-grid">
                <span><strong>الاسم الرباعي:</strong> {$oldOwnerName}</span>
                <span><strong>رقم الهوية:</strong> {$oldOwnerId}</span>
                <span><strong>جهة الإصدار:</strong> {$oldOwnerIdIssuer}</span>
                <span><strong>العنوان:</strong> {$oldOwnerAddress}</span>
                <span><strong>الهاتف:</strong> {$oldOwnerPhone}</span>
            </div>
        </div>

        <!-- المالك الجديد (المتنازل إليه) -->
        <div class="party-box new-owner">
            <h3>المالك الجديد (المتنازل إليه)</h3>
            <div class="party-grid">
                <span><strong>الاسم الرباعي:</strong> {$newOwnerName}</span>
                <span><strong>رقم الهوية:</strong> {$newOwnerId}</span>
                <span><strong>جهة الإصدار:</strong> {$newOwnerIdIssuer}</span>
                <span><strong>العنوان:</strong> {$newOwnerAddress}</span>
                <span><strong>الهاتف:</strong> {$newOwnerPhone}</span>
            </div>
        </div>

        <!-- المادة 1: وصف السيارة -->
        <div class="article">
            <h3>المادة 1: وصف السيارة المتنازل عنها</h3>
            <div class="car-data-grid">
                <span><strong>رقم اللوحة:</strong> {$carLicensePlate}</span>
                <span><strong>الماركة:</strong> {$carBrand}</span>
                <span><strong>الطراز:</strong> {$carModel}</span>
                <span><strong>اللون:</strong> {$carColor}</span>
                <span><strong>رقم الهيكل:</strong> {$carChassis}</span>
                <span><strong>رقم المحرك:</strong> {$carEngine}</span>
            </div>
        </div>

        <!-- المادة 2: تفاصيل التنازل -->
        <div class="article">
            <h3>المادة 2: تفاصيل التنازل</h3>
            <div class="article-body">
                <p><strong>نوع التنازل:</strong> {$isFullTransfer}</p>
                <p><strong>نسبة التنازل:</strong> {$transferPct} %</p>
                <p><strong>تاريخ التنازل:</strong> {$transferDate}</p>
                <p><strong>مكان التنازل:</strong> {$transferPlace}</p>
            </div>
        </div>

        <!-- المادة 3: إقرار المتنازل -->
        <div class="article">
            <h3>المادة 3: إقرار المتنازل</h3>
            <div class="article-body">
                <p>أقر أنا الموقع أدناه (المتنازل) بأنني قد قبضت كامل الثمن المتفق عليه عن التنازل،
                وأنني لم يعد لي أي حق أو دعوى أو مطالبة تجاه السيارة المذكورة أو تجاه المتنازل إليه،
                وأسقطت كل حق ودعوى وادعاء يتعلق بها، وأصبحت السيارة بملك المتنازل إليه وحده،
                وأتحمل أنا كامل المسؤولية عن أي مخالفات أو التزامات سابقة لتاريخ هذا التنازل.</p>
                <p>كما أتعهد بتقديم كل ما يلزم من وثائق ومستندات لتسهيل عملية نقل الملكية لدى مديرية النقل المختصة.</p>
            </div>
        </div>

        <!-- المادة 4: إقرار المتنازل إليه -->
        <div class="article">
            <h3>المادة 4: إقرار المتنازل إليه</h3>
            <div class="article-body">
                <p>أقر أنا الموقع أدناه (المتنازل إليه) بأنني تسلمت السيارة المذكورة بكامل ملحقاتها ووثائقها،
                وقبلت التنازل بحالتها الراهنة، وتحمّلت مسؤولية جميع ما يترتب عليها من مخالفات أو التزامات
                اعتباراً من تاريخ هذا التنازل، وأصبحت السيارة ملكاً خالصاً لي.</p>
            </div>
        </div>

        <!-- المادة 5: الوثائق المرفقة -->
        <div class="article">
            <h3>المادة 5: الوثائق المرفقة</h3>
            <div class="docs-grid">
                <span><strong>صورة الهوية الشخصية:</strong> {$docsCheck($doc->has_identity_copy)}</span>
                <span><strong>براءة ذمة مرور:</strong> {$docsCheck($doc->has_traffic_clearance)}</span>
                <span><strong>وثيقة التأمين الإلزامي:</strong> {$docsCheck($doc->has_insurance)}</span>
                <span><strong>محضر الفحص الفني:</strong> {$docsCheck($doc->has_inspection)}</span>
                <span><strong>رخصة السير القديمة:</strong> {$docsCheck($doc->has_old_registration)}</span>
            </div>
        </div>

        <!-- المادة 6: أحكام عامة -->
        <div class="article">
            <h3>المادة 6: أحكام عامة</h3>
            <div class="article-body">
                <ol>
                    <li>يعتبر هذا التنازل قطعياً نهائياً لا يحق للمتنازل الرجوع عنه لأي سبب من الأسباب.</li>
                    <li>يتحمل المتنازل إليه جميع مصاريف ورسوم نقل الملكية لدى مديرية النقل المختصة.</li>
                    <li>حررت هذه الوثيقة من نسختين أصليتين، بيد كل من الطرفين نسخة واحدة للعمل بموجبها.</li>
                    <li>أي تعديل أو إضافة على هذه الوثيقة لا تكون نافذة إلا إذا كانت خطية وموقعة من الطرفين.</li>
                    <li>كل نزاع ينشأ بخصوص تفسير أو تنفيذ هذه الوثيقة يكون الفصل فيه من اختصاص محاكم الجمهورية العربية السورية.</li>
                </ol>
            </div>
        </div>
HTML;

    // إضافة الملاحظات إن وُجدت
    if (!empty($doc->notes)) {
        $html .= <<<HTML
        <div class="article">
            <h3>ملاحظات</h3>
            <div class="notes-box">
                {$doc->notes}
            </div>
        </div>
HTML;
    }

    // التوقيعات
    $html .= <<<HTML
        <div class="signatures">
            <div class="signatures-row">
                <div class="signature-box old-owner">
                    <h4>المالك القديم (المتنازل)</h4>
                    <p><strong>الاسم:</strong> {$oldOwnerName}</p>
                    <p><strong>التوقيع:</strong> ........................</p>
                    <p><strong>البصمة:</strong> ........................</p>
                </div>
                <div class="signature-box new-owner">
                    <h4>المالك الجديد (المتنازل إليه)</h4>
                    <p><strong>الاسم:</strong> {$newOwnerName}</p>
                    <p><strong>التوقيع:</strong> ........................</p>
                    <p><strong>البصمة:</strong> ........................</p>
                </div>
            </div>
            <p class="contract-footer">
                <strong>حرر في:</strong> {$transferPlace} &nbsp;&nbsp; <strong>بتاريخ:</strong> {$today}
            </p>
        </div>

    </div>
</div>
HTML;

    return $html;
}
}