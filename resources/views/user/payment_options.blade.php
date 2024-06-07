@extends('layouts.main')

@section('content-header')
    <span class="fas fa-fw fa-user"></span> My Account
@endsection

@section('content-main')
    @include('include.errors')
    
    <div class="content-main">
        <div>
            @php ($active_tab = 'payment_options')
            @include('include.user.profile_nav')
        </div>
        <div class="p-3">
            @if($is_error)
                <div class="alert alert-warning"><span class="fas fa-fw fa-info-circle"></span> Please contact admin to activate your payment methods.</div>
            @endif
            @if($cc_gateway == 0)
                @include('user.account.credit_card')
            @else
                @include('user.account.intellipay')
            @endif
            <!-- include('user.account.stripe_bank_account') -->
            @include('user.account.bank_account')
        </div>
    </div>

@endsection

@section('scripts-main')
    <script src="https://js.stripe.com/v3/"></script>
    <script src="https://cdn.dwolla.com/1/dwolla.min.js"></script>
    <script src="{{ mix('js/user/account-payment-options.js') }}"></script>
@endsection