<?php namespace Car\Car\Controllers;

use Backend\Classes\Controller;
use BackendMenu;
use Car\Car\Models\Brand;
use Car\Car\Models\Country;
use Car\Car\Models\Modelnew;
use Flash;

class Cars extends Controller
{
    public $implement = [        'Backend\Behaviors\ListController',        'Backend\Behaviors\FormController' , \Backend\Behaviors\ImportExportController::class,
  ];
    
    public $listConfig = 'config_list.yaml';
    public $formConfig = 'config_form.yaml';

    public $importExportConfig = 'import_export_config.yaml';

    public $requiredPermissions = [
        'cars' 
    ];

    public function __construct()
    {
        parent::__construct();
        BackendMenu::setContext('Car.Car', 'cars', 'cars');
         $this->addCss('/plugins/car/car/assets/css/style_button.css', 'car.car');
    }

    public function formGetRedirectUrl($context = null, $model = null)
    {
        $url = post('url');


        if (($url == 'create') && !empty($url)) {
            return "car/car/cars";
        }else if (($url == 'preview') && !empty($url)) {
            return "car/car/cars/$url/$model->id";
        }else {
            if ((post("close") == 1) && !empty(post("close"))) {
                return "car/car/cars";
            } else {
                return "car/car/cars/update/$model->id";
            }
        }
    }

    public function preview($id, $context = null)
    {
        $this->addCss('/plugins/car/car/assets/filesignatore/jquery.signaturepad.css', 'car.car');
        $this->addJs('/plugins/car/car/assets/filesignatore/jquery.signaturepad.js', 'car.car');
        $this->addJs('/plugins/car/car/assets/filesignatore/json2.min.js', 'car.car');
        return $this->asExtension('FormController')->preview($id, $context);
    }


    public function onGeneralStatistics(){
            return [
        '#Lists' => $this->makePartial('statistices' , [ 'models' => Modelnew::withCount('cars')->get() ,'brands' => Brand::withCount('cars')->get() ,  'countries_old' => Country::withCount('cars')->get() , 'countries_new' => Country::withCount('cars_new')->get() ,]),
        '#Filter-listFilter'=>' ',
        ];
    }
}