<?php

return [
    'navigation' => [
        'label' => 'الشركات',
        'group' => 'إدارة العقارات',
    ],
    'model' => [
        'label' => 'شركة',
        'plural' => 'الشركات',
    ],
    'sections' => [
        'sales_team' => 'فريق المبيعات',
        'compounds' => 'المجمعات السكنية',
    ],
    'fields' => [
        'name' => 'اسم الشركة',
        'logo' => 'شعار الشركة',
        'number_of_compounds' => 'عدد المجمعات',
        'number_of_available_units' => 'عدد الوحدات المتاحة',
        'sales_team' => 'فريق المبيعات',
        'compounds' => 'المجمعات',
        'created_at' => 'تاريخ الإنشاء',
        'updated_at' => 'تاريخ التحديث',
    ],
    'filters' => [
        'has_sales_team' => 'لديها فريق مبيعات',
        'has_compounds' => 'لديها مجمعات',
        'compounds_range' => 'نطاق المجمعات',
    ],
    'messages' => [
        'save_first_sales' => 'احفظ الشركة أولاً لرؤية أعضاء فريق المبيعات.',
        'no_sales_members' => 'لا يوجد أعضاء في فريق المبيعات حتى الآن.',
        'save_first_compounds' => 'احفظ الشركة أولاً لرؤية المجمعات.',
        'no_compounds' => 'لا توجد مجمعات مسجلة حتى الآن.',
        'phone' => 'الهاتف',
        'location' => 'الموقع',
        'units' => 'الوحدات',
    ],
];
