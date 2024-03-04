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
    @if ($registrations->count() < 1)
        <div class="header">
            <div class="header-text">
                <h1 class="mb-0">
                    Refund Report
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
        No Refunds.
    @else

        @foreach ($registrations as $r)
            <div class="header">
                <div class="header-text">
                    <h1 class="mb-0">
                        Refund Report
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
            <table class="table-0" style="margin-bottom:5em;">
                <thead>
                <tr>
                    <th class="col-1">Club</th>
                    <th class="col-2">Name</th>
                    <th class="col-3">Event</th>
                    <th class="col-4">Date</th>
                    <th class="col-5">Action</th>
                    <th class="text-right">Amount</th>
                </tr>
                </thead>
                <tbody>
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

                            <strong>Phone:</strong> {{ $r->gym->office_phone }}
                        </td>

                        <td colspan="5" class="p-0">
                            <table class="table-1">
                                <tbody>
                                    @foreach ($r->athletes as $a)
                                        <tr>
                                            <td class="col-2">
                                                {{ $a->fullName() }}
                                            </td>

                                            <td class="col-3">
                                                -
                                            </td>

                                            <td class="col-4">
                                                {{ $a->updated_at->format(Helper::AMERICAN_SHORT_DATE_TIME) }}
                                            </td>

                                            <td class="col-5">
                                                @switch($a->status)
                                                    @case(\App\Models\RegistrationAthlete::STATUS_SCRATCHED)
                                                        Scratched
                                                        @break

                                                    @default
                                                        Moved
                                                @endswitch
                                            </td>
                                            <td colspan="3" class="text-right" >
                                                $ {{ number_format($a->refund_fee(), 2) }}
                                            </td>
                                        </tr>
                                    @endforeach

                                    @foreach ($r->specialists as $s)
                                        <tr>
                                            <td class="col-2">
                                                {{ $s->fullName() }}
                                            </td>

                                            <td colspan="6" class="p-0">
                                                <table class="table-2">
                                                    <tbody>
                                                        @foreach ($s->events as $evt)
                                                            <tr>
                                                                <td class="col-3">
                                                                    {{ $evt->specialist_event->name }}
                                                                </td>

                                                                <td class="col-4">
                                                                    {{ $evt->updated_at->format(Helper::AMERICAN_SHORT_DATE_TIME) }}
                                                                </td>

                                                                <td class="col-5">
                                                                    @switch($evt->status)
                                                                        @case(\App\Models\RegistrationSpecialistEvent::STATUS_SPECIALIST_SCRATCHED)
                                                                            Scratched
                                                                            @break

                                                                        @default
                                                                            Moved
                                                                    @endswitch
                                                                </td>

                                                                <td class="text-right">
                                                                    $ {{ number_format($evt->refund_fee(), 2) }}
                                                                </td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </td>
                                        </tr>
                                    @endforeach

                                    @foreach ($r->levels as $l)
                                        <tr>
                                            <td colspan="4">
                                                <strong>
                                                    {{ $l->sanctioning_body->initialism }} |
                                                    {{ $l->level_category->name }} |
                                                    {{ $l->name }}

                                                    @if ($l->pivot->disabled)
                                                        (disabled)
                                                    @endif
                                                </strong>
                                            </td>

                                            <td class="col-4">
                                                {{ $l->updated_at->format(Helper::AMERICAN_SHORT_DATE_TIME) }}
                                            </td>

                                            <td class="col-5">
                                                Team Refund
                                            </td>

                                            <td class="text-right">
                                                $ {{ number_format($l->pivot->refund_fee(), 2) }}
                                            </td>
                                        </tr>
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
                                        <td colspan="4"></td>
                                        <td colspan="2" class="">Total</td>
                                        <td class="text-right ">
                                            $ {{ number_format($r->total, 2) }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="4"></td>
                                        <td colspan="2" class="">Used Credit</td>
                                        <td class="text-right ">
                                            $ {{ number_format($r->credit_used, 2) }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="4"></td>
                                        <td colspan="2" class="total">Refund Amount</td>
                                        <td class="text-right total">
                                            $ {{ number_format(($r->total - $r->credit_used), 2) }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </td>
                    </tr>
                </tbody>
            </table>
            @if(!$loop->last)
                <div style="page-break-after: always;"></div>
            @endif
        @endforeach
    @endif
</body>
</html>
