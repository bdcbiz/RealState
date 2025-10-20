<?php

return [
    'navigation' => [
        'label' => 'Units Availability',
        'group' => 'Reports',
    ],
    'model' => [
        'label' => 'Units Availability',
        'plural' => 'Units Availability',
    ],
    'fields' => [
        'unit_name' => 'Unit Name',
        'project' => 'Project',
        'usage_type' => 'Usage Type',
        'bua' => 'BUA (Built-Up Area)',
        'garden_area' => 'Garden Area',
        'roof_area' => 'Roof Area',
        'floor' => 'Floor',
        'no__of_bedrooms' => 'Bedrooms',
        'nominal_price' => 'Nominal Price',
        'created_at' => 'Created At',
        'updated_at' => 'Updated At',
    ],
    'filters' => [
        'project' => 'Project',
        'usage_type' => 'Usage Type',
    ],
    'actions' => [
        'export_excel' => 'Export to Excel',
        'print' => 'Print',
    ],
];
