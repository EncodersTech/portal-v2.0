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
               Pending Withdrawal Balance Report
            </h1>
            <br/><br/>
            <h2 class="mb-0">
                {{ $user->fullName() }}
            </h2>
            <b>Email: </b> {{$user->email}}
            <br/>
            <b>Phone: </b> {{$user->office_phone}}
        </div>
        <div class="logo-container">
            @include('PDF.host.meet.reports.common_logo_image')
        </div>
    </div>

    <div>
        <table class="table-0 full-width adminMeetTables">
            <thead>
                <tr>
                    <th class="col" style="width: 80%">Transaction</th>
                    <th>Amount</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td  class="col" style="width: 80%">Total Revenue</td>
                    <td style="color:green;">$ {{ number_format($revenue, 2) }}</td>
                </tr>
                @if($registration_payment > 0)
                <tr>
                    <td class="col" style="width: 80%">Allgym Balance Payment</td>
                    <td style="color:red;">-$ {{ number_format($registration_payment, 2) }}</td>
                </tr>
                @endif
                @if($dwolla_verification_fee > 0)
                <tr>
                    <td class="col" style="width: 80%">ACH Verification Fee</td>
                    <td style="color:red;">-$ {{ number_format($dwolla_verification_fee, 2) }}</td>
                </tr>
                @endif
                @if($admin_transaction > 0)
                <tr>
                    <td class="col" style="width: 80%">Admin Transactions</td>
                    <td style="color:red;">-$ {{ number_format($admin_transaction, 2) }}</td>
                </tr>
                @endif
                @if($withdrawal > 0)
                <tr>
                    <td class="col" style="width: 80%">Previous Total Withdrawal Balance</td>
                    <td style="color:red;">-$ {{ number_format($withdrawal, 2) }}</td>
                </tr>
                @endif
                
                @if($total_cleared_balance - $total > 0.1)
                <tr>
                    <td class="col" style="width: 80%; color:red;">ERROR in BALANCE CALCULATION</td>
                    <td>$ {{ number_format(($total_cleared_balance - $total), 2) }} </td>
                </tr>
                @else
                <tr>
                    <td class="col" style="width: 80%; color:green;">Pending Cleared Withdrawal Balance</td>
                    <td>$ {{ number_format($total_cleared_balance, 2) }}</td>
                </tr>
                @endif
            </tbody>
        </table>
    </div>
</body>
</html>
