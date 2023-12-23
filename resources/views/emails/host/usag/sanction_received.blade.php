@component('mail::message')
Hello {{ $sanction->contact_name }},

@if ($sanction->action == \App\Models\USAGSanction::SANCTION_ACTION_ADD)
A new sanction for one of your clubs was
@elseif ($sanction->action == \App\Models\USAGSanction::SANCTION_ACTION_UPDATE)
Updated details for one of your sanctions were
@elseif ($sanction->action == \App\Models\USAGSanction::SANCTION_ACTION_DELETE)
Removal details for one of your sanctions were
@else
Vendor changing details were
@endif
received from USAG.<br/>


@if ($sanction->status == \App\Models\USAGSanction::SANCTION_STATUS_UNASSIGNED)
Please create an Allgymnastics account to be able to manage your sanctions. After creating your account,
you can create gyms with the correct USAG membership number for your sanctions to automatically show up.

Create your account by clicking the link below :

@component('mail::button', ['url' => $url, 'color' => 'success'])
Create An Account
@endcomponent
@else
Please review the details as soon as possible, and apply them by clicking the button below :

- **Sanction # :** {{ $sanction->number }}
- **Category :** {{ $category->name }}
- **For :** {{ $objectNameString }}

@component('mail::button', ['url' => $url, 'color' => 'success'])
View Details
@endcomponent
@endif

Thank you,<br>
{{ config('app.name') }}
@endcomponent