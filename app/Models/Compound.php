<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Compound extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'project',
        'company_id',
        'location',
        'built_up_area',
        'how_many_floors',
        'planned_delivery_date',
        'actual_delivery_date',
        'completion_progress',
        'land_area',
        'built_area',
        'finish_specs',
        'club',
        'is_sold',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'planned_delivery_date' => 'date',
        'actual_delivery_date' => 'date',
        'club' => 'boolean',
        'is_sold' => 'boolean',
    ];

    public function units()
    {
        return $this->hasMany(Unit::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}
