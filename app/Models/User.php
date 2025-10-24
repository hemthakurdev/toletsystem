<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, Notifiable, HasApiTokens, HasRoles, LogsActivity;

    protected $fillable = [
        'org_id',
        'name',
        'email',
        'phone',
        'password',
        'avatar_url',
        'last_login',
        'user_type', // 'organization' or 'frontend'
        'is_verified',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'last_login' => 'datetime',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'email', 'last_login'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    // Relationships
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class, 'org_id');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(Document::class, 'uploaded_by');
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class, 'approved_by');
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class);
    }

    public function conversations(): HasMany
    {
        return $this->hasMany(LeadConversation::class, 'sender_id');
    }

    public function favorites(): HasMany
    {
        return $this->hasMany(Favorite::class);
    }

    public function favoriteProperties()
    {
        return $this->belongsToMany(Property::class, 'favorites');
    }

    // Scopes
    public function scopeByOrganization($query, int $orgId)
    {
        return $query->where('org_id', $orgId);
    }

    public function scopeAdmins($query)
    {
        return $query->role('admin');
    }

    public function scopeStaff($query)
    {
        return $query->role('staff');
    }

    public function scopeFrontendUsers($query)
    {
        return $query->where('user_type', 'frontend');
    }

    public function scopeOrganizationUsers($query)
    {
        return $query->where('user_type', 'organization');
    }

    // Accessors
    public function getIsAdminAttribute(): bool
    {
        return $this->hasRole('admin');
    }

    public function getIsStaffAttribute(): bool
    {
        return $this->hasRole('staff');
    }

    public function getIsSuperAdminAttribute(): bool
    {
        return $this->hasRole('super_admin');
    }

    public function getIsFrontendUserAttribute(): bool
    {
        return $this->user_type === 'frontend';
    }

    public function getIsOrganizationUserAttribute(): bool
    {
        return $this->user_type === 'organization';
    }

    public function getAvatarUrlAttribute($value): string
    {
        return $value ?: 'https://ui-avatars.com/api/?name=' . urlencode($this->name) . '&color=7F9CF5&background=EBF4FF';
    }

    // Methods
    public function updateLastLogin(): void
    {
        $this->update(['last_login' => now()]);
    }

    public function canAccessOrganization(int $orgId): bool
    {
        return $this->org_id === $orgId || $this->hasRole('super_admin');
    }
}
