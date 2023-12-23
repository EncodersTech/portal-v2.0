@extends('layouts.main')

@section('content-header')
    <span class="fas fa-fw fa-running"></span>
    Create a Coach
    <span class="small text-dark">
        {{ $_managed->isNotCurrentUser() ? '(on behalf of ' . $_managed->first_name .')' : ''}}
    </span>
@endsection

@section('content-main')
    <h5 class="secondary-title font-weight-bold ml-3">
        <span class="fas fa-fw fa-dumbbell"></span>
        {{ $gym->name }}
    </h5>

    @include('include.errors')

    <div class="alert alert-primary">
        <strong class="d-block mb-2"><span class="fas fa-exclamation-circle"></span>
            We realize USAIGC doesn't issue Coach's Numbers. Please use your club# in the USAIGC Membership field. Please note, AllGym will assign a unique number as an add on. This is for AllGym purposes only. 
        </strong>
    </div>
    <div class="content-main p-3">
        <div class="row">
            <div class="col">
                <form method="POST" action="{{ route('gyms.coaches.store', ['gym' => $gym]) }}">
                    @csrf

                    <div class="row">
                        <div class="col">
                            <h5 class="border-bottom"><span class="fas fa-fw fa-user-plus"></span> Coach Info</h5>
                        </div>
                    </div>

                    <div class="row mt-2">
                        <div class="col-lg mb-3">
                            <label for="first_name">
                                <span class="fas fa-fw fa-user"></span> First Name <span class="text-danger">*</span>
                            </label>
                            <div class="input-group input-group-sm">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><span class="fas fa-fw fa-user"></span></span>
                                </div>
                                <input id="first_name" type="text" class="form-control @error('first_name') is-invalid @enderror"
                                        name="first_name" value="{{ old('first_name') }}" placeholder="First Name"
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
                                <span class="far fa-fw fa-user"></span> Last Name <span class="text-danger">*</span>
                            </label>
                            <div class="input-group input-group-sm">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><span class="far fa-fw fa-user"></span></span>
                                </div>
                                <input id="last_name" type="text" class="form-control @error('last_name') is-invalid @enderror"
                                        name="last_name" value="{{ old('last_name') }}" placeholder="Last Name"
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
                            <label for="gender">
                                <span class="fas fa-fw fa-venus-mars"></span> Gender <span class="text-danger">*</span>
                            </label>
                            <div class="input-group input-group-sm">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><span class="fas fa-fw fa-venus-mars"></span></span>
                                </div>
                                <select id="gender" class="form-control @error('gender') is-invalid @enderror"
                                        name="gender" required>
                                    <option value="">(Choose below ...)</option>
                                    <option value="female" {{ old('gender') == 'female' ? 'selected' : '' }}>
                                        Female
                                    </option>
                                    <option value="male" {{ old('gender') == 'male' ? 'selected' : '' }}>
                                        Male
                                    </option>
                                </select>
                            </div>
                            @error('gender')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="col-lg mb-3">
                            <label for="dob">
                                <span class="fas fa-fw fa-birthday-cake"></span> Date Of Birth <span class="text-danger">*</span>
                            </label>
                            <div class="input-group input-group-sm">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><span class="fas fa-fw fa-birthday-cake"></span></span>
                                </div>
                                <datepicker :input-class="'form-control form-control-sm bg-white'"
                                    :value="{{ old('dob') ? 'new Date(\'' . old('dob') . '\')' : 'date' }}"
                                    :wrapper-class="'flex-grow-1'" name="dob" :format="'MM/dd/yyyy'" placeholder="mm/dd/yyyy"
                                    :bootstrap-styling="true" :typeable="true" :required="true">
                                </datepicker>
                            </div>
                            @error('dob')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-lg mb-3">
                            <label for="tshirt_size_id">
                                <span class="fas fa-fw fa-tshirt"></span> T-Shirt Size
                            </label>
                            <div class="input-group input-group-sm">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><span class="fas fa-fw fa-tshirt"></span></span>
                                </div>
                                <select id="tshirt_size_id" class="form-control @error('tshirt_size_id') is-invalid @enderror"
                                        name="tshirt_size_id">
                                    <option value="">(Choose below ...)</option>
                                    @foreach ($tshirt_chart->sizes as $size)
                                        <option value="{{ $size->id }}"
                                            {{ $size->id == old('tshirt_size_id') ? 'selected' : '' }}>
                                            {{ $size->size }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            @error('tshirt_size_id')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="is_us_citizen"
                                    id="is_us_citizen" {{ old('is_us_citizen') != null ? 'checked' : ''}}>
                                <label class="form-check-label" for="is_us_citizen">
                                    US Citizen
                                </label>
                            </div>

                            @error('is_us_citizen')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                    </div>

                    <div class="row">
                        <div class="col">
                            <h5 class="border-bottom"><span class="fas fa-fw fa-receipt"></span> Memberships</h5>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-lg mb-1 pt-1">
                            <div class="form-check mb-2">
                                <input id="usag_checkbox" class="form-check-input coach-membership-checkbox"
                                    name="usag_checkbox" type="checkbox" data-body="usag"
                                    {{ old('usag_checkbox') != null ? 'checked' : ''}}>
                                <label class="form-check-label" for="usag_checkbox">
                                    USAG Membership
                                </label>
                            </div>

                            <div id="usag-membership-fields">
                                <div class="mb-3">
                                    <input id="usag_no" type="text" placeholder="Enter your USAG membership No."
                                            class="form-control form-control-sm @error('usag_no') is-invalid @enderror"
                                            name="usag_no" data-body="usag" value="{{ old('usag_no') }}"
                                            required autocomplete="usag_no" disabled>
                                    @error('usag_no')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="usag_expiry">
                                        <span class="fas fa-fw fa-calendar-check"></span>
                                        Professional No. Expiry Date
                                    </label>
                                    <datepicker input-class="form-control form-control-sm vue-date-picker-fixer"
                                        :value="{{ old('usag_expiry') ? 'new Date(\'' . old('usag_expiry') . '\')' : 'null' }}"
                                        :wrapper-class="'flex-grow-1'" name="usag_expiry" :format="'MM/dd/yyyy'" placeholder="mm/dd/yyyy"
                                        :bootstrap-styling="true" :typeable="true" :disabled="usagDatesDisabled"
                                        placeholder="Professional No. Expiry Date">
                                    </datepicker>
                                    @error('usag_expiry')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="usag_safety_expiry">
                                        <span class="fas fa-fw fa-calendar-check"></span>
                                        Safety Certification Expiry Date
                                    </label>
                                    <datepicker input-class="form-control form-control-sm vue-date-picker-fixer"
                                        :value="{{ old('usag_safety_expiry') ? 'new Date(\'' . old('usag_safety_expiry') . '\')' : 'null' }}"
                                        :wrapper-class="'flex-grow-1'" name="usag_safety_expiry" :format="'MM/dd/yyyy'" placeholder="mm/dd/yyyy"
                                        :bootstrap-styling="true" :typeable="true" :disabled="usagDatesDisabled"
                                        placeholder="Safety Certification Expiry Date">
                                    </datepicker>
                                    @error('usag_safety_expiry')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="usag_safesport_expiry">
                                        <span class="fas fa-fw fa-calendar-check"></span>
                                        SafeSport Expiry Date
                                    </label>
                                    <datepicker input-class="form-control form-control-sm vue-date-picker-fixer"
                                        :value="{{ old('usag_safesport_expiry') ? 'new Date(\'' . old('usag_safesport_expiry') . '\')' : 'null' }}"
                                        :wrapper-class="'flex-grow-1'" name="usag_safesport_expiry" :format="'MM/dd/yyyy'" placeholder="mm/dd/yyyy"
                                        :bootstrap-styling="true" :typeable="true" :disabled="usagDatesDisabled"
                                        placeholder="SafeSport Expiry Date">
                                    </datepicker>
                                    @error('usag_safesport_expiry')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="usag_background_expiry">
                                        <span class="fas fa-fw fa-calendar-check"></span>
                                        Background Expiry Date
                                    </label>
                                    <datepicker input-class="form-control form-control-sm vue-date-picker-fixer"
                                        :value="{{ old('usag_background_expiry') ? 'new Date(\'' . old('usag_background_expiry') . '\')' : 'null' }}"
                                        :wrapper-class="'flex-grow-1'" name="usag_background_expiry" :format="'MM/dd/yyyy'" placeholder="mm/dd/yyyy"
                                        :bootstrap-styling="true" :typeable="true" :disabled="usagDatesDisabled"
                                        placeholder="Background Expiry Date">
                                    </datepicker>
                                    @error('usag_background_expiry')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>

                                <div>
                                    <input type="checkbox" id="usag_u100_certification" name="usag_u100_certification"
                                        {{ old('usag_u100_certification') != null ? 'checked' : ''}}>
                                    <label for="usag_u100_certification">
                                            U100 Certification
                                    </label>
                                </div>

                                <div>
                                    <input type="checkbox" id="usag_active" name="usag_active"
                                        {{ old('usag_active') != null ? 'checked' : ''}}>
                                    <label for="usag_active">
                                            This membership is active
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg mb-1 pt-1 column-split">
                            <div class="form-check mb-2">
                                <input id="usaigc_checkbox" class="form-check-input coach-membership-checkbox"
                                    name="usaigc_checkbox" type="checkbox" data-body="usaigc"
                                    {{ old('usaigc_active') != null ? 'checked' : ''}}>
                                <label class="form-check-label" for="usaigc_checkbox">
                                    USAIGC Membership
                                </label>
                            </div>
                            <div id="usaigc-membership-fields">
                                <div class="mb-3">
                                    <input id="usaigc_no" type="text" placeholder="Enter your USAIGC membership No."
                                            class="form-control form-control-sm @error('usaigc_no') is-invalid @enderror"
                                            name="usaigc_no" data-body="usaigc" value="{{ old('usaigc_no') }}"
                                            required autocomplete="usaigc_no" disabled>
                                    @error('usaigc_no')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                                <div>
                                    <input type="checkbox" id="usaigc_background_check" name="usaigc_background_check"
                                        {{ old('usaigc_background_check') != null ? 'checked' : ''}}>
                                    <label for="usaigc_background_check">
                                        USAIGC background check
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg mb-1 pt-1 column-split">
                            <div class="form-check mb-2">
                                <input id="aau_checkbox" class="form-check-input coach-membership-checkbox"
                                    name="aau_checkbox" type="checkbox" data-body="aau"
                                    {{ old('aau_checkbox') != null ? 'checked' : ''}}>
                                <label class="form-check-label" for="aau_checkbox">
                                    AAU Membership
                                </label>
                            </div>

                            <div id="aau-membership-fields">
                                <div class="mb-3">
                                    <input id="aau_no" type="text" placeholder="Enter your AAU membership No."
                                            class="form-control form-control-sm @error('aau_no') is-invalid @enderror"
                                            name="aau_no" data-body="aau" value="{{ old('aau_no') }}"
                                            required autocomplete="aau_no" disabled>
                                    @error('aau_no')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="col-lg mb-1 pt-1 column-split">
                            <div class="form-check mb-2">
                                <input id="nga_checkbox" class="form-check-input coach-membership-checkbox"
                                       name="nga_checkbox" type="checkbox" data-body="nga"
                                    {{ old('nga_checkbox') != null ? 'checked' : ''}}>
                                <label class="form-check-label" for="nga_checkbox">
                                    NGA Membership
                                </label>
                            </div>

                            <div id="nga-membership-fields">
                                <div class="mb-3">
                                    <input id="nga_no" type="text" placeholder="Enter your NGA membership No."
                                           class="form-control form-control-sm @error('nga_no') is-invalid @enderror"
                                           name="nga_no" data-body="nga" value="{{ old('nga_no') }}"
                                           required autocomplete="nga_no" disabled>
                                    @error('nga_no')
                                    <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col text-right">
                            <button type="submit" class="btn btn-success">
                                <span class="fas fa-plus"></span> Create
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

    </div>

@endsection

@section('scripts-main')
    <script src="{{ mix('js/coach/coach-create.js') }}"></script>
@endsection
