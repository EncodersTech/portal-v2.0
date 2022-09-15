@component('mail::message')
Hello {{$meet->gym->user->first_name . ' ' . $meet->gym->user->last_name}},

The <b>{{ $gym->name }}</b> attempted to register for your <b>{{ $meet->name }}</b>, using the mailed check option. 
Unfortunately, your {{ $gateway }} was declined when charging the handling fees. 
As a result, the registration was cancelled. <br>

Please update your credit card details to ensure future registrations are successful. <br>

Thank you,<br>
{{ config('app.name') }}
@endcomponent