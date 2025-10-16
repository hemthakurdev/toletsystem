<?php

namespace App\Mail;

use App\Models\Property;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PropertyApprovalEmail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public Property $property;
    public User $user;
    public string $status; // 'approved' or 'rejected'
    public string $reason;

    /**
     * Create a new message instance.
     */
    public function __construct(Property $property, User $user, string $status, string $reason = null)
    {
        $this->property = $property;
        $this->user = $user;
        $this->status = $status;
        $this->reason = $reason;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $subject = $this->status === 'approved' 
            ? "Property Approved: {$this->property->title} - SaleMitra"
            : "Property Rejected: {$this->property->title} - SaleMitra";

        return new Envelope(
            subject: $subject,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.property-approval',
            with: [
                'property' => $this->property,
                'user' => $this->user,
                'status' => $this->status,
                'reason' => $this->reason,
                'dashboardUrl' => config('app.url') . '/dashboard/properties',
                'marketplaceUrl' => config('app.url') . '/marketplace/properties/' . $this->property->id,
            ]
        );
    }

    /**
     * Get the attachments for the message.
     */
    public function attachments(): array
    {
        return [];
    }
}
