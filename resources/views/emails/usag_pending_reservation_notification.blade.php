@component('mail::message')
Hello {{ $user->fullName() }},

You have some pending reservation for your already registered meets.
Please login to your account and merge the pending reservation with your existing meet registration.

<br>
If you have any questions or need assistance, please don't hesitate to reach out to us at


Thanks,<br>
{{ config('app.name') }}
@endcomponent
