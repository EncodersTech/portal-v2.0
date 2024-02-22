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
            border-spacing: 5px;
            border: 0px !important;
        }

        .table-0 td {
            padding: 0;
            margin: 0;
            border: 0px !important;
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
                            <td class="col-2" style="margin-right:2px;">
                                <strong>{{ $matrix[$p][$r][$c]->gym->name  }}</strong><br/>
                                <address style="font-size:12px;">
                                    {{ $matrix[$p][$r][$c]->gym->addr_1 }}<br/>

                                    @if ($matrix[$p][$r][$c]->gym->addr_2)
                                        {{ $matrix[$p][$r][$c]->gym->addr_2 }}<br/>
                                    @endif

                                    {{ $matrix[$p][$r][$c]->gym->city }}, {{ $matrix[$p][$r][$c]->gym->state->code }},
                                    {{ $matrix[$p][$r][$c]->gym->zipcode }},
                                    {{ $matrix[$p][$r][$c]->gym->country->name }}<br/>
                                    Phone:{{ $matrix[$p][$r][$c]->gym->office_phone }}
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
