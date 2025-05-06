<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class TicketMail extends Mailable
{
    use Queueable, SerializesModels;

    public $ticket;
    public $pdfPath;

    public function __construct($ticket, $pdfPath)
    {
        $this->ticket = $ticket;
        $this->pdfPath = $pdfPath;
    }

    public function build()
    {
        return $this->subject('Your Flight Ticket')
                    ->view('emails.ticket')
                    ->attach(Storage::path($this->pdfPath), [
                        'as' => 'ticket_' . $this->ticket['pnr'] . '.pdf',
                        'mime' => 'application/pdf',
                    ])
                    ->with(['ticket' => $this->ticket]);
    }
}