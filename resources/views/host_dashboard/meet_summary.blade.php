<table class="table" id="meet_summary_table">
    <thead>
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
    </thead>
    <tbody>
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
    </tbody>
</table>