<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Filament\Models\Contracts\FilamentUser;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'role',
        'company_id',
        'fcm_token',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Get the company that the user belongs to
     */
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Get the sales for this user
     */
    public function sales()
    {
        return $this->hasMany(Sale::class);
    }

    /**
     * Get units sold by this sales person
     */
    public function soldUnits()
    {
        return $this->hasMany(Unit::class, 'sales_id');
    }

    /**
     * Get units purchased by this buyer
     */
    public function purchasedUnits()
    {
        return $this->hasMany(Unit::class, 'buyer_id');
    }

    /**
     * Determine if the user can access the Filament admin panel.
     *
     * By default, all authenticated users can access the panel.
     * You can modify this to restrict access based on email, role, or other criteria.
     *
     * Examples:
     * - return in_array($this->email, ['admin@example.com', 'manager@example.com']);
     * - return $this->role === 'admin';
     * - return true; // Allow all authenticated users
     */
    public function canAccessPanel(\Filament\Panel $panel): bool
    {
        // Admin panel: allow admin and company users
        if ($panel->getId() === 'admin') {
            return in_array($this->role, ['admin', 'seller', 'buyer', 'company']);
        }

        // Allow all authenticated users by default
        return true;
    }
}
