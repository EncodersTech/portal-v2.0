@component('mail::message')
Hello {{ $contact_name }},

This is a reminder that you have the following pending USAG {{ $types }}:

@if ($hasSanctions)
### Sanctions No.:
@foreach ($items['sanctions'] as $s)
- {{ $s }}
@endforeach
@endif

@if ($hasReservations)
### Reservations No.:
@foreach ($items['reservations'] as $r)
- {{ $r }}
@endforeach
@endif

@if($isUnassigned)
To manage your USAG {{ $types }}, please create an Allgymnastics account. After creating your account, you can :

@if ($hasSanctions)
- Create gyms with the correct USAG membership number for your sanctions to automatically show up
@endif

@if ($hasReservations)
- Create gyms with the correct USAG membership number for your reservations to automatically show up
@endif

Create your account by clicking the link below :

@component('mail::button', ['url' => $url, 'color' => 'success'])
Create An Account
@endcomponent
@else
Please visit your dashboard by clicking the button below :

@component('mail::button', ['url' => $url, 'color' => 'success'])
View Dashboard
@endcomponent
@endif

Thank you,<br>
{{ config('app.name') }}
@endcomponent