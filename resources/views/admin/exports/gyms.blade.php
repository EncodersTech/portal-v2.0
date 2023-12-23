<table>
    <thead>
    <tr>
        <th>Id</th>
        <th>Email</th>
        <th>Gym Name</th>
        <th>Short Name</th>
        <th>Address 1</th>
        <th>Address 2</th>
        <th>Country</th>
        <th>State</th>
        <th>City</th>
        <th>Zipcode</th>
        <th>Office No.</th>
        <th>Mobile No.</th>
        <th>Fax</th>
        <th>Website</th>
        <th>USAG Membership</th>
        <th>USAIGC Membership</th>
        <th>AAU Membership</th>
        <th>NGA Membership</th>
        <th>Is Archive</th>
        <th>Handling Fee Override</th>
        <th>cc Fee Override</th>
        <th>Paypal Fee Override</th>
        <th>Ach Fee Override</th>
        <th>Check Fee Override</th>
        <th>Created At</th>
        <th>Updated At</th>
    </tr>
    </thead>
    <tbody>
    @foreach($levels as $level)
        <tr>
            <td>{{ $level['id'] }}</td>
            <td>{{ $level->user->email }}</td>
            <td>{{ $level['name'] }}</td>
            <td>{{ $level['short_name'] }}</td>
            <td>{{ $level['addr_1'] }}</td>
            <td>{{ $level['addr_2'] }}</td>
            <td>{{ $level->country->name }}</td>
            <td>{{ $level->state->name }}</td>
            <td>{{ $level['city'] }}</td>
            <td>{{ $level['zipcode'] }}</td>
            <td>{{ $level['office_phone'] }}</td>
            <td>{{ $level['mobile_phone'] }}</td>
            <td>{{ $level['fax'] }}</td>
            <td>{{ $level['website'] }}</td>
            <td>{{ $level['usag_membership'] }}</td>
            <td>{{ $level['usaigc_membership'] }}</td>
            <td>{{ $level['aau_membership'] }}</td>
            <td>{{ $level['nga_membership'] }}</td>
            <td>{{ $level['is_archived'] }}</td>
            <td>{{ $level['handling_fee_override'] }}</td>
            <td>{{ $level['cc_fee_override'] }}</td>
            <td>{{ $level['paypal_fee_override'] }}</td>
            <td>{{ $level['ach_fee_override'] }}</td>
            <td>{{ $level['check_fee_override'] }}</td>
            <td>{{ $level['created_at'] }}</td>
            <td>{{ $level['updated_at'] }}</td>
        </tr>
    @endforeach
    </tbody>
</table>
