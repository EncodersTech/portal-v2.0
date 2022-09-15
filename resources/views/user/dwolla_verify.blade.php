@extends('layouts.main')

@section('content-header')
    <span class="fas fa-fw fa-user"></span> My Account
@endsection

@section('content-main')
    @include('include.errors')

    <div class="content-main p-3">
        <h5 class="border-bottom">
            <span class="fas fa-project-diagram"></span> Verify My Linked Dwolla Customer Account
        </h5>

        <div class="alert alert-primary">
            <strong>
                <span class="fas fa-info-circle"></span> We do not store any of the information or
                documents you provide here on our servers.<br/>
            </strong>
            All the information and documents provided
            here are sent to Dwolla for verification purposes.
        </div>

        <div class="small text-info mb-3">
            <span class="fas fa-info-circle"></span> You have
            {{ $remainingAttempts > 0 ? $remainingAttempts : 'no' }} free verification attempts
            remaining. Additional attempts are
            ${{ number_format(\App\Models\Setting::dwollaVerificationFee(), 2) }} each.
        </div>

        @switch($dwolla->status)
            @case(\App\Services\DwollaService::STATUS_DOCUMENT)
                <div class="mb-3">
                    To verify your account, please provide a US Government issued identification.<br/>
                    Make sure to provide an up-to-date, valid, and clear color scan of your document.<br/>
                    <strong class="text-danger">
                        <span class="fas fa-exclamation-circle"></span> A blurry scan or an expired
                        documents will result in the verification taking longer or failing.
                    </strong>
                </div>

                <form action="{{ route('account.dwolla.verify.document') }}"
                    method="POST" enctype="multipart/form-data">
                    @csrf

                    <div class="form-group">
                        <label class="control-label" for="document_type">
                            <span class="fas fa-fw fa-passport"></span>
                            Document Type <span class="text-danger">*</span>
                        </label>
                        <select class="form-control" id="document_type" name="type" required>
                            <option value="">(Choose below ...)</option>
                            @foreach (\App\Models\DwollaVerificationAttempt::DOCUMENT_TYPE_STRINGS as $k => $v)
                                <option value="{{ $k }}">
                                    {{ $v }}
                                </option>
                            @endforeach
                        </select>
                    </div>
    
                    <div class="form-group">
                        <label class="mb-0">
                            <span class="fas fa-fw fa-file"></span>
                            Document <span class="text-danger">*</span>
                        </label>
                        <div class="custom-file">
                            <input type="file" class="custom-file-input" name="document"
                                id="dwolla_document" required>
                            <label class="custom-file-label hidden-overflow" for="dwolla_document">
                                Choose a document ...
                            </label>
                        </div>
                        <div class="small text-info mt-1">
                            <span class="fas fa-info-circle"></span> Maximum file size :
                            {{ Helper::formatByteSize(config('services.dwolla.verification_document_size') * 1024) }}.
                            <br/> Allowed file types :
                            {{ strtoupper(implode(', ', \App\Models\DwollaVerificationAttempt::ALLOWED_DOCUMENT_TYPES)) }}
                        </div>
                    </div>

                    <div class="row">
                        <div class="col text-right">
                            <button type="submit" class="btn btn-primary">
                                <span class="fas fa-user-check"></span> Verify
                            </button>
                        </div>
                    </div>
                </form>
                @break

            @default
                <div class="mb-3">
                    To verify your account, please provide the additional info below.
                    Items followed by a red asterisk are required.<br/>
                    Make sure to provide the most up-to-date and accurate information.<br/>
                    <strong class="text-danger">
                        <span class="fas fa-exclamation-circle"></span> Inacurate information will result
                        in the verification taking longer or failing.
                    </strong>
                </div>

                <form method="POST" action="{{ route('account.dwolla.verify.info') }}">
                    @csrf

                    <div class="row">
                        <div class="col-lg mb-3">
                            <label for="date_of_birth">
                                <span class="fas fa-fw fa-birthday-cake"></span> Date Of Birth <span class="text-danger">*</span>
                            </label>
                            <div class="input-group input-group-sm">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><span class="fas fa-fw fa-birthday-cake"></span></span>
                                </div>
                                <datepicker :input-class="'form-control form-control-sm bg-white'"
                                    :value="{{ old('date_of_birth') ? 'new Date(\'' . old('date_of_birth') . '\')' : 'state.date' }}"
                                    :wrapper-class="'flex-grow-1'" name="date_of_birth" :format="'yyyy-MM-dd'"
                                    :bootstrap-styling="true" :typeable="true" :required="true">
                                </datepicker>
                            </div>
                            @error('date_of_birth')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="col-lg mb-3">
                            <label for="ssn" class="mb-1">
                                <span class="fas fa-fw fa-address-card"></span>
                                {{
                                    $dwolla->status == \App\Services\DwollaService::STATUS_UNVERIFIED ?
                                    'Last 4' : 'All 9'
                                }}
                                Digits Of SSN <span class="text-danger">*</span>
                            </label>
                            <div class="input-group input-group-sm">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><span class="fas fa-fw fa-address-card"></span></span>
                                </div>
                                <input id="ssn" type="text" class="form-control @error('ssn') is-invalid @enderror" 
                                        name="ssn" value="{{ old('ssn') }}" placeholder="SSN" 
                                        required autocomplete="ssn">
                            </div>
                            @error('ssn')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-lg mb-3">
                            <label for="addr_1" class="mb-0">
                                <span class="fas fa-fw fa-map-marker-alt"></span> Address Line 1 <span class="text-danger">*</span>
                            </label>
                            <div class="input-group input-group-sm">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><span class="fas fa-fw fa-map-marker-alt"></span></span>
                                </div>
                                <input id="addr_1" type="text" class="form-control @error('addr_1') is-invalid @enderror" 
                                        name="addr_1" value="{{ old('addr_1') }}" placeholder="Address Line 1" 
                                        required autocomplete="addr_1">
                            </div>
                            @error('addr_1')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="col-lg mb-3">
                            <label for="addr_2" class="mb-0">
                                <span class="fas fa-fw fa-map-marker"></span> Address Line 2
                            </label>
                            <div class="input-group input-group-sm">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><span class="fas fa-fw fa-map-marker"></span></span>
                                </div>
                                <input id="addr_2" type="text" class="form-control @error('addr_2') is-invalid @enderror" 
                                        name="addr_2" value="{{ old('addr_2') }}" placeholder="Address Line 2" 
                                        autocomplete="addr_2">
                            </div>
                            @error('addr_2')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                    </div>
        
                    <div class="row">
                        <div class="col-lg mb-3">
                            <label for="city" class="mb-1">
                                <span class="fas fa-fw fa-map-marked-alt"></span> City <span class="text-danger">*</span>
                            </label>
                            <div class="input-group input-group-sm">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><span class="fas fa-fw fa-map-marked-alt"></span></span>
                                </div>
                                <input id="city" type="text" class="form-control @error('city') is-invalid @enderror" 
                                        name="city" value="{{ old('city') }}" placeholder="City" 
                                        required autocomplete="city">
                            </div>
                            @error('city')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="col-lg mb-3">
                            <label for="zipcode" class="mb-1">
                                <span class="fas fa-fw fa-location-arrow"></span> Zip code <span class="text-danger">*</span>
                            </label>
                            <div class="input-group input-group-sm">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><span class="fas fa-fw fa-location-arrow"></span></span>
                                </div>
                                <input id="zipcode" type="text" class="form-control @error('zipcode') is-invalid @enderror" 
                                        name="zipcode" value="{{ old('zipcode') }}" placeholder="Zip code" 
                                        required autocomplete="zipcode">
                            </div>
                            @error('zipcode')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                    </div>
            
                    <div class="row">
                        <div class="col-lg mb-3">
                            <label for="state" class="mb-1">
                                <span class="fas fa-fw fa-map"></span> State <span class="text-danger">*</span>
                            </label>
                            <div class="input-group input-group-sm">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><span class="fas fa-fw fa-map"></span></span>
                                </div>
                                <select id="state" class="form-control @error('state') is-invalid @enderror" 
                                        name="state" required>
                                    @foreach ($states as $state)
                                        <option value="{{ $state->code }}" {{ old('state') == $state->code ? 'selected' : '' }}>
                                            {{ $state->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            @error('state')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                    </div>

                    <div class="row">
                        <div class="col text-right">
                            <button type="submit" class="btn btn-primary">
                                <span class="fas fa-user-check"></span> Verify
                            </button>
                        </div>
                    </div>
                </form>
        @endswitch
    </div>

@endsection

@section('scripts-main')
    <script src="{{ mix('js/user/account-dwolla-verify.js') }}"></script>
@endsection