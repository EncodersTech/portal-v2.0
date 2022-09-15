<?php

namespace App\Mail\Host\USAG;

use App\Models\Gym;
use App\Models\LevelCategory;
use App\Models\Meet;
use App\Models\USAGSanction;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

class USAGSanctionReceivedMailable extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $tries = 3;
    public $retryAfter = 300;

    /** @var USAGSanction */
    public $sanction;

    /** @var Gym */
    public $host;

    /** @var Meet */
    public $meet;

    /** @var LevelCategory */
    public $category;

    public $objectNameString;
    public $url;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(USAGSanction $sanction)
    {
        $this->sanction = $sanction;
        $this->host = $this->sanction->gym ; /** @var Gym $host */
        $this->meet = $this->sanction->meet ; /** @var Meet $meet */
        $this->category = $this->sanction->level_category ; /** @var LevelCategory $category */

        if ($sanction->status != USAGSanction::SANCTION_STATUS_UNASSIGNED) {
            $this->objectNameString = (
                $this->meet === null ?
                'Your gym "' . $this->host->name . '"' :
                'Your meet ' . $this->meet->name
            );
            $this->url = route(
                'gyms.sanctions.usag',
                [
                    'gym' => $this->host,
                    'sanction' => $this->sanction->number
                ]
            );
        } else {
            $this->objectNameString = null;
            $this->url = route('register');
        }
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
                        'USAG Sanction #' . $this->sanction->number . ($this->host !== null ? ' For ' . $this->host->name : '')
                    )->markdown('emails.host.usag.sanction_received');
    }

    public function failed(\Exception $e)
    {
        Log::channel('slack-warning')->warning(self::class . ' email failed : ' . $e->getMessage(), [
            'Sanction' => $this->sanction,
            'Exception' => $e
        ]);
    }
}
