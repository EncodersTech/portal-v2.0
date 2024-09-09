@component('mail::message')
Hello {{ $user_name }},
<br>

Thank you for using AllGymnastics. <br>
This is to confirm that your ticket for attending meet <b>"{{ $meet->name }}"</b> has been booked successfully. <br>
Ticket details can be found below. <br>


<h2>Ticket ID: <b>{{ $ticket_id }}</b></h2>
<table style="width: 100%">
    <thead>
        <tr>
            <th>Ticket For</th>
            <th>Single Fee</th>
            <th>Number of tickets</th>
            <th>Total Fee</th>
        </tr>
    </thead>
    <tbody>
        @php
            $total = 0;
        @endphp
        @foreach($meet_admissions as $admission)
            @if(isset($ticket[$admission->id]))
                @php
                    $total += $ticket[$admission->id] * $admission->amount; 
                @endphp
                <tr>
                    <td style="text-align: center">{{ $admission->name }}</td>
                    <td style="text-align: center">{{ $admission->type == \App\Models\MeetAdmission::TYPE_PAID ?
                                                        '$' . number_format($admission->amount, 2) :
                                                        '—' 
                                                    }}
                    </td>
                    <td style="text-align: center">{{ $ticket[$admission->id] }}</td>
                    <td style="text-align: center">{{ number_format($ticket[$admission->id] * $admission->amount, 4) }}</td>
                </tr>
            @endif
        @endforeach
        <tr>
            <td colspan="3" style="text-align: right">Total</td>
            <td style="text-align: center">{{ number_format($total, 4) }}</td>
        </tr>
    </tbody>
</table>
Thanks,<br>
{{ config('app.name') }}
@endcomponent
