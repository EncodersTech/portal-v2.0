<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>{{ config('app.name', 'AllGymnastics') }}</title>
    @include('PDF.styles.reboot')
    @include('PDF.styles.reports')
</head>
<body>
    <div class="header">
        <div class="header-text">
            <h1 class="mb-0">
               Attending Gyms & Coaches
            </h1>
            <h2 class="mb-0">
                Meet: {{ $meet->name }}
                <br/>
                Date: {{ $meet->start_date->format(Helper::AMERICAN_FULL_DATE) }}
                <br/>
                Host: {{ $host->name }}
            </h2>
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
        No Team Participation's.
    @else
        <table class="table-0">
            <thead>
                <tr>
                    <th class="col-2">Gym</th>
                    <th class="col-2">Gym Contact</th>
                    <th class="col-2">Address</th>
                    <th class="col-4">Coaches</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($gyms as $r)
                    <tr style="{{ $loop->even?'background-color: #ccc;':'' }}">
                        <td  class="col-1">
                            <strong>{{ $r->name }}</strong><br/>
                        </td>
                        <td style="word-wrap: break-word;">{{ 'Phone: ' . $r->office_phone }}<br>{{'Email: '. $r->user->email }}</td>
                        <td>
                            {{ $r->addr_1 }}<br/>
                            @if ($r->addr_2)
                                {{ $r->addr_2 }}<br/>
                            @endif
                            {{ $r->city }}, {{ $r->state->code }}, {{ $r->zipcode }}, {{ $r->country->name }}
                        </td>
                        
                        @php 
                        $coaches = $r->getCoachesFromMeetRegistrations($meet->id)
                        @endphp
                        <td>
                            <table class="table-1">
                                @foreach($coaches as $c)
                                    <tr>
                                        <td>{{ $c->first_name .' '.$c->last_name }}</td>
                                        <td>
                                        @if($c->usag_no != null)
                                            {{ '  USAG: '.$c->usag_no }}.<br>
                                        @endif
                                        @if($c->usaigc_no != null)
                                            {{ '  USAIGC: '.$c->usaigc_no }}.<br>
                                        @endif
                                        @if($c->aau_no != null)
                                            {{ '  AAU: '.$c->aau_no }}.<br>
                                        @endif
                                        @if($c->nga_no != null)
                                            {{ '  NGA: '.$c->nga_no}}
                                        @endif

                                        </td>
                                    </tr>
                                @endforeach
                            </table>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
   
</body>
</html>
