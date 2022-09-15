@component('mail::message')
Hello{{ $invited != null ? ' ' . $invited->fullName() : '' }},

{{ $user->fullName() }} invited you to manage their account on Allgymnastics.

@if ($invited == null)
If you do not have an account with us yet, you will be presented with a form to sign up first.    
@endif

@component('mail::button', ['url' => $url, 'color' => 'success'])
Accept Invitation
@endcomponent

Thank you,<br>
{{ config('app.name') }}
@endcomponent