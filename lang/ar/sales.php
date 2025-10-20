<?php

return [
    'navigation' => [
        'label' => 'جميع الخصومات',
        'group' => 'التسويق',
    ],
    'model' => [
        'label' => 'خصم',
        'plural' => 'جميع الخصومات',
    ],
    'sections' => [
        'sale_information' => 'معلومات الخصم',
        'pricing' => 'التسعير',
        'duration' => 'المدة',
    ],
    'fields' => [
        'company' => 'الشركة',
        'sales_person' => 'موظف المبيعات',
        'sale_name' => 'اسم الخصم',
        'description' => 'الوصف',
        'sale_type' => 'نوع الخصم',
        'unit' => 'الوحدة',
        'compound' => 'المجمع',
        'discount_percentage' => 'نسبة الخصم',
        'old_price' => 'السعر القديم',
        'new_price' => 'السعر الجديد',
        'start_date' => 'تاريخ البداية',
        'end_date' => 'تاريخ النهاية',
        'is_active' => 'نشط',
        'created_at' => 'تاريخ الإنشاء',
    ],
    'types' => [
        'unit' => 'وحدة',
        'compound' => 'مجمع',
    ],
    'filters' => [
        'company' => 'الشركة',
        'sale_type' => 'نوع الخصم',
        'is_active' => 'نشط',
    ],
];
