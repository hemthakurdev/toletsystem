<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Subscription extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'org_id',
        'plan_id',
        'razorpay_subscription_id',
        'status',
        'started_at',
        'ends_at',
        'cancelled_at',
        'metadata',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'ends_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'metadata' => 'array',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['plan_id', 'status', 'ends_at'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    // Relationships
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class, 'org_id');
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active')
            ->where('ends_at', '>', now());
    }

    public function scopeExpired($query)
    {
        return $query->where('ends_at', '<=', now());
    }

    public function scopeCancelled($query)
    {
        return $query->where('status', 'cancelled');
    }

    public function scopePastDue($query)
    {
        return $query->where('status', 'past_due');
    }

    // Accessors
    public function getIsActiveAttribute(): bool
    {
        return $this->status === 'active' && $this->ends_at->isFuture();
    }

    public function getIsExpiredAttribute(): bool
    {
        return $this->ends_at->isPast();
    }

    public function getIsCancelledAttribute(): bool
    {
        return $this->status === 'cancelled';
    }

    public function getDaysUntilExpiryAttribute(): int
    {
        if ($this->is_expired) {
            return 0;
        }

        return $this->ends_at->diffInDays(now());
    }

    public function getIsExpiringSoonAttribute(): bool
    {
        return $this->days_until_expiry <= 7 && !$this->is_expired;
    }

    // Methods
    public function cancel(): void
    {
        $this->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
        ]);
    }

    public function renew(): void
    {
        $this->update([
            'status' => 'active',
            'ends_at' => $this->ends_at->addMonth(),
        ]);
    }

    public function markAsPastDue(): void
    {
        $this->update(['status' => 'past_due']);
    }
}
