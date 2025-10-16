<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Plan extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'price_monthly',
        'price_yearly',
        'property_limit',
        'user_limit',
        'feature_flags',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'feature_flags' => 'array',
        'is_active' => 'boolean',
    ];

    // Relationships
    public function organizations(): HasMany
    {
        return $this->hasMany(Organization::class);
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order');
    }

    // Accessors
    public function getYearlyDiscountAttribute(): float
    {
        $monthlyTotal = $this->price_monthly * 12;
        return round((($monthlyTotal - $this->price_yearly) / $monthlyTotal) * 100, 2);
    }

    public function hasFeature(string $feature): bool
    {
        return in_array($feature, $this->feature_flags ?? []);
    }
}
