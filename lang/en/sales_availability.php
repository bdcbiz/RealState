<?php

return [
    'navigation' => [
        'label' => 'Sales Availability',
        'group' => 'Reports',
    ],
    'model' => [
        'label' => 'Sales Availability',
        'plural' => 'Sales Availability',
    ],
    'fields' => [
        'project' => 'Project',
        'stage' => 'Stage',
        'category' => 'Category',
        'unit_type' => 'Unit Type',
        'unit_code' => 'Unit Code',
        'grand_total' => 'Grand Total',
        'total_finishing_price' => 'Total Finishing Price',
        'unit_total_with_finishing_price' => 'Unit Total with Finishing',
        'planned_delivery_date' => 'Planned Delivery Date',
        'actual_delivery_date' => 'Actual Delivery Date',
        'completion_progress' => 'Completion Progress',
        'land_area' => 'Land Area',
        'built_area' => 'Built Area',
        'basement_area' => 'Basement Area',
        'uncovered_basement_area' => 'Uncovered Basement Area',
        'penthouse_area' => 'Penthouse Area',
        'semi_covered_roof_area' => 'Semi Covered Roof Area',
        'roof_area' => 'Roof Area',
        'garden_outdoor_area' => 'Garden/Outdoor Area',
        'garage_area' => 'Garage Area',
        'pergola_area' => 'Pergola Area',
        'storage_area' => 'Storage Area',
        'extra_builtup_area' => 'BUA',
        'finishing_specs' => 'Finishing Specs',
        'club' => 'Club',
        'created_at' => 'Created At',
        'updated_at' => 'Updated At',
    ],
    'filters' => [
        'project' => 'Project',
        'category' => 'Category',
    ],
    'actions' => [
        'export_excel' => 'Export to Excel',
        'print' => 'Print',
    ],
];
