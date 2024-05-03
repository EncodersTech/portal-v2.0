@component('mail::message')
Hello {{ $name }},

Welcome to the AllGymnastics portal! 
<br><br>
Great news, your account has successfully been created!!  Please make sure to check your spam folder if you haven’t received a verification email.
<br><br>
If you have any issues please reach out to our support team at support@allgymnastics.com.

Thanks,<br>
{{ config('app.name') }}
@endcomponent
