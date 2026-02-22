<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

/**
 * General candidate communication email. Used to send interview invitations, status updates, and
 * other HR communications to job candidates during the recruitment process.
 *
 * Data Flow:
 *   HR action triggers → Prepare email content → Send to candidate → Track delivery
 *
 * @depends-on Candidate
 * @business-domain HR & Recruitment
 * @package App\Mail
 */
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
