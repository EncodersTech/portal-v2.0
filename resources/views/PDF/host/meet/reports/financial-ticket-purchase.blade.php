<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>{{ config('app.name', 'AllGymnastics') }}</title>
    @include('PDF.styles.reboot')
    @include('PDF.styles.reports')
    <style>
        .text-center {
            text-align: center !important;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="header-text">
            <h1 class="mb-0">
                Financial Ticket Purchase Report
            </h1>
            <h2 class="mb-0">
                Meet: {{ $meet->name }}
                <br/>
                Date: {{ $meet->start_date->format(Helper::AMERICAN_FULL_DATE) }}
                <br/>
            </h2>
        </div>
        <div class="logo-container">
            @include('PDF.host.meet.reports.common_logo_image')
        </div>
    </div>

    @if (count($tickets) == 0)
        <strong>No Tickets Were Sold</strong>
    @else
        <table class="table-0 table-bordered table-sm">
            <thead>
                <tr>
                    <th class="col-2">Name</th>
                    <th class="col-2">Email</th>
                    <th class="col-2">Phone</th>
                    <th class="col-2">Ticket For</th>
                    <th class="col-2">Price per Ticker</th>
                    <th class="col-2">Number of Tickets</th>
                    <th class="col-2">Total Fee</th>
                </tr>
            </thead>
            <tbody>
            @php
                $nettotal = 0;
                $nettotal_tickets = 0;
            @endphp
            @foreach ($tickets as $t)
                @php
                    $total = 0;
                    $total_tickets = 0;
                    $t->ticket = json_decode($t->tickets, true);
                    $rowspannumber = count($t->ticket) + 2;
                @endphp
                <tr>
                    <td rowspan="{{$rowspannumber}}">{{ $t->customer_name }}</td>
                    <td rowspan="{{$rowspannumber}}">{{ $t->customer_email }}</td>
                    <td rowspan="{{$rowspannumber}}">{{ $t->customer_phone }}</td>
                    @foreach($meet_admissions as $admission)
                        @if(isset($t->ticket[$admission->id]))
                            @php
                                $total += $t->ticket[$admission->id] * $admission->amount; 
                                $total_tickets += $t->ticket[$admission->id];
                            @endphp
                            <tr>
                                <td style="text-align: center">{{ $admission->name }}</td>
                                <td style="text-align: center">{{ $admission->type == \App\Models\MeetAdmission::TYPE_PAID ?
                                                                '$' . number_format($admission->amount, 2) :
                                                                '$0.00' 
                                                            }}
                                </td>
                                <td style="text-align: center">{{ $t->ticket[$admission->id] }}</td>
                                <td style="text-align: center">{{ number_format($t->ticket[$admission->id] * $admission->amount, 2) }}</td>
                            </tr>
                        @endif
                    @endforeach
                    <tr style="background-color: #DEE2E6;">
                        <td colspan="2" style="text-align: right"><b>Total</b></td>
                        <td style="text-align: center"><b>{{ $total_tickets }}</b></td>
                        <td style="text-align: center"><b>{{ number_format($total, 2) }}</b></td>
                    </tr>
                </tr>
                @php
                    $nettotal += $total;
                    $nettotal_tickets += $total_tickets;
                @endphp
            @endforeach
            <tr>
                <td colspan="5" style="text-align: right"><b>Net Total</b></td>
                <td style="text-align: center"><b>{{ $nettotal_tickets }}</b></td>
                <td style="text-align: center"><b>{{ number_format($nettotal, 2) }}</b></td>
            </tr>
            </tbody>
        </table>
    @endif
</body>
</html>