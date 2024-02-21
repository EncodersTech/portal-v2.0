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
                Event Specialist Report
            </h1>
            <h2 class="mb-0">
                Meet: {{ $meet->name }}
            </h2>
            <h4 class="mb-0">
                Date: {{ $meet->start_date->format(Helper::AMERICAN_SHORT_DATE) }} - {{ $meet->end_date->format(Helper::AMERICAN_SHORT_DATE) }}
            </h4>
        </div>
        <div class="logo-container">
            @include('PDF.host.meet.reports.common_logo_image')
        </div>
    </div>
    @if ($registrations->count() < 1 || count($report_data_gym) < 1 || count($report_data_level) < 1)
        No Event Specialists.
    @else

        <center><h2>--------- Specialist Report By Gym ---------</h2></center> <br>
        @foreach($report_data_gym as $key1 => $sanction)
            <center><h2> {{ $key1 }}</h2></center>
            @foreach($sanction as $key2 => $level_category)
                @foreach($level_category as $key3 => $level)
                    <?php
                        $current_sanction = $key2 . ' - ' . $key3;
                    ?>
                    <table class="table-0 full-width">
                        <thead>
                            <tr>
                                <th colspan="5"  style="background-color: #ccc; color: black; text-align:center;">
                                    {{ $current_sanction }}
                                </th>
                            </tr>
                        </thead>
                    </table>
                    @foreach($level as $key4 => $specialists)
                    <table class="table-0 full-width">
                        <thead>
                            <tr>
                                <th colspan="5"  style="background-color: #ccc; color: black; text-align:center;">
                                    {{ $key4 }}
                                </th>
                            </tr>
                        </thead>
                    </table>
                    <table class="table-0 full-width">
                        <thead>
                            <tr>
                                <th class="col-2">Name</th>
                                <th class="col-2">Athlete #</th>
                                <th class="col-2">DOB</th>
                                <th class="col-2">Events</th>
                                <th class="col-2">Gym</th>
                            </tr>
                        </thead>
                        <tbody>
                        @foreach($specialists as $key5 => $v5)
                            @foreach($v5 as $s)
                                <tr>
                                    <td class="col-2">{{$s->fullName()}}</td>
                                    <td class="col-2">{{!empty($s->usaigc_no)? 'IGC'.$s->usaigc_no:''}}</td>
                                    <td class="col-2">{{ $s->dob->format(Helper::AMERICAN_SHORT_DATE) }}</td>
                                    <td class="col-2">
                                        @foreach($s->events as $e)
                                            {{$loop->first?'':'/'}}
                                            {{$e->specialist_event->name}}
                                        @endforeach
                                    </td>
                                    <td>{{ $key1 }}</td>
                                </tr>

                            @endforeach
                        @endforeach
                        </tbody>
                    </table>
                    @endforeach
                @endforeach
            @endforeach
            <div style="margin-bottom: 5em;"></div>
        @endforeach
        <div style="page-break-after: always;"></div>

        <center><h2>--------- Specialist Report By Level ---------</h2></center> <br>
        @foreach($report_data_level as $key1 => $sanction)
            @foreach($sanction as $key2 => $level_category)
                <center><h2> {{ $key1 }} <br> {{ $key2 }}</h2></center>
                @foreach($level_category as $key3 => $gyms)
                    <table class="table-0 full-width">
                        <thead>
                            <tr>
                                <th colspan="5"  style="background-color: #ccc; color: black; text-align:center;">
                                    {{ $key3 }}
                                </th>
                            </tr>
                        </thead>
                    </table>
                    <table class="table-0 full-width">
                        <thead>
                            <tr>
                                <th class="col-2">Name</th>
                                <th class="col-2">Athlete #</th>
                                <th class="col-2">DOB
                                <th class="col-2">Events</th>
                                <th class="col-2">Gym</th>
                            </tr>
                        </thead>
                        <tbody>
                        @foreach($gyms as $key4 => $specialists)
                            @foreach($specialists as $key5 => $s)
                                <tr>
                                    <td class="col-2">{{$s->fullName()}}</td>
                                    <td class="col-2">{{!empty($s->usaigc_no)? 'IGC'.$s->usaigc_no:''}}</td>
                                    <td class="col-2">{{ $s->dob->format(Helper::AMERICAN_SHORT_DATE) }}</td>
                                    <td class="col-2">
                                        @foreach($s->events as $e)
                                            {{$loop->first?'':'/'}}
                                            {{$e->specialist_event->name}}
                                        @endforeach
                                    </td>
                                    <td>{{ $key4 }}</td>
                                </tr>
                            @endforeach
                        @endforeach
                        </tbody>
                    </table>
                    <div style="margin-bottom: 1em;"></div>
                @endforeach
            @endforeach
            <div style="margin-bottom: 5em;"></div>
        @endforeach
    @endif

</body>
</html>
