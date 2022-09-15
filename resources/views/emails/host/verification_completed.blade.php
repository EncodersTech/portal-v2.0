@component('mail::message')
Hello {{$host->user->fullName()}},

A verification you initiated for "{{ $gym->name }}" registration in "{{ $meet->name }}" has been completed.<br/>
For more details, please visit your meet dashboard.

@component('mail::button', ['url' => $url, 'color' => 'success'])
View Meet Dashboard
@endcomponent

Thank you,<br>
{{ config('app.name') }}
@endcomponent