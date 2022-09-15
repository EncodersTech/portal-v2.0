<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>{{ config('app.name', 'AllGymnastics') }}</title>
    @include('PDF.styles.reboot')
    @include('PDF.styles.reports')
</head>
<style>
    tr:nth-child(even) {
        background-color: #f2f2f2;
    }

    .text-center {
        text-align: center !important;
    }

    .esCount {
        padding: 5px 8px;
        border-radius: 50%;
        font-size: 15px;
        text-align: center;
        background: #2f2c2c;
        color: #fefefe;
        font-weight: 900;
    }

    .margin-top {
        padding-top: 10px !important;
    }
</style>
<body>
@if ($registrations->count() < 1)
    No Summary.
@else
    @if($esExists)
        <span>* ES column shows number of athletes, not number of events. To see number of events, please use the &nbsp;<b> <i>Event Specialist Report.</i></b></span>
        <br>
    @endif
    <div class="d-flex">
        * <span style="padding: 0 13px; background-color: #000000"></span>&nbsp; - <b>Levels of Woman </b> &nbsp;&nbsp;
        <span style="padding: 0 13px; background-color: #888888"></span>&nbsp; - <b>Levels of Man </b>
        <br><br>
    </div>
    <table class="table-0 table-summary">
        <thead>
        <tr>
            <th rowspan="2" class="col-sum-1 meet-summary-header-1">#&nbsp;&nbsp;&nbsp;&nbsp;Gym</th>
            @if ($levels->count() > 0)
                <th colspan="{{$levels->count() + (($esExists)?2:1)}}" class="p-0 col-sum-2">
                    <table class="table-0 table-summary">
                        <thead>
                        <tr>
                            <th colspan="{{$levels->count()+1}}" class="p-0">Level</th>
                        </tr>
                        <tr>
                            @foreach ($levels as $l)
                                @if($l->level_category_id == \App\Models\LevelCategory::GYMNASTICS_WOMEN)
                                    <th>{{$l->abbreviation}}</th>
                                @elseif($l->level_category_id == \App\Models\LevelCategory::GYMNASTICS_MEN)
                                    <th style="background-color: #888888">{{($l->abbreviation}}</th>
                                @else
                                    <th>{{$l->abbreviation}}</th>
                                @endif
                            @endforeach
                            @if($esExists)
                                <th>ES*</th>
                            @endif
                            <th>Total</th>
                            {{-- th>Athletes</th>--}}
                        </tr>
                        <thead>
                    </table>
                </th>
            @endif
        </tr>
        </thead>
        <tbody>
        <?php
        $count = 0;
        $totLavels = [];
        $sumTotAt = 0;
        $sumAthletes = 0;
        $esTotal = 0;
        ?>
        @foreach ($registrations as $r)
            <?php
            $count = $loop->iteration;
            $esC = 0;
            ?>
            <tr>
                <td class="col-sum-1 text-center">{{$loop->iteration}}
                    &nbsp;&nbsp;&nbsp;&nbsp;{{ $r->gym->short_name }}</td>
                @if ($levels->count() > 0)
                    @foreach ($levels as $l)
                        <?php
                        $lAt = 0;
                        ?>
                        @if($r->levels->find($l->id))
                            <?php
                            $esC = $r->levels->find($l->id)->pivot->specialists->count() + $esC;
                            $lAt = $r->levels->find($l->id)->pivot->athletes->count() + $r->levels->find($l->id)->pivot->specialists->count();
                            ?>
                            <td class="col-sum-3 text-center">{{$lAt}}</td>
                        @else
                            <td class="col-sum-3"></td>
                        @endif
                        <?php
                        if (!isset($totLavels[$l->id])) {
                            $totLavels[$l->id] = 0;
                        }
                        $totLavels[$l->id] = $totLavels[$l->id] + $lAt;
                        ?>
                    @endforeach
                    <?php
                    $tAt = $r->athletes->count() + $r->specialists->count();
                    $sumTotAt = $sumTotAt + $tAt;
                    $tatAthletes = $r->gym->athletes_count;
                    $sumAthletes = $sumAthletes + $tatAthletes;
                    $esTotal = $esTotal + $esC;
                    ?>
                    @if($esC > 0)
                        <td class=" text-center margin-top"><span class="esCount">{{ $esC }}</span></td>
                    @else
                            @if($esExists)
                        <td class="col-sum-3"></td>
                            @endif
                    @endif
                    <td class="col-sum-3 text-center">{{ $tAt }}</td>
                    {{-- <td class="col-sum-3">{{ $r->gym->athletes_count }}</td>--}}
                @endif
            </tr>
        @endforeach
        <tr>
            <td colspan="{{$levels->count()+(($esExists)?3:2)}}"></td>
        </tr>
        <tr>
            <td class="col-sum-1 text-center">#&nbsp;&nbsp;&nbsp;Total Gyms: {{ count($registrations) }}</td>
            @if ($levels->count() > 0)
                @foreach ($levels as $l)
                    <?PHP
                    if (!isset($totLavels[$l->id])) {
                        $totLavels[$l->id] = 0;
                    }
                    $totLavels[$l->id] = ($totLavels[$l->id] > 0) ? $totLavels[$l->id] : '';
                    ?>
                    <td class="col-sum-3 text-center">{{ $totLavels[$l->id]}}</td>
                @endforeach
                @if($esExists)
                    <td class="col-sum-3 text-center margin-top"><span class="esCount">{{ $esTotal }}</span></td>
                @endif
                <td class="col-sum-3 text-center">{{ $sumTotAt }}</td>
                {{--                        <td class="col-sum-3">{{ $sumAthletes }}</td>--}}
            @endif
        </tr>
        </tbody>
    </table>
@endif
</body>
</html>
