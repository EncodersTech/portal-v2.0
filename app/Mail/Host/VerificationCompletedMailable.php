<?php

namespace App\Mail\Host;

use App\Models\Gym;
use App\Models\Meet;
use App\Models\MeetRegistration;
use App\Models\RegistrationAthleteVerification;
use App\Models\RegistrationCoachVerification;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

class VerificationCompletedMailable extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $tries = 3;
    public $retryAfter = 300;

    /** @var RegistrationAthleteVerification|RegistrationCoachVerification */
    public $verification;
    
    /** @var MeetRegistration */
    public $registration; 

    /** @var Meet */
    public $meet; 

    /** @var Gym */
    public $gym;

    /** @var Gym */
    public $host;

    public $url;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($verification)
    {
        
        $this->verification = $verification;
        $this->registration = $this->verification->meet_registration; /** @var MeetRegistration $registration */
        $this->meet = $this->registration->meet; /** @var Meet $meet */
        $this->gym = $this->registration->gym ; /** @var Gym $gym */
        $this->host = $this->meet->gym ; /** @var Gym $host */
        $this->url = route(
            'host.meets.dashboard',
            [
                'gym' => $this->meet->gym,
                'meet' => $this->meet
            ]
        );
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
                        'Verification Completed: ' . $this->gym->name . ' in ' . $this->meet->name
                    )->markdown('emails.host.verification_completed');
    }

    public function failed(\Exception $e)
    {
        Log::channel('slack-warning')->warning(self::class . ' email failed : ' . $e->getMessage(), [
            'Verification' => $this->verification,
            'Exception' => $e
        ]);
    }
}
