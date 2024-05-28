@component('mail::message')
Hello {{ $name }},

Your meet <b>{{ $meet }}</b> is approaching the registration deadline. We have sent an email to registrants with pending reservations but we recommend you, the host, follow up with them as well. 
<br><br>
To see pending USAG reservations please go to the hosted meet under <b>Dashboard > USAG Reservations > Pending > View Details<b>
<br><br>
Thanks,<br>
{{ config('app.name') }}
@endcomponent
