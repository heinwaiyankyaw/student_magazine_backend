<?php
namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class GuestRegisterAlert extends Mailable
{
    use Queueable, SerializesModels;

    public $guest;
    public $user;
    /**
     * Create a new message instance.
     */
    public function __construct($guest, $user)
    {
        $this->guest = $guest;
        $this->user  = $user;
    }

    public function build()
    {
        return $this->from(config('mail.from.address'), $this->user->first_name)
            ->subject('New Guest was Registered')
            ->view('emails.guest_register_alert')
            ->with([
                'guest_name'   => $this->guest->first_name . ' ' . $this->guest->last_name,
                'guest_email'  => $this->guest->email,
                'faculty_name' => $this->user->faculty->name ?? 'Your Faculty',
            ]);
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Register Guest Mail',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.guest_register_alert',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}