<?php

namespace App\Mail;

use App\Models\HolidayBooking;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OwnerBookingNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $booking;

    public function __construct(HolidayBooking $booking)
    {
        $this->booking = $booking;
    }

    public function build()
    {
        return $this->view('emails.owner_booking_notification')
                    ->subject('New Holiday Booking Notification')
                    ->with([
                        'booking' => $this->booking,
                    ]);
    }
}
