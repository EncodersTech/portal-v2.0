@extends('layouts.main')

@section('content-header')
    <span class="fas fa-fw fa-calendar-check"></span> Create A Meet
    <span class="small text-dark">
        {{ $_managed->isNotCurrentUser() ? '(on behalf of ' . $_managed->first_name .')' : ''}}
    </span>
@endsection

@section('content-main')
    @include('include.errors')

    <h5 class="secondary-title font-weight-bold ml-3">
        <span class="fas fa-fw fa-dumbbell"></span>
        {{ $gym->name }}
    </h5>

    <div class="modal fade" id="modal-meet-copy" tabindex="-1" role="dialog" aria-labelledby="modal-meet-copy" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title text-primary">
                        <span class="fas fa-copy"></span> Copy Meet
                    </h5>
                    <button type="button" data-dismiss="modal" class="close" aria-label="Close">
                        <span class="fas fa-times" aria-hidden="true"></span>
                    </button>
                </div>
                
                <div class="modal-body">
                    <form method="POST" action="{{ route('gyms.meets.create.copy', ['gym' => $gym])}}">
                        @csrf
                        <div class="row mb-3">
                            <div class="col">
                                <label class="mb-0" for="meet-copy-meet">Copy from :</label>
                                <select class="form-control form-control-sm" name="meet" id="meet-copy-meet">
                                    <option value="">(Choose below ...)</option>

                                    @if (count($activeMeets) > 0)
                                        <optgroup label="Active Meets" class="bg-secondary"></optgroup>
                                        @foreach ($activeMeets as $meet)
                                            <option value="{{ $meet->id }}">
                                                {{ $meet->name }}
                                            </option>
                                        @endforeach
                                    @endif

                                    @if (count($archivedMeets) > 0)
                                        <optgroup label="Archived Meets" class="bg-secondary"></optgroup>
                                        @foreach ($archivedMeets as $meet)
                                            <option value="{{ $meet->id }}">
                                                {{ $meet->name }}
                                            </option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col">
                                <label class="mb-0" for="meet-copy-meet">The following attributes :</label>
                            </div>
                        </div>

                        <div class="ml-3">
                            <div class="row mb-1">
                                <div class="col">
                                    <div class="form-check">
                                        <input class="form-check-input" name="general"
                                            id="general" type="checkbox">
                                        <label class="form-check-label" for="general">
                                            General Info
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <div class="row mb-1">
                                <div class="col">
                                    <div class="form-check">
                                        <input class="form-check-input" name="venue"
                                            id="venue" type="checkbox">
                                        <label class="form-check-label" for="venue">
                                            Venue Details
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <div class="row mb-1">
                                <div class="col">
                                    <div class="form-check">
                                        <input class="form-check-input" name="registration"
                                            id="registration" type="checkbox">
                                        <label class="form-check-label" for="registration">
                                            Regular &amp; Late Registration Settings
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <div class="row mb-1">
                                <div class="col">
                                    <div class="form-check">
                                        <input class="form-check-input" name="payment"
                                            id="payment" type="checkbox">
                                        <label class="form-check-label" for="payment">
                                            Payment Settings
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <div class="row mb-1">
                                <div class="col">
                                    <div class="form-check">
                                        <input class="form-check-input" name="categories"
                                            id="categories" type="checkbox">
                                        <label class="form-check-label" for="categories">
                                            Categories, Levels, &amp; Competition Settings
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <div class="row mb-1">
                                <div class="col">
                                    <div class="form-check">
                                        <input class="form-check-input" name="contact"
                                            id="contact" type="checkbox">
                                        <label class="form-check-label" for="contact">
                                            Contact Info
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="text-right">
                            <button type="submit" class="btn btn-primary">
                                <span class="fas fa-copy"></span> Copy
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="content-main p-3">
        <div class="row">    
            <div class="col">
                <div class="card-group">
                    <div class="card">
                        <div class="card-header bg-primary text-light">
                            <span class="fas fa-fw fa-file"></span> Start from scratch
                        </div>
                        <div class="card-body bg-white pb-0">
                            Create a new meet from scratch.
                        </div>
                        <div class="card-footer text-right border-top-0 bg-white pt-0">
                            <a href="{{ route('gyms.meets.create.scratch', ['gym' => $gym]) }}" class="btn btn-primary">
                                <span class="fas fa-fw fa-file"></span> Create
                            </a>
                        </div>
                    </div>

                    @if ($gym->hasActiveMeets())
                        <div class="card">
                            <div class="card-header bg-dark text-light">
                                <span class="fas fa-fw fa-copy"></span> Copy from an existing meet
                            </div>
                            <div class="card-body bg-white pb-0">
                                <div>
                                    Copy details from an existing meet
                                </div>
                            </div>
                            <div class="card-footer text-right border-top-0 bg-white pt-0">
                                @if ((count($activeMeets) + count($archivedMeets)) > 0)
                                    <a href="#modal-meet-copy" class="btn btn-dark"  data-toggle="modal"
                                        data-backdrop="static" data-keyboard="false">
                                        <span class="fas fa-fw fa-copy"></span> Copy
                                    </a>
                                @else
                                    <button type="button" class="btn btn-dark" disabled>
                                        <span class="fas fa-fw fa-copy"></span> No meets to copy from
                                    </button>
                                @endif
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        @if ($gym->temporary_meets()->where('step', '>', 1)->count() > 0)
            <div class="row mt-3">    
                <div class="col">
                    <div class="card-group">
                        <div class="card">
                            <div class="card-header bg-info text-light">
                                <span class="fas fa-fw fa-undo-alt"></span> Or ... continue where you left off
                            </div>
                            <div class="card-body bg-white pb-0">
                                <div>
                                    Choose one of the entries below to continue where you left off.<br/>
                                    Please note that these temporary entries are only stored for 30 days.
                                </div>
                                <div class="mt-1 mb-3">
                                    <select class="form-control form-control-sm" id="continue-select">
                                        <option value="">(Choose below ...)</option>
                                            @foreach ($gym->temporary_meets as $tm)
                                                @if ($tm->step > 1)
                                                    <option value="{{ route('gyms.meets.create.step.view', ['gym' => $gym, 'step' => 1, 'temporary' => $tm]) }}">
                                                        [{{ $tm->updated_at->format(Helper::AMERICAN_SHORT_DATE_TIME) }}]
                                                        {{ $tm->name }}
                                                    </option>
                                                    @endif
                                            @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="card-footer text-right border-top-0 bg-white pt-0">
                                <a href="#" class="btn btn-info" id="continue-button">
                                    Continue <span class="fas fa-fw fa-angle-double-right"></span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif
        
    </div>

@endsection

@section('scripts-main')
    <script src="{{ mix('js/meet/meet-create-copy.js') }}"></script>
@endsection