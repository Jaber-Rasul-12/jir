<?php namespace Car\Car\Controllers;

use Backend\Classes\Controller;
use BackendMenu;

class SaleContracts extends Controller
{
    public $implement = [        'Backend\Behaviors\ListController',        'Backend\Behaviors\FormController', \Backend\Behaviors\RelationController::class     ];
    
    public $listConfig = 'config_list.yaml';
    public $formConfig = 'config_form.yaml';

    public $requiredPermissions = [
        'sale_contracts' 
    ];
    public $relationConfig = 'relation_config.yaml';

    public function __construct()
    {
        parent::__construct();
        BackendMenu::setContext('Car.Car', 'cars', 'sale_contracts');
    }

      public function formGetRedirectUrl($context = null, $model = null)
    {
        $url = post('url');


        if (($url == 'create') && !empty($url)) {
            return "car/car/salecontracts/create";
        }else if (($url == 'preview') && !empty($url)) {
            return "car/car/salecontracts/$url/$model->id";
        }else {
            if ((post("close") == 1) && !empty(post("close"))) {
                return "car/car/salecontracts";
            } else {
                return "car/car/salecontracts/update/$model->id";
            }
        }
    }
}
