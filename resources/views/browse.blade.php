@extends('layouts.main')
<style>
    .corner-cut {
        position: relative;
        overflow: hidden;
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
    <span class="fas fa-fw fa-calendar-check"></span> Browse Meets
    <span class="small text-dark">
        {{ $_managed->isNotCurrentUser() ? '(on behalf of ' . $_managed->first_name .')' : ''}}
    </span>
@endsection

@section('content-main')
    @include('include.errors')

    <div class="content-main p-3">
        <ag-browse-meet-list singular="meet" plural="meets" prefix="browse-meet-list">
        </ag-browse-meet-list>
    </div>

@endsection

@section('scripts-main')
    <script src="{{ mix('js/browse.js') }}"></script>
@endsection
