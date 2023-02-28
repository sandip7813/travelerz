<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ResetPassword extends Mailable
{
    use Queueable, SerializesModels;

    public $mailDetails;

    public function __construct($mailDetails)
    {
        $this->mailDetails = $mailDetails;
    }

    public function build()
    {
        $subject = config('app.name') . ' - Reset your password';
        return $this->subject($subject)->markdown('emails.reset-password')->with('mailDetails', $this->mailDetails );
    }
}
