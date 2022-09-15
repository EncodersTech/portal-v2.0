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
                Leotard Clothing Report Summary
            </h1>

            <h4 class="">
                Meet: {{ $meet->name }}
                <br/>
                Date: {{ $meet->start_date->format(Helper::AMERICAN_SHORT_DATE) }} - {{ $meet->end_date->format(Helper::AMERICAN_SHORT_DATE) }}
            </h4>
        </div>
        <div class="logo-container">
            @include('PDF.host.meet.reports.common_logo_image')
        </div>
    </div>
    @if (count($leo_size) < 1)
        No Leotard Clothing's.
    @else
            <table class="table-0 full-width">
                <thead>
                    <tr class="meet-subheader-text">
                        <th width="3%" >#</th>
                        <th width="20%" >Gym</th>
                        @foreach ($leo_size as $leo_s)
                            <th>{{$leo_s}}</th>
                        @endforeach
                        <th  width="5%" >Total</th>
                    </tr>

                </thead>
                <?php
                $count = 0;
                ?>
                <tbody>
                    @foreach ($re_leo_size as $r)
                        <tr  style="{{ $loop->even?'background-color: #ccc;':'' }}">
                            <td class="meet-subheader-text">{{$loop->iteration}}</td>
                            <td>{{$r['name']}}</td>
                            @foreach ($leo_size_total as $i => $leo_s_t)
                                <td class="meet-subheader-text">{{ ($r[$i] == 0)?'-':$r[$i]  }}</td>
                            @endforeach
                            <td class="meet-subheader-text">{{$r['total']}}</td>
                        </tr>
                    @endforeach
                    <tr>
                        <td class="meet-subheader-text" colspan="2">Total</td>
                        @foreach ($leo_size_total as $leo_s_t)
                            <?php
                            $to_size = $leo_s_t;
                            $sumAthletes = $to_size + $count;
                            ?>
                            <td class="meet-subheader-text">{{$sumAthletes}}</td>
                        @endforeach
                        <td  width="5%" class="meet-subheader-text">{{$sub_total}}</td>
                    </tr>
                </tbody>
            </table>
    @endif
    <br><br><br>
</body>
</html>
