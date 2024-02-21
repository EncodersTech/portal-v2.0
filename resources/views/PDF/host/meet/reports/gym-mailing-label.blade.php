<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>{{ config('app.name', 'AllGymnastics') }}</title>
    @include('PDF.styles.reboot')
    @include('PDF.styles.reports')

    <style>
        /* design td as 1" x 2-5/8" label */
        .table-0 {
            width: 100%;
            border-collapse: collapse;
            border-spacing: 0;
        }

        .table-0 td {
            padding: 0;
            margin: 0;
            border: 1px solid #000;
            height: 1.1in;
            width: 2.625in;
            text-align: left;
            vertical-align: top;
        }
        .col-2 {
            width: 33.333333%;
        }
    </style>
</head>
<body>
    <!-- <div class="header">
        <div class="header-text">
            <h1 class="mb-0">
                Gym Mailing Label
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
    </div> -->
    @if ($registrations < 1)
        No Scratches.
    @else
    <table class="table-0">
        @for($p=0; $p <= $page; $p++)
            @for($r=0; $r < 10; $r++)
                <tr>
                    @for($c=0; $c < 3; $c++)
                        @if(!isset($matrix[$p][$r][$c]))
                            <td class="col-2"></td>
                        @else
                            <td class="col-2" style="text-align:center;">
                                <strong>{{ $matrix[$p][$r][$c]->gym->name  }}</strong><br/>
                                <address>
                                    {{ $matrix[$p][$r][$c]->gym->addr_1 }}<br/>

                                    @if ($matrix[$p][$r][$c]->gym->addr_2)
                                        {{ $matrix[$p][$r][$c]->gym->addr_2 }}<br/>
                                    @endif

                                    {{ $matrix[$p][$r][$c]->gym->city }}, {{ $matrix[$p][$r][$c]->gym->state->code }},
                                    {{ $matrix[$p][$r][$c]->gym->zipcode }},
                                    {{ $matrix[$p][$r][$c]->gym->country->name }}<br/>
                                    <strong>Phone:</strong> {{ $matrix[$p][$r][$c]->gym->office_phone }}
                                </address>
                            </td>
                        @endif
                    @endfor
                </tr>
            @endfor
            
            @if($p < $page)
                <div style="page-break-after: always;"></div>
            @endif
        @endfor
    </table>
    @endif
</body>
</html>
