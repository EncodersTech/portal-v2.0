<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class USAGPRNotificationAdmin extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public $name;
    public $meet;

    public function __construct($name,$meet)
    {
        $this->name = $name;
        $this->meet = $meet;
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
        ->markdown('emails.usag_pending_reservation_notification_host');
    }
}
