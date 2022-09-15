@component('mail::message')
Hello {{$host->user->fullName()}},

A payment from ("{{ $gym->name }}") for "{{ $meet->name }}" has been completed.<br/>
For more details, please click the link below.

@component('mail::button', ['url' => $url, 'color' => 'success'])
View Meet Dashboard
@endcomponent

Thank you,<br>
{{ config('app.name') }}
@endcomponent