@component('mail::message')
Hello,

"{{ $gym->name }}" has registered for "{{ $meet->name }}". Details information are as follows<br/>

<b>Gym Info:</b>
<table style="text-align:left;">
    <tr>
        <th>Gym Name: </th>
        <td>{{ $gym->name }}</td>
    </tr>
    <tr>
        <th>Contact: </th>
        <td>
            Owner: {{ $gym->user->fullName() }} <br>
            Email: {{ $gym->user->email }} <br>
            Phone: {{ $gym->user->office_phone }} <br>
        </td>
    </tr>
</table>
<br/>
<b>Meet Info:</b>
<table style="text-align:left;">
    <tr>
        <th>Name: </th>
        <td>{{ $meet->name }}</td>
    </tr>
    <tr>
        <th>Address: </th>
        <td> {{ $meet->getMeetAddressAttribute() }} </td>
    </tr>
</table>
<br/>
<b>Registration Info:</b>
<table style="text-align:left;">
    <tr>
        <th>Number of Athletes: </th>
        <td>{{ $number_of["athletes"] + $number_of["specialists"] }}</td>
    </tr>
    <tr>
        <th>Numer of Coaches: </th>
        <td>{{ $number_of["coaches"] }}</td>
    </tr>
</table>
<br/>
Thank you,<br>
{{ config('app.name') }}
@endcomponent
