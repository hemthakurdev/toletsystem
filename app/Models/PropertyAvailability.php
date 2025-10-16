<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PropertyAvailability extends Model
{
    use HasFactory;

    protected $fillable = [
        'property_id',
        'date',
        'status',
        'price_override',
        'notes',
    ];

    protected $casts = [
        'date' => 'date',
        'price_override' => 'decimal:2',
    ];

    // Relationships
    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    // Scopes
    public function scopeAvailable($query)
    {
        return $query->where('status', 'available');
    }

    public function scopeOccupied($query)
    {
        return $query->where('status', 'occupied');
    }

    public function scopeMaintenance($query)
    {
        return $query->where('status', 'maintenance');
    }

    public function scopeBlocked($query)
    {
        return $query->where('status', 'blocked');
    }

    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('date', [$startDate, $endDate]);
    }

    public function scopeUpcoming($query)
    {
        return $query->where('date', '>=', now());
    }

    // Accessors
    public function getFormattedPriceAttribute(): string
    {
        return $this->price_override ? '₹' . number_format($this->price_override, 0) : 'N/A';
    }

    public function getIsAvailableAttribute(): bool
    {
        return $this->status === 'available';
    }

    public function getIsOccupiedAttribute(): bool
    {
        return $this->status === 'occupied';
    }

    public function getIsMaintenanceAttribute(): bool
    {
        return $this->status === 'maintenance';
    }

    public function getIsBlockedAttribute(): bool
    {
        return $this->status === 'blocked';
    }
}
