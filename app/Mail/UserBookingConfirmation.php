<?php

namespace App\Mail;

use App\Models\HolidayBooking;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class UserBookingConfirmation extends Mailable
{
    use Queueable, SerializesModels;

    public $booking;

    public function __construct(HolidayBooking $booking)
    {
        $this->booking = $booking;
    }

    public function build()
    {
        return $this->view('emails.user_booking_confirmation')
                    ->subject('Your Holiday Booking Confirmation - Next Gen Trip')
                    ->with([
                        'booking' => $this->booking,
                    ]);
    }
}