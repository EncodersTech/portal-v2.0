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

class DepositCompleteMailable extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $tries = 3;
    public $retryAfter = 300;

    public $meet; /** @var Meet $meet */
    public $gym; /** @var Gym $gym */
    public $deposit; /** @var MeetTransaction $transaction */
    public $edit;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(Deposit $deposit)
    {
        $this->deposit = $deposit;
        $this->edit = $deposit->edit;
        $this->meet = Meet::where('id',$deposit->meet_id)->first();
        $this->gym =  $deposit->gymDetails;
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
                        'You have a deposit coupon'
                    )->markdown('emails.registration.created_deposit');
    }

    public function failed(\Exception $e)
    {
        Log::channel('slack-warning')->warning(self::class . ' email failed : ' . $e->getMessage(), [
            'Deposit' => $this->deposit,
            'Exception' => $e
        ]);
    }
}
