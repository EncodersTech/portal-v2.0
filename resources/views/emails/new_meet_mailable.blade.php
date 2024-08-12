@component('mail::message')
Hello {{ $user }},
<br>

Thank you for using AllGymnastics to host your meet.
Your meet <b>"{{ $meet->name }}"</b> has been successfully created. 
Once your meet is created you must publish it so it appears <b>open</b> in the browse meets tab.
<br><br>
AllGymnastics offers a featured meet option which will put your meet at the very top when opening Browse Meets. Currently, the meets are sorted by the dates which the competition runs. By opting for the featured meet option you will get more users to view your meet. 
<br><br>
We have created a QR code to your meet to assist in your marketing. Place this code on your marketing material so users can easily get to your meet. 
<br><br>
If you’re having trouble merging a USAG sanction with your hosted meet please see the help page linked below. For your convenience we have also provided a help link with a video tutorial if you are using ProScore as your meet scoring software
<ul>
    <li>
        <a href="https://allgymnastics.zendesk.com/hc/en-us/articles/360051384552-Creating-New-Meet-from-Scratch">Creating/Hosting a Meet</a>
    </li>
    <li>
        <a href="https://allgymnastics.zendesk.com/hc/en-us/articles/30462659999769-ProScore-Sync-with-AllGymnastics">ProScore Sync with AllGymnastics</a>
    </li>
</ul>
<br><br>
Thanks,<br>
{{ config('app.name') }}
@endcomponent
