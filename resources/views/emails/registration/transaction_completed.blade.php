@component('mail::message')
Hello {{$gym->user->fullName()}},

A payment related to your gym's ("{{ $gym->name }}") registration for "{{ $meet->name }}" has been completed.<br/>
For more details, please click the link below.

@component('mail::button', ['url' => $url, 'color' => 'success'])
View Registration Details
@endcomponent

Thank you,<br>
{{ config('app.name') }}
@endcomponent