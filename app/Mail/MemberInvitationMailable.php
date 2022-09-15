<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class MemberInvitationMailable extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $tries = 3;
    public $retryAfter = 300;

    public $user;
    public $token;
    public $invited;
    public $url;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(User $user, string $token, ?User $invited = null)
    {
        $this->user = $user;
        $this->$token = $token;
        $this->invited = $invited;

        $route = 'register.member.invite';
        if ($invited != null)
            $route = 'invite.member.accept';

        $this->url = route($route, ['token' => $token]);
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->from(config('mail.from.address'))
                    ->replyTo($this->user->email)
                    ->subject($this->user->fullName() . ' invited you on Allgymnastics')
                    ->markdown('emails.member_invite');
    }

    public function failed(\Exception $e)
    {
        Log::channel('slack-warning')->warning(self::class . ' email failed : ' . $e->getMessage(), [
            'User' => $this->user->email,
            'Url' => $this->url,
            'Exception' => $e
        ]);
    }
}
