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
        'images',
        'start_date',
        'end_date',
        'is_active'
    ];

    protected $casts = [
        'discount_percentage' => 'float',
        'old_price' => 'decimal:2',
        'new_price' => 'decimal:2',
        'images' => 'array',
        'is_active' => 'boolean',
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    /**
     * Boot the model and add event listeners
     */
    protected static function boot()
    {
        parent::boot();

        // Auto-calculate prices and populate images before saving
        static::saving(function ($sale) {
            // If old_price is set and discount_percentage exists, calculate new_price
            if ($sale->old_price && $sale->discount_percentage) {
                $sale->new_price = $sale->old_price - ($sale->old_price * $sale->discount_percentage / 100);
            }

            // If it's a unit sale and old_price is not set, try to get it from the unit
            if ($sale->sale_type === 'unit' && $sale->unit_id && !$sale->old_price) {
                $unit = Unit::find($sale->unit_id);
                if ($unit) {
                    $unitPrice = $unit->normal_price ?? $unit->unit_total_with_finish_price;
                    if ($unitPrice) {
                        $sale->old_price = $unitPrice;
                        if ($sale->discount_percentage) {
                            $sale->new_price = $unitPrice - ($unitPrice * $sale->discount_percentage / 100);
                        }
                    }
                }
            }

            // Auto-populate images from unit or compound
            if (!$sale->images) {
                $images = null;

                if ($sale->sale_type === 'unit' && $sale->unit_id) {
                    $unit = Unit::find($sale->unit_id);
                    if ($unit && $unit->images) {
                        $images = $unit->images;
                    }
                    // If unit has no images, try to get from its compound
                    if (!$images && $unit && $unit->compound_id) {
                        $compound = Compound::find($unit->compound_id);
                        if ($compound && $compound->images) {
                            $images = $compound->images;
                        }
                    }
                } elseif ($sale->sale_type === 'compound' && $sale->compound_id) {
                    $compound = Compound::find($sale->compound_id);
                    if ($compound && $compound->images) {
                        $images = $compound->images;
                    }
                }

                if ($images) {
                    $sale->images = $images;
                }
            }
        });

        // Update compound after sale is saved
        static::saved(function ($sale) {
            // Get the compound ID - either directly or through unit
            $compoundId = null;

            if ($sale->sale_type === 'compound' && $sale->compound_id) {
                $compoundId = $sale->compound_id;
            } elseif ($sale->sale_type === 'unit' && $sale->unit_id) {
                $unit = Unit::find($sale->unit_id);
                if ($unit) {
                    $compoundId = $unit->compound_id;
                }
            }

            // Update the compound with sale information
            if ($compoundId) {
                Compound::where('id', $compoundId)->update([
                    'current_sale_id' => $sale->id,
                    'sales_person_id' => $sale->sales_person_id,
                ]);
            }
        });

        // Log activity when sale is created
        static::created(function ($sale) {
            Activity::log(
                'created',
                $sale,
                "New sale '{$sale->sale_name}' created",
                [
                    'sale_type' => $sale->sale_type,
                    'discount' => $sale->discount_percentage,
                    'old_price' => $sale->old_price,
                    'new_price' => $sale->new_price,
                ],
                null,
                $sale->company_id
            );
        });

        // Log activity when sale is updated
        static::updated(function ($sale) {
            $changes = $sale->getChanges();
            unset($changes['updated_at']);

            if (!empty($changes)) {
                Activity::log(
                    'updated',
                    $sale,
                    "Sale '{$sale->sale_name}' was updated",
                    [
                        'changes' => $changes,
                        'old_values' => $sale->getOriginal(),
                    ],
                    null,
                    $sale->company_id
                );
            }
        });

        // Clear compound sale reference and log activity when sale is deleted
        static::deleted(function ($sale) {
            // Clear compound reference
            $compoundId = null;

            if ($sale->sale_type === 'compound' && $sale->compound_id) {
                $compoundId = $sale->compound_id;
            } elseif ($sale->sale_type === 'unit' && $sale->unit_id) {
                $unit = Unit::find($sale->unit_id);
                if ($unit) {
                    $compoundId = $unit->compound_id;
                }
            }

            if ($compoundId) {
                $compound = Compound::find($compoundId);
                if ($compound && $compound->current_sale_id === $sale->id) {
                    $compound->update([
                        'current_sale_id' => null,
                        'sales_person_id' => null,
                    ]);
                }
            }

            // Log activity
            Activity::log(
                'deleted',
                $sale,
                "Sale '{$sale->sale_name}' was deleted",
                [
                    'sale_type' => $sale->sale_type,
                    'discount' => $sale->discount_percentage,
                ],
                null,
                $sale->company_id
            );
        });
    }

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
