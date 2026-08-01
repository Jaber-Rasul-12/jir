<?php namespace Car\Car\Controllers;

use Backend\Classes\Controller;
use BackendMenu;

class Rents extends Controller
{
    public $implement = [        'Backend\Behaviors\ListController',        'Backend\Behaviors\FormController' , \Backend\Behaviors\RelationController::class,    ];
    
    public $listConfig = 'config_list.yaml';
    public $formConfig = 'config_form.yaml';

    public $relationConfig = 'relation_config.yaml';

    public $requiredPermissions = [
        'rents' 
    ];

    public function __construct()
    {
        parent::__construct();
        BackendMenu::setContext('Car.Car', 'cars', 'rents');
         $this->addCss('/plugins/car/car/assets/css/style_button.css', 'car.car');

    }


       public function formGetRedirectUrl($context = null, $model = null)
    {
        $url = post('url');


        if (($url == 'create') && !empty($url)) {
            return "car/car/rents";
        }else if (($url == 'preview') && !empty($url)) {
            return "car/car/rents/$url/$model->id";
        }else {
            if ((post("close") == 1) && !empty(post("close"))) {
                return "car/car/rents";
            } else {
                return "car/car/rents/update/$model->id";
            }
        }
    }
}
