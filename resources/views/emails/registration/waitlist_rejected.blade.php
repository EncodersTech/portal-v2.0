@component('mail::message')
Hello {{$gym->user->fullName()}},

The host of "{{ $meet->name }}" reject your gym's ("{{ $gym->name }}") waitlist entry for
the meet.<br/>

Thank you,<br>
{{ config('app.name') }}
@endcomponent