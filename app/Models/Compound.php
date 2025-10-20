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
     * Get processed image URLs
     */
    public function getImagesUrlsAttribute()
    {
        $baseUrl = "http://127.0.0.1:8001/storage";
        $images = [];

        if ($this->images && is_array($this->images)) {
            foreach ($this->images as $img) {
                if (strpos($img, 'http://') === 0 || strpos($img, 'https://') === 0) {
                    $images[] = $img;
                } else {
                    $images[] = $baseUrl . '/' . $img;
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
}
