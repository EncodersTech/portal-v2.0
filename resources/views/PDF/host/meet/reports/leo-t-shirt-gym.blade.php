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
                Leotard Clothing Report per Gym
            </h1>
            <h2 class="mb-0">
                {{ $meet->name }}
            </h2>
            <h4 class="mb-0">
                Date:  {{ $meet->start_date->format(Helper::AMERICAN_SHORT_DATE) }} - {{ $meet->end_date->format(Helper::AMERICAN_SHORT_DATE) }}
            </h4>
        </div>
        <div class="logo-container">
            @include('PDF.host.meet.reports.common_logo_image')
        </div>
    </div>
    <div class="float-parent">
        <div class="float-child" style="padding: 0 10px 0 0  !important;">
            <div >
                <span><strong>Gym Details</strong>
                </span><br>
                @if ($registrations->count() < 1)
                    No Leotard Clothing's.
                @else
                    <table class="table-0  full-width">
                        <thead>
                            <tr>
                                <th class="col-1">Gym Name</th>
                                <th class="col-1">{{$registrations[0]->gym->short_name}}</th>
                            <tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td  class="col-1">Address</td>
                                <td  class="col-1">
                                    {{ $registrations[0]->gym->addr_1 }},

                                    @if ($registrations[0]->gym->addr_2)
                                        {{ $registrations[0]->gym->addr_2 }},
                                    @endif

                                    {{ $registrations[0]->gym->city }}, {{ $registrations[0]->gym->state->code }},
                                    {{ $registrations[0]->gym->zipcode }}
                                    {{ $registrations[0]->gym->country->name }}
                                </td>
                            <tr>
                            <tr>
                                <td  class="col-1">Contact Person</td>
                                <td  class="col-1"> {{ $registrations[0]->gym->user->first_name }}</td>
                            <tr>
                            <tr>
                                <td  class="col-1">USAG Club #</td>
                                <td  class="col-1"> {{ $registrations[0]->gym->usag_membership }}</td>
                            <tr>
                            <tr>
                                <td  class="col-1">Email Address</td>
                                <td  class="col-1"> {{ $registrations[0]->gym->user->email }}</td>
                            <tr>
                            <tr>
                                <td  class="col-1">Office No.</td>
                                <td  class="col-1">{{ $registrations[0]->gym->office_phone }}</td>
                            <tr>
                        </tbody>
                    </table>
                @endif
            </div>
        </div>
        <div class="float-child" style="padding: 0 !important;">
            <div>
                @if (!$meet || count($re_leo_size) < 1)
                    {{'No Size Distribution Summary.'}}
                @else
                    <span><strong>Size Distribution Summary </strong></span><br>
                    <table class="table-0  full-width">
                        <thead>
                        <tr>
                            <th class="col-1">Leotard Size</th>
                            <th class="col-1">Qty</th>
                        <tr>
                        </thead>
                        <tbody>
                        <?php
                        $re_les_total = 0;
                        ?>
                        {{-- {{ dd($re_leo_size) }}--}}
                        @foreach ($re_leo_size as $re_les)
                            {{-- @foreach ($re_les as $r)--}}
                            @if(isset($re_les['name']))
                                <tr style="{{ $loop->even?'background-color: #ccc;':'' }}">
                                    <td>{{$re_les['name']}}</td>
                                    <td class="meet-subheader-text">{{($re_les['count'] != 0)?$re_les['count']:'-'}}</td>
                                    <?php
                                    $re_les_total = $re_les_total + $re_les['count'];
                                    ?>
                                </tr>
                            @endif
                        @endforeach
                        <tr>
                            <td class="meet-subheader-text"><strong>Total</strong></td>
                            <td class="meet-subheader-text">{{ $re_les_total }}</td>
                        <tr>
                        {{-- @endforeach--}}
                        </tbody>
                    </table>
                @endif
            </div>
        </div>
    </div>
    <div class="float-parent">
        @if ($registrations->count() < 1)
            No refunds.
        @else
            @foreach ($registrations as $r)

                <table class="table-0 full-width">
                    <thead>
                        <tr>
                            <th class="meet-subheader-text">Athlete No </th>
                            <th class="meet-subheader-text">First Name </th>
                            <th class="meet-subheader-text">Last Name </th>
                            <th class="meet-subheader-text">Leotard Size </th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($r->levels as $l)
                            <tr style="background-color: #cfd4da;">
                                <td colspan="3">{{$l->level_category->name}} - {{$l->name}}</td>
                                <td >Total : {{$l->pivot->athletes->count()}}</td>
                            </tr>
                            @if ($l->pivot->athletes->count() > 0)
                                @foreach ($l->pivot->athletes as $a)
                                    <tr>
                                        <td>
                                            @if($a->usaigc_no)
                                                IGC{{$a->usaigc_no}}
                                            @elseif($a->usag_no)
                                                AGC{{$a->usag_no}}
                                            @endif
                                        </td>
                                        <td>{{$a->first_name}}</td>
                                        <td>{{$a->last_name}}</td>
                                        <td>
                                            @if($a->leo)
                                                {{$a->leo->size}}
                                            @else
                                                {{ '-' }}
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            @endif
                        @endforeach
                    </tbody>
                </table>
            @endforeach
        @endif
    </div>
    <br><br><br>
</body>
</html>
