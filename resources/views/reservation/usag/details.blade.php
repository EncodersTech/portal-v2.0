@extends('layouts.main')

@section('content-header')
    <span class="fas fa-fw fa-receipt"></span> USAG Reservation Details for Sanction #{{ $sanction->number }}
    <span class="small text-dark">
        {{ $_managed->isNotCurrentUser() ? '(owned by ' . $_managed->first_name .')' : ''}}
    </span>
@endsection

@section('content-main')
    @include('include.errors')

    <div class="content-main p-3">
        <ag-reservation-details :managed="{{ $_managed->id }}" :sanction_data="{{ $state }}" :sanction_id="{{ $sanction->number }}" :late="{{ $late ? 'true' : 'false' }}">
        </ag-reservation-details>
    </div>
@endsection

@section('scripts-main')
    <script src="{{ mix('js/usag/details.js') }}"></script>
@endsection
