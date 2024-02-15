<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>{{ config('app.name', 'AllGymnastics') }}</title>
    @include('PDF.styles.reboot')
    @include('PDF.styles.reports')

    <style>
        body { 
            background-image: url('http://127.0.0.1:8000/img/nga_background.png'); 
            background-repeat: no-repeat; 
            background-size: 100%;
            display: block; 
            margin: 0 auto;
        }
        .PageBorder {
			  border: 18px solid transparent;
			  border-image-slice: 13%;
			  border-image-width: 13px;
			  border-image-repeat: round round;background-size: cover;
			  border-image-source: url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAGQAAABkBAMAAACCzIhnAAAAJ1BMVEUAAADc1VKxzunu6qnl4H2ow72syNL29dTF2u6eweOvyt7g2mjp5ZOtY6MuAAAAAXRSTlMAQObYZgAAAK9JREFUWMPt2LENwjAQBdCTmOBWuAJBh3RsgLvUXoCCFdJQMQJjUGcH1uIcPMDPFVaK/4tU/0mx7OZOdM1FgBz+3RRZSikoieoUpLVR0j4Dycvq3YAcq506OePk3YnhP2ZZoloVTlRTxN0frksBMmlU3SWy6SwiJCQkJCQkJCQkJCQkJCSDSR94bw7k2gfeMZO4fufPjJ4lqs/EViGx7kgsVfa8IGoXj5L1CYzZwv0AT56E0VsJv2sAAAAASUVORK5CYII=);
          }
    </style>
</head>
<!-- how to put watermark image -->
<body>
    <div class="header PageBorder">
        <div class="header-text">
            <h1 class="mb-0">
               USAIGC Coach Sign In Report
            </h1>
            <h2 class="mb-0">
                Meet: {{ $meet->name }}
                <br/>
                Date: {{ $meet->start_date->format(Helper::AMERICAN_FULL_DATE) }}
                <br/>
                Host: {{ $host->name }}
            </h2>
            <h4 class="mb-0">
                Date: {{ $meet->start_date->format(Helper::AMERICAN_FULL_DATE) }}
            </h4>
        </div>
        <div class="logo-container">
            @include('PDF.host.meet.reports.common_logo_image')
        </div>
    </div>

    @if ($meet->registrationStatus() != \App\Models\Meet::REGISTRATION_STATUS_CLOSED)
        <div class="text-danger mb-3">
            The information on this report is not final and might change at a later date.
            <strong>A final report can be obtained after this meet is closed for registrations.</strong>
        </div>
    @endif
    <!-- {{ count($gyms) < 1 ? "1" :"2" }} -->
    @if ($cont < 1)
        No Gym Participation's.
    @else
        <table class="table-0">
            <thead>
                <tr>
                    <th class="col-2">Coach Name</th>
                    <th class="col-2">USAIGC No</th>
                    <th class="col-2">Gym</th>
                    <th class="col-2">Signature</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($gyms as $r)
                    @foreach ($r['coaches'] as $c)
                        @if($c->nga_no != null)
                            <tr>
                                <td>{{ $c->first_name .' '.$c->last_name }}</td>
                                <td>{{ '  NGA: '.$c->nga_no }}</td>
                                <td  class="col-1">
                                    <strong>{{ $r['gyms']->name }}</strong>
                                </td>
                                <td></td>
                            </tr>
                        @endif
                    @endforeach
                @endforeach
            </tbody>
        </table>
    @endif
   
</body>
</html>

@php
    // die();
@endphp