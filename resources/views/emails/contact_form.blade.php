@component('mail::message')
Hello,

You have received a new message via the contact form on portal.allgymnastics.com.

- **Email** : {{ $email }}
- **Message** :    
    `{{ $message }}`

Thanks,<br>
{{ config('app.name') }}
@endcomponent
