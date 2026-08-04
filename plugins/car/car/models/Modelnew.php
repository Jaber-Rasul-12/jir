<?php namespace Car\Car\Models;

use Model;
// use Winter\Storm\Database\Builder;
// use BackendAuth;
/**
 * Model
 */

use Jacob\Logbook\Traits\LogChanges;
class Modelnew extends Model
{
    use \Winter\Storm\Database\Traits\Validation;
    use LogChanges;

  public $logBookModelName = 'car.car::lang.plugin.models';
  public static function changeLogBookDisplayColumn($column)
  {
    return 'car.car::lang.model.model.' . $column;
  }
    



    /**
     * @var string The database table used by the model.
     */
    public $table = 'car_car_models';

    /**
     * @var array Validation rules
     */
    public $rules = [
              'name' => 'required|string|max:255|unique:car_car_models,name',
              'brand_id' => 'required|exists:car_car_brands,id',
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
        'brand' => [\Car\Car\Models\Brand::class, 'key' => 'brand_id'],
        
    ];

    public $hasMany = [
        'cars' => [\Car\Car\Models\Car::class, 'key' => 'model_id' , 'count'=>true],
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
