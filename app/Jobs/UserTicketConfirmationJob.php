<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Mail\UserTicketConfirmation;
use App\Mail\HostTicketConfirmation;
use App\Models\Meet;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class UserTicketConfirmationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    
    public $tries = 1;
    public $retryAfter = 300;

    public $user_email;
    public $user_phone;
    public $meet;
    public $user_name;
    public $ticket;
    public $ticket_id;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($user_email, Meet $meet, $user_name, $user_phone, $ticket, $ticket_id)
    {
        $this->user_email = $user_email;
        $this->user_phone = $user_phone;
        $this->meet = $meet;
        $this->user_name = $user_name;
        $this->ticket = $ticket;
        $this->ticket_id = $ticket_id;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Mail::to($this->user_email)->send(new UserTicketConfirmation($this->meet, $this->user_name, $this->ticket, $this->ticket_id));
        Mail::to($this->meet->gym->user->email)->send(new HostTicketConfirmation($this->meet, $this->user_name, $this->user_email, $this->user_phone, $this->ticket, $this->ticket_id));
    }
    public function failed(\Exception $e)
    {
        Log::critical(
            self::class . ' job failed',
            [
                'exception' => $e->getMessage(),
            ]
        );
    }
}
