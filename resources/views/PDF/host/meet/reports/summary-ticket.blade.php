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
        .text-right{
            text-align: right !important;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="header-text">
            <h1 class="mb-0">
                Summary of Tickets Sold
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
                    <th rowspan="2"># Gym</th>
                    <th colspan='{{ count($meet_admissions)+1 }}' class="text-center">Admission Level</th>
                </tr>
                <tr>
                    @foreach ($meet_admissions as $admission)
                        <th class="col-2 text-center">{{ $admission['name'] }}</th>
                    @endforeach
                    <th class="col-2 text-center">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($tickets as $k=>$t)
                    @if($loop->last)
                        <tr>
                            <td colspan="{{ count($meet_admissions)+2 }}"></td>
                        </tr>
                    @endif
                    <tr style="{{ $loop->last ? 'background-color:#b6c5d6' : '' }}">
                        @php 
                            $gym_total = 0;
                        @endphp
                        <td>{{ $k }}</td>
                        @foreach($t as $admission)
                            <td class="text-center">{{ $admission }}</td>
                            @php
                                $gym_total += $admission;
                            @endphp
                        @endforeach
                        <td class="text-center">{{$gym_total}}</td>
                    </tr>
                        
                @endforeach
                
            </tbody>
        </table>
    @endif
</body>
</html>
