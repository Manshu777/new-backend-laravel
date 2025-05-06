<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Barryvdh\DomPDF\Facade\Pdf;
class BookingConfirmation extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
   
    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Booking Confirmation',
        );
    }

   

    public $ticket; 

    public function __construct($ticket)
    {
        $this->ticket = $ticket;
    }

   public function build()
{
    $ticketPdf = Pdf::loadView('pdf.ticket', ['ticket' => $this->ticket]);
    $invoicePdf = Pdf::loadView('pdf.invoice', ['ticket' => $this->ticket]);

    return $this->view('emails.ticket_and_invoice') // you can also use 'emails.ticket' or 'emails.invoice'
        ->with(['ticket' => $this->ticket])
        ->subject('Booking Confirmation & Invoice - ' . $this->ticket['pnr'])
        ->attachData($ticketPdf->output(), 'ticket.pdf', [
            'mime' => 'application/pdf',
        ])
        ->attachData($invoicePdf->output(), 'invoice.pdf', [
            'mime' => 'application/pdf',
        ]);
}

}
