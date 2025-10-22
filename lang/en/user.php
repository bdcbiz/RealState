<?php

return [
    'navigation' => [
        'label' => 'Users',
        'group' => 'User Management',
    ],
    'model' => [
        'label' => 'User',
        'plural' => 'Users',
    ],
    'fields' => [
        'name' => 'Full Name',
        'email' => 'Email Address',
        'phone' => 'Phone Number',
        'role' => 'Role',
        'company' => 'Company',
        'image' => 'Profile Image',
        'password' => 'Password',
        'password_confirmation' => 'Confirm Password',
        'verified' => 'Verified',
        'banned' => 'Banned',
        'email_verified_at' => 'Email Verified At',
        'created_at' => 'Created At',
        'updated_at' => 'Updated At',
    ],
    'roles' => [
        'buyer' => 'Buyer',
        'sales' => 'Sales',
        'admin' => 'Admin',
        'company' => 'Company',
    ],
];
