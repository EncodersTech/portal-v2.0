@component('mail::message')
Hello {{$gym->first_name . ' ' . $gym->last_name}},

@if($edit['update'] == false || $edit['gym'] == true)
Your gym "{{ $gym->name }}" has a deposit of <b>${{ number_format($deposit->amount, 2, '.', ',') }}</b> for "{{ $meet->name }}".<br/>
@elseif($edit['amount'])
<b>Updated Deposit Amount</b><br/>
Your gym "{{ $gym->name }}" has a deposit of <b>${{ number_format($deposit->amount, 2, '.', ',') }}</b> for "{{ $meet->name }}".<br/>

@endif

Please register to this meet "{{ $meet->name }}" and on the final stage of payment, please enter this deposit 
coupon <b>"{{ $deposit->token_id }}"</b> to claim your deposit amount. <br/>

This coupon is valid for this meet only and can be used only once.  <br/>

Thank you,<br>
{{ config('app.name') }}
@endcomponent