@component('mail::message')
Hello {{$name}},

We wanted to reach out again for some other possible scenario when confirming checks. <br>

If you have not yet received a check, you may still accept pending mailed checks. In case of not receiving that check after 2 weeks from the date of sending, please let us know.
We will manually reject the registration and refund any pre-payments that have been made accepting the mailed checks. <br>

If you have already archived a meet in your AllGym account you will not see the meet dashboard to confirm checks. 
To accomplish this, please go into your Hosted Meets menu and select the Archived Meets tab. You will now be able to restore the archived meet. 
Once this is done, you will now see the meet dashboard. From here, simply go into transactions and confirm checks. Later, you can archive the meet again.<br>

Your mailed checks are pending in these meets : <b>{{$meet}}</b> <br>

If you already accepted the pending mailed checks of these meets, you may ignore this mail.<br>

If you have any questions, please let us know. <br><br>
Thank you,<br>
{{ config('app.name') }}
@endcomponent

