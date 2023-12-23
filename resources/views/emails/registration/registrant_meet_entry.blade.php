@component('mail::message')
# Hello {{ $gymName }}

## See the PDF of your meet entry attached to the mail.

Thank you,<br>
{{ config('app.name') }}
@endcomponent
