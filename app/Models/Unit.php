<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Unit extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'compound_id',
        'unit_name',
        'building_name',
        'unit_number',
        'code',
        'usage_type',
        'garden_area',
        'roof_area',
        'floor_number',
        'number_of_beds',
        'normal_price',
        'stage_number',
        'unit_type',
        'unit_code',
        'total_pricing',
        'total_finish_pricing',
        'unit_total_with_finish_price',
        'basement_area',
        'uncovered_basement',
        'penthouse',
        'semi_covered_roof_area',
        'garage_area',
        'pergola_area',
        'storage_area',
        'extra_built_up_area',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    public function compound()
    {
        return $this->belongsTo(Compound::class);
    }
}
