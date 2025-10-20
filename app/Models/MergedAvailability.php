<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Dummy model for MergedAvailability Filament Resource
 * This doesn't represent a real database table - it's used for the merged query in the resource
 */
class MergedAvailability extends Model
{
    // This model doesn't have a real table - queries are handled in the Filament resource
    protected $table = 'merged_availability';

    // Disable timestamps since this is a virtual model
    public $timestamps = false;

    // All attributes are guarded since this is read-only
    protected $guarded = ['*'];
}
