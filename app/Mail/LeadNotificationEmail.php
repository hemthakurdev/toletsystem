<?php

namespace App\Mail;

use App\Models\Lead;
use App\Models\Property;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class LeadNotificationEmail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public Lead $lead;
    public Property $property;
    public User $propertyOwner;

    /**
     * Create a new message instance.
     */
    public function __construct(Lead $lead, Property $property, User $propertyOwner)
    {
        $this->lead = $lead;
        $this->property = $property;
        $this->propertyOwner = $propertyOwner;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "New Lead for {$this->property->title} - SaleMitra",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.lead-notification',
            with: [
                'lead' => $this->lead,
                'property' => $this->property,
                'propertyOwner' => $this->propertyOwner,
                'dashboardUrl' => config('app.url') . '/dashboard/leads',
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
