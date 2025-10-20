<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class UnitArea extends Model
{
    use HasFactory;

    protected $table = 'unit_areas';

    protected $fillable = [
        'unit_id',
        'built_area',
        'land_area',
        'total_area',
        'basement_area',
        'uncovered_basement',
        'penthouse_area',
        'semi_covered_roof',
        'roof_area',
        'garage_area',
        'pergola_area',
        'storage_area',
        'garden_area',
        'extra_built_up'
    ];

    protected $casts = [
        'built_area' => 'float',
        'land_area' => 'float',
        'total_area' => 'float',
        'basement_area' => 'float',
        'roof_area' => 'float',
        'garage_area' => 'float',
        'garden_area' => 'float'
    ];

    /**
     * Get the unit that owns the area
     */
    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }
}
