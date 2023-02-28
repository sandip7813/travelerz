<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class EmailVerificationOtp extends Mailable
{
    use Queueable, SerializesModels;

    public $mailDetails;

    public function __construct($mailDetails)
    {
        $this->mailDetails = $mailDetails;
    }

    public function build()
    {
        $subject = config('app.name') . ' - Verify your Email';
        return $this->subject($subject)->markdown('emails.email-verification-otp')->with('mailDetails', $this->mailDetails );
    }
}
