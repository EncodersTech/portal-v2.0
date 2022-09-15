@extends('layouts.main')

@section('content-header')
<span class="fas fa-fw fa-user"></span> My Account
@endsection

<div class="content-main">
    <div class="row">
        @php
        print_r($active_gyms);
        @endphp
        @foreach($active_gyms as $gym)
        @include('user.minisite.gym')
        @endforeach
    </div>
    <div class="p-3">
    </div>
</div>
@section('scripts-main')
<script src="{{ mix('js/user/account-profile.js') }}"></script>
@endsection