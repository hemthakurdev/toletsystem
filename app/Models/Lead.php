<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Lead extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'property_id',
        'org_id',
        'name',
        'phone',
        'email',
        'message',
        'status',
        'source',
        'lead_score',
        'contacted_at',
        'notes',
    ];

    protected $casts = [
        'contacted_at' => 'datetime',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'phone', 'email', 'status', 'lead_score'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    // Relationships
    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class, 'org_id');
    }

    public function conversations(): HasMany
    {
        return $this->hasMany(LeadConversation::class);
    }

    // Scopes
    public function scopeNew($query)
    {
        return $query->where('status', 'new');
    }

    public function scopeContacted($query)
    {
        return $query->where('status', 'contacted');
    }

    public function scopeConverted($query)
    {
        return $query->where('status', 'converted');
    }

    public function scopeByProperty($query, int $propertyId)
    {
        return $query->where('property_id', $propertyId);
    }

    public function scopeBySource($query, string $source)
    {
        return $query->where('source', $source);
    }

    public function scopeHighPriority($query)
    {
        return $query->where('lead_score', '>=', 80);
    }

    // Accessors
    public function getIsNewAttribute(): bool
    {
        return $this->status === 'new';
    }

    public function getIsContactedAttribute(): bool
    {
        return $this->status === 'contacted';
    }

    public function getIsConvertedAttribute(): bool
    {
        return $this->status === 'converted';
    }

    public function getIsHighPriorityAttribute(): bool
    {
        return $this->lead_score >= 80;
    }

    public function getTimeSinceCreatedAttribute(): string
    {
        return $this->created_at->diffForHumans();
    }

    public function getTimeSinceContactedAttribute(): string
    {
        if (!$this->contacted_at) {
            return 'Never contacted';
        }

        return $this->contacted_at->diffForHumans();
    }

    // Methods
    public function markAsContacted(): void
    {
        $this->update([
            'status' => 'contacted',
            'contacted_at' => now(),
        ]);
    }

    public function markAsConverted(): void
    {
        $this->update(['status' => 'converted']);
    }

    public function markAsNotInterested(): void
    {
        $this->update(['status' => 'not_interested']);
    }

    public function calculateLeadScore(): int
    {
        $score = 0;

        // Base score for having contact information
        if ($this->phone) $score += 20;
        if ($this->email) $score += 10;

        // Message quality
        if (strlen($this->message) > 50) $score += 15;
        if (strlen($this->message) > 100) $score += 10;

        // Response time (if contacted)
        if ($this->contacted_at) {
            $responseTime = $this->created_at->diffInHours($this->contacted_at);
            if ($responseTime <= 1) $score += 20;
            elseif ($responseTime <= 24) $score += 10;
        }

        // Source quality
        if ($this->source === 'referral') $score += 15;
        elseif ($this->source === 'public_listing') $score += 10;

        // Recent activity
        if ($this->conversations()->count() > 0) $score += 10;

        $this->update(['lead_score' => min($score, 100)]);
        
        return $score;
    }

    public function addConversation(string $message, string $senderType, int $senderId = null): LeadConversation
    {
        return $this->conversations()->create([
            'sender_type' => $senderType,
            'sender_id' => $senderId,
            'message' => $message,
        ]);
    }
}
