<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Activity extends Model
{
    use HasFactory;

    protected $table = 'activities';

    protected $fillable = [
        'company_id',
        'user_id',
        'subject_type',
        'subject_id',
        'action',
        'description',
        'properties',
    ];

    protected $casts = [
        'properties' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the company associated with the activity
     */
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Get the user who performed the activity
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the subject (polymorphic relation)
     */
    public function subject()
    {
        return $this->morphTo();
    }

    /**
     * Scope to get activities for a specific company
     */
    public function scopeForCompany($query, $companyId)
    {
        return $query->where('company_id', $companyId);
    }

    /**
     * Scope to get activities by action
     */
    public function scopeByAction($query, $action)
    {
        return $query->where('action', $action);
    }

    /**
     * Scope to get recent activities
     */
    public function scopeRecent($query, $days = 7)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    /**
     * Helper method to log an activity
     */
    public static function log($action, $subject, $description = null, $properties = null, $userId = null, $companyId = null)
    {
        return static::create([
            'company_id' => $companyId ?? ($subject->company_id ?? null),
            'user_id' => $userId ?? auth()->id(),
            'subject_type' => get_class($subject),
            'subject_id' => $subject->id,
            'action' => $action,
            'description' => $description,
            'properties' => $properties,
        ]);
    }
}
