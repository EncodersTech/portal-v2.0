@extends('layouts.main')
<style>
    .dd{
        background-color: #084c66;
        color: white;
        padding: 10px;
        margin: 5% 0%;
        border-radius: 2%;
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
        <a class="btn btn-primary " href="{{ route('meets.browse') }} ">List View</a>
        <a class="btn btn-success disabled" href="{{ route('meets.calendar') }}">Calendar View</a>
    </div>
@include('include.errors')
  <div id="app">
        <calendar></calendar>
  </div>
@endsection

@section('scripts-main')
<!-- <script src="{{ mix('js/meet/meets.js') }}"></script> -->
<script src="{{ mix('js/meet/meet-calendar.js') }}"></script>

@endsection