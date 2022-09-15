<?php

namespace App\Mail\Registrant;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class MeetUpdatedMailable extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $tries = 3;
    public $retryAfter = 300;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct()
    {

    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        /*return $this->from(config('mail.from.address'))
                    ->replyTo($this->user->email)
                    ->subject($this->user->fullName() . ' invited you on Allgymnastics')
                    ->markdown('emails.member_invite');*/
    }

    public function failed(\Exception $e)
    {
        Log::channel('slack-warning')->warning(self::class . ' email failed : ' . $e->getMessage(), [
            'Exception' => $e
        ]);
    }
}
