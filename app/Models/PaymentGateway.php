<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentGateway extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'provider',
        'description',
        'is_active',
        'is_default',
        'countries',
        'credentials',
        'config',
        'currency',
        'is_test_mode',
        'transactions_count',
        'failed_count',
        'success_rate',
        'total_amount',
        'last_used_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_default' => 'boolean',
        'is_test_mode' => 'boolean',
        'countries' => 'array',
        'credentials' => 'array',
        'config' => 'array',
        'transactions_count' => 'integer',
        'failed_count' => 'integer',
        'success_rate' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'last_used_at' => 'datetime',
    ];

    /**
     * Get the default gateway
     */
    public static function getDefault(): ?self
    {
        return static::where('is_default', true)
            ->where('is_active', true)
            ->first();
    }

    /**
     * Get gateway by slug
     */
    public static function getBySlug(string $slug): ?self
    {
        return static::where('slug', $slug)
            ->where('is_active', true)
            ->first();
    }

    /**
     * Get gateway for specific country
     */
    public static function getForCountry(string $countryCode): ?self
    {
        // Find gateway that has this country in its countries array
        $gateway = static::where('is_active', true)
            ->whereJsonContains('countries', $countryCode)
            ->first();

        // If no specific gateway found, return default
        return $gateway ?? static::getDefault();
    }

    /**
     * Get all active gateways
     */
    public static function getActive()
    {
        return static::where('is_active', true)->get();
    }

    /**
     * Set as default gateway
     */
    public function setAsDefault(): bool
    {
        // Remove default flag from all other gateways
        static::where('id', '!=', $this->id)->update(['is_default' => false]);

        // Set this gateway as default
        return $this->update(['is_default' => true]);
    }

    /**
     * Increment transactions count
     */
    public function incrementTransactions(float $amount = 0): void
    {
        $this->increment('transactions_count');
        $this->increment('total_amount', $amount);
        $this->updateSuccessRate();
        $this->update(['last_used_at' => now()]);
    }

    /**
     * Increment failed count
     */
    public function incrementFailed(): void
    {
        $this->increment('failed_count');
        $this->updateSuccessRate();
    }

    /**
     * Update success rate
     */
    protected function updateSuccessRate(): void
    {
        $total = $this->transactions_count + $this->failed_count;
        if ($total > 0) {
            $this->update([
                'success_rate' => ($this->transactions_count / $total) * 100
            ]);
        }
    }

    /**
     * Get credential value
     */
    public function getCredential(string $key, $default = null)
    {
        return $this->credentials[$key] ?? $default;
    }

    /**
     * Get config value
     */
    public function getConfig(string $key, $default = null)
    {
        return $this->config[$key] ?? $default;
    }

    /**
     * Check if gateway is configured
     */
    public function isConfigured(): bool
    {
        if (!$this->is_active) {
            return false;
        }

        return !empty($this->credentials);
    }

    /**
     * Get countries names
     */
    public function getCountriesNamesAttribute(): array
    {
        if (empty($this->countries)) {
            return [];
        }

        $countryNames = [
            'EG' => '🇪🇬 مصر - Egypt',
            'SA' => '🇸🇦 السعودية - Saudi Arabia',
            'AE' => '🇦🇪 الإمارات - UAE',
            'KW' => '🇰🇼 الكويت - Kuwait',
            'QA' => '🇶🇦 قطر - Qatar',
            'BH' => '🇧🇭 البحرين - Bahrain',
            'OM' => '🇴🇲 عمان - Oman',
            'JO' => '🇯🇴 الأردن - Jordan',
            'LB' => '🇱🇧 لبنان - Lebanon',
            'IQ' => '🇮🇶 العراق - Iraq',
            'PK' => '🇵🇰 باكستان - Pakistan',
            'US' => '🇺🇸 الولايات المتحدة - USA',
            'GB' => '🇬🇧 المملكة المتحدة - UK',
        ];

        return array_map(fn($code) => $countryNames[$code] ?? $code, $this->countries);
    }
}
