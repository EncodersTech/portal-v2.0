@component('mail::message')
Hello <b>{{ $meet->gym->user->fullName() }}</b>,
<br>

This is to confirm that new ticket has been booked for meet <b>"{{ $meet->name }}"</b>. 
Ticket details can be found below. <br>

<h2>Ticket ID: {{ $ticket_id }}</h2>
<p>User Name: <b>{{ $user_name }}</b><br>
User Email: <b>{{ $user_email }}</b><br>
User Phone: <b>{{ $user_phone }}</b></p>
<br>
<table style="width: 100%">
    <thead>
        <tr>
            <th>Ticket For</th>
            <th>Price</th>
            <th>Number of Tickets</th>
            <th>Total Fee</th>
        </tr>
    </thead>
    <tbody>
        @php
            $total = 0;
            $total_tickets = 0;
        @endphp
        @foreach($meet_admissions as $admission)
            @if(isset($ticket[$admission->id]))
                @php
                    $total += $ticket[$admission->id] * $admission->amount; 
                    $total_tickets += $ticket[$admission->id];
                @endphp
                <tr>
                    <td style="text-align: center">{{ $admission->name }}</td>
                    <td style="text-align: center">{{ $admission->type == \App\Models\MeetAdmission::TYPE_PAID ?
                                                        '$' . number_format($admission->amount, 2) :
                                                        '$0.00' 
                                                    }}
                    </td>
                    <td style="text-align: center">{{ $ticket[$admission->id] }}</td>
                    <td style="text-align: center">{{ number_format($ticket[$admission->id] * $admission->amount, 2) }}</td>
                </tr>
            @endif
        @endforeach
        <tr>
            <td colspan="2" style="text-align: right"><b>Total</b></td>
            <td style="text-align: center"><b>{{ $total_tickets }}</b></td>
            <td style="text-align: center"><b>{{ number_format($total, 2) }}</b></td>
        </tr>
    </tbody>
</table>
<br><br>
Thanks,<br>
{{ config('app.name') }}
@endcomponent
