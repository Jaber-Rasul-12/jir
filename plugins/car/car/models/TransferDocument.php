<?php namespace Car\Car\Models;

use Model;
use Jacob\Logbook\Traits\LogChanges;
use Ramsey\Uuid\Guid\Guid;

class TransferDocument extends Model
{
    use \Winter\Storm\Database\Traits\Validation;
    use \Winter\Storm\Database\Traits\Nullable;


      use LogChanges;
      protected $nullable = ['old_owner_id_issuer' ,'new_owner_name' , 'new_owner_id_number', 'old_owner_address' , 'old_owner_phone' , 'new_owner_id_issuer' , 'new_owner_address' , 'new_owner_phone' , 'car_license_plate' , 'car_chassis_number' , 'car_engine_number' , 'car_brand' , 'car_model' , 'car_color' , 'transfer_place' , 'is_full_transfer' , 'transfer_percentage' , 'notes' ];

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
        'transfer_date'   => 'required|date',
    ];

    public $belongsTo = [
        'car' => ['Car\Car\Models\Car', 'key' => 'car_id'],
    ];
    
    /**
     * @var array Attribute names to encode and decode using JSON.
     */
    public $jsonable = [];

    // نسخ بيانات ألية تلقائياً
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

    // بيانات ألية
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

<div class="contract-wrapper" style="direction: rtl; font-family: 'Calibri', 'Carlito', 'Segoe UI', 'Tahoma', 'Arial', sans-serif; max-width: 1100px; margin: 0 auto; background: #fafafa; padding: 5px; border-radius: 12px; box-shadow: 0 10px 30px rgba(0,0,0,0.15); position: relative; overflow: hidden;">
    
<img src="{$imageUrl}" class="contract-background-image"
         style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; object-fit: cover; opacity: 0.05; z-index: 0; pointer-events: none;" alt="خلفية الوثيقة">
    <div style="background: #fff; padding: 5px 5px; border-radius: 10px; box-shadow: 0 4px 8px rgba(0,0,0,0.05); position: relative; z-index: 1;">

        <div id="header_print" style="display: flex; justify-content: space-between; align-items: center; border-bottom: 2px double #2c3e50; padding-bottom: 5px; margin-bottom: 3px; background: white; color: black!important; border-radius: 8px; pad">
            <div id="header_print_new" style="font-size: 22px; font-weight: bold; color: black!important;">بوابتكم إلى عالم التجارة والاستثمار</div>
            <div> <img src="{$imageUrl}" width="100" height="100" alt=""></div>
            <div id="header_print_new" style="text-align: left; font-size: 14px; color: black!important;">
             <h4 style="border: 1px solid #48979c; padding: 2px; border-radius: 8px; text-align: center;">وثيقة تنازل عن الية</h4>  
              
              رقم الوثيقة: {$documentNumber}<br> رقم الهيكل: {$carChassis}<br> رقم اللوحة: {$carLicensePlate}<br> التاريخ: {$transferDate}<br>  
            </div>
        </div>

        <div style="margin: 3px 0; padding: 3px; background: #fdfaf0; border-radius: 8px; border: 1px solid #f1c40f;">
            <h4 style="color: #8e44ad; margin-top: 0;">مقدمة</h4>
            <p style="line-height: 1.9; text-align: justify; margin: 0;">
                أنا الموقع أدناه (المالك القديم / المتنازل)، أقر بكامل الأهلية المعتبرة شرعاً وقانوناً،
                بأنني قد تنازلت عن ملكية ألية المبينة أوصافها أدناه إلى السيد (المالك الجديد / المتنازل إليه)،
                وقد تم هذا التنازل بموجب هذا السند الرسمي وفقاً لأحكام القانون السوري النافذ،
                وبعد أن تم تسليم ألية بكامل ملحقاتها ووثائقها، وأصبح المتنازل إليه هو المالك الشرعي الوحيد للسيارة.
            </p>
        </div>

        <div style="margin-bottom: 3px; background: #f0f4f8; padding: 5px; border-radius: 8px; border-right: 6px solid #e67e22;">
            <h3 style="margin: 0 0 8px 0; color: #e67e22; font-size: 18px;">المالك القديم (المتنازل)</h3>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 5px 20px;">
                <span><strong>الاسم الرباعي:</strong> {$oldOwnerName}</span>
                <span><strong>رقم الهوية:</strong> {$oldOwnerId}</span>
                <span><strong>جهة الإصدار:</strong> {$oldOwnerIdIssuer}</span>
                <span><strong>العنوان:</strong> {$oldOwnerAddress}</span>
                <span><strong>الهاتف:</strong> {$oldOwnerPhone}</span>
            </div>
        </div>

        <div style="margin-bottom: 3px; background: #f0f4f8; padding: 5px; border-radius: 8px; border-right: 6px solid #27ae60;">
            <h3 style="margin: 0 0 8px 0; color: #27ae60; font-size: 18px;">المالك الجديد (المتنازل إليه)</h3>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 5px 20px;">
                <span><strong>الاسم الرباعي:</strong> {$newOwnerName}</span>
                <span><strong>رقم الهوية:</strong> {$newOwnerId}</span>
                <span><strong>جهة الإصدار:</strong> {$newOwnerIdIssuer}</span>
                <span><strong>العنوان:</strong> {$newOwnerAddress}</span>
                <span><strong>الهاتف:</strong> {$newOwnerPhone}</span>
            </div>
        </div>

        <div style="margin-bottom: 2px;">
            <h3 style="color: #2c3e50; border-bottom: 2px solid #3498db; padding-bottom: 3px;">المادة 1: وصف ألية المتنازل عنها</h3>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px 30px; background: #fcfcfc; padding: 5px; border-radius: 6px;">
                <span><strong>رقم اللوحة:</strong> {$carLicensePlate}</span>
                <span><strong>الماركة:</strong> {$carBrand}</span>
                <span><strong>الطراز:</strong> {$carModel}</span>
                <span><strong>اللون:</strong> {$carColor}</span>
                <span><strong>رقم الهيكل:</strong> {$carChassis}</span>
                <span><strong>رقم المحرك:</strong> {$carEngine}</span>
            </div>
        </div>



        <div style="margin-bottom: 2px;">
            <h3 style="color: #2c3e50; border-bottom: 2px solid #3498db; padding-bottom: 5px;">المادة 2: الوثائق المرفقة</h3>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px 20px; background: #fcfcfc; padding: 15px; border-radius: 6px;">
                <span><strong>صورة الهوية الشخصية:</strong> {$docsCheck($doc->has_identity_copy)}</span>
                <span><strong>براءة ذمة مرور:</strong> {$docsCheck($doc->has_traffic_clearance)}</span>
                <span><strong>وثيقة التأمين الإلزامي:</strong> {$docsCheck($doc->has_insurance)}</span>
                <span><strong>محضر الفحص الفني:</strong> {$docsCheck($doc->has_inspection)}</span>
                <span><strong>رخصة السير القديمة:</strong> {$docsCheck($doc->has_old_registration)}</span>
            </div>
        </div>

        <div style="margin-bottom: 2px;">
            <h3 style="color: #2c3e50; border-bottom: 2px solid #3498db; padding-bottom: 5px;">المادة 3: أحكام عامة</h3>
            <div style="background: #f9f9f9; padding: 12px 20px; border-radius: 6px; line-height: 1.8;">
                <ol style="padding-right: 20px; margin: 0;">
                    <li>يعتبر هذا التنازل قطعياً نهائياً لا يحق للمتنازل الرجوع عنه لأي سبب من الأسباب.</li>
                    <li>يتحمل المتنازل إليه جميع مصاريف ورسوم نقل الملكية لدى مديرية النقل المختصة.</li>
                    <li>حررت هذه الوثيقة من نسختين أصليتين، بيد كل من الطرفين نسخة واحدة للعمل بموجبها.</li>
                    <li>أي تعديل أو إضافة على هذه الوثيقة لا تكون نافذة إلا إذا كانت خطية وموقعة من الطرفين.</li>
                   
                </ol>
            </div>
        </div>
