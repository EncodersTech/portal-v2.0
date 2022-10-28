<div class="row">
    <div class="col">
        <form method="POST" action="{{ route('gyms.meets.update.2', ['gym' => $gym, 'meet' => $meet]) }}">
            @csrf
            @method('PATCH')

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

            <div class="row">
                <div class="col">
                    <h5 class="border-bottom"><span class="fas fa-fw fa-credit-card">
                        </span> Payment
                    </h5>
                    <div class="text-info font-weight-bold">
                        <span class="fas fa-info-circle"></span> We offer payment by credit card by
                        default. Select below which other payment methods you would want to offer.
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col mb-2 mt-1">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="accept_cc" checked disabled>
                        <label class="form-check-label" for="accept_cc">
                            Accept <span class="fas fa-credit-card"></span> Credit Card payments.
                        </label>
                    </div>
                </div>
            </div>

            {{--            <div class="row">--}}
            {{--                <div class="col mb-2 mt-1">--}}
            {{--                    <div class="form-check">--}}
            {{--                        <input class="form-check-input" type="checkbox"--}}
            {{--                            name="accept_paypal" id="accept_paypal"--}}
            {{--                            {{ $meet->oldOrValue('accept_paypal') ? 'checked' : '' }}>--}}
            {{--                        <label class="form-check-label" for="accept_paypal">--}}
            {{--                            Accept <span class="fab fa-paypal"></span> PayPal payments.--}}
            {{--                        </label>--}}
            {{--                    </div>--}}
            {{--                </div>--}}
            {{--            </div>--}}

            <div class="row">
                <div class="col mb-2">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="accept_ach" id="accept_ach" checked
                            disabled>
                        <label class="form-check-label" for="accept_ach">
                            Accept <span class="fas fa-university"></span> ACH payments.
                        </label>
                    </div>
                </div>
            </div>
            <?php
                $disable_1 = Auth::user()->mail_check_disable? 0 : 1;
                $is_disable = 1;
                $is_disable2 = 1;
                $is_checked = 1;
                $is_checked_deposit = 1;
                $accept_mailed_check = $meet->oldOrValue('accept_mailed_check') == 'on' ? 1 : $meet->oldOrValue('accept_mailed_check');
                $accept_deposit = $meet->oldOrValue('accept_deposit') == 'on' ? 1 : $meet->oldOrValue('accept_deposit');
                if($disable_1 == 0 || $card_exist == 0)
                    $is_disable = 0;
                if($accept_mailed_check ==0 || $card_exist == 0)
                    $is_checked = 0;
                if($accept_deposit==0 || $card_exist == 0 || $is_checked == 0)
                    $is_checked_deposit = 0;
                if($disable_1 == 0 || $card_exist == 0 || $is_checked_deposit == 0)
                    $is_disable2 = 0;
            ?>
            <div class="row">
                <div class="col mb-1">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="accept_mailed_check"
                            id="accept_mailed_check" {{ $is_checked == 1 ? 'checked' : '' }}
                            {{ $is_disable == 0 ? 'disabled':''}}>
                        <label class="form-check-label" for="accept_mailed_check">
                            Accept <span class="fas fa-money-check"></span> Mailed Checks.
                        </label>
                        @if(Auth::user()->mail_check_disable)
                        <i class="fas fa-question-circle text-info font-weight-bold"></i>
                        <span class="text-info font-weight-bold"> Your ability to utilize mailed checks has been
                            disabled.
                            Contact AllGym support if you believe this was done in error.</span>
                        @endif
                    </div>
                    <div class="alert alert-warning small mb-0 ml-3">
                        <span class="fas fa-exclamation-triangle"></span>
                        This option will appear to registrants <strong>only if you have a valid credit card
                            linked to your account at the time of registration</strong>.<br>
                        This card will be charged for Credit Card and Handling fees. If you chose to defer these fee to
                        registering gym, They will be advised to include them in their entry fee check.
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-lg mb-3 ml-3">
                    <label for="mailed_check_instructions" class="mb-1">
                        <span class="fas fa-fw fa-info-circle"></span>
                        Instructions for Mailed Checks <span class="text-danger">*</span>
                    </label>
                    <textarea id="mailed_check_instructions" name="mailed_check_instructions"
                        autocomplete="mailed_check_instructions" placeholder="Mail check instructions ..."
                        class="form-control form-control-sm @error('mailed_check_instructions') is-invalid @enderror"
                        required>{{ $meet->oldOrValue('mailed_check_instructions') }}</textarea>
                    @error('mailed_check_instructions')
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                    @enderror
                </div>
            </div>
            
            <div class="row">
                <div class="col mb-1">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="accept_deposit"
                            id="accept_deposit" {{  $is_checked_deposit == 1 ? 'checked' : '' }}
                            {{$is_disable == 0 ? 'disabled':''}} >
                        <label class="form-check-label" for="accept_deposit">
                            Accept <span class="fas fa-money-check"></span> Deposit.
                        </label>
                    </div>
                    <div class="alert alert-warning small mb-0 ml-3">
                        <span class="fas fa-exclamation-triangle"></span>
                        This option will appear to registrants <strong>only if you have checked the "Mailed Check" option and unchecked the
                        "Defer Handling Fees" and "Defer Payment Processor Fees"
                        </strong>.<br>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-lg mb-3 ml-3">
                <label class="form-check-label" for="deposit_ratio">
                <span class="fas fa-fw fa-info-circle"></span> Deposit Ratio %</label>
                    <input id="deposit_ratio" name="deposit_ratio" autocomplete="deposit_ratio" {{$is_disable2 == 0 ? 'disabled':''}}
                        placeholder="Deposit Ratio"
                        class="form-control form-control-sm @error('deposit_ratio') is-invalid @enderror"
                        value="{{ $meet->oldOrValue('deposit_ratio') }}" type="number" min="0" max="100">
                </div>
            </div>            

            <div class="row">
                <div class="col mb-2">
                    @if ($restricted_edit)
                    <input type="checkbox" class="d-none" name="defer_handling_fees"
                        {{ $meet->defer_handling_fees ? 'checked' : '' }}>
                    @endif
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="defer_handling_fees"
                            id="defer_handling_fees" {{ $meet->oldOrValue('defer_handling_fees') ? 'checked' : '' }}
                            {{ $restricted_edit ? 'disabled' : '' }}>
                        <label class="form-check-label" for="defer_handling_fees">
                            Defer Handling Fees to Registering Gyms
                        </label>
                    </div>
                    <div class="text-info font-weight-bold ml-3">
                        <span class="fas fa-info-circle"></span>
                        Select this option if you would like the handling fees paid by the registering
                        gym. Leave unchecked if you would like said fees to be deducted from your
                        account instead.
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col mb-2">
                    @if ($restricted_edit)
                    <input type="checkbox" class="d-none" name="defer_processor_fees"
                        {{ $meet->defer_processor_fees ? 'checked' : '' }}>
                    @endif
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="defer_processor_fees"
                            id="defer_processor_fees" {{ $meet->oldOrValue('defer_processor_fees') ? 'checked' : '' }}
                            {{ $restricted_edit ? 'disabled' : '' }}>
                        <label class="form-check-label" for="defer_processor_fees">
                            Defer Payment Processor Fees to Registering Gyms
                        </label>
                    </div>
                    <div class="text-info font-weight-bold ml-3">
                        <span class="fas fa-info-circle"></span>
                        Select this option if you would like the payment processor fees paid by the registering gym.
                        Leave unchecked if you would like said fees to be deducted from your account instead.
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
        </form>
    </div>
</div>