@extends('layouts.auth')

@section('content-auth')

    <form method="POST" action="{{ route('password.update') }}">
        @csrf

        <input type="hidden" name="token" value="{{ $token }}">

        <div class="row mb-3">
            <div class="col">
                <label for="email">
                    <span class="fas fa-fw fa-envelope"></span> {{ __('messages.email') }} <span class="text-danger">*</span>
                </label>
                <div class="input-group input-group-sm">
                    <div class="input-group-prepend">
                        <span class="input-group-text"><span class="fas fa-fw fa-envelope"></span></span>
                    </div>
                    <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" 
                            name="email" value="{{ $email ?? old('email') }}" placeholder="{{ __('messages.email') }}" 
                            required autocomplete="email" autofocus>
                </div>
                @error('email')
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
            </div>
        </div>

        <div class="row">
            <div class="col-lg mb-3">
                <label for="password">
                    <span class="fas fa-fw fa-lock"></span> {{ __('messages.password') }} <span class="text-danger">*</span>
                </label>
                <div class="input-group input-group-sm">
                    <div class="input-group-prepend">
                        <span class="input-group-text"><span class="fas fa-fw fa-lock"></span></span>
                    </div>
                    <input id="password" type="password" class="form-control @error('password') is-invalid @enderror"
                            name="password" placeholder="{{ __('messages.password') }}" 
                            required autocomplete="off">
                </div>

                @error('password')
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
            </div>
        </div>
        
        <div class="row">
            <div class="col-lg mb-3">
                <label for="password_confirmation">
                    <span class="fas fa-fw fa-lock"></span> {{ __('messages.password_confirmation') }} <span class="text-danger">*</span>
                </label>
                <div class="input-group input-group-sm">
                    <div class="input-group-prepend">
                        <span class="input-group-text"><span class="fas fa-fw fa-lock"></span></span>
                    </div>
                    <input id="password_confirmation" type="password" class="form-control @error('password') is-invalid @enderror"
                            name="password_confirmation" placeholder="{{ __('messages.password_confirmation') }}" 
                            required autocomplete="off">
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md text-right mb-3">
                <button type="submit" class="btn btn-danger">
                    <span class="fas fa-paper-plane"></span> {{ __('messages.submit')}}
                </button>
            </div>
        </div>
    </form>
@endsection
