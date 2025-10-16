<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notification extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'org_id',
        'type',
        'title',
        'message',
        'data',
        'read_at',
    ];

    protected $casts = [
        'data' => 'array',
        'read_at' => 'datetime',
    ];

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class, 'org_id');
    }

    // Scopes
    public function scopeUnread($query)
    {
        return $query->whereNull('read_at');
    }

    public function scopeRead($query)
    {
        return $query->whereNotNull('read_at');
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }

    public function scopeByUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeByOrganization($query, int $orgId)
    {
        return $query->where('org_id', $orgId);
    }

    public function scopeRecent($query, int $days = 7)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    // Accessors
    public function getIsReadAttribute(): bool
    {
        return !is_null($this->read_at);
    }

    public function getIsUnreadAttribute(): bool
    {
        return is_null($this->read_at);
    }

    public function getTimeSinceCreatedAttribute(): string
    {
        return $this->created_at->diffForHumans();
    }

    // Methods
    public function markAsRead(): void
    {
        if (!$this->is_read) {
            $this->update(['read_at' => now()]);
        }
    }

    public function markAsUnread(): void
    {
        $this->update(['read_at' => null]);
    }

    // Static methods for creating notifications
    public static function createForUser(User $user, string $type, string $title, string $message, array $data = []): self
    {
        return self::create([
            'user_id' => $user->id,
            'org_id' => $user->org_id,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'data' => $data,
        ]);
    }

    public static function createForOrganization(Organization $organization, string $type, string $title, string $message, array $data = []): self
    {
        return self::create([
            'org_id' => $organization->id,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'data' => $data,
        ]);
    }

    public static function createForAllUsersInOrganization(Organization $organization, string $type, string $title, string $message, array $data = []): void
    {
        $organization->users()->each(function (User $user) use ($type, $title, $message, $data) {
            self::createForUser($user, $type, $title, $message, $data);
        });
    }
}
