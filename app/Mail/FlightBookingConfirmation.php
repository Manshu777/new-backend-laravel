<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class FlightBookingConfirmation extends Mailable
{
    use Queueable, SerializesModels;

    public $ticket;
    public $replyToEmail;

    /**
     * Create a new message instance.
     *
     * @param array $ticket
     * @param string $replyToEmail
     * @return void
     */
    public function __construct(array $ticket, string $replyToEmail)
    {
        $this->ticket = $ticket;
        $this->replyToEmail = $replyToEmail;
    }

    public function build()
    {
        // Generate LaTeX file dynamically
        $texContent = view('latex.flight_itinerary', ['ticket' => $this->ticket])->render();
        $texPath = storage_path('app/public/flight_itinerary_' . $this->ticket['pnr'] . '.tex');
        $pdfPath = storage_path('app/public/flight_itinerary_' . $this->ticket['pnr'] . '.pdf');

        // Save LaTeX file
        file_put_contents($texPath, $texContent);

        // Compile LaTeX to PDF
        $outputDir = storage_path('app/public');
        exec("latexmk -pdf -outdir={$outputDir} {$texPath} 2>&1", $output, $returnCode);

        if ($returnCode !== 0 || !file_exists($pdfPath)) {
            \Log::error('Failed to generate PDF invoice', ['output' => $output]);
            // Proceed with email even if PDF fails (optional)
        }

        // Build email with PDF attachment
        $email = $this->from('no-reply@nextgentrip.com', 'NextGenTrip')
                     ->replyTo($this->replyToEmail)
                     ->subject('Your Flight Ticket - NextGenTrip')
                     ->view('emails.flight-booking');

        // Attach PDF if generated successfully
        if (file_exists($pdfPath)) {
            $email->attach($pdfPath, [
                'as' => 'Invoice_' . $this->ticket['pnr'] . '.pdf',
                'mime' => 'application/pdf',
            ]);
        }

        return $email;
    }
}