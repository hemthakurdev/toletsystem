<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Property extends Model implements HasMedia
{
    use HasFactory, LogsActivity, InteractsWithMedia;

    protected $fillable = [
        'org_id',
        'title',
        'short_description',
        'long_description',
        'property_type',
        'category',
        'price',
        'security_deposit',
        'deposit_terms',
        'city',
        'locality',
        'pincode',
        'address_line',
        'latitude',
        'longitude',
        'furnished_status',
        'bedrooms',
        'bathrooms',
        'area_sqft',
        'availability_status',
        'published',
        'featured',
        'published_at',
        'amenities',
        'images',
    ];

    protected $casts = [
        'amenities' => 'array',
        'images' => 'array',
        'published' => 'boolean',
        'featured' => 'boolean',
        'published_at' => 'datetime',
        'price' => 'decimal:2',
        'security_deposit' => 'decimal:2',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['title', 'price', 'published', 'availability_status'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    // Relationships
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class, 'org_id');
    }

    public function tenants(): HasMany
    {
        return $this->hasMany(Tenant::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function leads(): HasMany
    {
        return $this->hasMany(Lead::class);
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class);
    }

    public function amenities(): HasMany
    {
        return $this->hasMany(PropertyAmenity::class);
    }

    public function availability(): HasMany
    {
        return $this->hasMany(PropertyAvailability::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(Document::class);
    }

    public function favorites(): HasMany
    {
        return $this->hasMany(Favorite::class);
    }

    public function favoritedBy()
    {
        return $this->belongsToMany(User::class, 'favorites');
    }

    // Scopes
    public function scopePublished($query)
    {
        return $query->where('published', true);
    }

    public function scopeFeatured($query)
    {
        return $query->where('featured', true);
    }

    public function scopeAvailable($query)
    {
        return $query->where('availability_status', 'vacant');
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('property_type', $type);
    }

    public function scopeByCity($query, string $city)
    {
        return $query->where('city', 'like', "%{$city}%");
    }

    public function scopeByLocality($query, string $locality)
    {
        return $query->where('locality', 'like', "%{$locality}%");
    }

    public function scopeByPriceRange($query, float $min, float $max)
    {
        return $query->whereBetween('price', [$min, $max]);
    }

    public function scopeByBedrooms($query, int $bedrooms)
    {
        return $query->where('bedrooms', $bedrooms);
    }

    // Accessors
    public function getFormattedPriceAttribute(): string
    {
        return '₹' . number_format($this->price, 0);
    }

    public function getFormattedDepositAttribute(): string
    {
        return $this->security_deposit ? '₹' . number_format($this->security_deposit, 0) : 'N/A';
    }

    public function getFullAddressAttribute(): string
    {
        return "{$this->address_line}, {$this->locality}, {$this->city} - {$this->pincode}";
    }

    public function getIsOccupiedAttribute(): bool
    {
        return $this->availability_status === 'occupied';
    }

    public function getIsVacantAttribute(): bool
    {
        return $this->availability_status === 'vacant';
    }

    // Methods
    public function publish(): void
    {
        $this->update([
            'published' => true,
            'published_at' => now(),
        ]);
    }

    public function unpublish(): void
    {
        $this->update([
            'published' => false,
            'published_at' => null,
        ]);
    }

    public function markAsOccupied(): void
    {
        $this->update(['availability_status' => 'occupied']);
    }

    public function markAsVacant(): void
    {
        $this->update(['availability_status' => 'vacant']);
    }
}
