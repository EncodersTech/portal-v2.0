@extends('layouts.main')

@section('content-header')
    <span class="fas fa-fw fa-shopping-cart"></span> Pay for {{ $gym->name }}'s registration in {{ $meet->name }}
    <span class="small text-dark">
        {{ $_managed->isNotCurrentUser() ? '(on behalf of ' . $_managed->first_name .')' : ''}}
    </span>
@endsection

@section('content-main')
    @include('include.errors')

    <div class="content-main p-3">
        <div class="row">
            <div class="col-lg-3 mb-3">
                <div class="mb-3">
                    <a href="{{ $meet->profile_picture }}" target="_blank">
                        <img id="profile-picture-display" src="{{ $meet->profile_picture }}"
                            class="rounded profile-picture-256" alt="Meet Picture">
                    </a>
                </div>

                @include('include.meet.sidebar_info')
            </div>

            <div class="col">
                <ag-registration-repayment :transaction-id="{{ $transaction->id }}"                    :gym-id="{{ $gym->id }}" :managed="{{ $_managed->id }}" :meet-id="{{ $meet->id }}"
                    :subtotal="{{ $subtotal }}" :registration-id="{{ $registration->id }}">
                </ag-registration-repayment>
            </div>
        </div>
    </div>

@endsection

@section('scripts-main')
    <script src="{{ mix('js/register/repay.js') }}"></script>
@endsection