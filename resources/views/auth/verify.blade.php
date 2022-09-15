@extends('layouts.auth')

@section('content-auth')
    <div class="p-3">
        @if (session('resent'))
            <div class="alert alert-success" role="alert">
                <span class="fas fa-info-circle"></span> A fresh verification link has been sent to your email address.
            </div>
        @endif

        <p>
            We sent you an email with a link to confirm your account.
            Once that's done, you will be able to login. <br/>
            If you did not receive the email, please 
            <a href="{{ route('verification.resend') }}" class="text-danger">
                click here
            </a>
            to request another.
        </p>

        <div>
            You can also go back to <a href="{{ config('app.main_website') }}" class="text-danger">the homepage</a> or 
            <form class="d-inline" action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-link text-danger pl-0">
                    sign out
                </button>
            </form>
        </div>
    </div>
@endsection
