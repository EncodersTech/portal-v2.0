<div class="row">
    <div class="col">
        <div class="row">
            <div class="col">
                <h5 class="border-bottom"><span class="fas fa-fw fa-clipboard-list">
                    </span> Registration
                </h5>
            </div>
        </div>

        <div class="row">
            <div class="col-lg mb-2">
                <table class="table table-sm table-striped table-borderless mb-0">
                    <tbody>
                        <tr>
                            <td class="align-middle font-weight-bold">
                                <span class="fas fa-fw fa-calendar-alt"></span> Start Date
                            </td>
                            <td class="align-middle">
                                {{ $meet->registration_start_date->format(Helper::AMERICAN_SHORT_DATE) }}
                            </td>
                        </tr>

                        <tr>
                            <td class="align-middle font-weight-bold">
                                <span class="fas fa-fw fa-calendar-alt"></span> End Date
                            </td>
                            <td class="align-middle">
                                {{ $meet->registration_end_date->format(Helper::AMERICAN_SHORT_DATE) }}
                            </td>
                        </tr>

                        <tr>
                            <td class="align-middle font-weight-bold">
                                <span class="fas fa-fw fa-calendar-alt"></span> Scratch Date :
                            </td>
                            <td class="align-middle">
                                {{ $meet->registration_scratch_end_date->format(Helper::AMERICAN_SHORT_DATE) }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        @if ($meet->allow_late_registration)
            <div class="row mt-3">
                <div class="col">
                    <h5 class="border-bottom">
                        <span class="fas fa-fw fa-clipboard-list"></span> Late Registration
                    </h5>
                </div>
            </div>

            <div class="row">
                <div class="col-lg mb-2">
                    <table class="table table-sm table-striped table-borderless mb-0">
                        <tbody>
                            <tr>
                                <td class="align-middle font-weight-bold">
                                    <span class="fas fa-fw fa-dollar-sign"></span> Late Registration Fee
                                </td>
                                <td class="align-middle">
                                    <div>
                                        ${{ number_format($meet->late_registration_fee, 2) }}
                                    </div>
                                    <div class="small text-info">
                                        <span class="fas fa-info-circle"></span> This fee applies to the club entry as a
                                        whole. Additional late fees per level might have been set in the Competition
                                        Settings tab.
                                    </div>
                                </td>
                            </tr>

                            <tr>
                                <td class="align-middle font-weight-bold">
                                    <span class="fas fa-fw fa-calendar-alt"></span> Late Registration Start Date
                                </td>
                                <td class="align-middle">
                                    {{ $meet->late_registration_start_date->format(Helper::AMERICAN_SHORT_DATE) }}
                                </td>
                            </tr>

                            <tr>
                                <td class="align-middle font-weight-bold">
                                    <span class="fas fa-fw fa-calendar-alt"></span> Late Registration End Date
                                </td>
                                <td class="align-middle">
                                    {{ $meet->late_registration_end_date->format(Helper::AMERICAN_SHORT_DATE) }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        @else
            <div class="row">
                <div class="col">
                    <div class="alert alert-info mb-0">
                        <span class="fas fa-info-circle"></span> Late registration is not enabled.
                    </div>
                </div>
            </div>
        @endif

        <div class="row mt-3">
            <div class="col">
                <h5 class="border-bottom">
                    <span class="fas fa-fw fa-chart-pie"></span> Athlete Limit
                </h5>
            </div>
        </div>

        <div class="row">
            <div class="col-lg mb-2">
                <table class="table table-sm table-striped table-borderless mb-0">
                    <tbody>
                        <tr>
                            <td class="align-middle font-weight-bold">
                                <span class="fas fa-fw fa-chart-pie"></span> Limit
                            </td>
                            <td class="align-middle">
                                {{ $meet->athlete_limit != null ? $meet->athlete_limit : '—' }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="row mt-3">
            <div class="col">
                <h5 class="border-bottom"><span class="fas fa-fw fa-credit-card">
                    </span> Payment
                </h5>
                <div class="small text-info">
                    <span class="fas fa-info-circle"></span> We offer payment by credit card by default.
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg mb-2">
                <table class="table table-sm table-striped table-borderless mb-0">
                    <tbody>
                        <tr>
                            <td class="align-middle font-weight-bold">
                                <span class="fab fa-paypal"></span> Accept PayPal Payments
                            </td>
                            <td class="align-middle">
                                {{ $meet->accept_paypal ? 'Yes': 'No' }}
                            </td>
                        </tr>

                        <tr>
                            <td class="align-middle font-weight-bold">
                                <span class="fas fa-university"></span> Accept ACH Payments
                            </td>
                            <td class="align-middle">
                                {{ $meet->accept_ach ? 'Yes': 'No' }}
                            </td>
                        </tr>

                        <tr>
                            <td class="align-middle font-weight-bold">
                                <span class="fas fa-money-check"></span> Accept Mailed Checks
                            </td>
                            <td class="align-middle">
                                <div>
                                    {{ $meet->accept_mailed_check ? 'Yes': 'No' }}
                                </div>

                                @if ($is_own)
                                    <div class="text-primary small">
                                        <span class="fas fa-exclamation-triangle"></span>
                                        This option will appear to registrants <strong>only if you have a valid credit
                                            card linked to your account at the time of registration</strong>.<br>
                                        This card will be charged for Credit Card and Handling fees. If you chose to
                                        defer these fee to
                                        registering gym, They will be advised to include them in their entry fee check.
                                    </div>
                                    @if(Auth::user()->mail_check_disable)
                                        <i class="fas fa-question-circle text-info font-weight-bold"></i>
                                        <span class="text-info font-weight-bold"> Your ability to utilize mailed checks has been disabled.
                                            Contact AllGym support if you believe this was done in error.</span>
                                    @endif

                                    @if ($meet->accept_mailed_check)
                                        <p class="preserve-new-lines mb-0">{{ $meet->mailed_check_instructions }}</p>
                                    @endif
                                @endif
                            </td>
                        </tr>

                        @if ($is_own)
                            <tr>
                                <td class="align-middle font-weight-bold">
                                    <span class="fas fa-check-circle"></span> Defer Handling Fees to Registering Gyms
                                </td>
                                <td class="align-middle">
                                    {{ $meet->defer_handling_fees ? 'Yes': 'No' }}
                                </td>
                            </tr>

                            <tr>
                                <td class="align-middle font-weight-bold">
                                    <span class="fas fa-check-circle"></span> Defer Payment Processor Fees to Registering Gyms
                                </td>
                                <td class="align-middle">
                                    {{ $meet->defer_processor_fees ? 'Yes': 'No' }}
                                </td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
