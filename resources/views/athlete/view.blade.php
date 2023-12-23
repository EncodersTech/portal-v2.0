@extends('layouts.main')

@section('content-header')
    <span class="fas fa-fw fa-running"></span>
    Athlete Details
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

    <div class="content-main p-3">
        <div class="row">
            <div class="col">
                <div class="row">
                    <div class="col">
                        <h5 class="border-bottom"><span class="fas fa-fw fa-user-plus"></span> Athlete Info</h5>
                    </div>
                </div>

                <div class="row">
                    <div class="col-lg mb-2">
                        <strong>
                            <span class="fas fa-fw fa-user"></span> First Name :
                        </strong>
                        {{ $athlete->first_name }}
                    </div>

                    <div class="col-lg mb-2">
                        <strong>
                            <span class="far fa-fw fa-user"></span> Last Name :
                        </strong>
                        {{ $athlete->last_name }}
                    </div>
                </div>

                <div class="row">
                    <div class="col-lg mb-2">
                        <strong>
                            <span class="fas fa-fw fa-venus-mars"></span> Gender :
                        </strong>
                        {{ ucfirst($athlete->gender) }}
                    </div>

                    <div class="col-lg mb-2">
                        <strong>
                            <span class="fas fa-fw fa-birthday-cake"></span> Date Of Birth :
                        </strong>
                        {{ $athlete->dob->format(Helper::AMERICAN_SHORT_DATE) }}
                    </div>
                </div>

                <div class="row">
                    @if ($athlete->tshirt != null)
                        <div class="col-lg mb-2">
                            <strong>
                                <span class="fas fa-fw fa-tshirt"></span> T-Shirt Size :
                            </strong>
                            {{ $athlete->tshirt->size }}
                        </div>
                    @endif

                    @if ($athlete->leo != null)
                        <div class="col-lg mb-2">
                            <strong>
                                <span class="fas fa-fw fa-female"></span> Leotard Size :
                            </strong>
                            {{ $athlete->leo->size }}
                        </div>
                    @endif
                </div>

                <div class="row mb-2">
                    <div class="col">
                        <strong>
                            <span class="fas fa-fw fa-check-circle"></span> US Citizen :
                        </strong>
                        {{ $athlete->is_us_citizen ? 'Yes' : 'No'}}
                    </div>
                </div>

                <div class="row mt-3">
                    <div class="col">
                        <h5 class="border-bottom"><span class="fas fa-fw fa-receipt"></span> Memberships</h5>
                    </div>
                </div>

                <div class="row">
                    @if ($athlete->usag_no != null)
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
                                    {{ $athlete->usag_no }}
                                </div>

                                <div class="mb-1">
                                    <strong>
                                        <span class="fas fa-fw fa-layer-group"></span> Level
                                    </strong>
                                    {{ $athlete->usag_level->name }}, {{ $athlete->usag_level->level_category->name }}
                                </div>

                                <div>
                                    <strong>
                                        <span class="fas fa-fw fa-check-circle"></span> Active :
                                    </strong>
                                    {{ $athlete->usag_active ? 'Yes' : 'No'}}
                                </div>
                            </div>
                        </div>
                    @endif

                    @if ($athlete->usaigc_no != null)
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
                                    {{ $athlete->usaigc_no }}
                                </div>

                                <div class="mb-1">
                                    <strong>
                                        <span class="fas fa-fw fa-layer-group"></span> Level
                                    </strong>
                                    {{ $athlete->usaigc_level->name }},
                                    {{ $athlete->usaigc_level->level_category->name }}
                                </div>

                                <div>
                                    <strong>
                                        <span class="fas fa-fw fa-check-circle"></span> Active :
                                    </strong>
                                    {{ $athlete->usaigc_active ? 'Yes' : 'No'}}
                                </div>
                            </div>
                        </div>
                    @endif

                    @if ($athlete->aau_no != null)
                        <div class="col-lg mt-1 mb-2 pt-1">
                            <div class="mb-2">
                                <strong class="text-primary">
                                    <span class="fas fa-fw fa-receipt"></span> AAU Membership
                                </strong>
                            </div>

                            <div class="ml-3">
                                <div class="mb-1">
                                    <strong>
                                        <span class="fas fa-fw fa-hashtag"></span> AAU No.:
                                    </strong>
                                    {{ $athlete->aau_no }}
                                </div>

                                <div class="mb-1">
                                    <strong>
                                        <span class="fas fa-fw fa-layer-group"></span> Level
                                    </strong>
                                    {{ $athlete->aau_level->name }},
                                    {{ $athlete->aau_level->level_category->name }}
                                </div>

                                <div>
                                    <strong>
                                        <span class="fas fa-fw fa-check-circle"></span> Active :
                                    </strong>
                                    {{ $athlete->aau_active ? 'Yes' : 'No'}}
                                </div>
                            </div>
                        </div>
                    @endif

                    @if ($athlete->nga_no != null)
                        <div class="col-lg mt-1 mb-2 pt-1">
                            <div class="mb-2">
                                <strong class="text-primary">
                                    <span class="fas fa-fw fa-receipt"></span> NGA Membership
                                </strong>
                            </div>

                            <div class="ml-3">
                                <div class="mb-1">
                                    <strong>
                                        <span class="fas fa-fw fa-hashtag"></span> NGA No.:
                                    </strong>
                                    {{ $athlete->nga_no }}
                                </div>

                                <div class="mb-1">
                                    <strong>
                                        <span class="fas fa-fw fa-layer-group"></span> Level
                                    </strong>
                                    {{ $athlete->nga_level->name }},
                                    {{ $athlete->nga_level->level_category->name }}
                                </div>

                                <div>
                                    <strong>
                                        <span class="fas fa-fw fa-check-circle"></span> Active :
                                    </strong>
                                    {{ $athlete->nga_active ? 'Yes' : 'No'}}
                                </div>
                            </div>
                        </div>
                    @endif
                </div>

                <div class="row">
                    <div class="col text-right">
                        <div class="mb-2 mr-2 d-inline-block">
                            <a href="{{ route('gyms.athletes.edit', ['gym' => $gym, 'athlete' => $athlete]) }}"
                                class="btn btn-success" title="Edit">
                                    <span class="fas fa-edit"></span> Edit
                            </a>
                        </div>

                        <div class="mb-2 mr-2 d-inline-block">
                            <a href="{{ route('gyms.athletes.index', ['gym' => $gym]) }}"
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
