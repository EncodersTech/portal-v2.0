<div class="row">
    <div class="col">
        <form method="POST" action="{{ route('gyms.meets.store.6', ['gym' => $gym, 'temporary' => $tm]) }}">
            @csrf
        <div id="payment_div">
            <div class="row">
                <div class="col">
                    <h5 class="border-bottom"><span class="fas fa-fw fa-credit-card">
                        </span> Payment
                    </h5>
                    <div class="text-info font-weight-bold">
                        <span class="fas fa-info-circle"></span> We offer payment by credit card and ACH by
                        default. Select below if you wish to enable mailed checks.
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
                
                $accept_mailed_check = $tm->oldOrValue('accept_mailed_check') == 'on' ? 1 : $tm->oldOrValue('accept_mailed_check');
                $accept_deposit = $tm->oldOrValue('accept_deposit') == 'on' ? 1 : $tm->oldOrValue('accept_deposit');
                if($disable_1 == 0 || $card_exist == 0)
                    $is_disable = 0;
                if($accept_mailed_check ==0 || $card_exist == 0)
                    $is_checked = 0;
                if($accept_deposit==0 || $card_exist == 0 || $is_checked == 0)
                    $is_checked_deposit = 0;
                if($disable_1 == 0 || $card_exist == 0 || $is_checked_deposit == 0)
                    $is_disable2 = 0;

            ?>
            <!-- <div class="row">
                <div class="col mb-1">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="accept_mailed_check"
                            id="accept_mailed_check" {{ $is_checked == 1 ? 'checked' : '' }}
                            {{ $is_disable == 0 ? 'disabled':'' }}>
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
                        required>{{ $tm->oldOrValue('mailed_check_instructions') }}</textarea>
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
                            {{$is_disable2 == 0 ? 'disabled':''}} >
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
                        value="{{ $tm->oldOrValue('deposit_ratio') }}" type="number" min="0" max="100">
                </div>
            </div> -->

            

            <div class="row">
                <div class="col mb-2">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="defer_handling_fees"
                            id="defer_handling_fees" {{ $tm->oldOrValue('defer_handling_fees') ? 'checked' : '' }}>
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
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="defer_processor_fees"
                            id="defer_processor_fees" {{ $tm->oldOrValue('defer_processor_fees') ? 'checked' : '' }}>
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
                    <a ref="" class="btn btn-primary"
                        href="{{ route('gyms.meets.create.step.view', ['gym' => $gym, 'step' => 2, 'temporary' => $tm]) }}">
                        <span class="fas fa-long-arrow-alt-left"></span> Back</a>
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