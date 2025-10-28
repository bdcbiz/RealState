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
        'is_verified',
        'is_banned',
        'image',
        'google_id',
        'photo_url',
        'login_method',
        'verification_code',
        'verification_code_expires_at',
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
     * Get user subscriptions
     */
    public function subscriptions()
    {
        return $this->hasMany(UserSubscription::class);
    }

    /**
     * Get active subscription
     */
    public function activeSubscription()
    {
        return $this->hasOne(UserSubscription::class)
            ->where('status', 'active')
            ->where(function ($q) {
                $q->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            })
            ->latest();
    }

    /**
     * Check if user has an active subscription
     */
    public function hasActiveSubscription(): bool
    {
        return $this->activeSubscription()->exists();
    }

    /**
     * Get current active subscription or null
     */
    public function getCurrentSubscription(): ?UserSubscription
    {
        return $this->activeSubscription;
    }

    /**
     * Check if user can search
     */
    public function canSearch(): bool
    {
        $subscription = $this->getCurrentSubscription();

        if (!$subscription) {
            return false;
        }

        return $subscription->canSearch();
    }

    /**
     * Increment user's search count
     */
    public function incrementSearchCount(): void
    {
        $subscription = $this->getCurrentSubscription();

        if ($subscription) {
            $subscription->incrementSearch();
        }
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
        // Admin panel: only admin users
        if ($panel->getId() === 'admin') {
            return $this->role === 'admin';
        }

        // Company panel: company users with company_id OR admin (for supervision)
        if ($panel->getId() === 'company') {
            return $this->role === 'admin' ||
                   ($this->role === 'company' && !is_null($this->company_id));
        }

        // Allow all authenticated users by default for other panels
        return true;
    }
}
