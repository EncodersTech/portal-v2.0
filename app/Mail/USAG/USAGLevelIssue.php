<?php

namespace App\Mail\USAG;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class USAGLevelIssue extends Mailable
{
    use Queueable, SerializesModels;

    public $tries = 3;
    public $retryAfter = 300;
    public $levels = [];
    public $data = [];
    public $url = '';
    public function __construct($levels, $data)
    {
        $this->levels = $levels;
        $this->data = $data;
        $this->url = route('admin.usag_level');
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
                        'USAG Level Issue: Level not found in the system'
                    )->markdown('emails.usag.level_issue');
    }
    public function failed(\Exception $e)
    {
        Log::channel('slack-warning')->warning(self::class . ' email failed : ' . $e->getMessage(), [
            'Items' => $this->items,
            'Exception' => $e
        ]);
    }
}
