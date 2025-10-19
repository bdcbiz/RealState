<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Stage extends Model
{
    use HasFactory;

    protected $table = 'stages';

    protected $fillable = [
        'compound_id',
        'stage_name',
        'completion_progress'
    ];

    protected $casts = [
        'completion_progress' => 'integer',
    ];

    /**
     * Get the compound this stage belongs to
     */
    public function compound()
    {
        return $this->belongsTo(Compound::class);
    }

    /**
     * Get all units in this stage
     */
    public function units()
    {
        return $this->hasMany(Unit::class);
    }
}
