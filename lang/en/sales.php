<?php

return [
    'navigation' => [
        'label' => 'All Discounts',
        'group' => 'Marketing',
    ],
    'model' => [
        'label' => 'Discount',
        'plural' => 'All Discounts',
    ],
    'sections' => [
        'sale_information' => 'Sale Information',
        'pricing' => 'Pricing',
        'duration' => 'Duration',
    ],
    'fields' => [
        'company' => 'Company',
        'sales_person' => 'Sales Person',
        'sale_name' => 'Sale Name',
        'description' => 'Description',
        'sale_type' => 'Sale Type',
        'unit' => 'Unit',
        'compound' => 'Compound',
        'discount_percentage' => 'Discount Percentage',
        'old_price' => 'Old Price',
        'new_price' => 'New Price',
        'start_date' => 'Start Date',
        'end_date' => 'End Date',
        'is_active' => 'Active',
        'created_at' => 'Created At',
    ],
    'types' => [
        'unit' => 'Unit',
        'compound' => 'Compound',
    ],
    'filters' => [
        'company' => 'Company',
        'sale_type' => 'Sale Type',
        'is_active' => 'Active',
    ],
];
