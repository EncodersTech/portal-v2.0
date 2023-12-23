<?php

namespace App\Mail\Host;

use App\Mail\Registrant\GymRegisteredMailable;
use App\Models\Gym;
use App\Models\Meet;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Class HostReceiveMeetRegistrationMailable
 */
class HostReceiveMeetRegistrationMailable extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $meet; /** @var Meet $meet */
    public $gym; /** @var Gym $gym */
    public $url = '#';


    public function __construct(Meet $meet, Gym $gym)
    {
        $this->gym = $gym;
        $this->meet = $meet;
        $this->url = route('host.meets.dashboard', ['gym' => $meet->gym, 'meet' => $meet]);

    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return  $this->from(config('mail.from.address'))
            ->subject('New Registration received.')
            ->markdown('emails.host.receive_meet_registration_mail');
    }

    public function failed(\Exception $e)
    {
        Log::channel('slack-warning')->warning(self::class . ' email failed : ' . $e->getMessage(), [
            'Meet' => $this->meet->name,
            'Exception' => $e
        ]);
    }
}
