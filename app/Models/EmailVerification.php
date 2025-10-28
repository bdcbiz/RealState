<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Str;
use Carbon\Carbon;

class EmailVerification extends Model
{
    protected $fillable = [
        'user_type',
        'user_id',
        'email',
        'code',
        'token',
        'status',
        'attempts',
        'max_attempts',
        'ip_address',
        'user_agent',
        'expires_at',
        'verified_at',
    ];

    protected $casts = [
        'attempts' => 'integer',
        'max_attempts' => 'integer',
        'expires_at' => 'datetime',
        'verified_at' => 'datetime',
    ];

    /**
     * Get the user (Client or Driver)
     */
    public function user(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Generate new verification code
     */
    public static function generateCode(): string
    {
        return str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    }

    /**
     * Generate unique token
     */
    public static function generateToken(): string
    {
        return Str::random(64);
    }

    /**
     * Create new verification for user
     */
    public static function createForUser($user, ?string $ipAddress = null, ?string $userAgent = null): self
    {
        // Expire any pending verifications for this user
        static::where('user_type', get_class($user))
            ->where('user_id', $user->id)
            ->where('status', 'pending')
            ->update(['status' => 'expired']);

        return static::create([
            'user_type' => get_class($user),
            'user_id' => $user->id,
            'email' => $user->email,
            'code' => static::generateCode(),
            'token' => static::generateToken(),
            'status' => 'pending',
            'attempts' => 0,
            'max_attempts' => 3,
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
            'expires_at' => Carbon::now()->addMinutes(15),
        ]);
    }

    /**
     * Verify the code
     */
    public function verify(string $code): bool
    {
        // Check if already verified
        if ($this->status === 'verified') {
            return false;
        }

        // Check if expired
        if ($this->isExpired()) {
            $this->update(['status' => 'expired']);
            return false;
        }

        // Check attempts
        if ($this->attempts >= $this->max_attempts) {
            $this->update(['status' => 'failed']);
            return false;
        }

        // Increment attempts
        $this->increment('attempts');

        // Verify code
        if ($this->code === $code) {
            $this->update([
                'status' => 'verified',
                'verified_at' => Carbon::now(),
            ]);

            // Mark user email as verified
            if ($this->user) {
                $this->user->update(['email_verified_at' => Carbon::now()]);
            }

            return true;
        }

        // Wrong code
        if ($this->attempts >= $this->max_attempts) {
            $this->update(['status' => 'failed']);
        }

        return false;
    }

    /**
     * Check if verification is expired
     */
    public function isExpired(): bool
    {
        return Carbon::now()->isAfter($this->expires_at);
    }

    /**
     * Check if verification is valid (pending and not expired)
     */
    public function isValid(): bool
    {
        return $this->status === 'pending' && !$this->isExpired();
    }

    /**
     * Check if can resend
     */
    public function canResend(): bool
    {
        // Can resend if expired or after 1 minute
        if ($this->status === 'expired') {
            return true;
        }

        return Carbon::now()->isAfter($this->created_at->addMinute());
    }

    /**
     * Get remaining time in seconds
     */
    public function getRemainingTime(): int
    {
        if ($this->isExpired()) {
            return 0;
        }

        return Carbon::now()->diffInSeconds($this->expires_at);
    }

    /**
     * Get remaining attempts
     */
    public function getRemainingAttempts(): int
    {
        return max(0, $this->max_attempts - $this->attempts);
    }

    /**
     * Scope for pending verifications
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope for verified verifications
     */
    public function scopeVerified($query)
    {
        return $query->where('status', 'verified');
    }

    /**
     * Scope for expired verifications
     */
    public function scopeExpired($query)
    {
        return $query->where('status', 'expired');
    }

    /**
     * Scope for failed verifications
     */
    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    /**
     * Scope for valid verifications
     */
    public function scopeValid($query)
    {
        return $query->where('status', 'pending')
            ->where('expires_at', '>', Carbon::now());
    }

    /**
     * Find by token
     */
    public static function findByToken(string $token): ?self
    {
        return static::where('token', $token)->first();
    }

    /**
     * Find valid verification for user
     */
    public static function findValidForUser($user): ?self
    {
        return static::where('user_type', get_class($user))
            ->where('user_id', $user->id)
            ->valid()
            ->latest()
            ->first();
    }
}
