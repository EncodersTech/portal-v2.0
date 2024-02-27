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
class RegistrationUpdateMailable extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $tries = 3;
    public $retryAfter = 300;
    
    public $meet; /** @var Meet $meet */
    public $gym; /** @var Gym $gym */
    public $changes;
    public $attachment;

    public function __construct(Meet $meet, Gym $gym, $changes, $attachment = null)
    {
        $this->gym = $gym;
        $this->meet = $meet;
        $this->changes = $changes;
        $this->attachment = $attachment;
    }
    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return  $this->from(config('mail.from.address'))
            ->subject('Update of '. $this->meet->name .' Registration.')
            ->markdown('emails.host.registrationupdate')
            ->attach($this->attachment, [
                'as' => 'meet_entry_report_for_' . $this->meet->name . '.pdf',
                'mime' => 'application/pdf',
            ]);
    }

    public function failed(\Exception $e)
    {
        Log::channel('slack-warning')->warning(self::class . ' email failed : ' . $e->getMessage(), [
            'Meet' => $this->meet->name,
            'Exception' => $e
        ]);
    }
}
