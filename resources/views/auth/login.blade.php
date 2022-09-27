@extends('layouts.auth')

@section('content-auth')
    <div class="row mb-2">
        <div class="col">
            <div class="alert alert-info">
                <span class="fas fa-fw fa-info-circle"></span> Welcome Back!
            </div>
        </div>
    </div>
    <div class="row pr-3 pl-3">
        <div class="col">
            @if ($errors->any())
                <div class="alert alert-danger p-0 row">
                    <ul class="m-2">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>
    </div>
    <form method="POST" action="{{ route('login') }}" id="login_form">
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
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
            </div>
        </div>

        <div class="row mb-3">
            <div class="col">
                <div class="input-group">
                    <div class="input-group-prepend">
                        <span class="input-group-text"><span class="fas fa-fw fa-lock"></span></span>
                    </div>
                    <input id="password" type="password" class="form-control @error('password') is-invalid @enderror"
                            name="password" placeholder="{{ __('messages.password') }}"
                            required autocomplete="current-password">
                </div>

                @error('password')
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
            </div>
        </div>
        <div class="row">
            <div class="col-auto">
                <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox" name="remember"
                                id="remember" {{ old('remember') ? 'checked' : '' }}>

                    <label class="form-check-label" for="remember">
                        {{ __('messages.remember_me') }}
                    </label>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col mb-3">
                <div class="form-check">
                <div class="h-captcha" 
                data-sitekey="{{env('HCAPTCHA_SITE_KEY')}}" 
                data-callback="correctCaptcha"
                data-expired-callback="expiredCaptcha"></div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col mb-3">
                <div id="recaptcha-error" class="text-danger" style="display:none;">CAPTCHA is not solved</div>
            </div>
        </div>
        <div class="row">
            <div class="col mb-3">
                <div class="alert alert-warning">
                    <span class="fas fa-fw fa-info-circle"></span>
                    Because the security of your information is paramount, we have incorporated this captcha
                    to help protect our information.</div>
            </div>
        </div>
        
        <div class="row">
            <div class="col-lg text-right mb-3">
                <button type="button" class="btn btn-danger" disabled id="ssd" onclick="submitForm()">
                    <span class="fas fa-sign-in-alt"></span> {{ __('messages.login')}}
                </button>
            </div>
        </div>
    </form>

    <div class="row mt-3 small">
        <div class="col-auto mb-1">
            <a href="{{ route('register') }}" class="text-light">
                <span class="fas fa-user-plus"></span> Create an account
            </a>
        </div>
        <div class="col-lg text-right mb-1">
            <a href="{{ route('password.request') }}" class="text-light">
                <span class="fas fa-undo-alt"></span> Forgot your password ? click here to reset
            </a>
        </div>
    </div>
@endsection
@section('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>
<script src="https://js.hcaptcha.com/1/api.js" async defer></script>

<script type="text/javascript">
    function correctCaptcha(token) {
        $('#ssd').removeAttr('disabled');
        $('#recaptcha-error').hide();
    }
    function expiredCaptcha() {
        $('#ssd').prop("disabled",true);
        
    }
    function submitForm()
    {
        var response = hcaptcha.getResponse();
        //recaptcha failed validation
        if (response.length == 0) {
          $('#recaptcha-error').show();
          return false;
        }
        $('#login_form').submit();
    }
  </script>
 @endsection