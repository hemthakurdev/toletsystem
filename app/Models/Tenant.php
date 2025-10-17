<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Tenant extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'org_id',
        'property_id',
        'name',
        'phone',
        'email',
        'occupation',
        'company',
        'id_proof_type',
        'id_proof_number',
        'lease_start',
        'lease_end',
        'rent_amount',
        'security_deposit',
        'emergency_contact_name',
        'emergency_contact_phone',
        'documents',
        'status',
        'notes',
    ];

    protected $casts = [
        'documents' => 'array',
        'lease_start' => 'date',
        'lease_end' => 'date',
        'rent_amount' => 'decimal:2',
        'security_deposit' => 'decimal:2',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'phone', 'email', 'rent_amount', 'status'])
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

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(Document::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeByProperty($query, int $propertyId)
    {
        return $query->where('property_id', $propertyId);
    }

    // Accessors
    public function getFormattedRentAttribute(): string
    {
        return '₹' . number_format($this->rent_amount, 0);
    }

    public function getFormattedDepositAttribute(): string
    {
        return $this->security_deposit ? '₹' . number_format($this->security_deposit, 0) : 'N/A';
    }

    public function getLeaseDurationAttribute(): int
    {
        return $this->lease_start->diffInMonths($this->lease_end);
    }

    public function getIsLeaseExpiringAttribute(): bool
    {
        return $this->lease_end->diffInDays(now()) <= 30;
    }

    public function getIsLeaseExpiredAttribute(): bool
    {
        return $this->lease_end->isPast();
    }

    // Methods
    public function generateInvoice(): Invoice
    {
        return $this->invoices()->create([
            'org_id' => $this->org_id,
            'property_id' => $this->property_id,
            'invoice_no' => $this->generateInvoiceNumber(),
            'invoice_date' => now(),
            'due_date' => now()->addDays(7),
            'amount' => $this->rent_amount,
            'total_amount' => $this->rent_amount,
            'status' => 'draft',
        ]);
    }

    private function generateInvoiceNumber(): string
    {
        $prefix = 'INV';
        $date = now()->format('Ymd');
        $count = $this->invoices()->count() + 1;
        
        return "{$prefix}-{$date}-" . str_pad($count, 4, '0', STR_PAD_LEFT);
    }

    public function terminateLease(): void
    {
        $this->update(['status' => 'terminated']);
        $this->property->markAsVacant();
    }
}
