<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class BusBookingConfirmation extends Mailable
{
    use Queueable, SerializesModels;

    public $booking;
    public $passengers;
    public $pdfPath;

    public function __construct($booking, $passengers, $pdfPath)
    {
        $this->booking = $booking;
        $this->passengers = $passengers;
        $this->pdfPath = $pdfPath;
    }

    public function build()
    {
        return $this->subject('Bus Booking Confirmation')
            ->view('emails.booking-confirmation')
            ->attach($this->pdfPath, [
                'as' => 'booking-confirmation.pdf',
                'mime' => 'application/pdf',
            ]);
    }
}