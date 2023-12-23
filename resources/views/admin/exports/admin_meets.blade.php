<table>
    <thead>
    <tr>
        <th>Meet Name</th>
        <th>Club Name</th>
        <th>Start Date</th>
        <th>End Date</th>
        <th>Website</th>
        <th>Location</th>
        <th>Sanctioning Bodies</th>
        <th>Registration Status</th>
    </tr>
    </thead>
    <tbody>
    @foreach($meetLists as $meetList)
        <tr>
            <td>{{ $meetList->name }}</td>
            <td>{{ $meetList->gym->name }}</td>
            <td>{{ \Illuminate\Support\Carbon::parse($meetList->start_date)->format('d-m-Y') }}</td>
            <td>{{ \Illuminate\Support\Carbon::parse($meetList->end_date)->format('d-m-Y') }}</td>
            <td>{{ $meetList->website }}</td>
            <td>{{ $meetList->venue_state->name }}, {{ $meetList->venue_state->code }}</td>
            <td>@foreach($meetList->sanctionBodies as $key => $sanction)  {{$loop->first?'':', '}} {{$sanction }}  @endforeach</td>
            <td>{{ \App\Models\Meet::STATUS_ARRAY[$meetList->registrationStatus] }}</td>
        </tr>
    @endforeach
    </tbody>
</table>
