<?php

namespace App\Mail;

use App\Helpers\IcsGenerator;
use App\Models\Meeting;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Queue\SerializesModels;

class MeetingInvitation extends Mailable
{
    use Queueable, SerializesModels;

    public $meeting;
    public $icsContent;
    public $type; // 'invitation', 'update', 'cancellation'

    /**
     * Create a new message instance.
     */
    public function __construct(Meeting $meeting, $icsContent = null, $type = 'invitation')
    {
        $this->meeting = $meeting;
        $this->icsContent = $icsContent;
        $this->type = $type;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $subjectPrefix = match ($this->type) {
            'update' => 'Update Meeting: ',
            'cancellation' => 'Pembatalan Meeting: ',
            default => 'Undangan Meeting: ',
        };

        return new Envelope(
            subject: $subjectPrefix . $this->meeting->topic,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.meetings.invitation',
            with: ['type' => $this->type],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        if ($this->icsContent) {
            return [
                Attachment::fromData(fn() => $this->icsContent, 'invite.ics')
                    ->withMime('text/calendar'),
            ];
        }

        // Fallback jika tidak ada icsContent
        try {
            $icsContent = IcsGenerator::generate($this->meeting);
            return [
                Attachment::fromData(fn() => $icsContent, 'invite.ics')
                    ->withMime('text/calendar'),
            ];
        } catch (\Exception $e) {
            return [];
        }
    }
}