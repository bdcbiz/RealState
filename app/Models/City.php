<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class City extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'name_ar',
        'name_en',
        'slug',
        'governorate',
        'governorate_ar',
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
     * Get all areas in this city
     */
    public function areas()
    {
        return $this->hasMany(Area::class);
    }

    /**
     * Boot the model
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($city) {
            if (empty($city->slug)) {
                $city->slug = Str::slug($city->name);
            }
        });

        static::updating(function ($city) {
            if (empty($city->slug)) {
                $city->slug = Str::slug($city->name);
            }
        });
    }
}
