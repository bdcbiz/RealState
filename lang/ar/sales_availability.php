<?php

return [
    'navigation' => [
        'label' => 'توافر المبيعات',
        'group' => 'التقارير',
    ],
    'model' => [
        'label' => 'توافر المبيعات',
        'plural' => 'توافر المبيعات',
    ],
    'fields' => [
        'project' => 'المشروع',
        'stage' => 'المرحلة',
        'category' => 'الفئة',
        'unit_type' => 'نوع الوحدة',
        'unit_code' => 'كود الوحدة',
        'grand_total' => 'الإجمالي الكلي',
        'total_finishing_price' => 'سعر التشطيب الكلي',
        'unit_total_with_finishing_price' => 'إجمالي الوحدة مع التشطيب',
        'planned_delivery_date' => 'تاريخ التسليم المخطط',
        'actual_delivery_date' => 'تاريخ التسليم الفعلي',
        'completion_progress' => 'نسبة الإنجاز',
        'land_area' => 'مساحة الأرض',
        'built_area' => 'المساحة المبنية',
        'basement_area' => 'مساحة القبو',
        'uncovered_basement_area' => 'مساحة القبو المكشوف',
        'penthouse_area' => 'مساحة البنتهاوس',
        'semi_covered_roof_area' => 'مساحة السطح شبه المغطى',
        'roof_area' => 'مساحة السطح',
        'garden_outdoor_area' => 'مساحة الحديقة/الخارجية',
        'garage_area' => 'مساحة الجراج',
        'pergola_area' => 'مساحة البرجولا',
        'storage_area' => 'مساحة التخزين',
        'extra_builtup_area' => 'المساحة الإجمالية',
        'finishing_specs' => 'مواصفات التشطيب',
        'club' => 'النادي',
        'created_at' => 'تاريخ الإنشاء',
        'updated_at' => 'تاريخ التحديث',
    ],
    'filters' => [
        'project' => 'المشروع',
        'category' => 'الفئة',
    ],
    'actions' => [
        'export_excel' => 'تصدير إلى Excel',
        'print' => 'طباعة',
    ],
];
