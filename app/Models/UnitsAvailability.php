<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Model for Units Availability
 * Represents units availability data in the database
 */
class UnitsAvailability extends Model
{
    protected $table = 'units_availability';

    public $timestamps = false;

    protected $guarded = [];
}
