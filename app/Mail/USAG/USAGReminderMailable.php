<?php

namespace App\Mail\USAG;

use App\Models\Gym;
use App\Models\Meet;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

class USAGReminderMailable extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $tries = 3;
    public $retryAfter = 300;

    /** @var array */
    public $items;

    /** @var string */
    public $types = '';

    /** @var bool */
    public $hasSanctions = false;

    /** @var bool */
    public $hasReservations = false;

    /** @var bool */
    public $isUnassigned = false;

    /** @var string */
    public $url;

    /** @var string */
    public $contact_name = null;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(array $items, bool $unassigned = false)
    {
        $this->items = $items;
        
        $this->contact_name = $this->items['contact_name'];

        $this->isUnassigned = $unassigned;
        
        $typesArray = [];

        $this->hasSanctions = (count($this->items['sanctions']) > 0);
        $this->hasReservations = (count($this->items['reservations']) > 0);

        if ($this->hasSanctions)
            $typesArray[] = 'Sanctions';

        if ($this->hasReservations)
            $typesArray[] = 'Reservations';
            
        $this->types = implode(' & ', $typesArray);

        $this->url = ($this->isUnassigned ? route('register') : route('dashboard'));
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
                        'Reminder: Pending USAG ' . $this->types
                    )->markdown('emails.usag.reminder');
    }

    public function failed(\Exception $e)
    {
        Log::channel('slack-warning')->warning(self::class . ' email failed : ' . $e->getMessage(), [
            'Items' => $this->items,
            'Exception' => $e
        ]);
    }
}
