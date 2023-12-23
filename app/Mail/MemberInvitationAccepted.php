<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class MemberInvitationAccepted extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $tries = 3;
    public $retryAfter = 300;

    public $user;
    public $invited;
    public $url;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(User $user, User $invited)
    {
        $this->user = $user;
        $this->invited = $invited;
        $this->url = route('account.access.management');
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->from(config('mail.from.address'))
                    ->replyTo(config('mail.from.address'))
                    ->subject($this->invited->fullName() . ' accepted your invitation on Allgymnastics')
                    ->markdown('emails.member_invite_accepted');
    }

    public function failed(\Exception $e)
    {
        Log::channel('slack-warning')->warning(self::class . ' email failed : ' . $e->getMessage(), [
            'User' => $this->user->email,
            'Invited' => $this->invited->email,
            'Exception' => $e
        ]);
    }
}
