<?php

namespace App\Mail\Registrant;

use App\Models\Gym;
use App\Models\Meet;
use App\Models\MeetRegistration;
use App\Models\MeetTransaction;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

class TransactionExecutedMailable extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $tries = 3;
    public $retryAfter = 300;

    public $meet; /** @var Meet $meet */
    public $gym; /** @var Gym $gym */
    public $registration; /** @var MeetRegistration $registration */
    public $transaction; /** @var MeetTransaction $transaction */
    public $breakdown;
    public $paymentMethodString;
    public $url = '#';

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(MeetTransaction $transaction, string $paymentMethodString)
    {
        $this->paymentMethodString = $paymentMethodString;
        $this->transaction = $transaction;
        $this->breakdown = $this->transaction->breakdown['gym'];
        $this->registration = $this->transaction->meet_registration;
        $this->meet = $this->registration->meet;
        $this->gym = $this->registration->gym;
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
                        'You have made a payment for your registration in for "' . $this->meet->name . '"'
                    )->markdown('emails.registration.transaction_executed');
    }

    public function failed(\Exception $e)
    {
        Log::channel('slack-warning')->warning(self::class . ' email failed : ' . $e->getMessage(), [
            'Registration' => $this->registration,
            'Exception' => $e
        ]);
    }
}
