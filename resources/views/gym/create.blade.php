@extends('layouts.main')

@section('content-header')
    <span class="fas fa-fw fa-dumbbell"></span> Create A Gym
    <span class="small text-dark">
        {{ $_managed->isNotCurrentUser() ? '(on behalf of ' . $_managed->first_name .')' : ''}}
    </span>
@endsection

@section('content-main')
    @include('include.errors')

    <div class="content-main p-3">
        <div class="row">
            <div class="col">
                <form method="POST" action="{{ route('gyms.store') }}">
                    @csrf

                    <div class="row">
                        <div class="col">
                            <h5 class="border-bottom"><span class="fas fa-fw fa-dumbbell"></span> Gym Info</h5>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-lg mb-3">
                            <label for="name" class="mb-1">
                                <span class="fas fa-fw fa-id-card"></span> Gym Name <span class="text-danger">*</span>
                            </label>
                            <div class="input-group input-group-sm">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><span class="fas fa-fw fa-id-card"></span></span>
                                </div>
                                <input id="name" type="text" class="form-control @error('name') is-invalid @enderror"
                                        name="name" value="{{ old('name') }}" placeholder="Gym name"
                                        required autocomplete="name" autofocus>
                            </div>
                            @error('name')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="col-lg mb-3">
                            <label for="short_name" class="mb-1">
                                <span class="far fa-fw fa-id-card"></span> Short Name <span class="text-danger">*</span>
                            </label>
                            <div class="input-group input-group-sm">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><span class="far fa-fw fa-id-card"></span></span>
                                </div>
                                <input id="short_name" type="text" class="form-control @error('short_name') is-invalid @enderror"
                                        name="short_name" value="{{ old('short_name') }}" placeholder="Short Name"
                                        required autocomplete="short_name">
                            </div>
                            @error('short_name')
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

                        <div class="col-lg mb-3">
                            <label for="country" class="mb-1">
                                <span class="fas fa-fw fa-globe-africa"></span> Country <span class="text-danger">*</span>
                            </label>
                            <div class="input-group input-group-sm">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><span class="fas fa-fw fa-globe-americas"></span></span>
                                </div>
                                <select id="country" class="form-control @error('country') is-invalid @enderror"
                                        name="country" required>
                                    @foreach ($countries as $country)
                                        <option value="{{ $country->code }}" {{ old('country') == $country->code ? 'selected' : '' }}>
                                            {{ $country->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            @error('country')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-lg mb-3">
                            <label for="office_phone" class="mb-1">
                                <span class="fas fa-fw fa-phone"></span> Office Phone <span class="text-danger">*</span>
                            </label>
                            <div class="input-group input-group-sm">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><span class="fas fa-fw fa-phone"></span></span>
                                </div>
                                <input id="office_phone" type="text" class="form-control @error('office_phone') is-invalid @enderror"
                                        name="office_phone" value="{{ old('office_phone') }}" placeholder="Office Phone"
                                        required autocomplete="office_phone">
                            </div>
                            @error('office_phone')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="col-lg mb-3">
                            <label for="mobile_phone" class="mb-1">
                                <span class="fas fa-fw fa-mobile"></span> Mobile Phone
                            </label>
                            <div class="input-group input-group-sm">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><span class="fas fa-fw fa-mobile"></span></span>
                                </div>
                                <input id="mobile_phone" type="text" class="form-control @error('mobile_phone') is-invalid @enderror"
                                        name="mobile_phone" value="{{ old('mobile_phone') }}" placeholder="Mobile Phone"
                                        autocomplete="mobile_phone">
                            </div>
                            @error('mobile_phone')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-lg mb-3">
                            <label for="fax" class="mb-1">
                                <span class="fas fa-fw fa-print"></span> Fax
                            </label>
                            <div class="input-group input-group-sm">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><span class="fas fa-fw fa-print"></span></span>
                                </div>
                                <input id="fax" type="text" class="form-control @error('fax') is-invalid @enderror"
                                        name="fax" value="{{ old('fax') }}" placeholder="Fax"
                                        autocomplete="fax">
                            </div>
                            @error('fax')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="col-lg mb-3">
                            <label for="website" class="mb-1">
                                <span class="fas fa-fw fa-link"></span> Website
                            </label>
                            <div class="input-group input-group-sm">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">
                                        <span class="fas fa-link"></span>
                                    </span>
                                </div>
                                <input id="website" type="text" class="form-control @error('website') is-invalid @enderror"
                                        name="website" value="{{ old('website') }}" placeholder="Website"
                                        autocomplete="website">
                            </div>
                            @error('website')
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
                        <div class="col-lg mb-3">
                            <div class="form-check">
                                <input id="usag_checkbox" class="form-check-input gym-membership-checkbox"
                                        name="usag_checkbox" type="checkbox" data-body="usag">
                                <label class="form-check-label" for="usag_checkbox">
                                    USAG Membership
                                </label>
                            </div>

                            <div>
                                <input id="usag_membership" type="text" placeholder="Enter your USAG Club No."
                                        class="form-control form-control-sm @error('usag_membership') is-invalid @enderror"
                                        name="usag_membership" data-body="usag" value="{{ old('usag_membership') }}"
                                        required autocomplete="usag_membership" disabled>
                                @error('usag_membership')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="col-lg mb-3">
                            <div class="form-check">
                                <input id="usaigc_checkbox" class="form-check-input gym-membership-checkbox"
                                        name="usaigc_checkbox" type="checkbox" data-body="usaigc">
                                <label class="form-check-label" for="usaigc_checkbox">
                                    USAIGC Membership
                                </label>
                            </div>

                            <div>
                                <div class="input-group input-group-sm">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">IGC</span>
                                    </div>
                                    <input id="usaigc_membership" type="text" placeholder="Enter your USAIGC Club No."
                                            class="form-control form-control-sm @error('usaigc_membership') is-invalid @enderror"
                                            name="usaigc_membership" data-body="usaigc" value="{{ old('usaigc_membership') }}"
                                            required autocomplete="usaigc_membership" disabled>
                                </div>
                                @error('usaigc_membership')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="col-lg mb-3">
                            <div class="form-check">
                                <input id="aau_checkbox" class="form-check-input gym-membership-checkbox"
                                        name="aau_checkbox" type="checkbox" data-body="aau">
                                <label class="form-check-label" for="aau_checkbox">
                                    AAU Membership
                                </label>
                            </div>

                            <div>
                                <input id="aau_membership" type="text" placeholder="Enter your AAU Club No."
                                        class="form-control form-control-sm @error('aau_membership') is-invalid @enderror"
                                        name="aau_membership" data-body="aau" value="{{ old('aau_membership') }}"
                                        required autocomplete="aau_membership" disabled>
                                @error('aau_membership')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="col-lg mb-3">
                            <div class="form-check">
                                <input id="nga_checkbox" class="form-check-input gym-membership-checkbox"
                                       name="nga_checkbox" type="checkbox" data-body="nga">
                                <label class="form-check-label" for="nga_checkbox">
                                    NGA Membership
                                </label>
                            </div>

                            <div>
                                <input id="nga_membership" type="text" placeholder="Enter your NGA Club No."
                                       class="form-control form-control-sm @error('nga_membership') is-invalid @enderror"
                                       name="nga_membership" data-body="nga" value="{{ old('nga_membership') }}"
                                       required autocomplete="nga_membership" disabled>
                                @error('nga_membership')
                                <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col">
                            <div class="alert alert-warning">
                                <span class="fas fa-exclamation-triangle"></span>
                                Please be advised once a gym is created it can only be archived, it cannot be deleted.
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
    <script src="{{ mix('js/gym/gym-create.js') }}"></script>
@endsection
