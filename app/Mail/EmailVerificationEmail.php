<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class EmailVerificationEmail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public User $user;
    public string $verificationUrl;

    public function __construct(User $user)
    {
        $this->user = $user;
        $this->verificationUrl = $this->generateVerificationUrl();
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Verify Your Email Address - SaleMitra',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.email-verification',
            with: [
                'user' => $this->user,
                'verificationUrl' => $this->verificationUrl,
            ]
        );
    }

    public function attachments(): array
    {
        return [];
    }

    private function generateVerificationUrl(): string
    {
        $hash = sha1($this->user->getEmailForVerification());
        return config('app.url') . "/verify-email/{$this->user->id}/{$hash}";
    }
}