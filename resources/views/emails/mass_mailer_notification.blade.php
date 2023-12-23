@component('mail::message')
{!!  $message  !!}

 Thank you,<br>
 {{ config('app.name') }}
@endcomponent
