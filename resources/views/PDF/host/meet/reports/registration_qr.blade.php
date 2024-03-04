<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>{{ config('app.name', 'AllGymnastics') }}</title>
    @include('PDF.styles.reboot')
    @include('PDF.styles.reports')

    <style>
        .imgdiv {
            margin-top: 25%;        
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="header-text">
            <h1 class="mb-0">
                <!-- Refund Report -->
            </h1>
            <h2 class="mb-0">
                Meet: {{ $meet->name }}
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
    <center class="imgdiv">
        <img src="{{$img}}" alt="">
    </center>
</body>
</html>
