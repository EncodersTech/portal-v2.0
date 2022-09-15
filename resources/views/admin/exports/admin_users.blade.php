<table>
    <thead>
    <tr>
        <th>First Name</th>
        <th>Last Name</th>
        <th>Email</th>
        <th>Office Phone</th>
        <th>Job Title</th>
    </tr>
    </thead>
    <tbody>
    @foreach($userLists as $userList)
        <tr>
            <td>{{ $userList->first_name }}</td>
            <td>{{ $userList->last_name }}</td>
            <td>{{ $userList->email }}</td>
            <td>{{ $userList->office_phone }}</td>
            <td>{{ $userList->job_title }}</td>
        </tr>
    @endforeach
    </tbody>
</table>
