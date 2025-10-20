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
        'developer_areas',
        'website',
        'headquarters',
        'phone',
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
        'developer_areas' => 'array',
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

        // If the logo already has a full URL, return it
        if (strpos($this->logo, 'http://') === 0 || strpos($this->logo, 'https://') === 0) {
            return $this->logo;
        }

        // If logo already starts with /storage/, just prepend the domain
        if (strpos($this->logo, '/storage/') === 0) {
            return url($this->logo);
        }

        // Otherwise construct the URL with /storage/ prefix
        return url('/storage/' . ltrim($this->logo, '/'));
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
     * Get all areas where this company operates
     */
    public function areas()
    {
        return $this->belongsToMany(Area::class);
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

    /**
     * Update the company statistics (compounds and units count)
     */
    public function updateStatistics()
    {
        $this->number_of_compounds = $this->compounds()->count();

        $this->number_of_available_units = Unit::whereHas('compound', function ($query) {
            $query->where('company_id', $this->id);
        })->where('is_sold', false)->count();

        $this->saveQuietly(); // Save without triggering events
    }

    /**
     * Get the number of compounds for this company
     */
    public function getCompoundsCountAttribute()
    {
        return $this->compounds()->count();
    }

    /**
     * Get the number of available units for this company
     */
    public function getAvailableUnitsCountAttribute()
    {
        return Unit::whereHas('compound', function ($query) {
            $query->where('company_id', $this->id);
        })->where('is_sold', false)->count();
    }
}
