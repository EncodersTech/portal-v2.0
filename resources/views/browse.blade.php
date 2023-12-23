@extends('layouts.main')
<style>
    .corner-cut {
        position: relative;
        overflow: hidden;
    }
    .background-feature{
        background-image: linear-gradient(190deg, #e71010, #ffff0f, #248c24);
    }

    .corner-cut:after {
        content: '';
        display: block;
        height: 25px;
        width: 25px;
        background: #ddd;
        position: absolute;
        top: 0px;
        left: -14px;
        transform: skewX(316deg);
    }

    .corner-red:after {
        background: #e0a800;
    }
</style>

@section('content-header')
    <span class="fas fa-fw fa-calendar-check"></span> Browse and Register for Meets
    <span class="small text-dark">
        {{ $_managed->isNotCurrentUser() ? '(on behalf of ' . $_managed->first_name .')' : ''}}
    </span>
@endsection

@section('content-main')
    <div class="text-right">
        <a class="btn btn-primary disabled" href="{{ route('meets.browse') }} ">List View</a>
        <a class="btn btn-success" href="{{ route('meets.calendar') }}">Calendar View</a>
    </div>
    @include('include.errors')
    
    <div class="content-main p-3">
        <ag-browse-meet-list singular="meet" plural="meets" prefix="browse-meet-list">
        </ag-browse-meet-list>
    </div>

@endsection

@section('scripts-main')
<script src="{{ mix('js/browse.js') }}"></script>
@endsection
