<?php

namespace App\Mail\Registrant;

use App\Models\Gym;
use App\Models\Meet;
use App\Models\MeetRegistration;
use App\Models\USAGSanction;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

class GymRegistrationUpdatedMailable extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $tries = 3;
    public $retryAfter = 300;

    public $meet; /** @var Meet $meet */
    public $gym; /** @var Gym $gym */
    public $registration; /** @var MeetRegistration $registration */
    public $breakdown;
    public $paymentMethodString;
    public $hadRegular;
    public $hadWaitlist;
    public $sanction; /** @var USAGSanction $sanction */
    public $url = '#';

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(Meet $meet, Gym $gym, MeetRegistration $registration,
        array $breakdown, string $paymentMethodString, bool $hadRegular, bool $hadWaitlist, USAGSanction $sanction = null, $pdf = null)
    {
        $this->meet = $meet;
        $this->gym = $gym;
        $this->registration = $registration;
        $this->breakdown = $breakdown;
        $this->paymentMethodString = $paymentMethodString;
        $this->hadRegular = $hadRegular;
        $this->hadWaitlist = $hadWaitlist;
        $this->sanction = $sanction;
        // This commented section userd for Meet Entry pdf send for registaring host and gym
        // $this->pdf = $pdf;
        $this->url = route(
            'gyms.registration',
            [
                'gym' => $this->gym,
                'registration' => $this->registration
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
                        ($this->sanction !== null ? 'USAG Sanction No. ' . $this->sanction->number . ': ' : '') .
                        'You have updated your registration for "' . $this->meet->name . '"'
                    )->markdown('emails.registration.updated');
                    
        // This commented section userd for Meet Entry pdf send for registaring host and gym
        // return $this->from(config('mail.from.address'))
        //     ->attachData($this->pdf->output(), "MeetEntryReport.pdf")
        //     ->subject(
        //         ($this->sanction !== null ? 'USAG Sanction No. ' . $this->sanction->number . ': ' : '') .
        //         'You have updated your registration for "' . $this->meet->name . '"'
        //     )->markdown('emails.registration.updated');
    }

    public function failed(\Exception $e)
    {
        Log::channel('slack-warning')->warning(self::class . ' email failed : ' . $e->getMessage(), [
            'Sanction' => $this->sanction,
            'Registration' => $this->registration,
            'Exception' => $e
        ]);
    }
}
