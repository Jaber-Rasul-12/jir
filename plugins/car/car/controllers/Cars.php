<?php namespace Car\Car\Controllers;

use Backend\Classes\Controller;
use BackendMenu;

class Cars extends Controller
{
    public $implement = [        'Backend\Behaviors\ListController',        'Backend\Behaviors\FormController'    ];
    
    public $listConfig = 'config_list.yaml';
    public $formConfig = 'config_form.yaml';

    public $requiredPermissions = [
        'cars' 
    ];

    public function __construct()
    {
        parent::__construct();
        BackendMenu::setContext('Car.Car', 'cars', 'cars');
    }

    public function formGetRedirectUrl($context = null, $model = null)
    {
        $url = post('url');


        if (($url == 'create') && !empty($url)) {
            return "car/car/cars";
        } else {
            if ((post("close") == 1) && !empty(post("close"))) {
                return "car/car/cars";
            } else {
                return "car/car/cars/update/$model->id";
            }
        }
    }
}
