@extends('layouts.main')

@section('content-header')
<span class="fas fa-fw fa-exchange-alt"></span> Transaction Balances
@endsection

@section('content-main')
@include('include.errors')

<div class="content-main">
    <div>
        @php ($active_tab = 'balance_transactions')
        @include('include.user.profile_nav')
    </div>
    <div class="p-3">
        <div class="modal fade" id="modal-payment-options-info" tabindex="-1" role="dialog"
            aria-labelledby="modal-payment-options-info" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title text-info">
                            <span class="fas fa-info-circle"></span> Money Flow On AllGymnastics Explained
                        </h5>
                        <button type="button" class="close modal-payment-options-info-close" aria-label="Close"
                            data-dismiss="modal">
                            <span class="fas fa-times" aria-hidden="true"></span>
                        </button>
                    </div>

                    <div class="modal-body">
                        <div>
                            Understanding and managing your funds on AllGymnastics is very simple.
                            Your funds can be in one of two statuses :<br /><br />

                            <strong><span class="fas fa-fw fa-clock"></span> Total Funds</strong>: This amount
                            is on-hold on your AllGymnastics account. Funds are automatically put on-hold for 6
                            days when received. After that, they will be added to your available funds.<br /><br />

                            <strong><span class="fas fa-fw fa-dollar-sign"></span> Available Funds</strong>: This
                            is the amount of money that is immediately available for withdrawal from your
                            AllGymnastics account.<br /><br />

                            If you have further questions, please contact us.
                        </div>

                        <div class="modal-footer mt-3 pb-0 pr-0">
                            <div class="text-right">
                                <button class="btn btn-info modal-payment-options-info-close" data-dismiss="modal">
                                    <span class="fas fa-check"></span> Got it !
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="modal-transaction-details" tabindex="-1" role="dialog"
            aria-labelledby="modal-transaction-details" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title text-primary">
                            <span class="fas fa-exchange-alt"></span> Transaction Details
                        </h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span class="fas fa-times" aria-hidden="true"></span>
                        </button>
                    </div>

                    <div class="modal-body">
                        <div v-if="transaction == null">
                            Something went wrong. Please try again later.
                        </div>
                        <div v-else class="container-fluid">
                            <div class="mb-3">
                                <div v-if="transaction.processor_id" class="row mt-1">
                                    <div class="col">
                                        <span class="fas fa-fw fa-hashtag"></span> Transaction ID
                                    </div>
                                    <div class="col">
                                        @{{ transaction.processor_id }}
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col">
                                        <span class="fas fa-fw fa-wallet"></span> Type
                                    </div>
                                    <div class="col">
                                        @{{ constants.balance.transactions.types[transaction.type] }}
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col">
                                        <span class="fas fa-fw fa-info-circle"></span> Description
                                    </div>
                                    <div class="col">
                                        @{{ transaction.description }}
                                    </div>
                                </div>

                                <div class="row mt-1">
                                    <div class="col">
                                        <span class="fas fa-fw fa-coins"></span> Amount
                                    </div>
                                    <div class="col">
                                        $@{{ numberFormat(transaction.total) }}
                                    </div>
                                </div>

                                <div class="row mt-1">
                                    <div class="col">
                                        <span class="fas fa-fw fa-calendar-day"></span> Initiated
                                    </div>
                                    <div class="col">
                                        @{{ transaction.created_at_display }}
                                    </div>
                                </div>

                                <div class="row mt-1">
                                    <div class="col">
                                        <span class="fas fa-fw fa-calendar-check"></span> Updated
                                    </div>
                                    <div class="col">
                                        @{{ transaction.updated_at_display }}
                                    </div>
                                </div>

                                <div class="row mt-1">
                                    <div class="col">
                                        <span class="fas fa-fw fa-info-circle"></span> Status
                                    </div>
                                    <div class="col">
                                        <div
                                            v-if="transaction.status == constants.balance.transactions.statuses.Cleared">
                                            <span class="badge badge-success">Cleared</span>
                                        </div>

                                        <div
                                            v-else-if="transaction.status == constants.balance.transactions.statuses.Pending">
                                            <span class="badge badge-warning">Pending</span>
                                        </div>

                                        <div
                                            v-else-if="transaction.status == constants.balance.transactions.statuses.Unconfirmed">
                                            <span class="badge badge-warning">Pending (Unconfirmed)</span>
                                        </div>

                                        <div v-else>
                                            <span class="badge badge-danger">Failed</span>
                                        </div>
                                    </div>
                                </div>

                                <div v-if="transaction.status == constants.balance.transactions.statuses.Pending"
                                    class="row mt-1">
                                    <div class="col">
                                        <span class="fas fa-fw fa-calendar-check"></span> Clears On
                                    </div>
                                    <div class="col">
                                        @{{ transaction.clears_on_display }}
                                    </div>
                                </div>
                            </div>

                            <div class="text-right mt-3">
                                <button class="btn btn-sm btn-secondary" data-dismiss="modal">
                                    <span class="far fa-fw fa-times-circle"></span> Close
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="modal-withdraw-request" tabindex="-1" role="dialog"
            aria-labelledby="modal-withdraw-request" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title text-info">
                            <span class="fas fa-hand-holding-usd"></span> Withdraw To Your Bank Account
                        </h5>
                    </div>

                    <div class="modal-body">
                        <div class="form-group">
                            <label class="control-label" for="withdrawal_account">
                                <span class="fas fa-fw fa-university"></span>
                                Withdraw to
                            </label>
                            <select id="withdrawal_account" class="form-control form-control-sm"
                                v-model="withdrawal.account">
                                <option value="">(Choose a bank account ...)</option>
                                <option v-for="ba in bankAccounts" :key="ba.id" :value="ba.id">
                                    @{{ ba.name }} (@{{ ba.bank_name }})
                                </option>
                            </select>
                        </div>

                        <div class="text-info">
                            <span class="fas fa-fw fa-info-circle"></span>
                            We automatically add the fee to your withdrawal amount.
                            Please ensure the total does not exceed your total available balance.
                        </div>
                        @if( ! $isDwollaVerified )
                        <div class="text-warning">
                            <span class="fas fa-fw fa-info-circle"></span>
                            As an unverified user you may not withdraw more than $5,000 at one time. 
                            To verify your account, click "payment options" and then verify.
                        </div>
                        @endif
                        <div class="form-group">
                            <label class="control-label" for="withdrawal_amount">
                                <span class="fas fa-fw fa-coins"></span>
                                Amount to withdraw
                            </label>
                            <div class="input-group input-group-sm" >
                                <div class="input-group-prepend">
                                    <span class="input-group-text">
                                        <span class="fas fa-fw fa-dollar-sign"></span>
                                    </span>
                                </div>
                                @if($isDwollaVerified)
                                <currency-input class="form-control form-control-sm" v-model="withdrawal.amount"
                                    :currency="null" locale="en" :precision="2" :allow-negative="false"
                                    :value-range="{min: 0, max: {{ (Auth::user()->cleared_balance - 0.01) }}}"
                                    :disabled="!withdrawal.account">
                                </currency-input>
                                @else
                                <currency-input class="form-control form-control-sm" v-model="withdrawal.amount"
                                    :currency="null" locale="en" :precision="2" :allow-negative="false"
                                    :value-range="{min: 0, max: {{ 5000 }}}"
                                    :disabled="!withdrawal.account">
                                </currency-input>
                                @endif
                            </div>
                        </div>

                        <div class="mt-1 mb-1">
                            <span class="fas fa-fw fa-comment-dollar"></span>
                            <span class="font-weight-bold"> Fee :</span>
                            $@{{ currencyFormat(withdrawal.fee) }}
                        </div>
                        <div class="mt-1 mb-1">
                            <span class="fas fa-fw fa-file-invoice-dollar"></span>
                            <span class="font-weight-bold"> Total :</span>
                            <span class="font-weight-bold text-danger">
                                $@{{ currencyFormat(withdrawal.total) }}
                            </span>
                        </div>

                        <div class="mt-1 mb-1">
                            <span class="fas fa-fw fa-comment-dollar"></span>
                            <span class="font-weight-bold"> Featured Fee Charge :</span>
                            <span class="font-weight-bold text-danger">
                                $@{{ currencyFormat(withdrawal.featured_meet) }}
                            </span>
                        </div>

                        <div class="mt-1 mb-1">
                            <span class="fas fa-fw fa-file-invoice-dollar"></span>
                            <span class="font-weight-bold"> Net Withdraw amount :</span>
                            <span class="font-weight-bold text-danger">
                                $@{{ currencyFormat(withdrawal.net_withdraw_amount) }}
                            </span>
                        </div>

                        <div class="form-group">
                            <label class="control-label" for="withdrawal_confirm">
                                <span class="fas fa-fw fa-check-double"></span>
                                Please type
                                <span class="font-weight-bold text-danger">CONFIRM</span>
                                (in capital letters) in this field
                            </label>
                            <input type="text" placeholder="Please type CONFIRM here"
                                class="form-control form-control-sm" v-model="withdrawal.confirm"
                                id="withdrawal_confirm" :disabled="(withdrawal.total <= 0)">
                        </div>
                        <div>
                            @if(Auth::user()->meetFeaturedWithdrawalFee()['total_net_value'] > 0)
                            <div class="col-12">
                                <h4 class="control-label">
                                    Featured Meets Charges:
                                </h4>
                            </div>
                            @endif
                            @foreach(Auth::user()->meetFeaturedWithdrawalFee() as $meetName => $data)
                            @if(isset($data['total']) && $data['total'] > 0)
                            <div class="col-12">
                                <table border="1" style="width: inherit;">
                                    <tr>
                                        <th colspan="2" class="text-center">{{ $meetName }}</th>
                                    </tr>
                                    <tr>
                                        <td class="custom-width-80">Total Meet registration Amount</td>
                                        <td class="text-center custom-width-80">${{ number_format($data['total'], 2) }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="custom-width-80">Featured Meets (in percentage)</td>
                                        <td class="text-center custom-width-80">$
                                            {{ number_format($data['net_value'], 2) }}
                                            ({{ App\Models\Setting::getSetting(App\Models\Setting::FEATURED_MEET_FEE)->value }}
                                            )%
                                        </td>
                                    </tr>
                                </table>
                            </div><br>
                            @endif
                            @endforeach
                        </div>
                    </div>

                    <div class="modal-footer mt-3 d-flex flex-row">
                        <div class="flex-grow-1">
                            <button class="btn btn-secondary modal-withdraw-request-close" data-dismiss="modal">
                                <span class="far fa-times-circle"></span> Close
                            </button>
                        </div>

                        <div class="">
                            <button class="btn btn-info modal-withdraw-request-withdraw" data-dismiss="modal"
                                @click="requestWithdrawal()" :disabled="(withdrawal.total <= 0)">
                                <span class="fas fa-hand-holding-usd"></span> Withdraw
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row" id="ag-balance">
            <div class="col">
                @if(Auth::user()->cleared_balance > 0 && Auth::user()->withdrawal_freeze)
                <h6 class="text-danger">Your account has pending issues that have resulted in withdrawals being frozen.
                    Please contact AllGym customer support at <a class="text-decoration-none"
                        href="mailto:support@allgymnastics.com">support@allgymnastics.com</a> and mention Frozen
                    Withdrawal.</h6>
                @endif
                <h5 class="border-bottom">
                    <span class="fas fa-money-check-alt"></span> Balance
                    <a href="#modal-payment-options-info" class="text-info ml-1" data-toggle="modal"
                        data-backdrop="static" data-keyboard="false">
                        <span class="fas fa-info-circle"></span>
                    </a>
                </h5>
                @if (!$isDwollaVerified)
                <small>
                    <p class="alert-warning"> To avoid delays in receiving your funds, if withdrawing more than
                 $5,000 your dwolla account must be verified. From the tabs above, select Click "Payment Options" and click verify to get started.
                 Do not attempt to withdraw more if you are unverified.</p>
                </small>
                @endif
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-lg mb-3">
                <label for="available_funds" class="mb-1">
                    <span class="fas fa-fw fa-dollar-sign"></span> Available Funds
                </label>
                <div class="input-group input-group-sm">
                    <div class="input-group-prepend">
                        <span class="input-group-text"><span class="fas fa-fw fa-dollar-sign"></span></span>
                    </div>
                    <input id="available_funds" type="text" class="form-control" name="available_funds"
                        value="{{ Auth::user()->availableFunds() }}" placeholder="Available Funds" disabled autofocus>

                    @if (Auth::user()->cleared_balance > 0 && !Auth::user()->withdrawal_freeze)
                    <div v-if="!isLoading" class="input-group-append">
                        <a href="#modal-withdraw-request" class="btn btn-info" data-toggle="modal"
                            data-backdrop="static" data-keyboard="false">
                            <span class="fas fa-hand-holding-usd"></span> Withdraw
                        </a>
                    </div>
                    @endif
                </div>
            </div>

            <div class="col-lg mb-3">
                <label for="pending_funds" class="mb-1">
                    <span class="fas fa-fw fa-clock"></span> Total Funds
                </label>
                <div class="input-group input-group-sm">
                    <div class="input-group-prepend">
                        <span class="input-group-text"><span class="fas fa-fw fa-dollar-sign"></span></span>
                    </div>
                    <input id="pending_funds" type="text" class="form-control" name="pending_funds"
                        value="{{ Auth::user()->pendingFunds() }}" placeholder="Pending Funds" disabled autofocus>
                </div>
                <small data-toggle="tooltip"
                    title="Featured Meet Fees:- {{ App\Models\Setting::getSetting(App\Models\Setting::FEATURED_MEET_FEE)->value }}%">Featured
                    Meets Charges:
                    ${{ number_format(Auth::user()->meetFeaturedWithdrawalFee()['total_net_value'], 2) }}</small>
            </div>
        </div>

        <div v-if="errorMessage" :class="{'d-block': errorMessage}" style="display: none">
            <div class="alert alert-danger">
                <span class="fas fa-times-circle"></span> <span v-html="errorMessage"></span>
            </div>
        </div>
        <div v-else-if="isLoading">
            <div class="small text-center py-3">
                <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true">
                </span> Loading, please wait ...
            </div>
        </div>
        <div v-else>
            <div v-if="transactions.length > 0">
                <div class="table-responsive-lg">
                    <table class="table table-sm table-striped">
                        <thead class="thead-dark">
                            <tr>
                                <th scope="col" class="align-middle created_at" @click="sortBy('created_at')">
                                    Created At
                                    <span v-if="sortColumn == 'created_at'">
                                        <span :class="'fas fa-fw fa-caret-' + sortDirection"></span>
                                    </span>
                                </th>
                                <th scope="col" class="align-middle" @click="sortBy('meet_name')">
                                    Meet Name
                                    <span v-if="sortColumn == 'meet_name'">
                                        <span :class="'fas fa-fw fa-caret-' + sortDirection"></span>
                                    </span>
                                </th>
                                <th scope="col" class="align-middle" @click="sortBy('description')">
                                    Description
                                    <span v-if="sortColumn == 'description'">
                                        <span :class="'fas fa-fw fa-caret-' + sortDirection"></span>
                                    </span>
                                </th>
                                <th scope="col" class="align-middle" @click="sortBy('total')">
                                    Amount
                                    <span v-if="sortColumn == 'total'">
                                        <span :class="'fas fa-fw fa-caret-' + sortDirection"></span>
                                    </span>
                                </th>
                                <th scope="col" class="align-middle">
                                    Status
                                </th>
                                <th scope="col" class="align-middle" @click="sortBy('updated_at')">
                                    Updated At
                                    <span v-if="sortColumn == 'updated_at'">
                                        <span :class="'fas fa-fw fa-caret-' + sortDirection"></span>
                                    </span>
                                </th>
                                <th scope="col text-right" class="align-middle">
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="tx in transactions" :key="tx.processor_id">
                                <td class="align-middle">
                                    @{{ tx.created_at_display }}
                                </td>
                                <td class="align-middle">
                                    @{{ tx.meet_name }}
                                </td>

                                <td class="align-middle">
                                    @{{ tx.description }}
                                </td>

                                <td class="align-middle" :class="tx.total < 0 ? 'text-danger' : 'text-success'">
                                    $@{{ numberFormat(tx.total) }}
                                </td>

                                <td class="align-middle">
                                    <div v-if="tx.status == constants.balance.transactions.statuses.Cleared">
                                        <span class="badge badge-success">Cleared</span>
                                    </div>

                                    <div v-else-if="tx.status == constants.balance.transactions.statuses.Pending">
                                        <span class="badge badge-warning">Pending</span>
                                    </div>

                                    <div v-else-if="tx.status == constants.balance.transactions.statuses.Unconfirmed">
                                        <span class="badge badge-warning">Pending (Unconfirmed)</span>
                                    </div>

                                    <div v-else>
                                        <span class="badge badge-danger">Failed</span>
                                    </div>
                                </td>

                                <td class="align-middle">
                                    @{{ tx.updated_at_display }}
                                </td>
                                <th scope="col" class="align-middle">
                                    <div class="text-right">
                                        <button class="btn btn-sm btn-primary" @click="showTransactionDetails(tx)">
                                            <span class="fas fa-fw fa-info"></span>
                                        </button>
                                    </div>
                                </th>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div v-else class="text-info">
                <span class="fas fa-info-circle"></span> No transactions.
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts-main')
<script src="{{ mix('js/user/account-balance-transactions.js') }}"></script>
<script>
$(document).ready(function() {
    $('[data-toggle="tooltip"]').tooltip();
});
let featuredMeetFee = "{{ Auth::user()->meetFeaturedWithdrawalFee()['total_net_value'] }}";
</script>
@endsection