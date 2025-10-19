<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class UnitType extends Model
{
    use HasFactory;

    protected $table = 'unit_types';

    protected $fillable = [
        'name',
        'category',
        'description'
    ];

    /**
     * Get all units of this type
     */
    public function units()
    {
        return $this->hasMany(Unit::class, 'unit_type_id');
    }
}
