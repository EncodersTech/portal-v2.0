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
    .adminUserTable tr th {
        text-align: center;
        vertical-align: middle !important;
    }
    tr:nth-child(even) {background-color: #f2f2f2;}
</style>
<body>
<div class="header">
    <div class="header-text">
        <h1 class="mb-0">
            Users Report
        </h1>
        <h4 class="mb-0">
            Date:  {{ now()->format(Helper::AMERICAN_FULL_DATE_TIME) }}
        </h4>
    </div>
    <div class="logo-container">
        @include('PDF.host.meet.reports.common_logo_image')
    </div>
</div>
@if ($userLists->count() < 1)
    No Users.
@else
    <table class="table-0 full-width adminUserTable">
        <thead>
        <tr>
            <th class="col-1" style="width: 2%"></th>
            <th class="col-2" style="width: 8%">Name</th>
            <th class="col-3" style="width: 12%">Email</th>
            <th class="col-4" style="width: 5%">Office Phone</th>
            <th class="col-5" style="width: 12%">Job Title</th>
            <th class="col-6" style="width: 8%">Created At</th>
        </tr>
        </thead>
        <tbody>
        @foreach ($userLists as $key => $userList)
            <tr>
                <td class="col-1">
                    {{ $loop->iteration  }}
                </td>
                <td class="col-2">
                    {{ $userList->full_name }}
                </td>
                <td class="col-3">
                    {{ $userList->email }}
                </td>
                <td class="col-4">
                    {{ $userList->office_phone }}
                </td>
                <td class="col-5">
                    {{ $userList->job_title }}
                </td>
                <td class="col-6">
                    {{ $userList->created_at->format(Helper::AMERICAN_FULL_DATE)  }}
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
    <br>
@endif
</body>
</html>
