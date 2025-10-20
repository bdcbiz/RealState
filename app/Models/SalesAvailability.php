<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Model for Sales Availability
 * Represents sales availability data in the database
 */
class SalesAvailability extends Model
{
    protected $table = 'sales_availability';

    public $timestamps = false;

    protected $guarded = [];
}
