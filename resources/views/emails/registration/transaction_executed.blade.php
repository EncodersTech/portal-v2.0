@component('mail::message')
Hello {{$gym->user->fullName()}},

You made a payment on Allgymnastics.com for your gym "{{ $gym->name }}" registration in
"{{ $meet->name }}".<br/>
Below are your transaction details. For more details, please click the button below.

@include('emails.registration.include.beakdown')

@component('mail::button', ['url' => $url, 'color' => 'success'])
View Registration Details
@endcomponent

Thank you,<br>
{{ config('app.name') }}
@endcomponent