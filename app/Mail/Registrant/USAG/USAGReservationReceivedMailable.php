<?php

namespace App\Mail\Registrant\USAG;

use App\Models\Gym;
use App\Models\LevelCategory;
use App\Models\Meet;
use App\Models\USAGReservation;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

class USAGReservationReceivedMailable extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $tries = 3;
    public $retryAfter = 300;

    /** @var USAGReservation */
    public $reservation;

    /** @var Gym */
    public $gym;

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
    public function __construct(USAGReservation $reservation)
    {
        $this->reservation = $reservation;
        $this->gym = $this->reservation->gym ;
        $this->meet = $this->reservation->usag_sanction->meet;
        $this->category = $this->reservation->usag_sanction->level_category;

        if ($reservation->status != USAGReservation::RESERVATION_STATUS_UNASSIGNED) {
            $this->objectNameString = (
                $this->meet === null ?
                'Your gym "' . $this->gym->name . '"' :
                'Your meet ' . $this->meet->name
            );
            $this->url = route(
                'gyms.reservation.usag',
                [
                    'gym' => $this->gym,
                    'reservation' => $this->reservation,
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
                        'USAG Reservation For Sanction #' . $this->reservation->usag_sanction->number . ($this->gym !== null ? ' For ' . $this->gym->name : '')
                    )->markdown('emails.gym.usag.reservation_received');
    }

    public function failed(\Exception $e)
    {
        Log::channel('slack-warning')->warning(self::class . ' email failed : ' . $e->getMessage(), [
            'Reservation' => $this->reservation,
            'Exception' => $e
        ]);
    }
}
