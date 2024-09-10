<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Models\Meet;
class HostTicketConfirmation extends Mailable
{
    use Queueable, SerializesModels;
    public $tries = 3;
    public $retryAfter = 300;

    public $meet;
    public $user_name;
    public $user_email;
    public $user_phone;
    public $ticket;
    public $meet_admissions;
    public $ticket_id;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(Meet $meet, $user_name, $user_email, $user_phone, $ticket, $ticket_id)
    {
        $this->meet = $meet;
        $this->user_name = $user_name;
        $this->user_email = $user_email;
        $this->user_phone = $user_phone;

        $this->ticket = $ticket;
        $this->meet_admissions = $meet->admissions()->get();
        $this->meet_admissions = $this->meet_admissions->sortBy('amount', SORT_REGULAR, true);
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
            ->subject('HOST::Ticket Confirmation of your meet '. $this->meet->name)
            ->markdown('emails.ticket.hostconfirmation');
    }
}
