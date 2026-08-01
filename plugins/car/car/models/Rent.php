<?php namespace Car\Car\Models;

use Model;
// use Winter\Storm\Database\Builder;
// use BackendAuth;
/**
 * Model
 */
use Jacob\Logbook\Traits\LogChanges;
class Rent extends Model
{
    use \Winter\Storm\Database\Traits\Validation;
    
  
    use LogChanges;

  public $logBookModelName = 'car.car::lang.plugin.rent';

  public static function changeLogBookDisplayColumn($column)
  {
    return 'car.car::lang.model.rent.' . $column;
  }
    /**
     * @var string The database table used by the model.
     */
    public $table = 'car_car_rents';

    /**
     * @var array Validation rules
     */
    public $rules = [
      // 'customer_owner_id' => 'required|integer|exists:car_car_customers,id',
      // 'customer_tenant_id' => 'required|integer|exists:car_car_customers,id',
      //   'car_id' => 'required|integer|exists:car_car_cars,id',
        // 'customer_bail_id' => 'required|integer|exists:car_car_customers,id',
        'start_date' => 'required|date|before:end_date',
        'end_date' => 'required|date|after:start_date',
        'watch_price' => 'required|numeric|min:0',
        'rent_allowance' => 'required|numeric|min:0',
        'additional_rent_amount' => 'required|numeric|min:0',
        'insurance_number' => 'required|string|max:255',
        'the_second_team_paid_for_any_damage' => 'required|numeric|min:0',
        'name_first_witness' => 'required|string|max:255',
        'name_second_witness' => 'required|string|max:255',
    ];


        public $belongsTo = [
        'car' => ['Car\Car\Models\Car', 'key' => 'car_id'],
        'customer_owner' => ['Car\Car\Models\Customer', 'key' => 'customer_owner_id'],
        'customer_tenant' => ['Car\Car\Models\Customer', 'key' => 'customer_tenant_id'],
        'customer_bail' => ['Car\Car\Models\Customer', 'key' => 'customer_bail_id'],
    ];

        public $attachMany = [
        'photos' => 'System\Models\File'
    ];

  
    
    /**
     * @var array Attribute names to encode and decode using JSON.
     */
    public $jsonable = [];



