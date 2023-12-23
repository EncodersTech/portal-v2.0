@extends('layouts.main')

@section('content-header')
    <span class="fas fa-fw fa-user"></span> My Account
@endsection

@section('content-main')
    @include('include.errors')

    <div class="content-main">
        <div>
            @php ($active_tab = 'access_management')
            @include('include.user.profile_nav')
        </div>
        <div class="p-3">
            @include('user.account.account_managers')
            @include('user.account.managed_accounts')
        </div>
    </div>

@endsection

@section('scripts-main')
    <script src="{{ mix('js/user/account-access-management.js') }}"></script>
@endsection