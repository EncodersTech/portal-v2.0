<?php

namespace App\Mail\Registrant;

use App\Models\Gym;
use App\Models\Deposit;
use App\Models\Meet;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

class NotifyMailCheckMailable extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $tries = 3;
    public $retryAfter = 300;

    public $name;
    public $meet;
    /**
     * Create a new message instance.
     *
     * @return void
     */
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
                    ->subject(
                        'Important Notice on Pending Mailed Check'
                    )->markdown('emails.registration.notify_mailed_check');
    }

    public function failed(\Exception $e)
    {
        Log::channel('slack-warning')->warning(self::class . ' email failed : ' . $e->getMessage(), [
            'Exception' => $e
        ]);
    }
}
