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

class HandlingFeeChargeMailable extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $tries = 3;
    public $retryAfter = 300;

    public $meet; /** @var Meet $meet */
    public $gym; /** @var Gym $gym */
    public $amount; /** @var MeetTransaction $transaction */
    public $gateway;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(Meet $meet, Gym $gym, $amount , $gateway)
    {
        $this->meet = $meet;
        $this->gym = $gym;
        $this->amount =  $amount;
        $this->gateway =  $gateway;
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
                        'Handling Fee Charged'
                    )->markdown('emails.registration.handling_fee_charged');
    }

    public function failed(\Exception $e)
    {
        Log::channel('slack-warning')->warning(self::class . ' email failed : ' . $e->getMessage(), [
            'Deposit' => $this->deposit,
            'Exception' => $e
        ]);
    }
}
