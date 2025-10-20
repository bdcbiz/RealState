<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Sale extends Model
{
    use HasFactory;

    protected $table = 'sales';

    protected $fillable = [
        'company_id',
        'sales_person_id',
        'sale_type',
        'unit_id',
        'compound_id',
        'sale_name',
        'description',
        'discount_percentage',
        'old_price',
        'new_price',
        'start_date',
        'end_date',
        'is_active'
    ];

    protected $casts = [
        'discount_percentage' => 'float',
        'old_price' => 'decimal:2',
        'new_price' => 'decimal:2',
        'is_active' => 'boolean',
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    /**
     * Get the company that owns the sale
     */
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Get the sales person
     */
    public function salesPerson()
    {
        return $this->belongsTo(User::class, 'sales_person_id');
    }

    /**
     * Get the unit if sale_type is 'unit'
     */
    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    /**
     * Get the compound if sale_type is 'compound'
     */
    public function compound()
    {
        return $this->belongsTo(Compound::class);
    }
}
