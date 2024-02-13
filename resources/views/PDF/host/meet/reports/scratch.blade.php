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
                Scratches & Modifications
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
        No Scratches.
    @else
        <table class="table-0">
            <thead>
                <tr>
                    <th class="col-2">Club</th>
                    <th class="col-4">Name</th>
                    <th class="col-4">DOB</th>
                    <th class="col-4">Action Details</th>
                    <th class="col-4">Action Date</th>
                    <th class="col-4">Level</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($registrations as $r)
                    <tr>
                        <td  class="col-2">
                            <strong>{{ $r->gym->name }}</strong><br/>

                            <address>
                                <strong>Address: </strong>
                                {{ $r->gym->addr_1 }}<br/>

                                @if ($r->gym->addr_2)
                                    {{ $r->gym->addr_2 }}<br/>
                                @endif

                                {{ $r->gym->city }}, {{ $r->gym->state->code }},
                                {{ $r->gym->zipcode }}<br/>
                                {{ $r->gym->country->name }}
                            </address>

                            <strong>Phone:</strong> {{ $r->gym->office_phone }}
                        </td>

                        <td colspan="10" class="p-0">
                        <table class="table-1">
                                <tbody>
                                @foreach ($r->audit_report as $indexs => $change)
                                    @php
                                        $count_change = count($change["new"]) + count($change["moved"]) + count($change["scratched"]);
                                    @endphp
                                    @if($indexs == "athlete" && $count_change > 0)
                                        @foreach($change as $key => $value) 
                                            @foreach($value as $k => $v)
                                            <tr>
                                                <td class="col-2">
                                                    {{ $v['first_name'] }} {{ $v['last_name']}}
                                                </td>

                                                <td class="col-2">
                                                    {{ Date('m/d/Y', strtotime($v['dob']))}}
                                                </td>

                                                <td class="col-2">
                                                    @if ($key == 'moved')
                                                    Moved from : {{ $v['previous_level'] }}
                                                    @elseif ($key == 'scratched')
                                                        <span style="color: red">{{ $key }}</span>
                                                    @else
                                                        <span style="color: green">{{ $key }}</span>
                                                    @endif
                                                </td>

                                                <td class="col-2">
                                                    {{ Date('m/d/Y g:i:s A', strtotime($v['updated_at']))}}
                                                </td>

                                                <td class="col-2">
                                                    {{ $v['current_level'] }}
                                                </td>
                                            </tr>
                                            @endforeach
                                        @endforeach
                                    @endif
                                    @if($indexs == "specialist" && $count_change > 0)
                                        @foreach($change as $key => $value) 
                                            @foreach($value as $k => $v)
                                            <tr>
                                                <td class="col-2">
                                                    {{ $v['first_name'] }} {{ $v['last_name']}}
                                                </td>

                                                <td class="col-2">
                                                    {{ Date('m/d/Y', strtotime($v['dob']))}}
                                                </td>

                                                <td class="col-2">
                                                    @if ($key == 'moved')
                                                    Moved from : {{ $v['previous_level'] }}
                                                    @elseif ($key == 'scratched')
                                                        <span style="color: red">{{ $key }}</span>
                                                    @else
                                                        <span style="color: green">{{ $key }}</span>
                                                    @endif
                                                </td>

                                                <td class="col-2">
                                                    {{ Date('m/d/Y g:i:s A', strtotime($v['updated_at']))}}
                                                </td>

                                                <td class="col-2">
                                                    {{ $v['current_level'] }} <br> Events: 
                                                    @foreach($v['event'] as $s => $t)
                                                        {{ $t['name'] }}
                                                    @endforeach
                                                </td>
                                            </tr>
                                            @endforeach
                                        @endforeach
                                    @endif
                                    
                                @endforeach

                                </tbody>
                            </table>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

</body>
</html>
