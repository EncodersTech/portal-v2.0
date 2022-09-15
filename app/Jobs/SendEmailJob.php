<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Mail\MassMailerNotification;
use Mail;
class SendEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    protected $mail_ids, $template, $subject, $input;
    public function __construct($mail_ids,$template,$subject,$input)
    {
        $this->mail_ids = $mail_ids;
        $this->template = $template;
        $this->subject = $subject;
        $this->input = $input;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $email = new MassMailerNotification($this->template,$this->subject,$this->input);
        Mail::to($this->mail_ids)->send($email);
    }
}
