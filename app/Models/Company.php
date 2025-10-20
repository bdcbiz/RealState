<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;

class Company extends Authenticatable implements FilamentUser
{
    use HasFactory, Notifiable;

    protected $table = 'companies';

    protected $fillable = [
        'name',
        'name_en',
        'name_ar',
        'logo',
        'email',
        'password',
        'number_of_compounds',
        'number_of_available_units'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $appends = ['logo_url', 'name_translated', 'name_localized'];

    protected $casts = [
        'password' => 'hashed',
    ];

    public function canAccessPanel(Panel $panel): bool
    {
        // Only allow access to company panel
        return $panel->getId() === 'company';
    }

    /**
     * Get the logo URL
     */
    public function getLogoUrlAttribute()
    {
        if (!$this->logo) {
            return null;
        }

        $baseUrl = "http://127.0.0.1:8001/storage";

        // If the logo already has a full URL, return it
        if (strpos($this->logo, 'http://') === 0 || strpos($this->logo, 'https://') === 0) {
            return $this->logo;
        }

        // Otherwise construct the URL
        return $baseUrl . '/' . $this->logo;
    }

    /**
     * Get translated name object
     */
    public function getNameTranslatedAttribute()
    {
        return [
            'en' => $this->name_en ?? $this->name,
            'ar' => $this->name_ar ?? $this->name,
        ];
    }

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
     * Get all compounds belonging to this company
     */
    public function compounds()
    {
        return $this->hasMany(Compound::class);
    }

    /**
     * Get all users belonging to this company
     */
    public function users()
    {
        return $this->hasMany(User::class);
    }

    /**
     * Get all sales belonging to this company
     */
    public function sales()
    {
        return $this->hasMany(Sale::class);
    }
}
