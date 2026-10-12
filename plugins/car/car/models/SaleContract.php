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

    $carsHtml = '';

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
            <div style="background: #ffffff; border: 1px solid #dfe6ec; border-radius: 8px; padding: 18px 22px; margin-bottom: 15px; box-shadow: 0 2px 4px rgba(0,0,0,0.03);">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px; border-bottom: 1px dashed #bdc3c7; padding-bottom: 8px;">
                    <h4 style="margin: 0; color: #2c3e50; font-size: 16px;">
                        <span style="display: inline-block; background: #3498db; color: #fff; width: 28px; height: 28px; line-height: 28px; text-align: center; border-radius: 50%; font-size: 14px; margin-left: 8px;">{$carNumber}</span>
                        السيارة رقم {$carNumber}
                    </h4>
                    <span style="font-size: 13px; color: #7f8c8d;">الماركة: <strong style="color: #2c3e50;">{$carBrand}</strong></span>
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 8px 20px; font-size: 14px;">
                    <span><strong>رقم اللوحة:</strong> {$carLicensePlate}</span>
                    <span><strong>الطراز:</strong> {$carModel}</span>
                    <span><strong>الفئة:</strong> {$carType}</span>
                    <span><strong>اللون:</strong> {$carColor}</span>
                    <span><strong>سنة الصنع:</strong> {$carYear}</span>
                    <span><strong>نوع الوقود:</strong> {$carFuel}</span>
                    <span style="grid-column: span 3;"><strong>رقم الهيكل:</strong> {$carChassis}</span>
                    <span style="grid-column: span 3;"><strong>رقم المحرك:</strong> {$carEngine}</span>
                </div>
            </div>
CAR;
        }
    } else {
        $carsHtml = '<div style="background: #fdf2f2; border: 1px solid #f5c6cb; color: #721c24; padding: 15px; border-radius: 6px; text-align: center;">لم يتم ربط أي سيارة بهذا العقد بعد.</div>';
    }

    // نص وصفي لعدد السيارات
    $carsCountText = $carsCount == 1
        ? 'السيارة المبينة أوصافها'
        : "السيارات ({$carsCount}) المبينة أوصافها";

    // بناء HTML
    $html = <<<HTML
