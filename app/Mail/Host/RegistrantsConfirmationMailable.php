<?php

namespace App\Mail\Host;

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
class RegistrantsConfirmationMailable extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $meet; /** @var Meet $meet */
    public $gym; /** @var Gym $gym */
    public $athlete_count;
    public $total_fee;


    public function __construct(Meet $meet, Gym $gym, $totalRegiAth = 0, $totalFees = 0)
    {
        $this->gym = $gym;
        $this->meet = $meet;
        $this->athlete_count = $totalRegiAth;
        $this->total_fee = $totalFees;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return  $this->from(config('mail.from.address'))
            ->subject('Confirmation of '. $this->meet->name .' Registration.')
            ->markdown('emails.host.meet_registrants_confirmation_mail');
    }

    public function failed(\Exception $e)
    {
        Log::channel('slack-warning')->warning(self::class . ' email failed : ' . $e->getMessage(), [
            'Meet' => $this->meet->name,
            'Exception' => $e
        ]);
    }
}
