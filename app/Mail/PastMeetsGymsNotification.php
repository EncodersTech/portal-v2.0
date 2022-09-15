<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class PastMeetsGymsNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $data;

    /**
     * MassMailerNotification constructor.
     * @param $view
     * @param $subject
     * @param array $data
     */
    public function __construct($view, $subject, $data = [])
    {
        $this->data = $data;
        $this->view = $view;
        $this->subject = $subject;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $mail =  $this->subject($this->subject)
            ->markdown($this->view)
            ->with($this->data);

        return $mail;
    }

    public function failed(\Exception $e)
    {
        Log::channel('slack-warning')->warning(self::class . ' email failed : ' . $e->getMessage(), [
            'subject' => $this->subject,
            'Exception' => $e
        ]);
    }
}