    public function getContractHtml()
{


    // جلب البيانات المرتبطة
    $owner = $this->customer_owner;
    $tenant = $this->customer_tenant;
    $car = $this->car;
    $bail = $this->customer_bail;

    // دالة مساعدة لمعالجة التواريخ بأمان
    $formatDate = function($dateValue, $format = 'Y-m-d H:i') {
        if (empty($dateValue)) {
            return '...........................';
        }
        try {
            if ($dateValue instanceof \DateTime) {
                return $dateValue->format($format);
            }
            return \Carbon\Carbon::parse($dateValue)->format($format);
        } catch (\Exception $e) {
            return '...........................';
        }
    };

    

    // تعبئة بيانات المؤجر
    $ownerName = $owner ? $owner->full_name : '...........................';
    $ownerId = $owner ? $owner->id_number : '...........................';
    $ownerAddress = $owner ? $owner->address : '...........................';
    $ownerPhone = $owner ? $owner->phone : '...........................';

    // تعبئة بيانات المستأجر
    $tenantName = $tenant ? $tenant->full_name : '...........................';
    $tenantId = $tenant ? $tenant->id_number : '...........................';
    $tenantAddress = $tenant ? $tenant->address : '...........................';
    $tenantPhone = $tenant ? $tenant->phone : '...........................';
    $tenantLicense = $tenant ? $tenant->driving_license : '...........................';
    $tenantLicenseType = $tenant ? ($tenant->type_of_drivers_license == 'private' ? 'خاصة' : 'عامة') : '...........................';
    $tenantLicenseDate = $tenant ? $formatDate($tenant->date_of_drivers_license, 'Y-m-d') : '...........................';
    $tenantLicenseIssuer = '...........................';
    $tenantLicenseExpiry = $tenant ? $formatDate($tenant->valid_for_the_end, 'Y-m-d') : '...........................';

    // تعبئة بيانات السيارة
    $carLicensePlate = $car ? $car->license_plate_number : '...........................';
    $carType = $car ? $car->type : '...........................';
    $carBrand = $car && $car->brand ? $car->brand->name : '...........................';
    $carModel = $car && $car->model ? $car->model->name : '...........................';
    $carYear = $car ? $formatDate($car->year_of_manufacturing_date, 'Y') : '...........................';
    $carChassis = $car ? $car->chassis_number : '...........................';
    $carEngine = '...........................';
    $carColor = '...........................';
    $carSeats = '...........................';
    $carHp = '...........................';
    $carFuel = $car ? trans('car.car::lang.model.car.' . $car->fuel_type) : '...........................';
    $carLicenseNumber = $car ? $car->license_plate_number : '...........................';
    $carLicenseDate = '...........................';
    $carLicenseIssuer = '...........................';
    $carLicenseExpiry = '...........................';

    // تواريخ العقد والمبالغ
    $startDate = $formatDate($this->start_date, 'Y-m-d H:i');
    $endDate = $formatDate($this->end_date, 'Y-m-d H:i');
    $rentDuration = $this->watch_price ?? 0 ;
    $rentPrice = $this->watch_price ?? 0;
    $rentAllowance = $this->rent_allowance ?? 0;
    $additionalRent = $this->additional_rent_amount ?? 0;
    $insuranceNumber = $this->insurance_number ?? '...........................';
    $damagePaid = $this->the_second_team_paid_for_any_damage ?? 0;
    $bailName = $bail ? $bail->full_name : '...........................';
    $bailId = $bail ? $bail->id_number : '...........................';
    $witness1 = $this->name_first_witness ?? '...........................';
    $witness2 = $this->name_second_witness ?? '...........................';
    $today = now()->format('Y-m-d');

     $imageUrl = e(\Backend\Models\BrandSetting::getFavicon());

    // بناء HTML للعقد مع تصميم أنيق
    $html = <<<HTML

    <div class="contract-wrapper" style="direction: rtl; font-family: 'Tahoma', 'Arial', sans-serif; max-width: 1100px; margin: 0 auto; background: #fafafa; padding: 30px; border-radius: 12px; box-shadow: 0 10px 30px rgba(0,0,0,0.15); position: relative; overflow: hidden;">
            

    <div class="contract-wrapper" style="direction: rtl; font-family: 'Tahoma', 'Arial', sans-serif; max-width: 1100px; margin: 0 auto; background: #fafafa; padding: 30px; border-radius: 12px; box-shadow: 0 10px 30px rgba(0,0,0,0.15);">
        <div style="background: #fff; padding: 40px 50px; border-radius: 10px; box-shadow: 0 4px 8px rgba(0,0,0,0.05);">
                    <img src="{$imageUrl}" 
                 class="contract-background-image"
                 style="position: fixed; 
                        top: 0; left: 0; 
                        width: 100%; height: 100%; 
                        object-fit: cover; 
                        opacity: 0.05; 
                        z-index: 0; 
                        pointer-events: none;"
                 alt="خلفية العقد">    
        
        <!-- رأس العقد مع شعار وهمي -->
            <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 3px double #2c3e50; padding-bottom: 15px; margin-bottom: 25px;">
                <div style="font-size: 22px; font-weight: bold; color: #2c3e50;">عقد استئجار سيارة</div>
                <div style="text-align: left; font-size: 14px; color: #7f8c8d;">رقم العقد: {$this->id}</div>
            </div>

            <!-- بيانات المؤجر -->
            <div style="margin-bottom: 20px; background: #f0f4f8; padding: 15px; border-radius: 8px; border-right: 6px solid #2980b9;">
                <h3 style="margin: 0 0 8px 0; color: #2980b9; font-size: 18px;">الفريق الأول (المؤجر)</h3>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 5px 20px;">
                    <span><strong>الاسم الرباعي:</strong> {$ownerName}</span>
                    <span><strong>رقم الهوية:</strong> {$ownerId}</span>
                    <span><strong>العنوان:</strong> {$ownerAddress}</span>
                    <span><strong>الهاتف:</strong> {$ownerPhone}</span>
                </div>
            </div>

            <!-- بيانات المستأجر -->
            <div style="margin-bottom: 20px; background: #f0f4f8; padding: 15px; border-radius: 8px; border-right: 6px solid #27ae60;">
                <h3 style="margin: 0 0 8px 0; color: #27ae60; font-size: 18px;">الفريق الثاني (المستأجر)</h3>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 5px 20px;">
                    <span><strong>الاسم الرباعي:</strong> {$tenantName}</span>
                    <span><strong>رقم الهوية:</strong> {$tenantId}</span>
                    <span><strong>العنوان:</strong> {$tenantAddress}</span>
                    <span><strong>الهاتف:</strong> {$tenantPhone}</span>
                    <span><strong>رقم إجازة السوق:</strong> {$tenantLicense}</span>
                    <span><strong>الفئة:</strong> {$tenantLicenseType}</span>
                    <span><strong>تاريخ الإصدار:</strong> {$tenantLicenseDate}</span>
                    <span><strong>صالحة لغاية:</strong> {$tenantLicenseExpiry}</span>
                </div>
            </div>

            <!-- المقدمة -->
            <div style="margin: 25px 0; padding: 15px; background: #fdfaf0; border-radius: 8px; border: 1px solid #f1c40f;">
                <h3 style="color: #8e44ad; margin-top: 0;">مقدمة</h3>
                <p style="line-height: 1.9; text-align: justify; margin: 0;">
                    لما كان الفريق الأول يملك السيارة المبينة أوصافها في هذا العقد، ويرغب باستثمارها بتأجيرها للاستعمال ضمن الجمهورية العربية السورية،<br>
                    ولما كان الفريق الثاني يرغب باستئجار السيارة المذكورة لغايات الاستعمال الشخصي / التجاري / الاستثماري،<br>
                    وقد اتفق الفريقان، وهما بكامل الأهلية المعتبرة شرعاً وقانوناً، على ما يلي:
                </p>
            </div>

            <!-- المادة 1: وصف السيارة -->
            <div style="margin-bottom: 25px;">
                <h3 style="color: #2c3e50; border-bottom: 2px solid #3498db; padding-bottom: 5px;">المادة 1: وصف السيارة المؤجرة</h3>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px 30px; background: #fcfcfc; padding: 15px; border-radius: 6px;">
                    <span><strong>الرقم التسلسلي / اللوحة:</strong> {$carLicensePlate}</span>
                    <span><strong>الفئة:</strong> {$carType}</span>
                    <span><strong>الماركة:</strong> {$carBrand}</span>
                    <span><strong>الطراز / الموديل:</strong> {$carModel}</span>
                    <span><strong>سنة الصنع:</strong> {$carYear}</span>
                    <span><strong>رقم الهيكل:</strong> {$carChassis}</span>
                    <span><strong>رقم المحرك:</strong> {$carEngine}</span>
                    <span><strong>اللون:</strong> {$carColor}</span>
                    <span><strong>عدد المقاعد:</strong> {$carSeats}</span>
                    <span><strong>قوة المحرك:</strong> {$carHp} حصان</span>
                    <span><strong>نوع الوقود:</strong> {$carFuel}</span>
                    <span><strong>رخصة السير رقم:</strong> {$carLicenseNumber}</span>
                    <span><strong>تاريخ الإصدار:</strong> {$carLicenseDate}</span>
                    <span><strong>الصادرة عن مديرية النقل في:</strong> {$carLicenseIssuer}</span>
                    <span><strong>الصالحة لغاية:</strong> {$carLicenseExpiry}</span>
                </div>
            </div>

            <!-- المادة 2: مدة الإيجار -->
            <div style="margin-bottom: 25px;">
                <h3 style="color: #2c3e50; border-bottom: 2px solid #3498db; padding-bottom: 5px;">المادة 2: مدة الإيجار</h3>
                <div style="background: #f9f9f9; padding: 12px 20px; border-radius: 6px;">
                    <p><strong>بدءاً من الساعة / اليوم:</strong> {$startDate}</p>
                    <p><strong>وحتى الساعة / اليوم:</strong> {$endDate}</p>
                    <p><strong>(تُقدر مدة الإيجار بـ {$rentDuration} ساعة / يوماً / شهراً).</strong></p>
                </div>
            </div>

            <!-- المادة 3: بدل الإيجار -->
            <div style="margin-bottom: 25px;">
                <h3 style="color: #2c3e50; border-bottom: 2px solid #3498db; padding-bottom: 5px;">المادة 3: بدل الإيجار وطريقة الدفع</h3>
                <div style="background: #f9f9f9; padding: 12px 20px; border-radius: 6px;">
                    <p>يُحدد بدل الإيجار بمبلغ <strong style="color: #c0392b;">{$rentPrice} ليرة سورية</strong> عن كل (ساعة / يوم / شهر).</p>
                    <p>يُدفع بدل الإيجار مقدماً نقداً فور تحرير هذا العقد، ويُعتبر توقيع الفريق الثاني على هذا العقد سنداً باستلام الفريق الأول للقيمة المذكورة.</p>
                    <p>في حال تجاوز مدة الإيجار المتفق عليها، يلتزم الفريق الثاني بدفع مبلغ إضافي قدره <strong style="color: #c0392b;">{$additionalRent} ليرة سورية</strong> عن كل ساعة / يوم تأخير، وذلك دون حاجة إلى أي إنذار أو تنبيه.</p>
                </div>
            </div>

            <!-- المادة 4: التسليم -->
            <div style="margin-bottom: 25px;">
                <h3 style="color: #2c3e50; border-bottom: 2px solid #3498db; padding-bottom: 5px;">المادة 4: تسليم السيارة واستلامها</h3>
                <div style="background: #f9f9f9; padding: 12px 20px; border-radius: 6px; line-height: 1.8;">
                    <p><strong>أ-</strong> يقر الفريق الثاني بأنه تسلم السيارة المذكورة في هذا العقد بحالة جيدة وسليمة وصالحة للاستعمال، وقد عاينها المعاينة التامة النافية للجهالة، ولم يجد بها خدشاً أو نقصاً أو كسراً أو عيباً خفياً أو ظاهراً، وأسقط حقه في ادعاء خلاف ذلك.</p>
                    <p><strong>ب-</strong> يلتزم الفريق الثاني بإعادة السيارة إلى الفريق الأول في نهاية مدة العقد بنفس الحالة التي تسلمها بها، ما عدا الهلاك والاستهلاك العادي الناتج عن الاستعمال.</p>
                </div>
            </div>

            <!-- المادة 5: التزامات المستأجر -->
            <div style="margin-bottom: 25px;">
                <h3 style="color: #2c3e50; border-bottom: 2px solid #3498db; padding-bottom: 5px;">المادة 5: التزامات الفريق الثاني (المستأجر)</h3>
                <div style="background: #f9f9f9; padding: 12px 20px; border-radius: 6px; line-height: 1.8;">
                    <ol style="padding-right: 20px; margin: 0;">
                        <li>قيادة السيارة بنفسه، وعدم استخدام سائق آخر للعمل عليها، وعدم إعارتها للغير أو استخدامها لأغراض تنافي هذا العقد أو تنافي طبيعة استعمال السيارة.</li>
                        <li>دفع أي غرامة أو رسم أو تعويض ناجم عن استخدام السيارة أو حيازتها أو قيادتها أثناء مدة الإيجار وبعد انقضائها وحتى إعادتها للفريق الأول.</li>
                        <li>تحمل المسؤولية المدنية والجزائية عن السيارة طيلة مدة الإيجار، ويكون مسؤولاً عن كل ما يصيبها أثناء انتفاعه بها.</li>
                        <li>تحمل كافة مخالفات السير التي تقع بعد استلام السيارة، ويلتزم بدفعها ولو ظهرت وتبينت بعد تسليم السيارة في نهاية عقد الإيجار.</li>
                        <li>تموين السيارة بالوقود والزيوت، وتحمل نفقات الصيانة الدورية والإصلاحات اللازمة.</li>
                        <li>اتخاذ الإجراءات اللازمة لحفظ حقوق الفريق الأول تجاه مؤسسة التأمين، وتنظيم محضر ضبط شرطة المرور بالحادث، وإبلاغ الفريق الأول بذلك فوراً.</li>
                        <li>إعادة السيارة مع كافة ملحقاتها ووثائقها (رخصة السير، بطاقة التأمين، وغيرها) في نهاية مدة العقد.</li>
                    </ol>
                </div>
            </div>

            <!-- المادة 6: التزامات المؤجر -->
            <div style="margin-bottom: 25px;">
                <h3 style="color: #2c3e50; border-bottom: 2px solid #3498db; padding-bottom: 5px;">المادة 6: التزامات الفريق الأول (المؤجر)</h3>
                <div style="background: #f9f9f9; padding: 12px 20px; border-radius: 6px; line-height: 1.8;">
                    <ul style="padding-right: 20px; margin: 0;">
                        <li>تسليم السيارة للفريق الثاني بحالة فنية سليمة، مع كافة الوثائق والملحقات.</li>
                        <li>التأكد من أن السيارة مؤمنة تأميناً إلزامياً، ويُفضل التأمين الشامل ضد جميع الأخطار، وذلك لصالح الغير.</li>
                        <li>تحمل كافة المخالفات والالتزامات المتعلقة بالسيارة والسابقة لتاريخ هذا العقد.</li>
                        <li>عدم مطالبة الفريق الثاني بأي مبالغ إضافية غير المتفق عليها في هذا العقد.</li>
                    </ul>
                </div>
            </div>

            <!-- المادة 7: التأمين -->
            <div style="margin-bottom: 25px;">
                <h3 style="color: #2c3e50; border-bottom: 2px solid #3498db; padding-bottom: 5px;">المادة 7: التأمين</h3>
                <div style="background: #f9f9f9; padding: 12px 20px; border-radius: 6px; line-height: 1.8;">
                    <p>السيارة مؤمنة لدى المؤسسة العامة للتأمين تأميناً (إلزامياً / شاملاً جميع الأخطار) بموجب وثيقة التأمين رقم <strong>{$insuranceNumber}</strong> تاريخ //// السارية المفعول لغاية ////.</p>
                    <p>يقع على عاتق الفريق الثاني: دفع قيمة أي زيادة في أقساط التأمين نتيجة الحوادث أو المخالفات التي يتسبب بها خلال مدة الإيجار.</p>
                </div>
            </div>

            <!-- المادة 8: التأمين النقدي -->
            <div style="margin-bottom: 25px;">
                <h3 style="color: #2c3e50; border-bottom: 2px solid #3498db; padding-bottom: 5px;">المادة 8: التأمين النقدي (الضمان)</h3>
                <div style="background: #f9f9f9; padding: 12px 20px; border-radius: 6px; line-height: 1.8;">
                    <p>دفع الفريق الثاني للفريق الأول مبلغ <strong style="color: #c0392b;">{$damagePaid} ليرة سورية</strong> كتأمين نقدي ضماناً للسيارة من أي عطل أو ضرر.</p>
                    <p>يُعاد هذا المبلغ إلى الفريق الثاني في نهاية مدة العقد بعد تسليم السيارة سليمة، مع خصم قيمة أي أضرار أو إصلاحات أو مخالفات يكون الفريق الثاني مسؤولاً عنها.</p>
                    <p>لا يُعفى هذا التأمين الفريق الثاني من التزامه بالتعويضات الإضافية إذا تجاوزت قيمة الأضرار مبلغ التأمين المذكور.</p>
                </div>
            </div>

            <!-- المادة 9: الفسخ -->
            <div style="margin-bottom: 25px;">
                <h3 style="color: #2c3e50; border-bottom: 2px solid #3498db; padding-bottom: 5px;">المادة 9: حالات فسخ العقد</h3>
                <div style="background: #f9f9f9; padding: 12px 20px; border-radius: 6px; line-height: 1.8;">
                    <ul style="padding-right: 20px; margin: 0;">
                        <li>إذا ثبت أن الفريق الثاني قد استعمل السيارة في غير الغرض المخصصة لها، أو استعملها بصورة تنافي الآداب العامة أو الأمن العام.</li>
                        <li>إذا ثبت أن الفريق الثاني قام بتأجير السيارة من الباطن أو إعارتها للغير دون موافقة خطية مسبقة من الفريق الأول.</li>
                        <li>إذا لم يقم الفريق الثاني بسداد بدل الإيجار في المواعيد المحددة.</li>
                        <li>في حالة وقوع حادث جسيم يؤدي إلى تلف كلي أو جزئي للسيارة.</li>
                    </ul>
                </div>
            </div>

            <!-- المادة 10: النزاعات -->
            <div style="margin-bottom: 25px;">
                <h3 style="color: #2c3e50; border-bottom: 2px solid #3498db; padding-bottom: 5px;">المادة 10: النزاعات والاختصاص القضائي</h3>
                <div style="background: #f9f9f9; padding: 12px 20px; border-radius: 6px;">
                    <p>كل نزاع ينشأ بخصوص تفسير أو تنفيذ هذا العقد يكون الفصل فيه من اختصاص محاكم الجمهورية العربية السورية.</p>
                </div>
            </div>

            <!-- المادة 11: أحكام عامة -->
            <div style="margin-bottom: 25px;">
                <h3 style="color: #2c3e50; border-bottom: 2px solid #3498db; padding-bottom: 5px;">المادة 11: أحكام عامة</h3>
                <div style="background: #f9f9f9; padding: 12px 20px; border-radius: 6px; line-height: 1.8;">
                    <ol style="padding-right: 20px; margin: 0;">
                        <li>تعتبر مقدمة هذا العقد جزءاً لا يتجزأ منه وتقرأ معه.</li>
                        <li>حرر هذا العقد من نسختين أصليتين، بيد كل من الفريقين نسخة واحدة للعمل بموجبها.</li>
                        <li>أي تعديل أو إضافة على هذا العقد لا تكون نافذة إلا إذا كانت خطية وموقعة من الطرفين.</li>
                        <li>في حال تعذر تنفيذ أي شرط من شروط هذا العقد، لا يؤثر ذلك على صحة وسريان باقي الشروط.</li>
                    </ol>
                </div>
            </div>

            <!-- المادة 12: الكفالة -->
            <div style="margin-bottom: 30px;">
                <h3 style="color: #2c3e50; border-bottom: 2px solid #3498db; padding-bottom: 5px;">المادة 12: الكفالة (إن وجدت)</h3>
                <div style="background: #f9f9f9; padding: 12px 20px; border-radius: 6px; line-height: 1.8;">
                    <p><strong>أ- في حال وجود كفيل:</strong></p>
                    <p>أنا الموقع أدناه <strong>{$bailName}</strong> (رقم الهوية: {$bailId}) أضمن الفريق الثاني (المستأجر) بالتضامن والتكافل في جميع التزاماته المترتبة على هذا العقد.</p>
                    <p><strong>ب- توقيع الكفيل:</strong></p>
                    <p>الاسم: ................................</p>
                    <p>التوقيع: ................................</p>
                    <p>التاريخ: ................................</p>
                </div>
            </div>

            <!-- التوقيعات والشهود -->
            <div style="border-top: 2px solid #2c3e50; padding-top: 25px; margin-top: 10px;">
                <div style="display: flex; flex-wrap: wrap; justify-content: space-between;">
                    <div style="width: 45%;">
                        <h4 style="margin: 0 0 10px 0; color: #2980b9;">الفريق الأول (المؤجر)</h4>
                        <p><strong>الاسم:</strong> {$ownerName}</p>
                        <p><strong>التوقيع:</strong> ........................</p>
                        <p><strong>الختم:</strong> ........................</p>
                    </div>
                    <div style="width: 45%;">
                        <h4 style="margin: 0 0 10px 0; color: #27ae60;">الفريق الثاني (المستأجر)</h4>
                        <p><strong>الاسم:</strong> {$tenantName}</p>
                        <p><strong>التوقيع:</strong> ........................</p>
                        <p><strong>الختم:</strong> ........................</p>
                    </div>
                </div>
                <div style="margin-top: 20px; background: #ecf0f1; padding: 10px 20px; border-radius: 6px;">
                    <p><strong>شاهد أول:</strong> {$witness1}</p>
                    <p><strong>شاهد ثان:</strong> {$witness2}</p>
                </div>
                <p style="text-align: left; margin-top: 20px; color: #7f8c8d;"><strong>حرر في:</strong> ................................ <strong>بتاريخ:</strong> {$today}</p>
            </div>

        </div> <!-- نهاية الـ inner div -->
    </div> <!-- نهاية الـ wrapper -->
    HTML;

    return $html;
}

}
