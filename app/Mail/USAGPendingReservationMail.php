<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class USAGPendingReservationMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public $user;
    public $meet_name;
    public function __construct($user, $meet_name)
    {
        $this->user = $user;
        $this->meet_name = $meet_name;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->from(config('mail.from.address'))
                    ->subject('Pending USAG Reservation Notification!')
                    ->markdown('emails.usag_pending_reservation_notification');
    }
}
