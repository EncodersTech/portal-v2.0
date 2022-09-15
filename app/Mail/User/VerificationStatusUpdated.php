<?php

namespace App\Mail\User;

use App\Models\DwollaVerificationAttempt;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

class VerificationStatusUpdated extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $tries = 3;
    public $retryAfter = 300;

    public $attempt; /** @var DwollaVerificationAttempt $attempt */
    public $succeeded;
    public $url;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(bool $succeeded, DwollaVerificationAttempt $attempt)
    {
        $this->attempt = $attempt;
        $this->succeeded = $succeeded;
        $this->url = route('account.payment.options');
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
                        'Your linked Dwolla account verification has ' .
                        ($this->succeeded ? 'succeeded' : 'failed')
                    )->markdown('emails.user.verification_updated');
    }

    public function failed(\Exception $e)
    {
        Log::channel('slack-warning')->warning(self::class . ' email failed : ' . $e->getMessage(), [
            'Attempt' => $this->attempt,
            'Exception' => $e
        ]);
    }
}
