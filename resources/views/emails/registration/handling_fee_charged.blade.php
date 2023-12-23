@component('mail::message')
Hello {{$meet->gym->user->first_name . ' ' . $meet->gym->user->last_name}},

Your meet <b>{{ $meet->name }}</b> has a new transaction through mailed check from {{ $gym->name }} and it's handling fee ${{ number_format($amount,2) }} has been 
charged from your {{ $gateway }} balance. <br>

Still, it requires you to accept the mailed check from <b>Transaction</b> tab in the meet dashboard once you receive the check from registering gym. <br>

Thank you,<br>
{{ config('app.name') }}
@endcomponent