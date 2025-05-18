<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class InsuranceBookingConfirmation extends Mailable
{
    use Queueable, SerializesModels;





    public $bookingDetails;
    public $pdfPath;

    public function __construct($bookingDetails, $pdfPath = null)
    {
        $this->bookingDetails = $bookingDetails;
        $this->pdfPath = $pdfPath;
    }

    public function build()
    {
        $mail = $this->subject('Your Insurance Booking Confirmation')
                     ->view('emails.insurance_booking_confirmation', [
                         'pax' => $this->bookingDetails['pax'],
                         'itinerary' => $this->bookingDetails['itinerary'],
                         'coverageHtml' => $this->bookingDetails['coverageHtml'],
                         'startDate' => $this->bookingDetails['startDate'],
                         'endDate' => $this->bookingDetails['endDate'],
                         'bookingDate' => $this->bookingDetails['bookingDate'],
                         'dob' => $this->bookingDetails['dob'],
                     ]);

        if ($this->pdfPath && file_exists($this->pdfPath)) {
            $mail->attach($this->pdfPath, ['as' => 'InsuranceBookingConfirmation.pdf', 'mime' => 'application/pdf']);
        }

        return $mail;
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
