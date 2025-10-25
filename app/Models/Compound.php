<?php

namespace App\Models;

use App\Traits\HasTranslations;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Compound extends Model
{
    use SoftDeletes, HasTranslations;

    protected $fillable = [
        'project',
        'project_en',
        'project_ar',
        'company_id',
        'current_sale_id',
        'sales_person_id',
        'location',
        'location_en',
        'location_ar',
        'location_url',
        'images',
        'built_up_area',
        'how_many_floors',
        'planned_delivery_date',
        'actual_delivery_date',
        'completion_progress',
        'land_area',
        'built_area',
        'finish_specs',
        'club',
        'is_sold',
        'status',
        'delivered_at',
        'total_units',
        'name',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'planned_delivery_date' => 'date',
        'actual_delivery_date' => 'date',
        'club' => 'boolean',
        'is_sold' => 'boolean',
        'delivered_at' => 'date',
        'images' => 'array',
    ];

    protected $appends = ['images_urls', 'project_translated', 'location_translated', 'project_localized', 'location_localized', 'status_localized'];

    /**
     * Boot the model and add event listeners
     */
    protected static function boot()
    {
        parent::boot();

        // Log activity when compound is created
        static::created(function ($compound) {
            Activity::log(
                'created',
                $compound,
                "New compound '{$compound->project}' created",
                [
                    'location' => $compound->location,
                    'total_units' => $compound->total_units,
                ],
                null,
                $compound->company_id
            );
        });

        // Log activity when compound is updated
        static::updated(function ($compound) {
            $changes = $compound->getChanges();
            unset($changes['updated_at']);

            if (!empty($changes)) {
                Activity::log(
                    'updated',
                    $compound,
                    "Compound '{$compound->project}' was updated",
                    [
                        'changes' => $changes,
                    ],
                    null,
                    $compound->company_id
                );
            }
        });

        // Log activity when compound is deleted
        static::deleted(function ($compound) {
            Activity::log(
                'deleted',
                $compound,
                "Compound '{$compound->project}' was deleted",
                [
                    'project' => $compound->project,
                ],
                null,
                $compound->company_id
            );
        });
    }

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
     * Get translated project name
     */
    public function getProjectTranslatedAttribute()
    {
        return $this->getTranslation('project');
    }

    /**
     * Get translated location
     */
    public function getLocationTranslatedAttribute()
    {
        return $this->getTranslation('location');
    }

    /**
     * Get localized project name based on current locale
     */
    public function getProjectLocalizedAttribute()
    {
        return $this->getLocalized('project');
    }

    /**
     * Get localized location based on current locale
     */
    public function getLocationLocalizedAttribute()
    {
        return $this->getLocalized('location');
    }

    /**
     * Get localized status based on current locale
     */
    public function getStatusLocalizedAttribute()
    {
        $locale = app()->getLocale();

        $statusTranslations = [
            'in_progress' => [
                'en' => 'In Progress',
                'ar' => 'قيد التنفيذ',
            ],
            'inhabited' => [
                'en' => 'Inhabited',
                'ar' => 'مأهول',
            ],
            'delivered' => [
                'en' => 'Delivered',
                'ar' => 'تم التسليم',
            ],
        ];

        $status = $this->status ?? 'in_progress';

        return $statusTranslations[$status][$locale] ?? $status;
    }

    public function units()
    {
        return $this->hasMany(Unit::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Get the current active sale for this compound
     */
    public function currentSale()
    {
        return $this->belongsTo(Sale::class, 'current_sale_id');
    }

    /**
     * Get all sales for this compound
     */
    public function sales()
    {
        return $this->hasMany(Sale::class);
    }

    /**
     * Get the sales person assigned to this compound
     */
    public function salesPerson()
    {
        return $this->belongsTo(User::class, 'sales_person_id');
    }
}
