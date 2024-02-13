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

class GymRegisteredMailable extends Mailable implements ShouldQueue
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
     * @var USAGSanction|null
     */
    public $attachment;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(Meet $meet, Gym $gym, MeetRegistration $registration,
        array $breakdown, string $paymentMethodString, bool $hadRegular, bool $hadWaitlist, USAGSanction $sanction = null, $attachment = null)
    {
        //Log::debug(compact('hadRegular', 'hadWaitlist'));
        $this->meet = $meet;
        $this->gym = $gym;
        $this->registration = $registration;
        $this->breakdown = $breakdown;
        $this->paymentMethodString = $paymentMethodString;
        $this->hadRegular = $hadRegular;
        $this->hadWaitlist = $hadWaitlist;
        $this->sanction = $sanction;
        $this->attachment = $attachment;
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
        $mail = $this->from(config('mail.from.address'))
                    ->subject(
                        ($this->sanction !== null ? 'USAG Sanction No. ' . $this->sanction->number . ': ' : '') .
                        'Confirmation of "' . $this->meet->name . '" Registration'
                    )->markdown('emails.registration.registered');

        if ($this->attachment) {
            $mail = $mail->attach($this->attachment, [
                'as' => "meet_entry_report.pdf",
                'mime' => "application/pdf",
            ]);
        }

        return  $mail;
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
