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
            height: 3in;
            width: 4in;
            text-align: left;
            vertical-align: top;
        }
        .col-2 {
            width: 50% !important;
        }
        .col-2 center{
            display: flex;
            justify-content: center;
            align-items: center;
            padding-top: 20%;
        }
        .col-2 strong{
            font-size: 20px;
        }
    </style>
</head>
<body>
    @if ($registrations < 1)
        No Scratches.
    @else
    <table class="table-0">
        @for($p=0; $p <= $page; $p++)
            @for($r=0; $r < 3; $r++)
                <tr>
                    @for($c=0; $c < 2; $c++)
                        @if(!isset($matrix[$p][$r][$c]))
                            <td class="col-2"></td>
                        @else
                            <td class="col-2" style="margin-right:2px;">
                                <center> 
                                    <strong>{{ $matrix[$p][$r][$c]['name']  }} </strong> <br>
                                    {{ $matrix[$p][$r][$c]['gym'] }} <br>
                                </center>
                            </td>
                        @endif
                    @endfor
                </tr>
            @endfor
        @endfor
    </table>
    @endif
</body>
</html>