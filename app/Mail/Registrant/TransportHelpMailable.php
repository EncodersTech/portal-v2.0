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

class TransportHelpMailable extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $tries = 3;
    public $retryAfter = 300;

    public $meet; /** @var Meet $meet */
    public $gym; /** @var Gym $gym */
    public $number_of;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(Meet $meet, Gym $gym, $number_of)
    {
        $this->gym = $gym;
        $this->meet = $meet;
        $this->number_of = $number_of;
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
                        'Gym Needs Help with Transportation'
                    )->markdown('emails.registration.transportation_help_markdown_mail');
    }

    public function failed(\Exception $e)
    {
        Log::channel('slack-warning')->warning(self::class . ' email failed : ' . $e->getMessage(), [
            'Deposit' => $this->number_of,
            'Exception' => $e
        ]);
    }
}
