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
            height: 2in;
            width: 4in;
            text-align: left;
            vertical-align: top;
        }
        .col-2 {
            width: 50%;
        }
    </style>
</head>
<body>
    @if ($registrations < 1)
        No Registrations.
    @else
    <table class="table-0">
        @for($p=0; $p <= $page; $p++)
            @for($r=0; $r < 10; $r++)
                <tr>
                    @for($c=0; $c < 2; $c++)
                        @if(!isset($matrix[$p][$r][$c]))
                            <td class="col-2"></td>
                        @else
                            <td class="col-2" style="margin-right:2px;">
                                <strong>{{ $matrix[$p][$r][$c]->gym->name  }}</strong><br/>
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