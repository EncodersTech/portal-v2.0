<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class NewMeetMailable extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public $meet;
    public $user;
    public function __construct($meet)
    {
        $this->meet = $meet;
        $this->user = auth()->user()->fullName();
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->from(config('mail.from.address'))
        ->subject('Congratulations! You have a new meet!')
        ->markdown('emails.new_meet_mailable');
    }
}
