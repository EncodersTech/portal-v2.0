<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Mail\PastMeetsGymsNotification;
use Illuminate\Support\Facades\Mail as Email;

class MailtoPastMeets implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    public $tries = 1;
    public $retryAfter = 300;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public $email;
    public $data;
    public function __construct($email, $data)
    {
        $this->email = $email;
        $this->data = $data;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Email::to($this->email)->send(new PastMeetsGymsNotification('emails.past_meets_gym_notification',$this->data['subject'], $this->data));
    }
}
