@component('mail::message')
Hello {{ $user->fullName() }},

{{ $invited->fullName() }} has accepted your invitation to manage your account on Allgymnastics.
Log into your dashboard to manage their permisisons.

@component('mail::button', ['url' => $url, 'color' => 'primary'])
Open My Dashboard
@endcomponent

Thank you,<br>
{{ config('app.name') }}
@endcomponent