HTML;

    // إضافة الملاحظات إن وُجدت
    if (!empty($doc->notes)) {
        $html .= <<<HTML
        <div style="margin-bottom: 2px;">
            <h3 style="color: #2c3e50; border-bottom: 2px solid #3498db; padding-bottom: 5px;">ملاحظات</h3>
            <div style="background: #fff8e1; padding: 12px 20px; border-radius: 6px; border-right: 4px solid #f39c12; line-height: 1.8;">
                {$doc->notes}
            </div>
        </div>
HTML;
    }

    // التوقيعات
    $html .= <<<HTML
        <div style="border-top: 2px solid #2c3e50; padding-top: 5px; margin-top: 2px;">
            <div style="display: flex; flex-wrap: wrap; justify-content: space-between;">
                <div style="width: 45%;">
                    <h4 style="margin: 0 0 10px 0; color: #e67e22;">المالك القديم (المتنازل)</h4>
                    <p><strong>الاسم:</strong> {$oldOwnerName}</p>
                    <p><strong>التوقيع:</strong> ........................</p>
                    <p><strong>البصمة:</strong> ........................</p>
                </div>
                <div style="width: 45%;">
                    <h4 style="margin: 0 0 10px 0; color: #27ae60;">المالك الجديد (المتنازل إليه)</h4>
                    <p><strong>الاسم:</strong> {$newOwnerName}</p>
                    <p><strong>التوقيع:</strong> ........................</p>
                    <p><strong>البصمة:</strong> ........................</p>
                </div>
            </div>
            <p style="text-align: left; margin-top: 5px; color: #7f8c8d;">
                <strong>حرر في:</strong> {$transferPlace} &nbsp;&nbsp; <strong>بتاريخ:</strong> {$today}
            </p>
        </div>

    </div>
</div>
HTML;

    return $html;
}
}