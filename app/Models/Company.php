<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    protected $fillable = [
        'name',
        'number_of_compounds',
        'number_of_available_units',
    ];

    public function compounds()
    {
        return $this->hasMany(Compound::class);
    }
}
