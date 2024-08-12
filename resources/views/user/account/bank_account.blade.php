<div class="modal fade" id="modal-linked-bank-account" tabindex="-1" role="dialog"
        aria-labelledby="modal-linked-bank-account" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title text-primary">
                    <span class="fas fa-plus"></span> Link a Bank Account
                </h5>
                <button type="button" class="close modal-linked-bank-account-close" aria-label="Close">
                    <span class="fas fa-times" aria-hidden="true"></span>
                </button>
            </div>

            <div class="modal-body">
                <div class="alert alert-danger py-2 px-3 small" id="dwolla-bank-link-form-iav-error" style="display: none">
                </div>

                <div id="dwolla-bank-link-form-iav-container"></div>
                <div>
                    <form action="{{ route('account.bank.add') }}" method="post">
                    @csrf
                        <table>
                            <tr>
                                <td>Account Name: </td>
                                <td>
                                    <input type="text" class="form-control" name="account_name" placeholder="My Account" value="">
                                </td>
                            </tr>
                            <tr>
                                <td>Account Type: </td>
                                <td>
                                    <select name="account_type" class="form-control">
                                        <option value="savings">Savings</option>
                                        <option value="checking">Checking</option>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <td>Routing Number: </td>
                                <td>
                                    <input type="number" class="form-control" name="routing_number" placeholder="#########" value="">
                                </td>
                            </tr>
                            <tr>
                                <td>Account Number: </td>
                                <td>
                                    <input type="text" class="form-control" name="account_number" placeholder="#########" value="">
                                </td>
                            </tr>
                            <tr>
                                <td></td>
                                <td>
                                    <button type="submit" class="btn btn-success btn-sm" style="width:100%;">Add Bank</button>
                                </td>
                            </tr>
                        </table>
                    </form>
                </div>

                <div class="modal-footer pb-0 pr-0 pl-0 -1 d-block">
                    <div id="modal-linked-bank-account-spinner" class="small" style="display: none;">
                        <span class="spinner-border spinner-border-sm mr-1" role="status" aria-hidden="true">
                        </span> Please wait ...
                    </div>
                    <div class="text-info small mb-3">
                        <span class="fas fa-info-circle mt-2 mb-2"></span> Your bank account information
                        is securely sent to our payment provider and never transits through our servers.
                    </div>
                    <div class="text-right" style="display: none;" id="modal-linked-bank-account-try-again">
                        <button class="btn btn-sm btn-warning">
                            <span class="fas fa-redo-alt"></span> Try Again
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modal-why-is-my-account-unverified" tabindex="-1" role="dialog"
        aria-labelledby="modal-why-is-my-account-unverified" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title text-info">
                    <span class="fas fa-question-circle"></span> Why Is My Account Unverified ?
                </h5>
                <button type="button" class="close modal-why-is-my-account-unverified-close" aria-label="Close">
                    <span class="fas fa-times" aria-hidden="true"></span>
                </button>
            </div>

            <div class="modal-body">
                <p>
                    Micro-deposit method is used to verify your bank account, our payment processor will
                    transfer <strong>two deposits of less than $0.10</strong> to your linked bank account in 1-3
                    business days.<br/><br/>

                    Once you see these deposits in your account, you need to verify the two amounts on this same page,
                    by clicking the corresponding yellow button with a check mark.<br/><br/>

                    You only have <strong>three attempts</strong> to verify your bank account.
                    If you fail all three, you will need to :
                    <ol>
                        <li>Unlink the bank account</li>
                        <li>Wait 48 hours</li>
                        <li>Add the bank account back</li>
                        <li>Wait for the micro-deposits to show up in your account</li>
                        <li>Then proceed to verify your bank account again</li>
                    </ol>

                    If you need further assistance, please contact us.
                </p>
                <div class="modal-footer pb-0 pr-0 pl-0 -1 d-block">
                    <div class="text-right modal-why-is-my-account-unverified-close">
                        <button class="btn btn-info">
                            <span class="fas fa-check"></span> Got it !
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="col d-none">
    <input type="hidden" id="dwolla-env" value="{{ config('services.dwolla.js.env')}}">
</div>

@if ($dwolla_error)
    <div class="alert alert-danger">
        <strong><span class="fas fa-times-circle"></span> Ooh !</strong><br/>
        {{ $dwolla_error }}
    </div>
