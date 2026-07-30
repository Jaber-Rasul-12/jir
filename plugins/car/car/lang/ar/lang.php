<?php

return [
    'plugin' => [
        'name' => 'السيارات',
        'description' => '',
        'cars_menu' => 'قائمة السيارات',
        'brands' => 'العلامات التجارية',
        'models' => 'الموديلات',
        'countries' => 'المدن',
        'cars' => 'السيارات',
        'brand' => 'العلامة التجارية',
        'model' => 'الموديل',
        'country' => 'المدينة',
        'car' => 'السيارة',
        'cars_settings' => 'إعدادات السيارات',
        'select' => 'اختر',
        'log_changes_cars' => 'سجل تغييرات السيارات',
        'create_and_new'=>'إنشاء و جديد',
        'message_delete'=>'حذف غير ممكن بسبب وجود سجلات مرتبطة بالقسم.',


    ],
    'model' => [
        'country' => [
            'id' => 'المعرف',
            'name' => 'الاسم',
            'created_at' => 'تاريخ الإنشاء',
            'updated_at' => 'تاريخ التحديث',
        ],
        'brand' => [
            'id' => 'المعرف',
            'name' => 'الاسم',
            'created_at' => 'تاريخ الإنشاء',
            'updated_at' => 'تاريخ التحديث',
        ],
        'car' => [
            'id' => 'المعرف',
            'model' => 'الموديل',
            'brand' => 'العلامة التجارية',
            'country' => 'المدينة',
            'country_new' => 'المدينة الجديدة',
            'ownership' => 'الملكية',
            'type' => 'النوع',
            'chassis_number' => 'رقم الهيكل',
            'year_of_manufacturing_date' => 'سنة التصنيع',
            'fuel_type' => 'نوع الوقود',
            'license_plate_number' => 'رقم اللوحة',
            'license_plate_number_new' => 'رقم اللوحة الجديد',
            'petrol' => 'بنزين',
            'diesel' => 'ديزل',
            'electric' => 'كهربائي',
            'hybrid' => 'هجين',
            'plug_in_hybrid' => 'هجين قابل للشحن',
            'hydrogen' => 'هيدروجين',
            'lpg' => 'غاز البترول المسال',
            'cng' => 'الغاز الطبيعي المضغوط',
            'images' => 'الصور',
            'created_at' => 'تاريخ الإنشاء',
            'updated_at' => 'تاريخ التحديث',
        ],
        'model' => [
            'id' => 'المعرف',
            'brand_id' => 'معرف العلامة التجارية',
            'name' => 'الاسم',
            'brand' => 'العلامة التجارية',
            'created_at' => 'تاريخ الإنشاء',
            'updated_at' => 'تاريخ التحديث',
        ],
    ],
    'controller' => [
        'brands' => [
            'brands' => 'العلامات التجارية',
        ],
        'models' => [
            'models' => 'الموديلات',
        ],
        'cars' => [
            'cars' => 'السيارات',
        ],
        'countries' => [
            'countries' => 'المدن',
        ],
    ],
];