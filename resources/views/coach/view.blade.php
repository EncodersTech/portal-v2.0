@extends('layouts.main')

@section('content-header')
    <span class="fas fa-fw fa-running"></span>
    Coach Details
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
    @if ($coach->usaigc_no != null)
    <div class="alert alert-primary"><strong class="d-block mb-2"><span class="fas fa-exclamation-circle"></span>
    The USAIGC Membership number listed as issued by AllGym for purposes only. 
    </strong></div>
    @endif

        <div class="row">
            <div class="col">
                <div class="row">
                    <div class="col">
                        <h5 class="border-bottom"><span class="fas fa-fw fa-user-plus"></span> Coach Info</h5>
                    </div>
                </div>

                <div class="row">
                    <div class="col-lg mb-2">
                        <strong>
                            <span class="fas fa-fw fa-user"></span> First Name :
                        </strong>
                        {{ $coach->first_name }}
                    </div>

                    <div class="col-lg mb-2">
                        <strong>
                            <span class="far fa-fw fa-user"></span> Last Name :
                        </strong>
                        {{ $coach->last_name }}
                    </div>
                </div>

                <div class="row">
                    <div class="col-lg mb-2">
                        <strong>
                            <span class="fas fa-fw fa-venus-mars"></span> Gender :
                        </strong>
                        {{ ucfirst($coach->gender) }}
                    </div>

                    <div class="col-lg mb-2">
                        <strong>
                            <span class="fas fa-fw fa-birthday-cake"></span> Date Of Birth :
                        </strong>
                        {{ $coach->dob->format(Helper::AMERICAN_SHORT_DATE) }}
                    </div>
                </div>

                @if ($coach->tshirt != null)
                    <div class="row">
                        <div class="col-lg mb-2">
                            <strong>
                                <span class="fas fa-fw fa-tshirt"></span> T-Shirt Size :
                            </strong>
                            {{ $coach->tshirt->size }}
                        </div>
                    </div>
                @endif

                <div class="row mt-3">
                    <div class="col">
                        <h5 class="border-bottom"><span class="fas fa-fw fa-receipt"></span> Memberships</h5>
                    </div>
                </div>

                <div class="row">
                    @if ($coach->usag_no != null)
                        <div class="col-lg mt-1 mb-2 pt-1">
                            <div class="mb-2">
                                <strong class="text-primary">
                                    <span class="fas fa-fw fa-receipt"></span> USAG Membership
                                </strong>
                            </div>

                            <div class="ml-3">
                                <div class="mb-1">
                                    <strong>
                                        <span class="fas fa-fw fa-hashtag"></span> USAG No.:
                                    </strong>
                                    {{ $coach->usag_no }}
                                </div>

                                <div>
                                    <strong>
                                        <span class="far fa-fw fa-clock"></span>
                                        Professional No. Expiry :
                                    </strong>
                                    {{ $coach->usag_expiry != null ?
                                        $coach->usag_expiry->format(Helper::AMERICAN_SHORT_DATE) : '—'}}
                                </div>

                                <div>
                                    <strong>
                                        <span class="far fa-fw fa-clock"></span>
                                        Safety Certification Expiry :
                                    </strong>
                                    {{ $coach->usag_safety_expiry != null ?
                                        $coach->usag_safety_expiry->format(Helper::AMERICAN_SHORT_DATE) : '—'}}
                                </div>

                                <div>
                                    <strong>
                                        <span class="far fa-fw fa-clock"></span>
                                        SafeSport Expiry :
                                    </strong>
                                    {{ $coach->usag_safesport_expiry != null ?
                                        $coach->usag_safesport_expiry->format(Helper::AMERICAN_SHORT_DATE) : '—'}}
                                </div>

                                <div>
                                    <strong>
                                        <span class="far fa-fw fa-clock"></span>
                                        Background Expiry :
                                    </strong>
                                    {{ $coach->usag_background_expiry != null ?
                                        $coach->usag_background_expiry->format(Helper::AMERICAN_SHORT_DATE) : '—'}}
                                </div>

                                <div>
                                    <strong>
                                        <span class="fas fa-fw fa-check-circle"></span>
                                        U100 Certification :
                                    </strong>
                                    {{ $coach->usag_u100_certification ? 'Yes' : 'No'}}
                                </div>

                                <div>
                                    <strong>
                                        <span class="fas fa-fw fa-check-circle"></span> Active :
                                    </strong>
                                    {{ $coach->usag_active ? 'Yes' : 'No'}}
                                </div>
                            </div>
                        </div>
                    @endif

                    @if ($coach->usaigc_no != null)
                        <div class="col-lg mt-1 mb-2 pt-1">
                            <div class="mb-2">
                                <strong class="text-primary">
                                    <span class="fas fa-fw fa-receipt"></span> USAIGC Membership
                                </strong>
                            </div>

                            <div class="ml-3">
                                <div class="mb-1">
                                    <strong>
                                        <span class="fas fa-fw fa-hashtag"></span> USAIGC No.:
                                    </strong>
                                    {{ $coach->usaigc_no }}
                                </div>

                                <div>
                                    <strong>
                                        <span class="fas fa-fw fa-check-circle"></span> Background Check :
                                    </strong>
                                    {{ $coach->usaigc_background_check ? 'Yes' : 'No'}}
                                </div>
                            </div>
                        </div>
                    @endif

                    @if ($coach->aau_no != null)
                        <div class="col-lg mt-1 mb-2 pt-1">
                            <div class="mb-2">
                                <strong class="text-primary">
                                    <span class="fas fa-fw fa-receipt"></span> AAU Membership
                                </strong>
                            </div>

                            <div class="ml-3">
                                <strong>
                                    <span class="fas fa-fw fa-hashtag"></span> AAU No.:
                                </strong>
                                {{ $coach->aau_no }}
                            </div>
                        </div>
                    @endif

                    @if ($coach->nga_no != null)
                        <div class="col-lg mt-1 mb-2 pt-1">
                            <div class="mb-2">
                                <strong class="text-primary">
                                    <span class="fas fa-fw fa-receipt"></span> NGA Membership
                                </strong>
                            </div>

                            <div class="ml-3">
                                <strong>
                                    <span class="fas fa-fw fa-hashtag"></span> NGA No.:
                                </strong>
                                {{ $coach->nga_no }}
                            </div>
                        </div>
                    @endif
                </div>

                <div class="row mt-1">
                    <div class="col text-right">
                        <div class="mb-2 mr-2 d-inline-block">
                            <a href="{{ route('gyms.coaches.edit', ['gym' => $gym, 'coach' => $coach]) }}"
                                class="btn btn-success" title="Edit">
                                    <span class="fas fa-edit"></span> Edit
                            </a>
                        </div>

                        <div class="mb-2 mr-2 d-inline-block">
                            <a href="{{ route('gyms.coaches.index', ['gym' => $gym]) }}"
                                class="btn btn-primary">
                                <span class="fas fa-long-arrow-alt-left"></span> Back
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

@endsection

@section('scripts-main')
    <script src="{{ mix('js/app.js') }}"></script>
@endsection
