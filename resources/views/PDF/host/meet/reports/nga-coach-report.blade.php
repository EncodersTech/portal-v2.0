<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>{{ config('app.name', 'AllGymnastics') }}</title>
    @include('PDF.styles.reboot')
    @include('PDF.styles.reports')

    <style>
        .watermark{
            position: absolute;
            top: 80%;
            left: 21%;
            width: 60%;
            height: 60%;
            opacity: .3;
            z-index: -1;
        }
        .watermark img {
            opacity: 0.3;
        }
        table{
            border-collapse: separate !important;
            border-spacing: 50px 0;
        }
        .ntd{
            text-align:center; 
            width: 33%; 
            padding: 5px;
            border-bottom: 0px !important;
            border-right: 0px !important;
            border-left: 0px !important;
        }
        .line{
            color: red;
            border-top: 1px solid black;
        }
        .rbottom > td{
            padding-bottom: 3em !important;
        }
    </style>
</head>
<body>
    <div class="watermark">
        @include('PDF.host.meet.reports.nga_logo_image')
    </div>
    <div class="header">
        <div class="header-text">
            <h1 class="mb-0">
               NGA Coach Sign In Report
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
    <br><br>
    <!-- {{ count($gyms) < 1 ? "1" :"2" }} -->
    @if ($cont < 1)
        No Gym Participation's.
    @else

        <table class="table-1" style="border: 0;">
            <tbody>
                    @foreach ($gyms as $r)
                        @foreach ($r['coaches'] as $c)
                            @if($c->nga_no != null)
                                <tr>
                                    <td class="ntd">{{ $c->first_name .' '.$c->last_name }}</td>
                                    <td class="ntd">{{ $r['gyms']->name }}</td>
                                    <td class="ntd">{{ $c->nga_no }}</td>
                                </tr>
                                <tr class="rbottom">
                                    <td class="ntd line"></span>Coach Name</td>
                                    <td class="ntd line"></span>Club</td>
                                    <td class="ntd line"></span>NGA #</td>
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