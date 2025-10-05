<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UnitsAvailability extends Model
{
    protected $table = 'units_availability';

    protected $fillable = [
        'unit_name',
        'project',
        'usage_type',
        'bua',
        'garden_area',
        'roof_area',
        'floor',
        'no__of_bedrooms',
        'nominal_price',
    ];
}
