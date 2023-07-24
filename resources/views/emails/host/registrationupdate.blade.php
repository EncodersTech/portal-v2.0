@component('mail::message')
Hello {{$meet->gym->user->fullName()}},

"{{ $gym->name }}" has updated registration for your {{ $meet->formatted_date }} "{{ $meet->name }}". For more information please visit your AllGym Account Dashboard.<br>

Update Summary: <br>

@if(count($changes['athlete']['new'])> 0)
<h4>New Athlete Registrations: {{count($changes['athlete']['new'])}}</h4>
<table style="width: 100%">
    <thead>
        <tr>
            <th>First Name</th>
            <th>Last Name</th>
            <th>Level</th>
            <th>Sanction</th>
        </tr>
    </thead>
    <tbody>
    @foreach($changes['athlete']['new'] as $athlete)
        <tr>
            <td style="text-align: center">{{ $athlete['first_name'] }}</td>
            <td style="text-align: center">{{ $athlete['last_name'] }}</td>
            <td style="text-align: center">{{ $athlete['current_level'] }}</td>
            <td style="text-align: center">{{ $athlete['sanction'] }}</td>
        </tr>
    @endforeach
    </tbody>
</table>
<br>
@endif

@if(count($changes['specialist']['new'])> 0)
<h4>New Specialist Registrations: {{count($changes['specialist']['new'])}}</h4>
<table style="width: 100%">
    <tr>
        <th>First Name</th>
        <th>Last Name</th>
        <th>Level</th>
        <th>Sanction</th>
        <th>Event</th>
    </tr>
@foreach($changes['specialist']['new'] as $specialist)
    <tr>
        <td style="text-align: center">{{ $specialist['first_name'] }}</td>
        <td style="text-align: center">{{ $specialist['last_name'] }}</td>
        <td style="text-align: center">{{ $specialist['current_level'] }}</td>
        <td style="text-align: center">{{ $specialist['sanction'] }}</td>
        <td style="text-align: center">
            @foreach($specialist['event'] as $event)
                {{ $event['name'] }}<br>
            @endforeach
        </td>
    </tr>
@endforeach
</table>
<br>
@endif

@if(count($changes['coach']['new'])> 0)
<h4>New Coach Registrations: {{count($changes['coach']['new'])}}</h4>
<table style="width: 100%">
    <tr>
        <th>First Name</th>
        <th>Last Name</th>
    </tr>
@foreach($changes['coach']['new'] as $coach)
    <tr>
        <td style="text-align: center">{{ $coach['first_name'] }}</td>
        <td style="text-align: center">{{ $coach['last_name'] }}</td>
    </tr>
@endforeach
</table>
<br>
@endif

@if(count($changes['athlete']['moved'])> 0)
<h4>Athlete Change Level: {{count($changes['athlete']['moved'])}}</h4>
<table style="width: 100%">
    <tr>
        <th>First Name</th>
        <th>Last Name</th>
        <th>Previous Level</th>
        <th>New Level</th>
        <th>Sanction</th>
    </tr>
@foreach($changes['athlete']['moved'] as $athlete)
    <tr>
        <td style="text-align: center">{{ $athlete['first_name'] }}</td>
        <td style="text-align: center">{{ $athlete['last_name'] }}</td>
        <td style="text-align: center">{{ $athlete['previous_level'] }}</td>
        <td style="text-align: center">{{ $athlete['current_level'] }}</td>
        <td style="text-align: center">{{ $athlete['sanction'] }}</td>
    </tr>
@endforeach
</table>
<br>
@endif


@if(count($changes['specialist']['moved'])> 0)
<h4>Specialist Change Level: {{count($changes['specialist']['moved'])}}</h4>
<table style="width: 100%">
    <tr>
        <th>First Name</th>
        <th>Last Name</th>
        <th>Previous Leve</th>
        <th>New Level</th>
        <th>Sanction</th>
    </tr>
@foreach($changes['specialist']['moved'] as $specialist)
    <tr>
        <td style="text-align: center">{{ $specialist['first_name'] }}</td>
        <td style="text-align: center">{{ $specialist['last_name'] }}</td>
        <td style="text-align: center">{{ $specialist['previous_level'] }}</td>
        <td style="text-align: center">{{ $specialist['current_level'] }}</td>
        <td style="text-align: center">{{ $specialist['sanction'] }}</td>
    </tr>
@endforeach
</table>
<br>
@endif


@if(count($changes['athlete']['scratched'])> 0)
<h4>Athlete Scratched: {{count($changes['athlete']['scratched'])}}</h4>
<table style="width: 100%">
    <tr>
        <th>First Name</th>
        <th>Last Name</th>
        <th>Level</th>
        <th>Sanction</th>
    </tr>
@foreach($changes['athlete']['scratched'] as $athlete)
    <tr>
        <td style="text-align: center">{{ $athlete['first_name'] }}</td>
        <td style="text-align: center">{{ $athlete['last_name'] }}</td>
        <td style="text-align: center">{{ $athlete['current_level'] }}</td>
        <td style="text-align: center">{{ $athlete['sanction'] }}</td>
    </tr>
@endforeach
</table>
<br>
@endif

@if(count($changes['specialist']['scratched'])> 0)
<h4>Specialist Scratched: {{count($changes['specialist']['scratched'])}}</h4>
<table style="width: 100%">
    <tr>
        <th>First Name</th>
        <th>Last Name</th>
        <th>Level</th>
        <th>Sanction</th>
        <th>Event</th>
    </tr>
@foreach($changes['specialist']['scratched'] as $specialist)
    <tr>
        <td style="text-align: center">{{ $specialist['first_name'] }}</td>
        <td style="text-align: center">{{ $specialist['last_name'] }}</td>
        <td style="text-align: center">{{ $specialist['current_level'] }}</td>
        <td style="text-align: center">{{ $specialist['sanction'] }}</td>
        <td style="text-align: center">
            @foreach($specialist['event'] as $event)
                {{ $event['name'] }}<br>
            @endforeach
        </td>
    </tr>
@endforeach
</table>
<br>
@endif

@if(count($changes['coach']['scratched'])> 0)
<h4>Coach Scratched: {{count($changes['coach']['scratched'])}}</h4>
<table style="width: 100%">
    <tr>
        <th>First Name</th>
        <th>Last Name</th>
    </tr>
@foreach($changes['coach']['scratched'] as $coach)
    <tr>
        <td style="text-align: center">{{ $coach['first_name'] }}</td>
        <td style="text-align: center">{{ $coach['last_name'] }}</td>
    </tr>
@endforeach
</table>
<br>
@endif

Thank you,<br>
{{ config('app.name') }}
@endcomponent