<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalesAvailability extends Model
{
    protected $table = 'sales_availability';

    protected $fillable = [
        'project',
        'stage',
        'category',
        'unit_type',
        'unit_code',
        'grand_total',
        'total_finishing_price',
        'unit_total_with_finishing_price',
        'planned_delivery_date',
        'actual_delivery_date',
        'completion_progress',
        'land_area',
        'built_area',
        'basement_area',
        'uncovered_basement_area',
        'penthouse_area',
        'semi_covered_roof_area',
        'roof_area',
        'garden_outdoor_area',
        'garage_area',
        'pergola_area',
        'storage_area',
        'extra_builtup_area',
        'finishing_specs',
        'club',
    ];
}
