<div class="col-12">
    <div class="card p-3">
        <div class="card-body p-0">
            <span class="text-danger">All fields are required.</span><br><br>
            <div class="row settings">
                <div class="form-group col-sm-12 col-md-4 col-lg-3">
                    {{ Form::label('app_name', 'Handling Fee:') }}
                    <div class="input-group mb-3">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fas fa-percentage"></i></span>
                        </div>
                        {{ Form::text('fee_handling', $settings['fee_handling'], ['class' => 'form-control','required','id'=>'feeHandling','autocomplete'=>'off']) }}
                    </div>
                </div>

                <div class="form-group col-sm-12 col-md-4 col-lg-3">
                    {{ Form::label('app_name', 'Ach Fee:') }}
                    <div class="input-group mb-3">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fas fa-dollar-sign"></i></span>
                        </div>
                        {{ Form::text('fee_ach', $settings['fee_ach'], ['class' => 'form-control','required','id'=>'feeAch','autocomplete'=>'off']) }}
                    </div>
                </div>

                <div class="form-group col-sm-12 col-md-4 col-lg-3">
                    {{ Form::label('app_name', 'Cheque Fee:') }}
                    <div class="input-group mb-3">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fas fa-dollar-sign"></i></span>
                        </div>
                        {{ Form::text('fee_check', $settings['fee_check'], ['class' => 'form-control','required','id'=>'feeCheck','autocomplete'=>'off']) }}
                    </div>
                </div>

                <div class="form-group col-sm-12 col-md-4 col-lg-3">
                    {{ Form::label('app_name', 'Dwolla Verification Fee:') }}
                    <div class="input-group mb-3">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fas fa-dollar-sign"></i></span>
                        </div>
                        {{ Form::text('dwolla_verification_fee', $settings['dwolla_verification_fee'], ['class' => 'form-control','required','id'=>'dwollaVeriFee','autocomplete'=>'off']) }}
                    </div>
                </div>

                <div class="form-group col-sm-12 col-md-4 col-lg-3">
                    {{ Form::label('app_name', 'CC Fee:') }}
                    <div class="input-group mb-3">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fas fa-percent"></i></span>
                        </div>
                        {{ Form::text('fee_cc', $settings['fee_cc'], ['class' => 'form-control','required', 'id'=>'feeCc','autocomplete'=>'off']) }}
                    </div>
                </div>

                <div class="form-group col-sm-12 col-md-4 col-lg-3">
                    {{ Form::label('app_name', 'Balance Fee:') }}
                    <div class="input-group mb-3">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fas fa-dollar-sign"></i></span>
                        </div>
                        {{ Form::text('fee_balance', $settings['fee_balance'], ['class' => 'form-control','required', 'id'=>'feeBalance','autocomplete'=>'off']) }}
                    </div>
                </div>

                <div class="form-group col-sm-12 col-md-4 col-lg-3">
                    {{ Form::label('app_name', 'Paypal Fee:') }}
                    <div class="input-group mb-3">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fas fa-dollar-sign"></i></span>
                        </div>
                        {{ Form::text('fee_paypal', $settings['fee_paypal'], ['class' => 'form-control','required', 'id'=>'feePaypal','autocomplete'=>'off']) }}
                    </div>
                </div>

                <div class="form-group col-sm-12 col-md-4 col-lg-3">
                    {{ Form::label('app_name', 'All Time Withdrawn Credit Fee:') }}
                    <div class="input-group mb-3">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fas fa-dollar-sign"></i></span>
                        </div>
                        {{ Form::text('all_time_withdrawn_credit_fee', $settings['all_time_withdrawn_credit_fee'], ['class' => 'form-control','required','id'=>'allTimeWithFee','autocomplete'=>'off']) }}
                    </div>
                </div>

                <div class="form-group col-sm-12 col-md-4 col-lg-3">
                    {{ Form::label('app_name', 'Dwolla Unverified Tran. Cap:') }}
                    <div class="input-group mb-3">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fas fa-exchange-alt"></i></span>
                        </div>
                        {{ Form::text('dwolla_unverified_transfer_cap', $settings['dwolla_unverified_transfer_cap'], ['class' => 'form-control','required', 'id'=>'dwollaTranCap','autocomplete'=>'off']) }}
                    </div>
                </div>


                <div class="form-group col-sm-12 col-md-4 col-lg-3">
                    {{ Form::label('app_name', 'Dwolla Free Veri. Att:') }}
                    <div class="input-group mb-3">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fas fa-lira-sign"></i></span>
                        </div>
                        {{ Form::number('dwolla_free_verification_attempts', $settings['dwolla_free_verification_attempts'], ['class' => 'form-control','required', 'id'=>'dwollaAttempts','autocomplete'=>'off']) }}
                    </div>
                </div>


                <div class="form-group col-sm-12 col-md-4 col-lg-3">
                    {{ Form::label('app_name', 'Meet File Max Count:') }}
                    <div class="input-group mb-3">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fab fa-cuttlefish"></i></span>
                        </div>
                        {{ Form::number('meet_file_max_count', $settings['meet_file_max_count'], ['class' => 'form-control','required', 'id'=>'meetFileCount','autocomplete'=>'off']) }}
                    </div>
                </div>

                <div class="form-group col-sm-12 col-md-4 col-lg-3">
                    {{ Form::label('app_name', 'User Balance Hold Duration:') }}
                    <div class="input-group mb-3">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fas fa-stopwatch"></i></span>
                        </div>
                        {{ Form::number('user_balance_hold_duration', $settings['user_balance_hold_duration'], ['class' => 'form-control','min'=>'1','max'=>'31','required', 'id'=>'userDuration','autocomplete'=>'off']) }}
                    </div>
                </div>

                <div class="form-group col-sm-12 col-md-4 col-lg-3">
                    {{ Form::label('app_name', 'Athlete Import Max Size:') }}
                    <div class="input-group mb-3">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fas fa-file"></i></span>
                        </div>
                        {{ Form::number('athlete_import_max_size', $settings['athlete_import_max_size'], ['class' => 'form-control','min'=>'100','required','id'=>'athleteSize','autocomplete'=>'off']) }}
                        <div class="input-group-append">
                            <span class="input-group-text">MB</span>
                        </div>
                    </div>
                </div>

                <div class="form-group col-sm-12 col-md-4 col-lg-3">
                    {{ Form::label('app_name', 'Meet File Max Size:') }}
                    <div class="input-group mb-3">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fas fa-file"></i></span>
                        </div>
                        {{ Form::number('meet_file_max_size', $settings['meet_file_max_size'], ['class' => 'form-control','min'=>'100','required','id'=>'meetFileMaxSize','autocomplete'=>'off']) }}
                        <div class="input-group-append">
                            <span class="input-group-text">MB</span>
                        </div>
                    </div>
                </div>

                <div class="form-group col-sm-12 col-md-4 col-lg-3">
                    {{ Form::label('app_name', 'Profile Picture Max Size:') }}
                    <div class="input-group mb-3">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fas fa-file"></i></span>
                        </div>
                        {{ Form::number('profile_picture_max_size', $settings['profile_picture_max_size'], ['class' => 'form-control','min'=>'100','required','id'=>'profileSize','autocomplete'=>'off']) }}
                        <div class="input-group-append">
                            <span class="input-group-text">MB</span>
                        </div>
                    </div>
                </div>

                <div class="form-group col-sm-12 col-md-4 col-lg-3">
                    {{ Form::label('app_name', 'Featured Meets Fees: (In Percentage)') }}
                    <div class="input-group mb-3">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fas fa-percent"></i></span>
                        </div>
                        {{ Form::text('featured_meet_fee', $settings['featured_meet_fee'], ['class' => 'form-control','required','autocomplete'=>'off', 'id' => 'featuredMeetFee']) }}
                    </div>
                </div>
                <div class="form-group col-sm-12 col-md-4 col-lg-3">
                    {{ Form::label('app_name', 'Max Unverified Withdraw Limit') }}
                    <div class="input-group mb-3">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fas fa-dollar-sign"></i></span>
                        </div>
                        {{ Form::text('max_unverified_withdraw_limit', $settings['max_unverified_withdraw_limit'], ['class' => 'form-control','required','autocomplete'=>'off', 'id' => 'max_unverified_withdraw_limit']) }}
                    </div>
                </div>
                <div class="form-group col-sm-12 col-md-4 col-lg-3">
                    {{ Form::label('app_name', 'Max Verified Withdraw Limit') }}
                    <div class="input-group mb-3">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fas fa-dollar-sign"></i></span>
                        </div>
                        {{ Form::text('max_verified_withdraw_limit', $settings['max_verified_withdraw_limit'], ['class' => 'form-control','required','autocomplete'=>'off', 'id' => 'max_verified_withdraw_limit']) }}
                    </div>
                </div>
                <div class="form-group col-sm-12 col-md-4 col-lg-3">
                    {{ Form::label('app_name', 'Min Unverified Withdraw Limit') }}
                    <div class="input-group mb-3">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fas fa-dollar-sign"></i></span>
                        </div>
                        {{ Form::text('min_unverified_withdraw_limit', $settings['min_unverified_withdraw_limit'], ['class' => 'form-control','required','autocomplete'=>'off', 'id' => 'min_unverified_withdraw_limit']) }}
                    </div>
                </div>
                <div class="form-group col-sm-12 col-md-4 col-lg-3">
                    {{ Form::label('app_name', 'Min Verified Withdraw Limit') }}
                    <div class="input-group mb-3">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fas fa-dollar-sign"></i></span>
                        </div>
                        {{ Form::text('min_verified_withdraw_limit', $settings['min_verified_withdraw_limit'], ['class' => 'form-control','required','autocomplete'=>'off', 'id' => 'min_verified_withdraw_limit']) }}
                    </div>
                </div>
                <div class="form-group col-sm-12 col-md-4 col-lg-3">
                    {{ Form::label('app_name', 'Auto Withdraw Charge') }}
                    <div class="input-group mb-3">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fas fa-dollar-sign"></i></span>
                        </div>
                        {{ Form::text('auto_withdraw_charge', $settings['auto_withdraw_charge'], ['class' => 'form-control','required','autocomplete'=>'off', 'id' => 'auto_withdraw_charge']) }}
                    </div>
                </div>

                <div class="form-group col-sm-12 col-md-4 col-lg-3">
                    {{ Form::label('app_name', 'Terms And Service Of Featured Meets') }}
                    <div class="input-group mb-3">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fas fa-link"></i></span>
                        </div>
                        {{ Form::text('terms_service_link', $settings['terms_service_link'], ['class' => 'form-control','required','autocomplete'=>'off', 'id' => 'termsServiceLink']) }}
                    </div>
                </div>
                <div class="form-group col-sm-12 col-md-4 col-lg-3">
                    {{ Form::label('app_name', 'Competitors {Name : [Admin %, CC %]}') }}
                    <div class="input-group mb-3">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fas fa-link"></i></span>
                        </div>
                        {{ Form::text('competitors', $settings['competitors'], ['class' => 'form-control','required','autocomplete'=>'off', 'id' => 'termsServiceLink']) }}
                    </div>
                </div>

                <div class="form-group col-sm-12 col-md-4 col-lg-3">
                    {{ Form::label('app_name', 'Enable Featured Meets Fees:') }}
                    <div class="form-group">
                        <div class="custom-control custom-switch">
                            <input type="checkbox" name="enabled_feature_meet_fee" class="custom-control-input" value="1" id="enabledFeatureMeet"
                                {{ ($settings['enabled_feature_meet_fee'] == true)?'checked':'' }}>
                            <label class="custom-control-label" for="enabledFeatureMeet"></label>
                        </div>
                    </div>
                </div>

                <div class="form-group col-sm-12 col-md-4 col-lg-3">
                    {{ Form::label('app_name', 'Audit Enabled:') }}
                    <div class="form-group">
                        <div class="custom-control custom-switch">
                            <input type="checkbox" name="audit_enabled" class="custom-control-input" value="1" id="customSwitch1" {{ ($settings['audit_enabled'] == true)?'checked':'' }}>
                            <label class="custom-control-label" for="customSwitch1"></label>
                        </div>
                    </div>
                </div>
                <div class="form-group col-sm-12 col-md-4 col-lg-3">
                    {{ Form::label('app_name', 'Auto Withdraw Enabled:') }}
                    <div class="form-group">
                        <div class="custom-control custom-switch">
                            <input type="checkbox" name="is_schedule_withdraw_enabled" class="custom-control-input" value="1" id="customSwitch2" {{ ($settings['is_schedule_withdraw_enabled'] == true)?'checked':'' }}>
                            <label class="custom-control-label" for="customSwitch2"></label>
                        </div>
                    </div>
                </div>

            </div>
            <div class="text-right">
                <button type="submit" class="btn btn-sm btn-success">
                    <span class="fas fa-save"></span> Save
                </button>
            </div>
        </div>
    </div>
</div>
