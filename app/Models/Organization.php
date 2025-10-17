<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Organization extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'gstin',
        'address',
        'city',
        'state',
        'pincode',
        'timezone',
        'plan_id',
        'status',
        'settings',
        'trial_ends_at',
    ];

    protected $casts = [
        'settings' => 'array',
        'trial_ends_at' => 'datetime',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'email', 'status', 'plan_id'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    // Relationships
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    public function properties(): HasMany
    {
        return $this->hasMany(Property::class, 'org_id');
    }

    public function tenants(): HasMany
    {
        return $this->hasMany(Tenant::class, 'org_id');
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class, 'org_id');
    }

    public function leads(): HasMany
    {
        return $this->hasMany(Lead::class, 'org_id');
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class, 'org_id');
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class, 'org_id');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(Document::class, 'org_id');
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class, 'org_id');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    // Accessors & Mutators
    public function getIsOnTrialAttribute(): bool
    {
        return $this->trial_ends_at && $this->trial_ends_at->isFuture();
    }

    public function getActiveSubscriptionAttribute()
    {
        return $this->subscriptions()
            ->where('status', 'active')
            ->where('ends_at', '>', now())
            ->latest()
            ->first();
    }
}
