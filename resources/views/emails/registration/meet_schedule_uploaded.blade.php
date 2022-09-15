@component('mail::message')
    Dear {{ $gymName }} - You are receiving this email because you are registered for {{ $meetName }} on {{ $meetStart }}.
     We just wanted to let you know that an updated schedule or an attachment has been uploaded by the {{ $meetHost }}.
     To view, please click <a href="{{ $attachments }}" download="" target="_blank">here </a>. <br><br>
     For the most up-to-date information pushed directly to your phone, and to follow live scoring at the meet, download our mobile app- <a href="https://www.allgymnastics.com/meethub-mobile-app/" download="" target="_blank">MeetHub </a>

 Thank you,<br>
 {{ config('app.name') }}
@endcomponent
