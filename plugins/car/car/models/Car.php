<?php namespace Car\Car\Models;

use Model;
// use Winter\Storm\Database\Builder;
// use BackendAuth;
/**
 * Model
 */
use Jacob\Logbook\Traits\LogChanges;

class Car extends Model
{
    use \Winter\Storm\Database\Traits\Validation;
    use LogChanges;

    use \Winter\Storm\Database\Traits\Nullable;
protected $nullable = ['type', 'license_plate_number' , 'year_of_manufacturing_date' , 'country_id' , 'license_plate_number_new' , 'country_new_id'];

  public $logBookModelName = 'car.car::lang.plugin.cars';
  public static function changeLogBookDisplayColumn($column)
  {
    return 'car.car::lang.model.car.' . $column;
  }
    
    


    /**
     * @var string The database table used by the model.
     */
    public $table = 'car_car_cars';

    /**
     * @var array Validation rules
     */
       public $rules = [
        'ownership' => 'required|string|max:255',
        'type' => 'nullable|string|max:255',
        'model_id' => 'required|integer|exists:car_car_models,id',
        'chassis_number' => 'required|string|max:255|unique:car_car_cars,chassis_number',
        'brand_id' => 'required|integer|exists:car_car_brands,id',
        'year_of_manufacturing_date' => 'nullable|date',
        'fuel_type' => 'required|string|max:255|in:petrol,diesel,electric,hybrid,plug-in_hybrid,hydrogen,lpg,cng',
        'license_plate_number' => 'nullable|string|max:255|unique:car_car_cars,license_plate_number',
        'country_id' => 'nullable|integer|exists:car_car_countries,id',
        'license_plate_number_new' => 'nullable|string|max:255',
        'country_new_id' => 'nullable|integer|exists:car_car_countries,id',
    ];

    /**
     * Defines a "belongsTo" relationship.
     *
     * - Establishes a relationship between this model and the `nameClass` model.
     * - The foreign key `key_relation_id` is used to link the related model.
     * - This allows accessing the related `namerelation` data conveniently.
     *
     * @var array
     */
    public $belongsTo = [
        'model' => [\Car\Car\Models\Modelnew::class, 'key' => 'model_id'],
        'brand' => [\Car\Car\Models\Brand::class, 'key' => 'brand_id'],
        'country' => [\Car\Car\Models\Country::class, 'key' => 'country_id'],
        'country_new' => [\Car\Car\Models\Country::class, 'key' => 'country_new_id'],

    ];


    public $attachMany = [
        'photos' => 'System\Models\File'
    ];

        public $hasMany = [
        'rents' => ['Car\Car\Models\Rent', 'key' => 'car_id'],
    ];


 public function getFuelListsAttribute()
  {
    return trans('car.car::lang.model.car.' . $this->attributes['fuel_type']);
  }


      public function filterFields($fields, $context = null)
  {         
      if (isset($fields->brand->value) && !empty($fields->brand->value)) {
                $fields->model_id->options = Modelnew::where('brand_id' , $fields->brand->value)->get()->lists('name', 'id');     
      }else{
            $fields->model_id->options = [];

          }


  }



    
    /**
     * @var array Attribute names to encode and decode using JSON.
     */
    public $jsonable = [];


          /**
     * Perform actions before deleting 
     *
     * @throws \ValidationException
     */
    public function beforeDelete()
    {
        foreach ($this->hasMany as $relation => $details) {
            if ($this->{$relation}->count() > 0) {
                throw new \ValidationException(['name' => trans('car.car::lang.plugin.message_delete')]);
            }
        }
    }



}
