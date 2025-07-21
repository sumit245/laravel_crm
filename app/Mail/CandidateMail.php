<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class CandidateMail extends Mailable
{
    use Queueable, SerializesModels;

    public $candidate;

    /**
     * Create a new message instance.
     */
    public function __construct($candidate)
    {
        $this->candidate = $candidate;
    }

    /**
     * Build the email message.
     */
    public function build()
    {
        return $this->subject('Offer Letter from Sugs Lloyd Limited for' . $this->candidate->name)
            ->view('emails.candidate')
            ->with(['candidate' => $this->candidate]);
    }
}
