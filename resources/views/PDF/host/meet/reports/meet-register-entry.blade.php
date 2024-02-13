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
    td { position: relative; }

    tr.strikeout td:before {
        content: " ";
        position: absolute;
        top: 50%;
        left: 0;
        border-bottom: 1px solid #111;
        width: 100%;
    }

    tr.strikeout td:after {
        content: "\00B7";
        font-size: 1px;
    }
</style>
<body>
    <div class="header">
        <div class="header-text">
            <h1 class="mb-0">
                Meet Entry Report
            </h1>
            <h2 class="mb-0">
                Meet: {{ $meet->name }}
            </h2>
            <h4 class="mb-0">
                Date: {{ $meet->start_date->format(Helper::AMERICAN_FULL_DATE) }}
            </h4>
        </div>
        <div class="logo-container">
            @include('PDF.host.meet.reports.common_logo_image')
        </div>
    </div>

            <h5 class="table-header">Gym</h5>
            <table class="table-0 full-width">
                <thead>
                <tr>
                    <th class="col-2" style="width: 8%">Name</th>
                    <th class="col-2">Address</th>
                    <th class="col-3">Email</th>
                </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="col-2">
                            {{ $registrations->gym->name }}
                        </td>
                        <td class="col-2">
                            {{ $registrations->gym->addr_1 }},
                            @if ($registrations->gym->addr_2)
                                {{ $registrations->gym->addr_2 }},
                            @endif
                            {{ $registrations->gym->city }}, {{ $registrations->gym->state->code }}, {{ $registrations->gym->zipcode }}, {{ $registrations->gym->country->name }}
                        </td>
                        <td class="col-3">
                            {{ $registrations->gym->user->email  }}

                        </td>
                    </tr>
                </tbody>
            </table>
            <br>

            <h5 class="table-header">Coaches</h5>
            <table class="table-0 full-width">
                <thead>
                    <tr>
                        <th class="col-2">Name</th>
                        <th class="col-2">Coach #</th>
                        <th class="col-2">Sanctions</th>
                        <th class="col-2">Size</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($registrations->coaches as $c)
                        <tr>
                            <td class="col-2">
                                {{ $c->fullName() }}
                            </td>

                            <td class="col-2">
                                {{ $c->usag_no }}
                            </td>

                            <td class="col-2">
                                @if ($c->usag_active)
                                    USAG
                                @endif
                            </td>

                            <td class="col-2">
                                @if ($c->tshirt)
                                    {{ $c->tshirt->size }}
                                @endif

                            </td>
                        </tr>
                    @endforeach

                </tbody>
            </table>
            <br>

            <h5 class="table-header">Athletes</h5>
            <table class="table-0 full-width">
                <thead>
                    <tr>
                        <th class="col-1">Name</th>
                        <th class="col-2">Team</th>
                        <th class="col-2">Athlete #</th>
                        <th class="col-2">Level</th>
                        <th class="col-2">Birthday</th>
                        <th class="col-2">Age</th>
                        <th class="col-2">Size</th>
                    </tr>
                </thead>
                <tbody>

                    @foreach ($registrations->athletes as $a)
                        <tr style="{{ $loop->even?'background-color: #ccc;':'' }}"
                            class="{{$a->status == \App\Models\RegistrationAthlete::STATUS_SCRATCHED?'strikeout':''}}">
                            <td class="col-1">
                                {{ $a->fullName() }}
                            </td>

                            <td class="col-2">
                                @if($a->registration_level->has_team)
                                    Y
                                @else
                                    N
                                @endif
                            </td>

                            <td class="col-2">
                                {{ ($a->usaigc_no ?? $a->usaigc_no ?? $a->usag_no ?? $a->nga_no) }}
                            </td>

                            <td class="col-2">
                                {{ $a->registration_level->level->abbreviation }}
                            </td>

                            <td class="col-2">
                                {{ $a->dob->format(Helper::AMERICAN_SHORT_DATE) }}
                            </td>

                            <td class="col-2">
                                {{ Helper::age($a->dob) }}
                            </td>

                            <td class="col-2">
                                @if($a->tshirt)
                                    {{$a->tshirt->size}}
                                @endif
                            </td>

                        </tr>
                    @endforeach
                </tbody>
            </table>
        <br>
        <br>
</body>
</html>
