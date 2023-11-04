@component('mail::message')
Hello,

Dear **{{ $name }}**
<br><br>
Confirming we've received your e-mail. We will review and reply as soon as possible. Our customer service team is available Monday Through Friday from 8am-5pm Eastern Standard time. 
<br><br>
We look forward to helping out! - AllGymnastics Happy Customer Service E-Mailing Robot
<br><br>
Thanks,<br>
{{ config('app.name') }}
@endcomponent
