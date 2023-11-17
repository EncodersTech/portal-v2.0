@component('mail::message')

Dear Admin,

<b> This is a warning about the following USAG Level that is not found in the system: </b>
</br>

@if($data['action'] == 1)
###USAG Reservation ADD details:
@elseif($data['action'] == 2)
###USAG Reservation Update details:
@elseif($data['action'] == 3)
###USAG Sanction Add details:
@elseif($data['action'] == 4)
###USAG Sanction Sanction details:
@endif
</br>
<table>
    <tr>
        <td>Gym Id</td>
        <td>: {{ $data['gym_id'] }}</td>
    </tr>
    <tr>
        <td>Gym USAG ID</td>
        <td>: {{ $data['gym_usag_no'] }}</td>
    </tr>
    <tr>
        <td>USAG Sanction Id</td>
        <td>: {{ $data['usag_sanction_id'] }}</td>
    </tr>
    <tr>
        <td>Contact Name</td>
        <td>: {{$data['contact_name']}}</td>
    </tr>
    <tr>
        <td>Contact Email</td>
        <td>: {{ strtolower($data['contact_email']) }}</td>
    </tr>
</table>

</br></br>

@if(count($levels['male']) > 0)
###Men's Level:
</br>
    @foreach ($levels['male'] as $level)
    - {{ $level }}
    @endforeach
@endif
</br>
@if(count($levels['female']) > 0)
###Women's Level:
</br>
    @foreach ($levels['female'] as $level)
    - {{ $level }}
    @endforeach
@endif
</br>

Please, login to admin and update the USAG Levels from : {{ $url }}.

Thank you,<br>
{{ config('app.name') }}
@endcomponent