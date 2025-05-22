<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Barryvdh\DomPDF\Facade\Pdf;
class BookingConfirmationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct($bookingData, $passengerData, $invoiceData)
    {
        $this->bookingData = $bookingData;
        $this->passengerData = $passengerData;
        $this->invoiceData = $invoiceData;
    }

    public function build()
    {

        // Generate PDF from the same view
        $pdf = Pdf::loadView('emails.booking_confirmation', [
            'bookingData' => $this->bookingData,
            'passengerData' => $this->passengerData,
            'invoiceData' => $this->invoiceData,
        ]);

        return $this->subject('Your Flight Booking Confirmation')
                    ->view('emails.booking_confirmation')
                    ->with([
                        'bookingData' => $this->bookingData,
                        'passengerData' => $this->passengerData,
                        'invoiceData' => $this->invoiceData,
                    ])
                    ->attachData($pdf->output(), 'booking_confirmation.pdf', [
                        'mime' => 'application/pdf',
                    ]);
    
    }
}
