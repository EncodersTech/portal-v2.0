@component('mail::message')
Hello {{ $name }},

Welcome to the AllGymnastics portal! 
<br><br>
We are excited to have you as a member of our community. 
Please take a moment to complete your profile by adding your gym(s), athletes and coaches. 
You can also add your payment methods by following the link: {{ $payment_method_url }}.
<br>
<br>
If you have any questions or need assistance, please don't hesitate to reach out to us at


Thanks,<br>
{{ config('app.name') }}
@endcomponent
