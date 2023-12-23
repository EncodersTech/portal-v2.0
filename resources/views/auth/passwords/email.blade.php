@extends('layouts.auth')

@section('content-auth')

    @if (session('status'))
        <div class="alert alert-success" role="alert">
            <span class="fas fa-info-circle"></span> {{ session('status') }}
        </div>
    @endif

    <p>
        Please enter the email address associated with your account. <br/>
        We'll send you  an email with instructions on how to reset your password.
    </p>

    <form method="POST" action="{{ route('password.email') }}">
        @csrf

        <div class="row mb-3">
            <div class="col">
                <div class="input-group">
                    <div class="input-group-prepend">
                        <span class="input-group-text"><span class="fas fa-fw fa-envelope"></span></span>
                    </div>
                    <input id="email" type="email" class="form-control @error('email') is-invalid @enderror"
                            name="email" value="{{ old('email') }}" placeholder="{{ __('messages.email') }}"
                            required autocomplete="email" autofocus>
                </div>
                @error('email')
                    <span class="invalid-feedback" role="alert">
                        <strong>We can't find a user with that e-mail address. Please contact customer service at support@allgymnastics.com</strong>
                    </span>
                @enderror
            </div>
        </div>

        <div class="row">
            <div class="col-auto">
                <a href="{{ route('login') }}" class="text-light">
                    <span class="fas fa-sign-in-alt"></span> Go back to sign-in page.
                </a>
            </div>
            <div class="col-lg text-right mb-3">
                <button type="submit" class="btn btn-danger">
                    <span class="fas fa-paper-plane"></span> {{ __('messages.submit')}}
                </button>
            </div>
        </div>
    </form>
@endsection
