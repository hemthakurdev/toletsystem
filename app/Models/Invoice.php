<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Invoice extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'org_id',
        'tenant_id',
        'property_id',
        'invoice_no',
        'invoice_date',
        'due_date',
        'amount',
        'tax',
        'total_amount',
        'status',
        'payment_method',
        'payment_reference',
        'pdf_url',
        'notes',
    ];

    protected $casts = [
        'invoice_date' => 'date',
        'due_date' => 'date',
        'amount' => 'decimal:2',
        'tax' => 'decimal:2',
        'total_amount' => 'decimal:2',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['invoice_no', 'amount', 'status', 'payment_method'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    // Relationships
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class, 'org_id');
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    // Scopes
    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    public function scopeUnpaid($query)
    {
        return $query->whereIn('status', ['draft', 'sent', 'overdue']);
    }

    public function scopeOverdue($query)
    {
        return $query->where('status', 'overdue')
            ->where('due_date', '<', now());
    }

    public function scopeByTenant($query, int $tenantId)
    {
        return $query->where('tenant_id', $tenantId);
    }

    public function scopeByProperty($query, int $propertyId)
    {
        return $query->where('property_id', $propertyId);
    }

    // Accessors
    public function getFormattedAmountAttribute(): string
    {
        return '₹' . number_format($this->amount, 0);
    }

    public function getFormattedTotalAttribute(): string
    {
        return '₹' . number_format($this->total_amount, 0);
    }

    public function getIsOverdueAttribute(): bool
    {
        return $this->due_date->isPast() && $this->status !== 'paid';
    }

    public function getIsPaidAttribute(): bool
    {
        return $this->status === 'paid';
    }

    public function getDaysOverdueAttribute(): int
    {
        if (!$this->is_overdue) {
            return 0;
        }

        return $this->due_date->diffInDays(now());
    }

    public function getTotalPaidAttribute(): float
    {
        return $this->payments()->where('status', 'completed')->sum('amount');
    }

    public function getRemainingAmountAttribute(): float
    {
        return $this->total_amount - $this->total_paid;
    }

    // Methods
    public function markAsPaid(string $paymentMethod = null, string $paymentReference = null): void
    {
        $this->update([
            'status' => 'paid',
            'payment_method' => $paymentMethod,
            'payment_reference' => $paymentReference,
        ]);
    }

    public function markAsOverdue(): void
    {
        if ($this->is_overdue) {
            $this->update(['status' => 'overdue']);
        }
    }

    public function generatePdf(): string
    {
        // This would integrate with a PDF generation library like DomPDF or similar
        // For now, return a placeholder
        return "invoices/{$this->invoice_no}.pdf";
    }

    public function sendToTenant(): void
    {
        // This would integrate with email/SMS services
        // For now, just mark as sent
        $this->update(['status' => 'sent']);
    }
}
