<?php

return [
    'navigation' => [
        'label' => 'توافر الوحدات',
        'group' => 'التقارير',
    ],
    'model' => [
        'label' => 'توافر الوحدات',
        'plural' => 'توافر الوحدات',
    ],
    'fields' => [
        'unit_name' => 'اسم الوحدة',
        'project' => 'المشروع',
        'usage_type' => 'نوع الاستخدام',
        'bua' => 'المساحة المبنية',
        'garden_area' => 'مساحة الحديقة',
        'roof_area' => 'مساحة السطح',
        'floor' => 'الطابق',
        'no__of_bedrooms' => 'عدد غرف النوم',
        'nominal_price' => 'السعر الاسمي',
        'created_at' => 'تاريخ الإنشاء',
        'updated_at' => 'تاريخ التحديث',
    ],
    'filters' => [
        'project' => 'المشروع',
        'usage_type' => 'نوع الاستخدام',
    ],
    'actions' => [
        'export_excel' => 'تصدير إلى Excel',
        'print' => 'طباعة',
    ],
];
