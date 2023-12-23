<?php

namespace App\Mail\User;

use App\Models\UserBalanceTransaction;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

class WithdrawalRequestedMailable extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $tries = 3;
    public $retryAfter = 300;
    
    public $transaction; /** @var UserBalanceTransaction $attempt */
    public $url;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(UserBalanceTransaction $transaction)
    {
        $this->transaction = $transaction;
        $this->url = route('account.balance.transactions');
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
                        'Your withdrawal is being processed '
                    )->markdown('emails.user.withdrawal_requested');
    }

    public function failed(\Exception $e)
    {
        Log::channel('slack-warning')->warning(self::class . ' email failed : ' . $e->getMessage(), [
            'Transaction' => $this->transaction,
            'Exception' => $e
        ]);
    }
}
