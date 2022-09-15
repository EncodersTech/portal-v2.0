@component('mail::message')
Hello {{ $reservation->contact_name }},

@if ($reservation->action == \App\Models\USAGReservation::RESERVATION_ACTION_ADD)
A new reservation for one of your clubs was
@elseif ($reservation->action == \App\Models\USAGReservation::RESERVATION_ACTION_UPDATE)
Updated details for one of your reservations were
@endif
received from USAG.<br/>


@if ($reservation->status == \App\Models\USAGReservation::RESERVATION_STATUS_UNASSIGNED)
Please create an Allgymnastics account to be able to manage your reservations. After creating your account,
you can create gyms with the correct USAG membership number for your reservations to automatically show up.

Create your account by clicking the link below :

@component('mail::button', ['url' => $url, 'color' => 'success'])
Create An Account
@endcomponent
@else
Please review the details as soon as possible, and apply them by clicking the button below :

- **Sanction # :** {{ $reservation->usag_sanction->number }}
- **Category :** {{ $category->name }}
- **For :** {{ $objectNameString }}

@component('mail::button', ['url' => $url, 'color' => 'success'])
View Details
@endcomponent
@endif

Thank you,<br>
{{ config('app.name') }}
@endcomponent