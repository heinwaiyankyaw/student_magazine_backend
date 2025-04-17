<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SubmitArticleMail extends Mailable
{
    use Queueable, SerializesModels;

    public $receiver;
    public $sender;
    /**
     * Create a new message instance.
     */
    public function __construct($receiver, $sender)
    {
        $this->receiver = $receiver;
        $this->sender = $sender;
    }

    public function build()
    {
        return $this->from(config('mail.from.address'), $this->sender->first_name)
            ->subject('New Article Submitted')
            ->view('emails.submit_article')
            ->with([
                'student_name' => $this->sender->first_name . ' ' . $this->sender->last_name,
                'student_email' => $this->sender->email,
                'faculty_name' => $this->receiver->faculty->name ?? 'Your Faculty',
            ]);
    }


    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Submit Article Mail',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.submit_article',
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
