@extends('layouts.main')

@section('content-header')
    <span class="fas fa-fw fa-receipt"></span> USAG Sanction #{{ $sanction }}  Details
    <span class="small text-dark">
        {{ $_managed->isNotCurrentUser() ? '(owned by ' . $_managed->first_name .')' : ''}}
    </span>
    <div v-if="{{isset($meetName)}}">
        <span class="small text-dark" style="padding-top: 10px !important; display: inline-block">
            <strong>Meet:</strong> {{ $meetName }}
        </span>
    </div>
@endsection

@section('content-main')
    @include('include.errors')

    <div class="content-main p-3">
        <ag-sanction-details :managed="{{ $_managed->id }}"  :sanction_data="{{ $state }}" :sanction_id="{{ $sanction }}">
        </ag-sanction-details>
    </div>
@endsection

@section('scripts-main')
    <script src="{{ mix('js/usag/details.js') }}"></script>
@endsection
