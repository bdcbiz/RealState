<?php

return [
    'super_admins' => [
        'navigation' => [
            'label' => 'Super Admins',
            'group' => 'User Management',
        ],
        'model' => [
            'label' => 'Super Admin',
            'plural' => 'Super Admins',
        ],
        'fields' => [
            'name' => 'Full Name',
            'email' => 'Email Address',
            'phone' => 'Phone Number',
            'role' => 'Role',
            'email_verified_at' => 'Email Verified At',
            'password' => 'Password',
            'password_confirmation' => 'Confirm Password',
            'is_verified' => 'Verified',
            'is_banned' => 'Banned',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ],
    ],
    'sales_team' => [
        'navigation' => [
            'label' => 'All Sales Team',
            'group' => 'User Management',
        ],
        'model' => [
            'label' => 'Sales Person',
            'plural' => 'All Sales Team',
        ],
        'sections' => [
            'information' => 'Sales Person Information',
        ],
        'fields' => [
            'name' => 'Name',
            'email' => 'Email',
            'phone' => 'Phone',
            'company' => 'Company',
            'verified' => 'Verified',
            'created_at' => 'Created At',
        ],
    ],
];
