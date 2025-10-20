<?php

return [
    'navigation' => [
        'label' => 'المبيعات',
        'group' => 'إدارة المستخدمين',
    ],
    'model' => [
        'label' => 'موظف مبيعات',
        'plural' => 'موظفي المبيعات',
    ],
    'fields' => [
        'name' => 'الاسم الكامل',
        'email' => 'البريد الإلكتروني',
        'phone' => 'رقم الهاتف',
        'company' => 'الشركة',
        'image' => 'الصورة الشخصية',
        'password' => 'كلمة المرور',
        'password_confirmation' => 'تأكيد كلمة المرور',
        'verified' => 'تم التحقق',
        'banned' => 'محظور',
        'created_at' => 'تاريخ الإنشاء',
        'updated_at' => 'تاريخ التحديث',
    ],
    'filters' => [
        'company' => 'الشركة',
        'verified' => 'تم التحقق',
        'banned' => 'محظور',
    ],
];
