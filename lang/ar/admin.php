<?php

return [
    'super_admins' => [
        'navigation' => [
            'label' => 'المدراء الرئيسيين',
            'group' => 'إدارة المستخدمين',
        ],
        'model' => [
            'label' => 'مدير رئيسي',
            'plural' => 'المدراء الرئيسيين',
        ],
        'fields' => [
            'name' => 'الاسم الكامل',
            'email' => 'البريد الإلكتروني',
            'phone' => 'رقم الهاتف',
            'role' => 'الدور',
            'email_verified_at' => 'تاريخ التحقق من البريد',
            'password' => 'كلمة المرور',
            'password_confirmation' => 'تأكيد كلمة المرور',
            'is_verified' => 'تم التحقق',
            'is_banned' => 'محظور',
            'created_at' => 'تاريخ الإنشاء',
            'updated_at' => 'تاريخ التحديث',
        ],
    ],
    'sales_team' => [
        'navigation' => [
            'label' => 'جميع فريق المبيعات',
            'group' => 'إدارة المستخدمين',
        ],
        'model' => [
            'label' => 'موظف مبيعات',
            'plural' => 'جميع فريق المبيعات',
        ],
        'sections' => [
            'information' => 'معلومات موظف المبيعات',
        ],
        'fields' => [
            'name' => 'الاسم',
            'email' => 'البريد الإلكتروني',
            'phone' => 'الهاتف',
            'company' => 'الشركة',
            'verified' => 'تم التحقق',
            'created_at' => 'تاريخ الإنشاء',
        ],
    ],
];
