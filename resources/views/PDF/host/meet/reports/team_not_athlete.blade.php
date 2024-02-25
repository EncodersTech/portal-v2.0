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
    .usag{
        background-color: #DAFFF5;
    }
    .nga{
        background-color: #b6c5d6;
    }
    .aau{
        background-color: #c4bbaf;
    }
    .usaigc{
        background-color: #d1b3e9;
    }
</style>
<body>
@if ($registrations->count() < 1)
    No Summary.
@else
    <div class="d-flex">
        * <span style="padding: 0 13px; background-color: #000000"></span>&nbsp; - <b>Women’s levels </b> &nbsp;&nbsp;
        <span style="padding: 0 13px; background-color: #888888"></span>&nbsp; - <b>Men’s levels </b>
        <br><br>
    </div>
    <table class="table-0 table-summary">
        <thead>
        <tr>
            <th rowspan="2" class="col-sum-1 meet-summary-header-1">#&nbsp;&nbsp;&nbsp;&nbsp;Gym</th>
            @if ($levels->count() > 0)
                <th colspan="{{$levels->count() + 1 }}" class="p-0 col-sum-2">
                    <table class="table-0 table-summary">
                        <thead>
                        <tr>
                            <th colspan="{{$levels->count()+1}}" class="p-0">Level</th>
                        </tr>
                        <tr>
                            @foreach($sanctions as $k => $s)
                                <th colspan="{{$s}}" class="p-0">{{$k}}</th>
                            @endforeach
                        </tr>
                        <tr>
                            @foreach ($levels as $l)
                                @if($l->level_category_id == \App\Models\LevelCategory::GYMNASTICS_WOMEN)
                                    <th>{{$l->abbreviation}}</th>
                                @elseif($l->level_category_id == \App\Models\LevelCategory::GYMNASTICS_MEN)
                                    <th style="background-color: #888888">{{$l->abbreviation}}</th>
                                @else
                                    <th>{{$l->abbreviation}}</th>
                                @endif
                            @endforeach
                            <th>Total</th>
                            
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
        $gym_team_total = 0;
        $row_team_sum = 0;
        $col_team_sum = [];
        ?>
        @foreach ($registrations as $r)
            <?php
            $count = $loop->iteration;
            ?>
            <tr>
                <td class="col-sum-1 text-left">{{$loop->iteration}}
                    &nbsp;&nbsp;&nbsp;&nbsp;{{ $r->gym->short_name }}</td>
                @if ($levels->count() > 0)
                        <?php
                            $lAt = 0;
                        ?>
                    @foreach ($levels as $l)
                        <?php 
                            if (!isset($col_team_sum[$l->id])) {
                                $col_team_sum[$l->id] = 0;
                            }
                        ?>
                        @if($r->levels->find($l->id) && $r->levels->find($l->id)->pivot->allow_teams && $r->levels->find($l->id)->pivot->has_team)
                            <?php
                                $lAt += 1;
                                $col_team_sum[$l->id] += 1;
                            ?>
                            <td class="col-sum-3 text-center {{ $sanction_class[$l->sanctioning_body_id] }} ">X</td>
                        @else
                            <td class="col-sum-3  {{ $sanction_class[$l->sanctioning_body_id] }}"></td>
                        @endif

                        
                    @endforeach
                    <?php
                        $gym_team_total += $lAt;
                        $row_team_sum += $lAt;
                    ?>
                    <td class="col-sum-3 text-center">{{ $lAt }}</td>
                @endif
            </tr>
            
        @endforeach
        <tr>
            <td colspan="{{$levels->count() + 2 }}"></td>
        </tr>
        <tr>
            <td class="col-sum-1 text-left">#&nbsp;&nbsp;&nbsp;Total Gyms: {{ count($registrations) }}</td>
            @if ($levels->count() > 0)
                @foreach ($levels as $l)
                    <td class="col-sum-3 text-center">{{ $col_team_sum[$l->id] > 0 ? $col_team_sum[$l->id] : '' }}</td>
                @endforeach

                <td class="col-sum-3 text-center">{{ $row_team_sum }}</td>
            @endif
        </tr>
        </tbody>
    </table>
@endif
</body>
</html>
