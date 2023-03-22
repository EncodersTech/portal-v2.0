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
               Team Participation Report
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

    @if ($registrations->count() < 1)
        No Team Participation's.
    @else
        <table class="table-0">
            <thead>
                <tr>
                    <th class="col-1" style="width: 22%">Club</th>
{{--                    <th class="col-2"></th>--}}
                    <th class="col-3">Event</th>
                    <th class="col-10">DoB</th>
                    <th class="col-7">Sex</th>
                    <th class="col-4">Date</th>
                    <th class="col-5">Action</th>
                    <th class="text-right col-9">Amount</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($registrations as $r)
                    <tr>
                        <td  class="col-1">
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

                            <strong>Phone:</strong> {{ $r->gym->office_phone }}<br>
                            <strong>Email:</strong> {{ $r->gym->user->email }}
                        </td>

                        <td colspan="7" class="p-0">
                            <table class="table-1">
                                <tbody>
                                    @foreach ($r->levels as $l)
                                        @if(( $l->pivot->allow_teams ? 1 : 0 ) * ($l->pivot->team_fee>0 ? 1 : 0))
                                        <tr>
                                            <td colspan="3">
                                                <strong>
                                                    {{ $l->sanctioning_body->initialism }} |
                                                    {{ $l->level_category->name }} |
                                                    {{ $l->name }}

                                                    @if ($l->pivot->disabled)
                                                        (disabled))
                                                    @endif
                                                </strong>
                                            </td>

                                            <td class="col-4">
                                                {{ $l->updated_at->format(Helper::AMERICAN_SHORT_DATE_TIME) }}
                                            </td>

                                            <td class="col-5">
                                                Team Registration
                                            </td>

                                            <td class="text-right">
                                                $ {{ number_format($l->pivot->net_fee(), 2) }}
                                            </td>
                                        </tr>
                                        @endif
                                    @endforeach

                                    @if ($r->late_refund > 0)
                                        <tr>
                                            <td colspan="4">
                                                <strong>
                                                    Late Registration Refund
                                                </strong>
                                            </td>

                                            <td class="col-4">
                                                {{ $l->updated_at->format(Helper::AMERICAN_SHORT_DATE_TIME) }}
                                            </td>

                                            <td class="col-5">
                                                Late Refund
                                            </td>

                                            <td class="text-right">
                                                $ {{ number_format($r->late_refund, 2) }}
                                            </td>
                                        </tr>
                                    @endif

                                    <tr>
                                        <td colspan="3"></td>
                                        <td colspan="2" class="total">Total</td>
                                        <td class="text-right total col-9">
                                            $ {{ number_format($r->net_fee_not_athlete, 2) }}
                                        </td>
                                    </tr>
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
