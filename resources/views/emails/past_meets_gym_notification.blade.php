@component('mail::message')
Hello, {{ $meet_registration_gym }}!

<p>We are excited to announce that {{ $host_club }}`s  {{ $meet_name }} is now open for registration Because you attended this meet in the past, we wanted to give you one of the first opportunities to register for this year`s event.</p>

Click <a href="{{ $details_link }}">here</a> for meet details.

Happy Handstands!<br>

 {{ config('app.name') }}
@endcomponent
