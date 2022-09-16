@extends('layouts.auth')
@section('content-auth')
    <form method="POST" action="{{ route('register') }}" enctype="multipart/form-data" id="registation_form" >
        @csrf

        @if ($invitation != null)
            <input type="hidden" name="member_invite" value="{{ $invitation->token }}">
        @endif

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
                            value="{{ $invitation == null ? old('email') : $invitation->email }}"
                            name="email" placeholder="{{ __('messages.email') }}" required autofocus
                            autocomplete="email" {{ $invitation != null ? 'readonly' : '' }}>
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
                    
                    <div class="input-group-append">
                        <button class="btn btn-info" type="button" id="password-view-switch">
                            <span id="btn-eye" class="fas fa-eye"></span>
                        </button>
                    </div>
                </div>

                @error('password')
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
            </div>

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

        <div class="row mt-3">
            <div class="col-lg mb-3">
                <label for="first_name">
                    <span class="fas fa-fw fa-user"></span> {{ __('messages.first_name') }} <span class="text-danger">*</span>
                </label>
                <div class="input-group input-group-sm">
                    <div class="input-group-prepend">
                        <span class="input-group-text"><span class="fas fa-fw fa-user"></span></span>
                    </div>
                    <input id="first_name" type="text" class="form-control @error('first_name') is-invalid @enderror" 
                            name="first_name" value="{{ old('first_name') }}" placeholder="{{ __('messages.first_name')}}" 
                            required autocomplete="first_name" autofocus>
                </div>
                @error('first_name')
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
            </div>

            <div class="col-lg mb-3">
                <label for="last_name">
                    <span class="far fa-fw fa-user"></span> {{ __('messages.last_name') }} <span class="text-danger">*</span>
                </label>
                <div class="input-group input-group-sm">
                    <div class="input-group-prepend">
                        <span class="input-group-text"><span class="far fa-fw fa-user"></span></span>
                    </div>
                    <input id="last_name" type="text" class="form-control @error('last_name') is-invalid @enderror" 
                            name="last_name" value="{{ old('last_name') }}" placeholder="{{ __('messages.last_name')}}" 
                            required autocomplete="last_name">
                </div>
                @error('last_name')
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
            </div>
        </div>

        <div class="row">
            <div class="col-lg mb-3">
                <label for="office_phone">
                    <span class="fas fa-fw fa-phone"></span> {{ __('messages.office_phone') }} <span class="text-danger">*</span>
                </label>
                <div class="input-group input-group-sm">
                    <div class="input-group-prepend">
                        <span class="input-group-text"><span class="fas fa-fw fa-phone"></span></span>
                    </div>
                    <input id="office_phone" type="text" class="form-control @error('office_phone') is-invalid @enderror" 
                            name="office_phone" value="{{ old('office_phone') }}" placeholder="{{ __('messages.office_phone')}}" 
                            required autocomplete="office_phone">
                </div>
                @error('office_phone')
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
            </div>

            <div class="col-lg mb-3">
                <label for="job_title">
                    <span class="fas fa-fw fa-briefcase"></span> {{ __('messages.job_title') }} <span class="text-danger">*</span>
                </label>
                <div class="input-group input-group-sm">
                    <div class="input-group-prepend">
                        <span class="input-group-text"><span class="fas fa-fw fa-briefcase"></span></span>
                    </div>
                    <input id="job_title" type="text" class="form-control @error('job_title') is-invalid @enderror" 
                            name="job_title" value="{{ old('job_title') }}" placeholder="{{ __('messages.job_title')}}" 
                            required autocomplete="job_title">
                </div>
                @error('job_title')
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
            </div>
        </div>

        <div class="row">
            <div class="col mb-3">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="terms_of_service_and_privacy_policy" 
                            id="terms_of_service_and_privacy_policy" {{ old('terms_of_service_and_privacy_policy') ? 'checked' : '' }}>

                    <label class="form-check-label" for="terms_of_service_and_privacy_policy">
                        By clicking here you agree to All Gymnastics
                        <a id="ag-tos" href="#terms_of_service_and_privacy_policy" target="_blank" class="text-danger">
                            Terms of Service
                        </a> and
                        <a id="ag-pp" href="#privacy-policy" target="_blank" class="text-danger">
                            Privacy Policy
                        </a>
                        as well as our payment partner Dwolla
                        <a id="dwola-tos" href="https://www.dwolla.com/legal/tos/" target="_blank" class="text-danger">
                            Terms Of Service
                        </a> and
                        <a id="dwola-pp" href="https://www.dwolla.com/legal/privacy/" target="_blank" class="text-danger">
                            Privacy Policy
                        </a>
                        .
                    </label>
                </div>

                @error('terms_of_service_and_privacy_policy')
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
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
            <div class="col-lg text-right mb-3">
                <button type="button" class="btn btn-danger" disabled id="ssd" onclick="submitForm()" >
                    <span class="fas fa-user-plus"></span> {{ __('messages.register')}}
                </button>
            </div>
        </div>
    </form>

    <div class="row small">
        <div class="col-lg text-right">
            <a href="{{ route('login') }}" class="text-light">
                <span class="fas fa-sign-in-alt"></span>  Already have an account ? Sign in
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
        $('#registation_form').submit();
    }
    $('#password-view-switch').click(e => {
        let type = ($('#password').attr('type') == 'password' ? 'text' : 'password');
        $('#btn-eye').toggleClass('fa-eye fa-eye-slash');
        $('#password').attr('type', type);
    });
  </script>
 @endsection