<div class="contract-wrapper" style="direction: rtl; font-family: 'Tahoma', 'Arial', sans-serif; max-width: 1100px; margin: 0 auto; background: #fafafa; padding: 30px; border-radius: 12px; box-shadow: 0 10px 30px rgba(0,0,0,0.15); position: relative; overflow: hidden;">
    <img src="{$imageUrl}" class="contract-background-image"
         style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; object-fit: cover; opacity: 0.05; z-index: 0; pointer-events: none;" alt="خلفية العقد">
    <div style="background: #fff; padding: 40px 50px; border-radius: 10px; box-shadow: 0 4px 8px rgba(0,0,0,0.05); position: relative; z-index: 1;">

        <!-- رأس الوثيقة -->
        <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 3px double #2c3e50; padding-bottom: 15px; margin-bottom: 25px;">
            <div style="font-size: 22px; font-weight: bold; color: #2c3e50;">عقد بيع سيارات قطعي</div>
            <div style="text-align: left; font-size: 14px; color: #7f8c8d;">
                رقم العقد: {$contractNumber}<br>
                التاريخ: {$contractDate}<br>
                عدد السيارات: {$carsCount}
            </div>
        </div>

        <!-- مقدمة -->
        <div style="margin: 25px 0; padding: 15px; background: #fdfaf0; border-radius: 8px; border: 1px solid #f1c40f;">
            <h3 style="color: #8e44ad; margin-top: 0;">مقدمة</h3>
            <p style="line-height: 1.9; text-align: justify; margin: 0;">
                لما كان الفريق الأول (البائع) يملك ويتصرف {$carsCountText} في هذا العقد،
                ولما كان الفريق الثاني (المشتري) يرغب بشرائها،
                فقد اتفق الفريقان وهما بكامل الأهلية المعتبرة شرعاً وقانوناً على ما يلي:
            </p>
        </div>

        <!-- الفريق الأول: البائع -->
        <div style="margin-bottom: 20px; background: #f0f4f8; padding: 15px; border-radius: 8px; border-right: 6px solid #2980b9;">
            <h3 style="margin: 0 0 8px 0; color: #2980b9; font-size: 18px;">الفريق الأول (البائع)</h3>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 5px 20px;">
                <span><strong>الاسم الرباعي:</strong> {$sellerName}</span>
                <span><strong>رقم الهوية:</strong> {$sellerId}</span>
                <span><strong>جهة الإصدار:</strong> {$sellerIdIssuer}</span>
                <span><strong>العنوان:</strong> {$sellerAddress}</span>
                <span><strong>الهاتف:</strong> {$sellerPhone}</span>
            </div>
        </div>

        <!-- الفريق الثاني: المشتري -->
        <div style="margin-bottom: 20px; background: #f0f4f8; padding: 15px; border-radius: 8px; border-right: 6px solid #27ae60;">
            <h3 style="margin: 0 0 8px 0; color: #27ae60; font-size: 18px;">الفريق الثاني (المشتري)</h3>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 5px 20px;">
                <span><strong>الاسم الرباعي:</strong> {$buyerName}</span>
                <span><strong>رقم الهوية:</strong> {$buyerId}</span>
                <span><strong>جهة الإصدار:</strong> {$buyerIdIssuer}</span>
                <span><strong>العنوان:</strong> {$buyerAddress}</span>
                <span><strong>الهاتف:</strong> {$buyerPhone}</span>
            </div>
        </div>

        <!-- المادة 1: موضوع البيع ووصف السيارات -->
        <div style="margin-bottom: 25px;">
            <h3 style="color: #2c3e50; border-bottom: 2px solid #3498db; padding-bottom: 5px;">
                المادة 1: موضوع البيع ووصف السيارات
            </h3>
            <p style="background: #fcfcfc; padding: 12px 20px; border-radius: 6px; margin-bottom: 12px; line-height: 1.8;">
                باع الفريق الأول (البائع) إلى الفريق الثاني (المشتري) {$carsCountText} التالية:
            </p>
            {$carsHtml}
        </div>

        <!-- المادة 2: الضمان -->
        <div style="margin-bottom: 25px;">
            <h3 style="color: #2c3e50; border-bottom: 2px solid #3498db; padding-bottom: 5px;">المادة 2: ضمان البائع</h3>
            <div style="background: #f9f9f9; padding: 12px 20px; border-radius: 6px; line-height: 1.8;">
                <p>يضمن الفريق الأول (البائع) أن {$carsCountText} مملوكة له ملكية خالصة، وأنه يضع اليد عليها بصفة ظاهرة وهادئة ومستمرة، ولا ينازعه أحد في ملكيتها، وأنها خالية من أي حقوق أو ديون أو رهون أو حجوزات أو قيود تحول دون التصرف فيها، ولم تقم بشأنها أي دعوى، وهي خالية من الضرائب والرسوم كافة.</p>
            </div>
        </div>

        <!-- المادة 3: المعاينة -->
        <div style="margin-bottom: 25px;">
            <h3 style="color: #2c3e50; border-bottom: 2px solid #3498db; padding-bottom: 5px;">المادة 3: المعاينة والقبول</h3>
            <div style="background: #f9f9f9; padding: 12px 20px; border-radius: 6px; line-height: 1.8;">
                <p>يقر الفريق الثاني (المشتري) بأنه عاين {$carsCountText} المعاينة التامة النافية للجهالة شرعاً وقانوناً، وأنه قبل شراءها بحالتها الراهنة بعد اختبارها، وأسقط حقه في الرجوع على الفريق الأول بأي عيب أو نقص، ما عدا العيوب الخفية التي لا يمكن اكتشافها بالمعاينة العادية.</p>
            </div>
        </div>

        <!-- المادة 4: الثمن -->
        <div style="margin-bottom: 25px;">
            <h3 style="color: #2c3e50; border-bottom: 2px solid #3498db; padding-bottom: 5px;">المادة 4: الثمن وطريقة الدفع</h3>
            <div style="background: #f9f9f9; padding: 12px 20px; border-radius: 6px; line-height: 1.8;">
                <p>تم هذا البيع وقبل بثمن إجمالي قدره <strong style="color: #c0392b;">{$totalPrice} ليرة سورية</strong> عن كامل السيارات المذكورة.</p>
                <p>دفع الفريق الثاني (المشتري) للفريق الأول (البائع) مبلغاً وقدره <strong style="color: #c0392b;">{$paidAmount} ليرة سورية</strong> عند التوقيع على هذا العقد.</p>
                <p>يتعهد الفريق الثاني بدفع المبلغ المتبقي وقدره <strong style="color: #c0392b;">{$remainingAmount} ليرة سورية</strong> عند نقل ملكية السيارات من اسم الفريق الأول إلى اسم الفريق الثاني لدى مديريات النقل المختصة.</p>
            </div>
        </div>

        <!-- المادة 5: قطعية البيع -->
        <div style="margin-bottom: 25px;">
            <h3 style="color: #2c3e50; border-bottom: 2px solid #3498db; padding-bottom: 5px;">المادة 5: قطعية البيع</h3>
            <div style="background: #f9f9f9; padding: 12px 20px; border-radius: 6px; line-height: 1.8;">
                <p>يعتبر هذا البيع قطعياً نهائياً لا يحق لأي من الفريقين الرجوع فيه لأي سبب من الأسباب، ويعتبر التوقيع على هذا العقد ملزماً للطرفين بمجرد إتمامه.</p>
            </div>
        </div>

        <!-- المادة 6: تسليم السيارات -->
        <div style="margin-bottom: 25px;">
            <h3 style="color: #2c3e50; border-bottom: 2px solid #3498db; padding-bottom: 5px;">المادة 6: تسليم السيارات</h3>
            <div style="background: #f9f9f9; padding: 12px 20px; border-radius: 6px; line-height: 1.8;">
                <p>يقر الفريق الثاني (المشتري) بأنه تسلم {$carsCountText} تسليماً فعلياً على سبيل الأمانة لحين انتهاء نقل ملكيتها، وأنه أصبح وحده المسؤول عنها وعن جميع الحوادث والمخالفات التي تحدث بسببها اعتباراً من لحظة تحرير هذا العقد، أما تلك السابقة على البيع فيسأل عنها الفريق الأول (البائع).</p>
            </div>
        </div>

        <!-- المادة 7: النكول -->
        <div style="margin-bottom: 25px;">
            <h3 style="color: #2c3e50; border-bottom: 2px solid #3498db; padding-bottom: 5px;">المادة 7: النكول</h3>
            <div style="background: #f9f9f9; padding: 12px 20px; border-radius: 6px; line-height: 1.8;">
                <p>من ينكل من الفريقين عن تنفيذ أي بند من بنود هذا العقد يلزم بأداء مبلغ قدره .................... ليرة سورية إلى الفريق الآخر، دون حاجة إلى حكم قضائي أو إنذار، وذلك بمثابة تعويض عن ذلك النكول، مع ثبوت حق الفريق المتضرر غير الناكل في تثبيت العقد أو فسخه حسب رغبته.</p>
            </div>
        </div>

        <!-- المادة 8: المصروفات والرسوم -->
        <div style="margin-bottom: 25px;">
            <h3 style="color: #2c3e50; border-bottom: 2px solid #3498db; padding-bottom: 5px;">المادة 8: المصروفات والرسوم</h3>
            <div style="background: #f9f9f9; padding: 12px 20px; border-radius: 6px; line-height: 1.8;">
                <p>يتحمل الفريق الثاني (المشتري) وحده جميع مصروفات ورسوم هذا العقد ونقل ترخيص السيارات المبيعة ووثائق تأمينها، ويتولى أيضاً إتمام الإجراءات اللازمة لذلك دون أدنى مسؤولية أو رجوع على الفريق الأول.</p>
            </div>
        </div>

        <!-- التوقيعات -->
        <div style="border-top: 2px solid #2c3e50; padding-top: 25px; margin-top: 10px;">
            <div style="display: flex; flex-wrap: wrap; justify-content: space-between;">
                <div style="width: 45%;">
                    <h4 style="margin: 0 0 10px 0; color: #2980b9;">الفريق الأول (البائع)</h4>
                    <p><strong>الاسم:</strong> {$sellerName}</p>
                    <p><strong>التوقيع:</strong> ........................</p>
                    <p><strong>البصمة:</strong> ........................</p>
                </div>
                <div style="width: 45%;">
                    <h4 style="margin: 0 0 10px 0; color: #27ae60;">الفريق الثاني (المشتري)</h4>
                    <p><strong>الاسم:</strong> {$buyerName}</p>
                    <p><strong>التوقيع:</strong> ........................</p>
                    <p><strong>البصمة:</strong> ........................</p>
                </div>
            </div>
            <p style="text-align: left; margin-top: 20px; color: #7f8c8d;">
                <strong>حرر في:</strong> {$contractPlace} &nbsp;&nbsp; <strong>بتاريخ:</strong> {$today}
            </p>
        </div>

    </div>
</div>
HTML;

    return $html;
}
}