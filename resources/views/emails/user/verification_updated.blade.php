@component('mail::message')
Hello {{$attempt->user->fullName()}},

@if ($succeeded)
    Your linked Dwolla account has been successfully verified.
    You can see more details by clicking the link below.
@else
    Your linked Dwolla account verification has failed.
    You can see more details or try again by clicking the link below.
@endif

@component('mail::button', ['url' => $url, 'color' => ($succeeded ? 'success' : 'error')])
View Payment Options
@endcomponent

Thank you,<br>
{{ config('app.name') }}
@endcomponent