<?php

namespace App\Models;

use App\Traits\HasTranslations;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Unit extends Model
{
    use HasFactory, HasTranslations;

    protected $table = 'units';

    /**
     * Boot the model and add event listeners
     */
    protected static function boot()
    {
        parent::boot();

        // Automatically mark unit as sold when a buyer is assigned
        static::saving(function ($unit) {
            if ($unit->isDirty('buyer_id')) {
                if ($unit->buyer_id !== null) {
                    $unit->is_sold = true;
                } else {
                    $unit->is_sold = false;
                }
            }
        });

        // Log activity when unit is created
        static::created(function ($unit) {
            $compound = $unit->compound;
            Activity::log(
                'created',
                $unit,
                "New unit '{$unit->unit_code}' created" . ($compound ? " in {$compound->project}" : ""),
                [
                    'unit_type' => $unit->unit_type,
                    'usage_type' => $unit->usage_type,
                    'normal_price' => $unit->normal_price,
                ],
                null,
                $compound->company_id ?? null
            );
        });

        // Log activity when unit is updated
        static::updated(function ($unit) {
            $changes = $unit->getChanges();
            unset($changes['updated_at']);

            if (!empty($changes)) {
                $compound = $unit->compound;
                Activity::log(
                    'updated',
                    $unit,
                    "Unit '{$unit->unit_code}' was updated",
                    [
                        'changes' => $changes,
                    ],
                    null,
                    $compound->company_id ?? null
                );
            }
        });

        // Log activity when unit is deleted
        static::deleted(function ($unit) {
            $compound = $unit->compound;
            Activity::log(
                'deleted',
                $unit,
                "Unit '{$unit->unit_code}' was deleted",
                [
                    'unit_code' => $unit->unit_code,
                ],
                null,
                $compound->company_id ?? null
            );
        });
    }

    protected $fillable = [
        'compound_id',
        'stage_id',
        'unit_code',
        'unit_name',
        'unit_name_en',
        'unit_name_ar',
        'unit_type',
        'unit_type_en',
        'unit_type_ar',
        'usage_type',
        'usage_type_en',
        'usage_type_ar',
        'status',
        'status_en',
        'status_ar',
        'base_price',
        'total_price',
        'normal_price',
        'number_of_beds',
        'is_sold',
        'available',
        'images',
        'sales_id',
        'buyer_id'
    ];

    protected $casts = [
        'is_sold' => 'boolean',
        'base_price' => 'decimal:2',
        'total_price' => 'decimal:2',
        'images' => 'array',
    ];

    protected $appends = ['images_urls', 'unit_name_translated', 'unit_type_translated', 'usage_type_translated', 'status_translated', 'unit_name_localized', 'unit_type_localized', 'usage_type_localized', 'status_localized'];

    /**
     * Get processed image URLs
     */
    public function getImagesUrlsAttribute()
    {
        $images = [];

        if ($this->images && is_array($this->images)) {
            foreach ($this->images as $img) {
                // If already a full URL, return as is
                if (strpos($img, 'http://') === 0 || strpos($img, 'https://') === 0) {
                    $images[] = $img;
                } else {
                    // Use url() helper like Company logo - automatically uses APP_URL
                    $images[] = url('/storage/' . ltrim($img, '/'));
                }
            }
        }

        return $images;
    }

    /**
     * Get translated unit name
     */
    public function getUnitNameTranslatedAttribute()
    {
        return $this->getTranslation('unit_name');
    }

    /**
     * Get translated unit type
     */
    public function getUnitTypeTranslatedAttribute()
    {
        return $this->getTranslation('unit_type');
    }

    /**
     * Get translated usage type
     */
    public function getUsageTypeTranslatedAttribute()
    {
        return $this->getTranslation('usage_type');
    }

    /**
     * Get translated status
     */
    public function getStatusTranslatedAttribute()
    {
        return $this->getTranslation('status');
    }

    /**
     * Get localized unit name based on current locale
     */
    public function getUnitNameLocalizedAttribute()
    {
        return $this->getLocalized('unit_name');
    }

    /**
     * Get localized unit type based on current locale
     */
    public function getUnitTypeLocalizedAttribute()
    {
        return $this->getLocalized('unit_type');
    }

    /**
     * Get localized usage type based on current locale
     */
    public function getUsageTypeLocalizedAttribute()
    {
        return $this->getLocalized('usage_type');
    }

    /**
     * Get localized status based on current locale
     */
    public function getStatusLocalizedAttribute()
    {
        return $this->getLocalized('status');
    }

    /**
     * Get the compound this unit belongs to
     */
    public function compound()
    {
        return $this->belongsTo(Compound::class);
    }

    /**
     * Get the stage this unit belongs to
     */
    public function stage()
    {
        return $this->belongsTo(Stage::class);
    }

    /**
     * Get the sales person for this unit
     */
    public function sales()
    {
        return $this->belongsTo(User::class, 'sales_id');
    }

    /**
     * Get the buyer for this unit
     */
    public function buyer()
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }
}
