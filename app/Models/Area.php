<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Area extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'name_ar',
        'name_en',
        'slug',
        'city_id',
    ];

    protected $appends = ['name_localized'];

    /**
     * Get localized name based on current locale
     */
    public function getNameLocalizedAttribute()
    {
        $locale = app()->getLocale();

        if ($locale === 'ar') {
            return $this->name_ar ?? $this->name_en ?? $this->name;
        }

        return $this->name_en ?? $this->name;
    }

    /**
     * Get the city this area belongs to
     */
    public function city()
    {
        return $this->belongsTo(City::class);
    }

    /**
     * Get companies operating in this area
     */
    public function companies()
    {
        return $this->belongsToMany(Company::class);
    }

    /**
     * Boot the model
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($area) {
            if (empty($area->slug)) {
                $area->slug = Str::slug($area->name);
            }
        });
    }
}