@else
    <div class="row">
        <div class="col">
            <h5 class="border-bottom"><span class="fas fa-project-diagram"></span> Linked Bank Accounts (Dwolla)</h5>
        </div>
    </div>

    <div class="mb-3">
        <div class="d-flex flex-no-wrap flex-row">
            <div class="flex-grow-1">
                <strong>
                    Status :
                </strong>
                @switch($dwolla->status)
                    @case(\App\Services\DwollaService::STATUS_UNVERIFIED)
                        <span class="text-warning font-weight-bold">Unverified.</span><br/>
                        As an unverified user you may not withdraw more than ${{ number_format(\App\Models\Setting::dwollaUnverifiedTransferCap()) }} per week. 
                        To lift these restrictions, verify your account.
                        @break

                    @case(\App\Services\DwollaService::STATUS_RETRY)
                        <span class="text-warning font-weight-bold">Verification Failed.</span> @{{ $reason }}<br/>
                        Please try again.
                        @break

                    @case(\App\Services\DwollaService::STATUS_DOCUMENT)
                        <span class="text-warning font-weight-bold">Additional Documents Required.</span>
                        @break

                    @case(\App\Services\DwollaService::STATUS_VERIFIED)
                        <span class="text-success font-weight-bold">Verified.</span>
                        @break

                    @default
                        <span class="text-danger">Suspended / Deactivated.</span><br/>
                        You cannot make ACH transactions on Allgymnastics.com. Please contact us.
                @endswitch
            </div>

            @if ($dwollaCanVerify)
                <div class="align-self-end">
                    <a class="btn btn-success" href="{{ route('account.dwolla.verify') }}">
                        <span class="fas fa-user-check"></span> Verify
                    </a>
                </div>
            @endif
        </div>

        @if ($dwollaAttempts->count() > 0)
            <div>
                <h6 class="border-bottom mt-1">
                    <span class="fas fa-history"></span> Verification Attempts
                </h6>

                <div class="table-responsive-lg">
                    <table class="table table-sm table-hover">
                        <thead class="thead-dark">
                            <tr>
                                <th scope="col" class="align-middle">Initiated At</th>
                                <th scope="col" class="align-middle">Last Updated At</th>
                                <th scope="col" class="align-middle">Result</th>
                                <th scope="col" class="align-middle">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($dwollaAttempts as $attempt)
                                <tr>
                                    <td class="align-middle">
                                        {{ $attempt->created_at->format(Helper::AMERICAN_SHORT_DATE_TIME) }}
                                    </td>

                                    <td class="align-middle">
                                        {{ $attempt->updated_at->format(Helper::AMERICAN_SHORT_DATE_TIME) }}
                                    </td>

                                    <td class="align-middle">
                                        @if ($attempt->status == \App\Models\DwollaVerificationAttempt::STATUS_PENDING)
                                            —
                                        @else
                                            {{ \App\Services\DwollaService::STATUS_STRINGS[$attempt->resulting_status] }}
                                        @endif
                                    </td>

                                    <td class="align-middle">
                                        @switch($attempt->status)
                                            @case(\App\Models\DwollaVerificationAttempt::STATUS_SUCCEEDED)
                                                <span class="badge badge-success">
                                                    Succeeded
                                                </span>
                                                @break
                                            @case(\App\Models\DwollaVerificationAttempt::STATUS_FAILED)
                                                <span class="badge badge-danger">
                                                    Failed
                                                </span>
                                                @break

                                            @default
                                                <span class="badge badge-warning">
                                                    Pending
                                                </span>
                                        @endswitch
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </div>

    @if ($bank_accounts == null)
        <div class="alert alert-info">
            <strong><span class="fas fa-exclamation-triangle"></span> Whoops !</strong><br/>
            It looks like you do not have any bank accounts linked to your account yet.
            You can do so by clicking the button below.
        </div>
    @else
        <div class="row">
            <div class="col">
                <h5 class="border-bottom"><span class="fas fa-university"></span> Linked Bank Accounts</h5>
            </div>
        </div>

        <div class="modal fade" id="modal-verify-micro-deposits" tabindex="-1" role="dialog"
                aria-labelledby="modal-verify-micro-deposits" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">

                    <div class="modal-header">
                        <h5 class="modal-title text-primary">
                            <span class="fas fa-check"></span> Verify Bank Account
                        </h5>
                        <button type="button" class="close modal-verify-micro-deposits-close" aria-label="Close">
                            <span class="fas fa-times" aria-hidden="true"></span>
                        </button>
                    </div>

                    <div class="modal-body">
                        <form method="POST" action="{{ route('account.bank.verify') }}"
                                id="modal-verify-micro-deposits-form">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="bank_account">

                            <div class="container-fluid">
                                <div class="row mb-3">
                                    <div class="col">
                                        Please fill-in the micro-deposit amount below to verify your account :
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg mb-3">
                                        <label for="amount1" class="mb-1">
                                            <span class="fas fa-fw fa-coins"></span> Amount 1 <span class="text-danger">*</span>
                                        </label>
                                        <div class="input-group input-group-sm">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text">
                                                    <span class="fas fa-fw fa-dollar-sign"></span>
                                                </span>
                                            </div>
                                            <input id="amount1" type="text" class="form-control"
                                                name="amount1" placeholder="0.00" required autofocus>
                                        </div>
                                    </div>

                                    <div class="col-lg mb-3">
                                        <label for="amount2" class="mb-1">
                                            <span class="fas fa-fw fa-coins"></span> Amount 2 <span class="text-danger">*</span>
                                        </label>
                                        <div class="input-group input-group-sm">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text">
                                                    <span class="fas fa-fw fa-dollar-sign"></span>
                                                </span>
                                            </div>
                                            <input id="amount2" type="text" class="form-control form-control-sm"
                                                name="amount2" placeholder="0.00" required>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer pb-0 pr-0 pl-0 -1 d-block">
                                <div class="text-right">
                                    <button type="submit" class="btn btn-success">
                                        <span class="spinner-border spinner-border-sm mr-1" role="status" aria-hidden="true"
                                            id="modal-verify-micro-deposits-spinner" style="display: none;">
                                        </span>
                                        <span class="fas fa-paper-plane"></span> Submit
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="table-responsive-lg">
            <table class="table table-sm table-hover">
                <thead class="thead-dark">
                    <tr>
                        <th scope="col" class="align-middle">Bank</th>
                        <th scope="col" class="align-middle">Name</th>
                        <th scope="col" class="align-middle">Type</th>
                        <th scope="col" class="align-middle">Status</th>
                        <th scope="col" class="text-right align-middle"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($bank_accounts as $bank_account)
                        <tr>
                            <td class="align-middle">
                                {{ ucfirst($bank_account->bankName) }}
                            </td>

                            <td class="align-middle">
                                {{ ucfirst($bank_account->name) }}
                            </td>

                            <td class="align-middle">
                                {{ ucfirst($bank_account->bankAccountType) }}
                            </td>

                            <td class="align-middle">
                                {{ ucfirst($bank_account->status) }}
                                @if ($bank_account->status == 'unverified')
                                    <a href="#modal-why-is-my-account-unverified"
                                        title="Why is my account unverified ?" data-toggle="modal"
                                        data-backdrop="static" data-keyboard="false">
                                        <span class="fas fa-question-circle"></span>
                                    </a>
                                @endif
                            </td>

                            <td class="text-right align-middle">

                                @if ($bank_account->status == 'unverified')
                                    <div class="mb-1 mr-1 d-inline-block">
                                        <a href="#" data-bank="{{ $bank_account->id }}"
                                            class="btn btn-sm btn-warning bank-account-verify"
                                            title="Verify Micro-Deposits">
                                            <span class="fas fa-check"></span>
                                        </a>
                                    </div>
                                @endif

                                <div class="mb-1 mr-1 d-inline-block bank-account-remove">
                                    <button type="submit" class="btn btn-sm btn-danger" title="Remove"
                                            data-bank="{{ $bank_account->id }}">
                                        <span class="fas fa-trash"></span>
                                    </button>
                                    <form action="{{ route('account.bank.remove', ['id' => $bank_account->id]) }}"
                                            data-bank="{{ $bank_account->id }}" class="d-none" method="post">
                                        @csrf
                                        @method('DELETE')
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
@endif

<div class="text-right">
    <a href="#modal-linked-bank-account" class="btn btn-sm btn-primary" data-toggle="modal"
        data-backdrop="static" data-keyboard="false">
        <span class="fas fa-plus"></span> Link an Account
    </a>
</div>
