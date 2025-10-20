<?php

return [
    'navigation' => [
        'label' => 'المستخدمين',
        'group' => 'إدارة المستخدمين',
    ],
    'model' => [
        'label' => 'مستخدم',
        'plural' => 'المستخدمين',
    ],
    'fields' => [
        'name' => 'الاسم الكامل',
        'email' => 'البريد الإلكتروني',
        'phone' => 'رقم الهاتف',
        'role' => 'الدور',
        'image' => 'الصورة الشخصية',
        'password' => 'كلمة المرور',
        'password_confirmation' => 'تأكيد كلمة المرور',
        'verified' => 'تم التحقق',
        'banned' => 'محظور',
        'email_verified_at' => 'تاريخ التحقق من البريد',
        'created_at' => 'تاريخ الإنشاء',
        'updated_at' => 'تاريخ التحديث',
    ],
    'roles' => [
        'buyer' => 'مشتري',
        'sales' => 'مبيعات',
        'admin' => 'مدير',
        'company' => 'شركة',
    ],
];
