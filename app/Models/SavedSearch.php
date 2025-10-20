<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SavedSearch extends Model
{
    use HasFactory;

    protected $table = 'saved_searches';

    protected $fillable = [
        'user_id',
        'name',
        'search_parameters',
        'email_notifications_enabled'
    ];

    protected $casts = [
        'search_parameters' => 'array',
        'email_notifications_enabled' => 'boolean'
    ];

    /**
     * Get the user that owns the saved search
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
