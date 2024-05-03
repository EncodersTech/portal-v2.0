@component('mail::message')
Hello {{ $name }},

Welcome to AllGymnastics. Your account has been created and verified, let’s finish the set up. 
<br><br>
If you created an account to use the MeetHub app you are all set. If you are an owner or coach who is going to register or host meets please see the below tools to help set up your account. 
<b>Please note: If your gym is already attached to an existing AllGymnastics account please ask that account to add you as an authorized user via the Access Management page. </b>
<br><br>
The next steps include adding your gym, adding payment methods, and verifying your bank account. Please see the following help pages if you need assistance.
<br><br>
<ul>
    <li><a href="https://allgymnastics.zendesk.com/hc/en-us/articles/115001980912-Completing-Your-Profile">Completing your Profile - Adding your Gym </a></li>
    <li><a href="https://allgymnastics.zendesk.com/hc/en-us/articles/360052047232-Entering-Payment-Information-Becoming-ACH-Dwolla-Verified">Adding Payment Methods and Verification</a></li>
    <li><a href="https://allgymnastics.zendesk.com/hc/en-us/articles/29221872758169-Adding-Authorized-Users">Adding user via Access Management</a> – this must be done from the original account holder with the gym linked to their account</li>
</ul>
<br>
If you’d prefer a video explanation, please view our tutorial video <a href="https://youtu.be/X5XQdLecdGs?si=VwvkURjkjMlTkiRM">Here</a>.

Once your account is set up you will be able to register for and/or host meets. The instructions on how to do that are linked below
<br><br>
<ul>
    <li><a href="https://allgymnastics.zendesk.com/hc/en-us/articles/360051384552-Creating-New-Meet-from-Scratch">Creating/Hosting a Meet</a></li>
    <li>
        Registering for Meets:
        <a href="https://allgymnastics.zendesk.com/hc/en-us/articles/360050988731-Registering-for-a-USA-Gymnastics-Meet">USAG</a>
        <a href="https://allgymnastics.zendesk.com/hc/en-us/articles/360060510252-Registering-for-NGA-Gymnastics-Meet">NGA</a>
        <a href="https://allgymnastics.zendesk.com/hc/en-us/articles/360050529932-Registering-USAIGC">USAIGC</a>
    </li>
</ul>
<br>
If you have any issues that you don’t see on our help pages above please reach out to our support team at support@allgymnastics.com
<br><br>
Thanks,<br>
{{ config('app.name') }}
@endcomponent
