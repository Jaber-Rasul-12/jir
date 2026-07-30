<?php namespace Car\Car\Controllers;

use Backend\Classes\Controller;
use BackendMenu;

class Countries extends Controller
{
    public $implement = [        'Backend\Behaviors\ListController',        'Backend\Behaviors\FormController'    ];
    
    public $listConfig = 'config_list.yaml';
    public $formConfig = 'config_form.yaml';

    public $requiredPermissions = [
        'country' 
    ];

    public function __construct()
    {
        parent::__construct();
        BackendMenu::setContext('Car.Car', 'cars_settings', 'country');
    }

   public function formGetRedirectUrl($context = null, $model = null)
    {
        $url = post('url');


        if (($url == 'create') && !empty($url)) {
            return "car/car/countries/create";
        } else {
            if ((post("close") == 1) && !empty(post("close"))) {
                return "car/car/countries";
            } else {
                return "car/car/countries/update/$model->id";
            }
        }
    }
}
