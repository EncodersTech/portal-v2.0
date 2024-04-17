@component('mail::message')
Hello {{ $user }},
<br>

Congratualiions! You have successfully created a new meet on the AllGymnastics portal!

- **Meet Name** : {{ $meet->name }}
- **Meet Start Date** : {{ $meet->start_date->format(Helper::AMERICAN_SHORT_DATE) }}
- **Meet End Date** : {{ $meet->end_date->format(Helper::AMERICAN_SHORT_DATE) }}

Please take a moment to review your meet details and make any necessary changes. 
<br><br>
Thanks,<br>
{{ config('app.name') }}
@endcomponent
