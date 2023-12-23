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
    @if ($registrations->count() < 1)
        No Event Specialists.
    @else
        <?php
            $spe = false;
        ?>
        @foreach ($registrations as $r)
            @foreach ($r->levels as $l)
                @if ($l->pivot->specialists->count() > 0)
                    <?php
                        $spe = true;
                    ?>
                    <table class="table-0 full-width">
                        <thead>
                            <tr>
                                <th colspan="{{count($events)+1}}" class="meet-subheader-text">

                                    {{$l->level_category->name}} - {{$l->name}} - {{$l->pivot->specialists->count()}}
                                @php($att = $l->pivot->specialists->count() > 1 ? 'athletes': 'athlete')
                                {{$att}}
                                </th>
                            </tr>
                        </thead><br>
                        <tbody>
                            @foreach ($l->pivot->specialists as $s)
                                <tr>
                                    <td colspan="3">{{$loop->iteration}}. {{$s->fullName()}}
                                        (@foreach($s->events as $e)
                                            {{$loop->first?'':'/'}}
                                            {{$e->specialist_event->name}}
                                        @endforeach()) {{!empty($s->usaigc_no)? ': IGC'.$s->usaigc_no:''}}</td>
                                    <td colspan="2">{{$r->gym->name}}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            @endforeach
        @endforeach

        @if($spe == false)
            No Event Specialists.
        @endif
    @endif


</body>
</html>
