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
protected $nullable = ['type', 'license_plate_number' , 'year_of_manufacturing_date' , 'country_id' , 'license_plate_number_new' , 'country_new_id' , 'country_location_id'];

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
        'country_location_id' => 'nullable|integer|exists:car_car_countries,id',
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
        'country_location' => [\Car\Car\Models\Country::class, 'key' => 'country_location_id'],

        

    ];


    public $attachMany = [
        'photos' => 'System\Models\File'
    ];

        public $hasMany = [
        'rents' => ['Car\Car\Models\Rent', 'key' => 'car_id'],
    ];

    // In your Car model
public function getYearOptions()
{
    // PostgreSQL compatible
    return self::selectRaw('EXTRACT(YEAR FROM year_of_manufacturing_date) as year')
        ->distinct()
        ->whereNotNull('year_of_manufacturing_date')
        ->orderBy('year', 'desc')
        ->pluck('year', 'year')
        ->toArray();
}


    public function getBrandOptions()
{
    return Brand::lists('name', 'id');
}

public function getModelOptions($scopes = null)
{
    if (!empty($scopes['brand']->value)) {
        return Modelnew::whereIn('brand_id', array_keys($scopes['brand']->value))->lists('name', 'id');
    } else {
        return Modelnew::lists('name', 'id');
    }
}


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

  public function getCountryNameAttribute(){

    return $this->attributes['country_id'] ? Country::find($this->attributes['country_id'])->name : Country::find($this->attributes['country_id'])->name;
  }

  public function getCountryNewNameAttribute(){

    return $this->attributes['country_new_id'] ? Country::find($this->attributes['country_new_id'])->name : Country::find($this->attributes['country_id'])->name;
  }


   public function getBrandNameAttribute(){

    return $this->attributes['brand_id'] ? Brand::find($this->attributes['brand_id'])->name : Brand::find($this->attributes['brand_id'])->name;
  }

   public function getModelNameAttribute(){

    return $this->attributes['model_id'] ? Modelnew::find($this->attributes['model_id'])->name : Modelnew::find($this->attributes['model_id'])->name;
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
