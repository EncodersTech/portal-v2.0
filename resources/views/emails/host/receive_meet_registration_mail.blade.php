@component('mail::message')
Hello {{$meet->gym->user->fullName()}},

"{{ $gym->name }}" has registered for your {{ $meet->formatted_date }} "{{ $meet->name }}". For more information please visit your AllGym Account Dashboard.<br/>

@component('mail::button', ['url' => $url, 'color' => 'success'])
 View Registration Details
@endcomponent

Thank you,<br>
{{ config('app.name') }}
@endcomponent
