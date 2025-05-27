<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;



class HotelBookingConfirmation extends Mailable
{
    use Queueable, SerializesModels;
    public $bookingDetails;
    public $pdfPath;

    public function __construct($bookingDetails, $pdfPath)
    {
        $this->bookingDetails = $bookingDetails;
        $this->pdfPath = $pdfPath;
    }

    public function build()
    {
        return $this->subject('Your Hotel Booking Confirmation')
                    ->view('emails.booking_confirmationhotel')
                    ->attach($this->pdfPath, [
                        'as' => 'Hotel_Booking_Confirmation.pdf',
                        'mime' => 'application/pdf',
                    ]);
    }
}
