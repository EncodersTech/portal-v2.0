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
    .adminMeetTables tr th {
        text-align: center;
        vertical-align: middle !important;
    }

    tr:nth-child(even) {
        background-color: #f2f2f2;
    }
</style>
<body>
<div class="header">
    <div class="header-text">
        <h1 class="mb-0">
            Check Sending Details
        </h1>
        <h4 class="mb-0">
            Date: {{ now()->format(Helper::AMERICAN_FULL_DATE_TIME) }}
        </h4>
    </div>
    <div class="logo-container">
        @include('PDF.host.meet.reports.common_logo_image')
    </div>
</div>
<table class="table-0 table-striped full-width adminMeetTables" style="font-size: 20px">
    <tbody>
    <tr>
        <td style="width: 30%">
            <strong>Meet Name</strong>
        </td>
        <td class="col">
            {{ $meetRe->meet->name  }}
        </td>
    </tr>
    <tr>
        <td>
            <strong>Email</strong>
        </td>
        <td class="col">
            {{ $meetRe->meet->primary_contact_email }}
        </td>
    </tr>
    <tr>
        <td>
            <strong>Phone</strong>
        </td>
        <td class="col">
            {{ $meetRe->gym->office_phone }}
        </td>
    </tr>
    <tr>
        <td>
            <strong>Gym Name</strong>
        </td>
        <td class="col">
            {{ $meetRe->gym->name }}
        </td>
    </tr>
    <tr>
        <td>
            <strong>Address</strong>
        </td>
        <td class="col">
            {{ $meetRe->gym->addr_1 }},
            {{ ($meetRe->gym->addr_2 != null)?$meetRe->gym->addr_2.', ':'' }}
            {{ $meetRe->gym->city }},
            {{ $meetRe->gym->gym_state }},
            {{ $meetRe->gym->zipcode }}.
        </td>
    </tr>
    <tr>
        <td>
            <strong>Amount</strong>
        </td>
        <td class="col">
           $ {{ $meetRe->transactions[0]->total }}
        </td>
    </tr>
    </tbody>
</table>
</body>
</html>
