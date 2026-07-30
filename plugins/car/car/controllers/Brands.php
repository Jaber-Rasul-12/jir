<?php namespace Car\Car\Controllers;

use Backend\Classes\Controller;
use BackendMenu;

class Brands extends Controller
{
    public $implement = [        'Backend\Behaviors\ListController',        'Backend\Behaviors\FormController'    ];
    
    public $listConfig = 'config_list.yaml';
    public $formConfig = 'config_form.yaml';

    public $requiredPermissions = [
        'brands' 
    ];

    public function __construct()
    {
        parent::__construct();
        BackendMenu::setContext('Car.Car', 'cars_settings', 'brand');
    }

    public function formGetRedirectUrl($context = null, $model = null)
    {
        $url = post('url');


        if (($url == 'create') && !empty($url)) {
            return "car/car/brands/create";
        } else {
            if ((post("close") == 1) && !empty(post("close"))) {
                return "car/car/brands";
            } else {
                return "car/car/brands/update/$model->id";
            }
        }
    }
}
