<?php namespace Car\Car\Models;

use Model;
// use Winter\Storm\Database\Builder;
// use BackendAuth;
/**
 * Model
 */
use Jacob\Logbook\Traits\LogChanges;

class Brand extends Model
{
    use \Winter\Storm\Database\Traits\Validation;

    use LogChanges;

  public $logBookModelName = 'car.car::lang.plugin.brands';
  public static function changeLogBookDisplayColumn($column)
  {
    return 'car.car::lang.model.brand.' . $column;
  }
    
    
    

    /**
     * @var string The database table used by the model.
     */
    public $table = 'car_car_brands';

    /**
     * @var array Validation rules
     */
    public $rules = [
              'name' => 'required|string|max:255|unique:car_car_brands,name',

    ];


    /**
     * Defines a "hasMany" relationship.
     *
     * - Establishes a one-to-many relationship between this model and the `nameClass` model.
     * - The foreign key `key_relation_id` is used to link multiple related records.
     * - This allows retrieving multiple `nameRelation` records associated with this model.
     *
     * @var array
     */
    public $hasMany = [
        'models' => [\Car\Car\Models\Modelnew::class, 'key' => 'brand_id' , 'count'=>true],
        'cars' => [\Car\Car\Models\Car::class, 'key' => 'brand_id' , 'count'=>true],
    ];

    
    
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
