<div class="row">
    <div class="col">
        <form method="POST" action="{{ route('gyms.meets.update.2', ['gym' => $gym, 'meet' => $meet]) }}">
            @csrf
            @method('PATCH')
        <div id="registratoin_div">
            <div class="row">
                <div class="col">
                    <h5 class="border-bottom"><span class="fas fa-fw fa-clipboard-list">
                        </span> Registration
                    </h5>
                </div>
            </div>

            <div class="row">
                <div class="col-lg mb-3">
                    @if ($restricted_edit)
                    <input type="hidden" name="registration_start_date"
                        value="{{ $meet->registration_start_date->format(Helper::AMERICAN_SHORT_DATE) }}">
                    @endif
                    <label for="registration_start_date" class="mb-1" ref="oldRegistrationStartDate"
                        data-value="{{ $meet->oldOrValue('registration_start_date') }}">
                        <span class="fas fa-fw fa-calendar-alt"></span>
                        Registration Start Date <span class="text-danger">*</span>
                    </label>
                    <datepicker input-class="form-control form-control-sm vue-date-picker-fixer"
                        @selected="registrationStartDateChanged" @input="registrationStartDateChanged"
                        :wrapper-class="'flex-grow-1'" name="registration_start_date" :format="'MM/dd/yyyy'"
                        placeholder="mm/dd/yyyy" :value="registrationStartDate" :bootstrap-styling="true"
                        :typeable="true" :required="true" :disabled="{{ $restricted_edit ? 'true' : 'false' }}">
                    </datepicker>
                    @error('registration_start_date')
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                    @enderror
                </div>

                <div class="col-lg mb-3">
                    <label for="registration_end_date" class="mb-1" ref="oldRegistrationEndDate"
                        data-value="{{ $meet->oldOrValue('registration_end_date') }}">
                        <span class="fas fa-fw fa-calendar-alt"></span>
                        Registration End Date <span class="text-danger">*</span>
                    </label>
                    <datepicker :input-class="'form-control form-control-sm bg-white'" :value="registrationEndDate"
                        :wrapper-class="'flex-grow-1'" name="registration_end_date" :format="'MM/dd/yyyy'"
                        placeholder="mm/dd/yyyy" :bootstrap-styling="true" :typeable="true" :required="true">
                    </datepicker>
                    @error('registration_end_date')
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                    @enderror
                </div>
            </div>
            <!-- First Payment Discount -->
            <div class="row">
                <div class="col">
                    <h5 class="border-bottom">
                        <div class="form-check mb-1">
                            <input class="form-check-input" type="checkbox" name="registration_first_discount_is_enable"
                                id="registration_first_discount_is_enable"
                                {{ $meet->oldOrValue('registration_first_discount_is_enable') != null ? 'checked' : '' }} >
                            <label class="form-check-label" for="registration_first_discount_is_enable">
                                <span class="fas fa-fw fa-clipboard-list"></span> Enable Early Registration
                            </label>
                        </div>
                    </h5>
                </div>
            </div>
            <div class="row">
                <div class="col-lg mb-3">
                    <label for="registration_first_discount_end_date" class="mb-1" ref="registration_first_discount_end_date"
                        data-value="{{ $meet->oldOrValue('registration_first_discount_end_date') }}">
                        <span class="fas fa-fw fa-calendar-alt"></span>
                        Early Registration End Date <span class="text-danger"></span>
                    </label>
                    <datepicker input-class="form-control form-control-sm vue-date-picker-fixer"
                        id="registration_first_discount_end_date"
                        :disabled="registration_first_discount_end_date_disabled"
                        :value="registration_first_discount_end_date"
                        :wrapper-class="'flex-grow-1'" name="registration_first_discount_end_date" :format="'MM/dd/yyyy'"
                        placeholder="mm/dd/yyyy" :bootstrap-styling="true"
                        :typeable="true">
                    </datepicker>
                    @error('registration_first_discount_end_date')
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                    @enderror
                </div>
                <input type="hidden" name="registration_first_discount_amount" value="0">
            </div>


            <div class="row">
                <div class="col-lg mb-3">
                    <label for="registration_scratch_end_date" class="mb-1" ref="oldRegistrationScratchDate"
                        data-value="{{ $meet->oldOrValue('registration_scratch_end_date') }}">
                        <span class="fas fa-fw fa-calendar-alt"></span>
                        Scratch Date <span class="text-danger">*</span>
                    </label>
                    <datepicker :input-class="'form-control form-control-sm bg-white'" :value="registrationScratchDate"
                        :wrapper-class="'flex-grow-1'" name="registration_scratch_end_date" :format="'MM/dd/yyyy'"
                        placeholder="mm/dd/yyyy" :bootstrap-styling="true" :typeable="true" :required="true">
                    </datepicker>
                    @error('registration_scratch_end_date')
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                    @enderror
                </div>
            </div>

            <div class="row">
                <div class="col">
                    <h5 class="border-bottom">
                        <div class="form-check mb-1">
                            <input class="form-check-input" type="checkbox" name="allow_late_registration"
                                id="allow_late_registration"
                                {{ $meet->oldOrValue('allow_late_registration') != null ? 'checked' : '' }}>
                            <label class="form-check-label" for="allow_late_registration">
                                <span class="fas fa-fw fa-clipboard-list"></span> Enable Late Registration
                            </label>
                        </div>
                    </h5>
                </div>
            </div>

            <div class="row">
                <div class="col-lg mb-3">
                    <label for="late_registration_fee">
                        <span class="fas fa-fw fa-dollar-sign"></span> Late Registration Fee <span
                            class="text-danger">*</span>
                    </label>
                    <div class="input-group input-group-sm">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><span class="fas fa-fw fa-dollar-sign"></span></span>
                        </div>
                        <input id="late_registration_fee" type="text"
                            class="form-control @error('late_registration_fee') is-invalid @enderror"
                            name="late_registration_fee" placeholder="Late Registration Fee" autocomplete="off" required
                            value="{{
                                    $meet->oldOrValue('late_registration_fee') != null ?
                                    number_format($meet->oldOrValue('late_registration_fee'), 2) :
                                    null
                                }}">
                    </div>

                    @error('late_registration_fee')
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                    @enderror

                    <div class="text-info font-weight-bold">
                        <span class="fas fa-info-circle"></span> This fee applies to the club entry as a
                        whole. Additional late fees per level can be set later in the Competition
                        Settings step.
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-lg mb-3">
                    <label for="late_registration_start_date" class="mb-1" ref="oldLateStartDate"
                        data-value="{{ $meet->oldOrValue('late_registration_start_date') }}">
                        <span class="fas fa-fw fa-calendar-alt"></span>
                        Late Registration Start Date <span class="text-danger">*</span>
                    </label>
                    <datepicker :input-class="'form-control form-control-sm bg-white'" :value="lateStartDate"
                        @selected="lateStartDateChanged" @input="lateStartDateChanged" :wrapper-class="'flex-grow-1'"
                        name="late_registration_start_date" :format="'MM/dd/yyyy'" placeholder="mm/dd/yyyy"
                        :bootstrap-styling="true" :typeable="true" :disabled="lateDisabled"
                        input-class="form-control form-control-sm vue-date-picker-fixer" :required="true">
                    </datepicker>
                    @error('late_registration_start_date')
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                    @enderror
                </div>

                <div class="col-lg mb-3">
                    <label for="late_registration_end_date" class="mb-1" ref="oldLateEndDate"
                        data-value="{{ $meet->oldOrValue('late_registration_end_date') }}">
                        <span class="fas fa-fw fa-calendar-alt"></span>
                        Late Registration End Date <span class="text-danger">*</span>
                    </label>
                    <datepicker :input-class="'form-control form-control-sm bg-white'" :value="lateEndDate"
                        :wrapper-class="'flex-grow-1'" name="late_registration_end_date" :format="'MM/dd/yyyy'"
                        placeholder="mm/dd/yyyy" :bootstrap-styling="true" :typeable="true" :disabled="lateDisabled"
                        input-class="form-control form-control-sm vue-date-picker-fixer" :required="true">
                    </datepicker>
                    @error('late_registration_end_date')
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                    @enderror
                </div>
            </div>

            <div class="row">
                <div class="col">
                    @if ($restricted_edit)
                    <input type="checkbox" class="d-none" name="athlete_limit_checkbox"
                        {{ ($meet->athlete_limit != null) ? 'checked' : '' }}>
                    <input type="hidden" name="athlete_limit" value="{{ $meet->athlete_limit }}">
                    @endif
                    <h5 class="border-bottom">
                        <div class="form-check mb-1">
                            <input class="form-check-input" type="checkbox" name="athlete_limit_checkbox"
                                id="athlete_limit_checkbox" {{
                                    (old('athlete_limit_checkbox') != null) ||
                                    ($meet->athlete_limit != null) ?
                                    'checked' : ''
                                }} {{ $restricted_edit ? 'disabled' : '' }}>
                            <label class="form-check-label" for="athlete_limit_checkbox">
                                <span class="fas fa-fw fa-chart-pie"></span> Athlete Limit
                            </label>
                        </div>
                    </h5>
                </div>
            </div>

            <div class="row">
                <div class="col-lg mb-3">
                    <input id="athlete_limit" name="athlete_limit" autocomplete="athlete_limit"
                        placeholder="Athlete Limit"
                        class="form-control form-control-sm @error('athlete_limit') is-invalid @enderror"
                        value="{{ $meet->oldOrValue('athlete_limit') }}" type="number" min="0" max="2147483647" required
                        {{ $restricted_edit ? 'disabled' : '' }}>
                    @error('athlete_limit')
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                    @enderror
                    <div class="text-info font-weight-bold">
                        <span class="fas fa-info-circle"></span> This limit applies globally to the meet. Additional
                        limits can be set per level later in the Competition Settings tab.
                    </div>
                </div>
            </div>
            <div class="d-flex flex-row flex-nowrap mt-3">
                <div class="flex-grow-1">
                    <a href="{{ route('gyms.meets.index', ['gym' => $gym]) }}" class="btn btn-primary">
                        <span class="fas fa-long-arrow-alt-left"></span> Back
                    </a>
                </div>

                <div class="ml-3">
                    <button class="btn btn-success" type="submit">
                        <span class="fas fa-save"></span> Save
                    </button>
                </div>
            </div>
        </div>
        </form>
    </div>
</div>