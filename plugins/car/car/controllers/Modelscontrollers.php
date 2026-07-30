<?php namespace Car\Car\Controllers;

use Backend\Classes\Controller;
use BackendMenu;

class ModelsControllers extends Controller
{
    public $implement = [        'Backend\Behaviors\ListController',        'Backend\Behaviors\FormController'    ];
    
    public $listConfig = 'config_list.yaml';
    public $formConfig = 'config_form.yaml';

    public $requiredPermissions = [
        'models' 
    ];

    public function __construct()
    {
        parent::__construct();
        BackendMenu::setContext('Car.Car', 'cars_settings', 'model');
    }

   public function formGetRedirectUrl($context = null, $model = null)
    {
        $url = post('url');


        if (($url == 'create') && !empty($url)) {
            return "car/car/modelscontrollers/create";
        } else {
            if ((post("close") == 1) && !empty(post("close"))) {
                return "car/car/modelscontrollers";
            } else {
                return "car/car/modelscontrollers/update/$model->id";
            }
        }
    }
}
