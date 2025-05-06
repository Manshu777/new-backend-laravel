<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class FlightTicketMail extends Mailable
{
    use Queueable, SerializesModels;

    public $booking;
    public $passenger;
    public $segment;
    public $fare;

    /**
     * Create a new message instance.
     */
    public function __construct($booking, $passenger, $segment, $fare)
    {
        $this->booking = $booking;
        $this->passenger = $passenger;
        $this->segment = $segment;
        $this->fare = $fare;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        return $this->subject('Your Flight Ticket - NextGenTrip')
                    ->view('emails.flight_ticket');
    }
}