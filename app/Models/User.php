<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
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
        'image',
        'role',
        'company_id',
        'is_verified',
        'is_banned',
        'verification_token',
        'verification_token_expires_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'created_at',
        'updated_at',
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

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function purchasedUnits()
    {
        return $this->hasMany(Unit::class, 'buyer_id');
    }

    public function getFilamentAvatarUrl(): ?string
    {
        // For company users, show company logo
        if ($this->role === 'company' && $this->company && $this->company->logo) {
            return url('storage/' . $this->company->logo);
        }

        // For other users, show their profile image
        return $this->image ? url('storage/' . $this->image) : null;
    }

    public function getFilamentName(): string
    {
        return $this->name;
    }
}
