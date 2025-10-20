<?php

return [
    'navigation' => [
        'label' => 'Company Accounts',
        'group' => 'User Management',
    ],
    'model' => [
        'label' => 'Company',
        'plural' => 'Company Accounts',
    ],
    'fields' => [
        'name' => 'Company Name',
        'email' => 'Email Address',
        'phone' => 'Phone Number',
        'image' => 'Company Logo',
        'password' => 'Password',
        'password_confirmation' => 'Confirm Password',
        'verified' => 'Verified',
        'banned' => 'Banned',
        'email_verified_at' => 'Email Verified At',
        'created_at' => 'Created At',
        'updated_at' => 'Updated At',
    ],
];
