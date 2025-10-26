<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class UserHistory extends Model
{
    use HasFactory;

    protected $table = 'user_history';

    protected $fillable = [
        'user_id',
        'action_type',
        'unit_id',
        'compound_id',
        'search_query',
        'metadata'
    ];

    protected $casts = [
        'metadata' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * Get the user that owns the history entry
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the unit associated with the history entry
     */
    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    /**
     * Get the compound associated with the history entry
     */
    public function compound()
    {
        return $this->belongsTo(Compound::class);
    }
}
