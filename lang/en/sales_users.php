<?php

return [
    'navigation' => [
        'label' => 'Sales',
        'group' => 'User Management',
    ],
    'model' => [
        'label' => 'Sales User',
        'plural' => 'Sales Users',
    ],
    'fields' => [
        'name' => 'Full Name',
        'email' => 'Email Address',
        'phone' => 'Phone Number',
        'company' => 'Company',
        'image' => 'Profile Image',
        'password' => 'Password',
        'password_confirmation' => 'Confirm Password',
        'verified' => 'Verified',
        'banned' => 'Banned',
        'created_at' => 'Created At',
        'updated_at' => 'Updated At',
    ],
    'filters' => [
        'company' => 'Company',
        'verified' => 'Verified',
        'banned' => 'Banned',
    ],
];
