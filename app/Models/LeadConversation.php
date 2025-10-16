<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeadConversation extends Model
{
    use HasFactory;

    protected $fillable = [
        'lead_id',
        'sender_type',
        'sender_id',
        'message',
        'attachments',
        'read_at',
    ];

    protected $casts = [
        'attachments' => 'array',
        'read_at' => 'datetime',
    ];

    // Relationships
    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    // Scopes
    public function scopeBySenderType($query, string $type)
    {
        return $query->where('sender_type', $type);
    }

    public function scopeUnread($query)
    {
        return $query->whereNull('read_at');
    }

    public function scopeRead($query)
    {
        return $query->whereNotNull('read_at');
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

    public function getHasAttachmentsAttribute(): bool
    {
        return !empty($this->attachments);
    }

    // Methods
    public function markAsRead(): void
    {
        if (!$this->is_read) {
            $this->update(['read_at' => now()]);
        }
    }

    public function addAttachment(string $filePath, string $fileName): void
    {
        $attachments = $this->attachments ?? [];
        $attachments[] = [
            'file_path' => $filePath,
            'file_name' => $fileName,
            'uploaded_at' => now()->toISOString(),
        ];
        
        $this->update(['attachments' => $attachments]);
    }
}
