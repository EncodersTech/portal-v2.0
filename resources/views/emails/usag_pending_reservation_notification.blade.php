@component('mail::message')
Hello {{ $user->fullName() }},

The registration deadline for <b>{{ $meet_name }}</b> is approaching and there are currently pending reservations on your dashboard. Please merge these reservations to ensure there are no problems with your registration. 
Once the deadline passes these registrations will not be able to be processed unless the meet host has enabled late registration (Please note, late fees may apply).
<br><br>
Your registration is not confirmed until the reservations are merged and payment is processed. If you have questions pertaining to the meet please reach out to the meet host directly. Any refunds due will be handled directly by the meet host. 

<br>
If you have any questions or need assistance, please don't hesitate to reach out to us at support@allgymnastics.com.

<br>
Thanks,<br>
{{ config('app.name') }}
@endcomponent
