<?php namespace Car\Car\Models;

use Model;
// use Winter\Storm\Database\Builder;
// use BackendAuth;
/**
 * Model
 */
use Jacob\Logbook\Traits\LogChanges;
class Customer extends Model
{
    use \Winter\Storm\Database\Traits\Validation;
   
    use LogChanges;

  public $logBookModelName = 'car.car::lang.plugin.customers';
  public static function changeLogBookDisplayColumn($column)
  {
    return 'car.car::lang.model.customer.' . $column;
  }


    /**
     * @var string The database table used by the model.
     */
    public $table = 'car_car_customers';

    /**
     * @var array Validation rules
     */
       public $rules = [
        'full_name' => 'required|string|max:255',
        'id_number' => 'nullable|string|max:50',
        'address' => 'required|string|max:500',
        'phone' => 'required|string|max:20',
        'driving_license' => 'nullable|string|max:50',
        'type_of_drivers_license' => 'nullable|string|max:50|in:private,public',
        'date_of_drivers_license' => 'nullable|date',
        'valid_for_the_end' => 'nullable|date',

    ];


    public $hasMany = [
        'owner_rents' => ['Car\Car\Models\Rent', 'key' => 'customer_owner_id' , 'count'=>true],
        'tenant_rents' => ['Car\Car\Models\Rent', 'key' => 'customer_tenant_id', 'count'=>true],
        'bail_rents' => ['Car\Car\Models\Rent', 'key' => 'customer_bail_id', 'count'=>true],
    ];

    public $attachMany = [
        'photos' => 'System\Models\File'
    ];

     public function getTypeOfDriversLicenseListsAttribute()
  {
    return trans('car.car::lang.model.customer.' . $this->attributes['type_of_drivers_license']);
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
