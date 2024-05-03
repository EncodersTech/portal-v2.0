<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Mail\USAGPendingReservationMail;
use Illuminate\Support\Facades\Mail;

class USAGPendingReservationNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    public $tries = 3;
    public $retryAfter = 5;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public $user;
    public $meet_name;
    public function __construct($user, $meet_name)
    {
        $this->user = $user;
        $this->meet_name = $meet_name;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Mail::to($this->user->email)->send(new USAGPendingReservationMail($this->user, $this->meet_name));
    }
}
