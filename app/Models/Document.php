<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Document extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'org_id',
        'property_id',
        'tenant_id',
        'document_type',
        'file_name',
        'file_path',
        'file_size',
        'mime_type',
        'uploaded_by',
        'description',
    ];

    protected $casts = [
        'file_size' => 'integer',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['document_type', 'file_name'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    // Relationships
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class, 'org_id');
    }

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    // Scopes
    public function scopeByType($query, string $type)
    {
        return $query->where('document_type', $type);
    }

    public function scopeByProperty($query, int $propertyId)
    {
        return $query->where('property_id', $propertyId);
    }

    public function scopeByTenant($query, int $tenantId)
    {
        return $query->where('tenant_id', $tenantId);
    }

    public function scopeAgreements($query)
    {
        return $query->where('document_type', 'agreement');
    }

    public function scopeIdProofs($query)
    {
        return $query->where('document_type', 'id_proof');
    }

    public function scopeReceipts($query)
    {
        return $query->where('document_type', 'receipt');
    }

    public function scopeInvoices($query)
    {
        return $query->where('document_type', 'invoice');
    }

    public function scopeMaintenance($query)
    {
        return $query->where('document_type', 'maintenance');
    }

    // Accessors
    public function getFormattedFileSizeAttribute(): string
    {
        $bytes = $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }

    public function getFileExtensionAttribute(): string
    {
        return pathinfo($this->file_name, PATHINFO_EXTENSION);
    }

    public function getIsImageAttribute(): bool
    {
        return in_array($this->mime_type, ['image/jpeg', 'image/png', 'image/gif', 'image/webp']);
    }

    public function getIsPdfAttribute(): bool
    {
        return $this->mime_type === 'application/pdf';
    }

    public function getDownloadUrlAttribute(): string
    {
        return route('documents.download', $this->id);
    }

    // Methods
    public function getFullPath(): string
    {
        return storage_path('app/' . $this->file_path);
    }

    public function exists(): bool
    {
        return file_exists($this->getFullPath());
    }

    public function deleteFile(): bool
    {
        if ($this->exists()) {
            return unlink($this->getFullPath());
        }
        
        return false;
    }
}
