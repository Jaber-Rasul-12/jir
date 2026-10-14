<?php namespace Car\Car\Controllers;

use Backend\Classes\Controller;
use BackendMenu;

class TransferDocuments extends Controller
{
    public $implement = [        'Backend\Behaviors\ListController',        'Backend\Behaviors\FormController'    ];
    
    public $listConfig = 'config_list.yaml';
    public $formConfig = 'config_form.yaml';

    public $requiredPermissions = [
        'transfer_documents' 
    ];

    public function __construct()
    {
        parent::__construct();
        BackendMenu::setContext('Car.Car', 'cars', 'transfer_documents');
    }

      public function formGetRedirectUrl($context = null, $model = null)
    {
        $url = post('url');


        if (($url == 'create') && !empty($url)) {
            return "car/car/transferdocuments/create";
        }else if (($url == 'preview') && !empty($url)) {
            return "car/car/transferdocuments/$url/$model->id";
        }else {
            if ((post("close") == 1) && !empty(post("close"))) {
                return "car/car/transferdocuments";
            } else {
                return "car/car/transferdocuments/update/$model->id";
            }
        }
    }

public function printall()
{
    $this->vars['records'] = \Car\Car\Models\TransferDocument::where('id', '<=', 74)->get();
}
}
