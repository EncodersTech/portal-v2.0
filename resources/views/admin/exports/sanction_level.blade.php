<table>
    <thead>
    <tr>
        <th>Body</th>
        <th>Level Name</th>
        <th>Code</th>
        <th>Abbreviation</th>
    </tr>
    </thead>
    <tbody>
    @foreach($levels as $level)
        <tr>
            <td>{{ $level['body'] }}</td>
            <td>{{ $level['level_name'] }}</td>
            <td>{{ $level['code'] }}</td>
            <td>{{ $level['abr'] }}</td>
        </tr>
    @endforeach
    </tbody>
</table>
