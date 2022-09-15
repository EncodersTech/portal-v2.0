<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class ContactForm extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $email;
    public $message;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(string $email, string $message)
    {
        $this->email = $email;
        $this->message = $message;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->from(config('mail.from.address'))
                    ->replyTo($this->email)
                    ->subject('New message from Portal')
                    ->markdown('emails.contact_form');
    }
}
