@component('mail::message')

{{ \Carbon\Carbon::now()->format('F jS Y') }}<br>
Hello {{$gym->name}},

You have successfully registered {{ $athlete_count }} Athletes for the {{ \Carbon\Carbon::parse($meet->start_date)->format('F jS Y') }}, {{$meet->name}}. You paid a total of &#36;{{ $total_fee }}. For more information please visit your <a href="{{ route('dashboard') }}" style="text-decoration: none !important;">AllGym dashboard</a>.

Thank you,<br>
{{ config('app.name') }}
@endcomponent
