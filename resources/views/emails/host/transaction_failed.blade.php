@component('mail::message')
Hello {{$gym->user->fullName()}},

A payment related to your gym's ("{{ $gym->name }}") registration for "{{ $meet->name }}" has failed.<br/>

Click the link below to try again.

@component('mail::button', ['url' => $repayUrl, 'color' => 'primary'])
Try Again
@endcomponent

For more details, please click the link below.

@component('mail::button', ['url' => $url, 'color' => 'success'])
View Registration Details
@endcomponent

Thank you,<br>
{{ config('app.name') }}
@endcomponent