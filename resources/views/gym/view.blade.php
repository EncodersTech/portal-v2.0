@extends('layouts.main')

@section('content-header')
    <span class="fas fa-fw fa-dumbbell"></span> {{ $gym->name }}  Details
    <span class="small text-dark">
        {{ $_managed->isNotCurrentUser() ? '(owned by ' . $_managed->first_name .')' : ''}}
    </span>
@endsection

@section('content-main')
    @include('include.errors')

    <div class="content-main p-3">
        <div class="row">
            <div class="col-sm-2 mb-3">
                <a href="{{ $gym->profile_picture }}" target="_blank">
                    <img id="profile-picture-display" src="{{ $gym->profile_picture }}"
                        class="rounded profile-picture-256" alt="Gym Picture">
                </a>
            </div>

            <div class="col">
                    <div class="row">
                        <div class="col">
                            <h5 class="border-bottom"><span class="fas fa-fw fa-dumbbell"></span> Gym Info</h5>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-lg mb-2">
                            <strong>
                                <span class="fas fa-fw fa-id-card"></span> Gym Name :
                            </strong>
                            {{ $gym->name }}
                        </div>

                        <div class="col-lg mb-2">
                            <strong>
                                <span class="far fa-fw fa-id-card"></span> Short Name :
                            </strong>
                            {{ $gym->short_name }}
                        </div>
                    </div>

                    <div class="row">
                        <div class="col mb-2">
                        <strong>
                            <span class="fas fa-fw fa-map-marker-alt"></span> Address :
                        </strong>
                        {{ $gym->compiledAddress(false) }}
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-lg mb-2">
                            <strong>
                                <span class="fas fa-fw fa-map-marked-alt"></span> City :
                            </strong>
                            {{ $gym->city }}
                        </div>

                        @if ($gym->state->code != 'WW')
                            <div class="col-lg mb-2">
                                <strong>
                                    <span class="fas fa-fw fa-map"></span> State :
                                </strong>
                                {{ $gym->state->name }}
                            </div>
                        @endif
                    </div>
                    <div class="row">
                        <div class="col-lg mb-2">
                            <strong for="zipcode" class="mb-1">
                                <span class="fas fa-fw fa-location-arrow"></span> Zip code :
                            </strong>
                            {{ $gym->zipcode }}
                        </div>

                        <div class="col-lg mb-2">
                            <strong>
                                <span class="fas fa-fw fa-globe-africa"></span> Country :
                            </strong>
                            {{ $gym->country->name }}
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-lg mb-2">
                            <strong>
                                <span class="fas fa-fw fa-phone"></span> Office Phone :
                            </strong>
                            {{ $gym->office_phone }}
                            <!-- {{ "+".phone($gym->office_phone, $gym->country->code)->getPhoneNumberInstance()->getCountryCode()." ".phone($gym->office_phone, $gym->country->code)->formatNational() }} -->
                        </div>

                        @if ($gym->mobile_phone != null)
                            <div class="col-lg mb-2">
                                <strong>
                                    <span class="fas fa-fw fa-mobile"></span> Mobile Phone :
                                </strong>
                                {{ $gym->mobile_phone }}
                            </div>
                        @endif
                    </div>

                    <div class="row">
                        @if ($gym->fax != null)
                            <div class="col-lg mb-2">
                                <strong>
                                    <span class="fas fa-fw fa-print"></span> Fax :
                                </strong>
                                {{ "+".phone($gym->fax, $gym->country->code)->getPhoneNumberInstance()->getCountryCode()." ".phone($gym->fax, $gym->country->code)->formatNational() }}
                            </div>
                        @endif

                        @if ($gym->website != null)
                            <div class="col-lg mb-2">
                                <strong>
                                    <span class="fas fa-fw fa-link"></span> Website :
                                </strong>
                                <a href="{{ $gym->website }}" target="_blank">{{ $gym->website }}</a>
                            </div>
                        @endif
                    </div>

                    @if (($gym->usag_membership != null) || ($gym->usaigc_membership != null) || ($gym->aau_membership != null) || ($gym->nga_membership != null))
                        <div class="row mt-2">
                            <div class="col">
                                <h5 class="border-bottom"><span class="fas fa-fw fa-receipt"></span> Memberships</h5>
                            </div>
                        </div>

                        <div class="row mb-3">
                            @if ($gym->usag_membership != null)
                                <div class="col-lg mb-2">
                                    <strong>
                                        USAG Membership :
                                    </strong>
                                    {{ $gym->usag_membership }}
                                </div>
                            @endif

                            @if ($gym->usaigc_membership != null)
                                <div class="col-lg mb-2">
                                    <strong>
                                        USAIGC Membership :
                                    </strong>
                                    IGC{{ $gym->usaigc_membership }}
                                </div>
                            @endif

                            @if ($gym->aau_membership != null)
                                <div class="col-lg mb-2">
                                    <strong>
                                        AAU Membership :
                                    </strong>
                                    {{ $gym->aau_membership }}
                                </div>
                            @endif

                                @if ($gym->nga_membership != null)
                                    <div class="col-lg mb-2">
                                        <strong>
                                            NGA Membership :
                                        </strong>
                                        {{ $gym->nga_membership }}
                                    </div>
                                @endif
                        </div>
                    @endif

                    <div class="row">
                        <div class="col text-right">
                            @if (!$gym->is_archived)
                                <div class="mb-2 mr-2 d-inline-block">
                                    <a href="{{ route('gyms.edit', ['gym' => $gym ]) }}"
                                        class="btn btn-success" title="Edit">
                                            <span class="fas fa-edit"></span> Edit
                                    </a>
                                </div>
                            @endif

                            <div class="mb-2 mr-2 d-inline-block">
                                <a href="{{ URL::previous() }}" class="btn btn-primary">
                                    <span class="fas fa-long-arrow-alt-left"></span> Back
                                </a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

    </div>

@endsection
