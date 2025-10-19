<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class FinishSpec extends Model
{
    use HasFactory;

    protected $table = 'finish_specs';

    protected $fillable = [
        'name',
        'description',
        'base_price'
    ];

    protected $casts = [
        'base_price' => 'decimal:2',
    ];
}
