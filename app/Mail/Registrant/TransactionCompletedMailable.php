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

class TransactionCompletedMailable extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $tries = 3;
    public $retryAfter = 300;

    public $meet; /** @var Meet $meet */
    public $gym; /** @var Gym $gym */
    public $registration; /** @var MeetRegistration $registration */
    public $transaction; /** @var MeetTransaction $transaction */
    public $url;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(MeetTransaction $transaction)
    {
        $this->transaction = $transaction;
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
                        'Your transaction was completed'
                    )->markdown('emails.registration.transaction_completed');
    }

    public function failed(\Exception $e)
    {
        Log::channel('slack-warning')->warning(self::class . ' email failed : ' . $e->getMessage(), [
            'Transaction' => $this->transaction,
            'Exception' => $e
        ]);
    }
}
