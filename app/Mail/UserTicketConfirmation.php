<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Models\Meet;

class UserTicketConfirmation extends Mailable
{
    use Queueable, SerializesModels;
    public $tries = 3;
    public $retryAfter = 300;

    public $meet;
    public $user_name;
    public $ticket;
    public $meet_admissions;
    public $ticket_id;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(Meet $meet, $user_name, $ticket, $ticket_id)
    {
        $this->meet = $meet;
        $this->user_name = $user_name;
        $this->ticket = $ticket;
        $this->meet_admissions = $meet->admissions()->get();
        $this->ticket_id = $ticket_id;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return  $this->from(config('mail.from.address'))
            ->subject('Ticket Confirmation of '. $this->meet->name)
            ->markdown('emails.ticket.userconfirmation');
    }
}
