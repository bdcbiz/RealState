<?php

return [
    'navigation' => [
        'label' => 'حسابات الشركات',
        'group' => 'إدارة المستخدمين',
    ],
    'model' => [
        'label' => 'شركة',
        'plural' => 'حسابات الشركات',
    ],
    'fields' => [
        'name' => 'اسم الشركة',
        'email' => 'البريد الإلكتروني',
        'phone' => 'رقم الهاتف',
        'image' => 'شعار الشركة',
        'password' => 'كلمة المرور',
        'password_confirmation' => 'تأكيد كلمة المرور',
        'verified' => 'تم التحقق',
        'banned' => 'محظور',
        'email_verified_at' => 'تاريخ التحقق من البريد',
        'created_at' => 'تاريخ الإنشاء',
        'updated_at' => 'تاريخ التحديث',
    ],
];
