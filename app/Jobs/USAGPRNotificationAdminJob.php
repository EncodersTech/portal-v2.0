<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Mail\USAGPRNotificationAdmin;
use Illuminate\Support\Facades\Mail;

class USAGPRNotificationAdminJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    public $tries = 3;
    public $retryAfter = 5;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public $email;
    public $name;
    public $meet_name;
    public function __construct($email, $name, $meet_name)
    {
        $this->email = $email;
        $this->name = $name;
        $this->meet_name = $meet_name;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Mail::to($this->email)->send(new USAGPRNotificationAdmin($this->name, $this->meet_name));
    }
}
