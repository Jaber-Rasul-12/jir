<?php namespace Finance\Finance\Models;

use Model;
// use Winter\Storm\Database\Builder;
// use BackendAuth;
/**
 * Model
 */
use Jacob\Logbook\Traits\LogChanges;
class Budget extends Model
{
    use \Winter\Storm\Database\Traits\Validation;

            use LogChanges;
  public $logBookModelName = 'finance.finance::lang.plugin.budgets';
  public static function changeLogBookDisplayColumn($column)
  {
    return 'finance.finance::lang.model.budget.' . $column;
  }
    
   
    /**
     * @var string The database table used by the model.
     */
    public $table = 'finance_finance_budgets';

   public $rules = [

        'currency'              => 'required|string|max:255',
        'amount'                => 'required|numeric|min:0',
        'type_id'         => 'required|exists:finance_finance_types,id',

    ];

    
    public $belongsTo = [
        'model_type' => ['Finance\Finance\Models\ModelType', 'key' => 'type_id'],
    ];


    public function getTypeIdOptions(){
        return ModelType::where('type' , 'receipt')->lists('name', 'id');
    }


      public function getCurrencyOptions()
  {
      return ['dollar'=>trans('finance.finance::lang.model.budget.dollar') , 'syrian' => trans('finance.finance::lang.model.budget.syrian')];
    
  }

    public function getTypeOptions()
  {

  return ['payment'=>trans('finance.finance::lang.model.budget.payment') , 'receipt' => trans('finance.finance::lang.model.budget.receipt')];
    
  }
   


}
