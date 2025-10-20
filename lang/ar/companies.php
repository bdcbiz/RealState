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
        'basic_info' => 'المعلومات الأساسية',
        'contact_info' => 'معلومات التواصل',
        'statistics' => 'الإحصائيات',
        'statistics_description' => 'هذه القيم محسوبة تلقائياً من قاعدة البيانات',
        'sales_team' => 'فريق المبيعات',
        'compounds' => 'المجمعات السكنية',
    ],
    'fields' => [
        'name' => 'اسم الشركة',
        'name_ar' => 'اسم الشركة بالعربي',
        'logo' => 'شعار الشركة',
        'website' => 'الموقع الإلكتروني',
        'headquarters' => 'المقر الرئيسي',
        'phone' => 'رقم الهاتف',
        'email' => 'البريد الإلكتروني',
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
    'widgets' => [
        'compounds' => 'المجمعات',
        'total_compounds' => 'إجمالي المجمعات السكنية',
        'available_units' => 'الوحدات المتاحة',
        'units_for_sale' => 'الوحدات المتاحة للبيع',
        'sold_units' => 'الوحدات المباعة',
        'total_sold' => 'إجمالي الوحدات المباعة',
        'sales_team' => 'فريق المبيعات',
        'team_members' => 'إجمالي أعضاء الفريق',
    ],
];
