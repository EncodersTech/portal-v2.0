@component('mail::message')
Hello {{$transaction->user->fullName()}},

Your withdrawal request of ${{ number_format(-$transaction->total, 2) }} is being processed.

@component('mail::button', ['url' => $url, 'color' => 'success'])
View Transactions
@endcomponent

Thank you,<br>
{{ config('app.name') }}
@endcomponent