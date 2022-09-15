@extends('layouts.main')

@section('content-header')
    <span class="fas fa-fw fa-user"></span> My Account
@endsection

@section('content-main')
    @include('include.errors')

    <div class="content-main">
        <div>
            @php ($active_tab = 'profile')
            @include('include.user.profile_nav')
        </div>
        <div class="p-3">
            @include('user.account.profile')
            @include('user.account.password')
        </div>
    </div>

@endsection

@section('scripts-main')
    <script src="{{ mix('js/user/account-profile.js') }}"></script>
@endsection