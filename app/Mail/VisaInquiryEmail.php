<?php

// app/Mail/VisaInquiryEmail.php
namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class VisaInquiryEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $inquiryData;

    public function __construct($inquiryData)
    {
        $this->inquiryData = $inquiryData;
    }

    public function build()
    {
        return $this->subject('Visa Inquiry Confirmation')
                    ->markdown('emails.visa_inquiry')
                    ->with(['data' => $this->inquiryData]);
    }
}
