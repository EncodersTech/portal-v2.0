@component('mail::message')
Hello {{$gym->user->fullName()}},

The host of "{{ $meet->name }}" confirmed your gym's ("{{ $gym->name }}") waitlist entry for
the meet.<br/>

However, your spots are not reserved in the meet until you complete the related payment.

Click the link below to pay for your registration.

@component('mail::button', ['url' => $repayUrl, 'color' => 'success'])
Pay Now
@endcomponent

For more details, please click the link below.

@component('mail::button', ['url' => $url, 'color' => 'primary'])
View Registration Details
@endcomponent

Thank you,<br>
{{ config('app.name') }}
@endcomponent