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
                        <tr>
                            <td>{{ $c->first_name .' '.$c->last_name }}</td>
                            <td>
                                @if($c->usaigc_no != null)
                                    {{ '  USAIGC: '.$c->usaigc_no }}.<br>
                                @endif
                            </td>
                            <td  class="col-1">
                                <strong>{{ $r['gyms']->name }}</strong>
                            </td>
                            <td></td>
                        </tr>
                    <tr>   
                    @endforeach
                @endforeach
            </tbody>
        </table>
    @endif
   
</body>
</html>
