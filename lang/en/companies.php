<?php

return [
    'navigation' => [
        'label' => 'Companies',
        'group' => 'Real Estate Management',
    ],
    'model' => [
        'label' => 'Company',
        'plural' => 'Companies',
    ],
    'sections' => [
        'basic_info' => 'Basic Information',
        'contact_info' => 'Contact Information',
        'statistics' => 'Statistics',
        'statistics_description' => 'These values are automatically calculated from the database',
        'sales_team' => 'Sales Team',
        'compounds' => 'Compounds',
    ],
    'fields' => [
        'name' => 'Company Name',
        'name_ar' => 'Company Name (Arabic)',
        'logo' => 'Company Logo',
        'website' => 'Website',
        'headquarters' => 'Headquarters',
        'phone' => 'Phone Number',
        'email' => 'Email',
        'number_of_compounds' => 'Number of Compounds',
        'number_of_available_units' => 'Number of Available Units',
        'sales_team' => 'Sales Team',
        'compounds' => 'Compounds',
        'created_at' => 'Created At',
        'updated_at' => 'Updated At',
    ],
    'filters' => [
        'has_sales_team' => 'Has Sales Team',
        'has_compounds' => 'Has Compounds',
        'compounds_range' => 'Compounds Range',
    ],
    'messages' => [
        'save_first_sales' => 'Save the company first to see sales team members.',
        'no_sales_members' => 'No sales team members assigned yet.',
        'save_first_compounds' => 'Save the company first to see compounds.',
        'no_compounds' => 'No compounds assigned yet.',
        'phone' => 'Phone',
        'location' => 'Location',
        'units' => 'Units',
    ],
    'widgets' => [
        'compounds' => 'Compounds',
        'total_compounds' => 'Total residential compounds',
        'available_units' => 'Available Units',
        'units_for_sale' => 'Units available for sale',
        'sold_units' => 'Sold Units',
        'total_sold' => 'Total units sold',
        'sales_team' => 'Sales Team',
        'team_members' => 'Total team members',
    ],
];
