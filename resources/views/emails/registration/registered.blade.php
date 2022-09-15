@component('mail::message')
Hello {{$gym->user->fullName()}},

Your gym "{{ $gym->name }}" has registered for "{{ $meet->name }}".<br/>

@if ($hadWaitlist)
Athletes entered the waitlist for "{{ $meet->name }}".
You were not charged for said athletes.<br/>
If the meet host approves or rejects your athletes, you will receive an email.<br/>
@endif

@if ($hadRegular)
Athletes were registered in "{{ $meet->name }}".
Below are your transaction details. For more details, please click the button below.<br/>

@include('emails.registration.include.beakdown')
@endif

@component('mail::button', ['url' => $url, 'color' => 'success'])
View Registration Details
@endcomponent

Thank you,<br>
{{ config('app.name') }}
@endcomponent