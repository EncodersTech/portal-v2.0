<table>
    <tr>
        <td colspan="8" style="text-align:center; background-color:gray;"><b>Lifetime Totals</b></td>
    </tr>
    <tr>
        <th colspan="2">Money Earned</th>
        <th colspan="2">Total Athletes</th>
        <th colspan="2">Total Coaches</th>
        <th colspan="2">Total Gyms</th>
    </tr>
    <tr>
        <td colspan="2">{{ number_format($summaryData['total_earn'], 2) }}</td>
        <td colspan="2">{{ $summaryData['total_ath'] }}</td>
        <td colspan="2">{{ $summaryData['total_coa'] }}</td>
        <td colspan="2">{{ $summaryData['total_gym'] }}</td>
    </tr>
    <tr>
        <td colspan="8" style="text-align:center; background-color:gray;"><b>Current (<?= date('Y') ?>) Year Totals</b></td>
    </tr>
    <tr>
        <td colspan="2">{{ number_format($summaryDataThisYear['total_earn'], 2) }}</td>
        <td colspan="2">{{ $summaryDataThisYear['total_ath'] }}</td>
        <td colspan="2">{{ $summaryDataThisYear['total_coa'] }}</td>
        <td colspan="2">{{ $summaryDataThisYear['total_gym'] }}</td>
    </tr>
    <tr>
        <td colspan="8" style="text-align:center; background-color:gray;"><b>Meet List</b></td>
    </tr>
    <tr>
        <th>Meet Name</th>
        <th>Meet Id</th>
        <th>Athlete Count</th>
        <th>Coach Count</th>
        <th>Gym Count</th>
        <th>Total Revenue</th>
        <th>AllGym Fees</th>
        <th>Total</th>
    </tr>
    @foreach($meetSummary as $meet)
        <tr>
            <td>{{ $meet['name'] }}</td>
            <td>{{ $meet['id'] }}</td>
            <td>{{ $meet['data']['total_ath'] }}</td>
            <td>{{ $meet['data']['total_coa'] }}</td>
            <td>{{ $meet['data']['total_gym'] }}</td>
            <td>{{ number_format($meet['data']['total_earn'],2) }}</td>
            <td>{{ number_format($meet['data']['allgym_fees'],2) }}</td>
            <td>{{ number_format($meet['data']['allgym_fees'] + $meet['data']['total_earn'],2)  }}</td>
        </tr>
    @endforeach
</table>