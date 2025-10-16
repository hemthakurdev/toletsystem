<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PropertyAmenity extends Model
{
    use HasFactory;

    protected $fillable = [
        'property_id',
        'amenity_type',
        'amenity_name',
        'amenity_value',
    ];

    // Relationships
    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    // Scopes
    public function scopeByType($query, string $type)
    {
        return $query->where('amenity_type', $type);
    }

    public function scopeBasic($query)
    {
        return $query->where('amenity_type', 'basic');
    }

    public function scopeLuxury($query)
    {
        return $query->where('amenity_type', 'luxury');
    }

    public function scopeSafety($query)
    {
        return $query->where('amenity_type', 'safety');
    }

    public function scopeConvenience($query)
    {
        return $query->where('amenity_type', 'convenience');
    }

    public function scopeOutdoor($query)
    {
        return $query->where('amenity_type', 'outdoor');
    }
}
