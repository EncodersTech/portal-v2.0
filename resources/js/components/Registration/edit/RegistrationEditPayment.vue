<template>
    <div>
        <div class="modal fade" id="modal-coupon" tabindex="-1" role="dialog" aria-labelledby="modal-coupon" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title text-primary">
                            <span class="fas fa-check"></span> Coupon
                        </h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span class="fas fa-times" aria-hidden="true"></span>
                        </button>
                    </div>

                    <div class="modal-body">
                        <div class="d-flex flex-row flex-no-wrap mb-3">
                            <div style="width: 50%;">
                                Enter Your Coupon Code
                            </div>

                            <div class="ml-1">
                                <input type="text" class="form-control form-control-sm" v-model="coupon"  placeholder="XXXXXXXX" value="">
                            </div>
                        </div>
                        <div class="container-fluid">
                            <div class="text-right mt-3">
                                <button class="btn btn-sm btn-secondary mr-1" data-dismiss="modal">
                                    <span class="far fa-fw fa-times-circle"></span> Close
                                </button>
                                <button class="btn btn-sm btn-success"
                                    @click="checkCoupon(coupon)">
                                    <span class="fas fa-fw fa-check"></span> Confirm
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div v-if="this.meet != null && this.gymDetails != null">
            <div class="modal fade" id="modal-check-sending-details" tabindex="-1" role="dialog"
                 aria-labelledby="modal-check-sending-details" aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">
                                <span class="fas fa-money-check"></span> Check Sending Details
                            </h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span class="fas fa-times" aria-hidden="true"></span>
                            </button>
                        </div>

                        <div class="modal-body">
                            <table class="table table-sm table-striped border-1 mb-0">
                                <tbody>
                                <tr class="check-details-tr">
                                    <td style="width: 30%">
                                        <strong>Meet Name</strong>
                                    </td>
                                    <td>
                                        {{ this.meet.name }}
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <strong>Email</strong>
                                    </td>
                                    <td>
                                        {{ this.meet.primary_contact_email }}
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <strong>Phone</strong>
                                    </td>
                                    <td>
                                        {{ this.gymDetails.gym.office_phone }}
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <strong>Gym Name</strong>
                                    </td>
                                    <td>
                                        {{ this.gymDetails.gym.name }}
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <strong>Address</strong>
                                    </td>
                                    <td>
                                        {{ this.gymDetails.gym.addr_1 }},
                                        {{ (this.gymDetails.gym.addr_2 != null)?this.gymDetails.gym.addr_2+', ':'' }}
                                        {{ this.gymDetails.gym.city }},
                                        {{ this.gymDetails.gym.gym_state }},
                                        {{ this.gymDetails.gym.zipcode }}.
                                    </td>
                                </tr>
                                <tr v-if="summary != null">
                                    <td>
                                        <strong>Amount</strong>
                                    </td>
                                    <td>
                                        $ {{ numberFormat(summary.total) }}
                                    </td>
                                </tr>
                                </tbody>
                            </table>
                        </div>

                        <div class="text-right m-3">
                            <a class="btn btn-sm btn-secondary p-3" @click="printCheckDetails">Print</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div v-if="paymentProcessedMessage != null">
            <div v-if="this.chosenMethod.type == 'check'">
                {{ showCheckSendingModel() }}
            </div>
            <div class="alert alert-success">
                <strong>
                    <span class="fas fa-check-circle"></span> Thank you !
                </strong><br/>
                {{ this.paymentProcessedMessage }}
                <div class="text-right">
                    <a :href="'/gyms/' + gymId + '/registration/' + registrationId"
                        class="btn btn-small btn-info">
                        <span class="fas fa-eye"></span> View Registration Details
                    </a>
                </div>
            </div>
        </div>
        <div v-else>
            <div v-if="isLoading || isProcessingPayment">
                <div v-if="isProcessingPayment" class="alert alert-warning">
                    <strong>
                        <span class="fas fa-exclamation-circle"></span> Do not close or refresh this window.
                    </strong><br/>
                    Your payment is being processed.
                </div>

                <div class="text-center p-3">
                    <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                    Processing, please wait ...
                </div>
            </div>
            <div v-else>
                <div v-if="(paymentOptions != null)">
                    <div>
                        <h5 class="border-bottom">
                            <span class="fas fa-fw fa-file-invoice-dollar"></span> Choose a payment method
                        </h5>

                        <div v-if="paymentOptions.methods.card"
                            class="py-1 px-2 mb-2 border bg-white rounded">

                            <h6 class="clickable m-0 py-2" :class="{'border-bottom': (optionsExpanded == 'card')}"
                                @click="optionsExpanded = 'card'">
                                <span class="fas fa-fw fa-credit-card"></span> Credit or Debit Card
                                <span :class="'fas fa-fw fa-caret-' + (optionsExpanded == 'card' ? 'down' : 'right')"></span>
                            </h6>

                            <div v-if="optionsExpanded == 'card'">
                                <div v-if="paymentOptions.methods.card.cards.length < 1" class="py-1 small">
                                    <span class="fas fa-exclamation-circle"></span> You have no cards stored in
                                    your account.
                                </div>

                                <div v-else v-for="card in paymentOptions.methods.card.cards" :key="card.id"
                                    class="py-1 border-bottom border-light hoverable clickable"
                                    @click="useCard(card)">
                                    <div class="row">
                                        <div class="col-auto">
                                            <img class="credit-card-brand-image" :src="card.image"
                                                :alt="card.brand" :title="card.brand">
                                        </div>
                                        <div class="col">
                                            XXXX—{{ card.last4 }}
                                        </div>
                                        <div class="col">
                                            {{ card.expires.month }}/{{ card.expires.year }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div v-if="paymentOptions.methods.ach"
                            class="py-1 px-2 mb-2 border bg-white rounded">

                            <h6 class="clickable m-0 py-2" :class="{'border-bottom': (optionsExpanded == 'ach')}"
                                @click="optionsExpanded = 'ach'">
                                <span class="fas fa-fw fa-money-check-alt"></span> ACH
                                <span :class="'fas fa-fw fa-caret-' + (optionsExpanded == 'ach' ? 'down' : 'right')"></span>
                            </h6>

                            <div v-if="optionsExpanded == 'ach'">
                                <div v-if="paymentOptions.methods.ach.accounts.length < 1" class="py-1 small">
                                    <span class="fas fa-exclamation-circle"></span> You have no bank accounts
                                    stored in your account.
                                </div>

                                <div v-else v-for="account in paymentOptions.methods.ach.accounts" :key="account.id"
                                    class="py-1 border-bottom border-light hoverable clickable"
                                    @click="useACH(account)">
                                    <div class="row">
                                        <div class="col-auto">
                                            <span class="fas fa-fw fa-university"></span>
                                        </div>
                                        <div class="col">
                                            {{ account.name }}
                                        </div>
                                        <div class="col">
                                            {{ capitalize(account.type) }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- one time ach payment -->
                        <div v-if="paymentOptions.methods.onetimeach" class="py-1 px-2 mb-2 border bg-white rounded" @click="useOneTimeACH()">

                            <h6 class="clickable m-0 py-2" :class="{'border-bottom': (optionsExpanded == 'onetimeach')}"
                                @click="optionsExpanded = 'onetimeach'">
                                <span class="fas fa-fw fa-money-check-alt"></span> One Time ACH
                                <span :class="'fas fa-fw fa-caret-' + (optionsExpanded == 'onetimeach' ? 'down' : 'right')"></span>
                            </h6>

                            <div v-if="optionsExpanded == 'onetimeach'">
                                <div>
                                    <div>
                                        <label for="routingNumber">Routing Number:</label>
                                        <input type="text" class="form-control" id="routingNumber" v-model="routingNumber" required>
                                    </div>

                                    <div>
                                        <label for="accountNumber">Account Number:</label>
                                        <input type="text"  class="form-control" id="accountNumber" v-model="accountNumber" required>
                                    </div>

                                    <div>
                                        <label for="accountType">Account Type:</label>
                                        <select id="accountType"  class="form-control" v-model="accountType" required>
                                            <option value="c" selected="selected">Checking</option>
                                            <option value="s">Savings</option>
                                        </select>
                                    </div>

                                    <div>
                                        <label for="accountName">Account Name:</label>
                                        <input type="text"  class="form-control" id="accountName" v-model="accountName" required>
                                    </div>
                                </div>
                            </div>
                        </div>

<!--                        <div v-if="paymentOptions.methods.paypal"-->
<!--                            class="py-1 px-2 mb-2 border bg-white rounded">-->

<!--                            <h6 class="clickable hoverable m-0 py-2" @click="usePaypal()">-->
<!--                                <span class="fab fa-fw fa-paypal"></span> Paypal-->
<!--                                <span class="small muted">(coming soon)</span>-->
<!--                            </h6>-->
<!--                        </div>-->

                        <div v-if="paymentOptions.methods.check"
                            class="py-1 px-2 mb-2 border bg-white rounded">
                            <div @click="useCheck()">
                                <div>
                                
                                <h6 class="clickable m-0 py-2"  :class="{'border-bottom': (optionsExpanded == 'check')}"
                                    @click="optionsExpanded = 'check'">
                                    <span class="fas fa-fw fa-money-check-alt"></span> Mailed Check
                                </h6>
                                </div>
                                <div v-if="optionsExpanded == 'check'">
                                    <div class="form-group small mt-1 ml-3">
                                        <label class="control-label" for="check_no">
                                            <span class="fas fa-fw fa-money-check-alt"></span>
                                            Check # <span class="text-danger">*</span>
                                        </label>
                                        <input type="text" class="form-control form-control-sm"
                                            v-model="checkNo" id="check_no">
                                    </div>

                                    <div class="small ml-3 d-flex flex-row flew-nowrap">
                                        <div class="d-inline-block">
                                            <span class="fas fa-info-circle"></span> <strong>Instructions Provided By Host :</strong><br>
                                            <span class="m-0">
                                                {{ meet.mailed_check_instructions }}</span>
                                        </div>

<!--                                        <div class="d-inline-block ml-5">-->
<!--                                            <button class="btn btn-sm btn-warning" @click="showCheckSendingModel">-->
<!--                                                <span class="fas fa-money-check"></span> Check Sending Details-->
<!--                                            </button>-->
<!--                                        </div>-->
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div v-if="paymentOptions.methods.balance && (paymentOptions.methods.balance.current > 0)"
                            class="py-1 px-2 mb-2 border bg-white rounded">
                            <div v-if="chosenMethod && (chosenMethod.type == 'check')" class="text-danger">
                                <span class="fas fa-exclamation-circle"></span>
                                Allgymnastics.com balance cannot be used with mailed checks.
                            </div>
                            <div v-else class="form-check">
                                <input class="form-check-input" type="checkbox" id="use_balance"
                                    v-model="useBalance" @change="recalculateTotals()">
                                <label class="form-check-label" for="use_balance">
                                    <span class="fas fa-fw fa-coins"></span>
                                    Use my Allgymnastics.com balance towards this payment
                                </label>
                            </div>
                        </div>
                    </div>

                    <div v-if="summary != null" class="mb-3">
                        <h5 class="border-bottom">
                            <span class="fas fa-fw fa-clipboard-list"></span> Summary
                        </h5>

                        <div class="row">
                            <div class="col">
                                <span class="fas fa-fw fa-file-invoice-dollar"></span> Chosen Method :
                            </div>
                            <div class="col">
                                <div v-if="chosenMethod.type == 'card'">
                                    Card ending with {{ chosenMethod.last4 }}
                                </div>

                                <div v-else-if="chosenMethod.type == 'ach'">
                                    {{ capitalize(chosenMethod.accountType) }} bank account "{{ chosenMethod.name }}"
                                </div>
                                <div v-else-if="chosenMethod.type == 'onetimeach'">
                                    One Time ACH Payment
                                </div>

<!--                                <div v-else-if="chosenMethod.type == 'paypal'">-->
<!--                                    PayPal-->
<!--                                </div>-->

                                <div v-else>
                                    Mailed Check #{{ checkNo }}
                                </div>
                            </div>
                        </div>
                        <div v-if="previous_remaining > 0" class="row">
                            <div class="col">
                                <span class="fas fa-fw fa-file-invoice"></span> Previous Remaining Deposit :
                            </div>
                            <div class="col">
                                ${{ numberFormat(previous_remaining) }}
                            </div>
                        </div>
                        <div class="row">
                            <div class="col">
                                <span class="fas fa-fw fa-tasks"></span> Registration Subtotal :
                            </div>
                            <div class="col">
                                ${{ numberFormat(summary.subtotal) }}
                            </div>
                        </div>

                        <div v-if="summary.own_meet_refund > 0" class="row">
                            <div class="col">
                                <span class="fas fa-fw fa-user-check"></span> Own Meet Refund :
                            </div>
                            <div class="col">
                                <span class="text-success">-${{ numberFormat(summary.own_meet_refund) }}</span>
                            </div>
                        </div>

                        <div v-if="(summary.processor + summary.handling) > 0" class="row" v-on:click="toggleDiv()" style="cursor: pointer;">
                            <div class="col">
                                <span class="fas fa-fw fa-file-invoice"></span> Fees 
                                <span class="fas fa-fw fa-caret-down" id="caret-div" > </span> :
                            </div>
                            <div class="col">
                                ${{ numberFormat(summary.processor + summary.handling) }}

                                <span v-if="this.summary.saving != ''" class="alert alert-success" style="padding:0px 5px;">
                                {{ this.summary.saving }}
                                </span>
                            </div>
                        </div>

                        <div v-if="display_div">
                            <div v-if="summary.handling > 0" class="row">
                                <div class="col">
                                    <span class="fas fa-fw fa-server"></span>
                                    Handling Fee ({{this.paymentOptions.handling.fee}}%):
                                        <span data-toggle="tooltip"
                                            :title="'The Handling Fee is based on a percentage of the fees. The fees range from 0 to '+ this.paymentOptions.handling.fee +'% . These fees cover expenses consistent with running an online business. (Rent, Payroll, Programing/hosting, Insurance, Utilities, Hardware, Professional Services, etc.)'"> 
                                            <span class="fas fa-info-circle"></span>
                                        </span>
                                </div>
                                <div class="col">
                                    ${{ numberFormat(summary.handling) }}
                                </div>
                            </div>
                            <div v-if="summary.processor > 0" class="row">
                                <div class="col">
                                    <span class="fas fa-fw fa-file-invoice"></span>
                                        Payment Processor Fee ({{this.chosenMethod.fee}}{{(this.chosenMethod.type == 'ach' || this.chosenMethod.type == 'onetimeach') ? '' : '%'}}):
                                        <span data-toggle="tooltip"
                                            title="The Payment Processor fees cover expenses related to ACH and Credit Card charges. ACH fees are a flat $10 per transaction and Credit Card fees are between 3%-3.25%. Processing fees are added to the Subtotal & Handling Fee. We utilize 3rd Party Processors to facilitate safe and secure payments."> 
                                            <span class="fas fa-info-circle"></span>
                                        </span>
                                </div>
                                <div class="col">
                                    ${{ numberFormat(summary.processor) }}
                                </div>
                            </div>
                        </div>
                        <div v-if="summary.used_balance != 0" class="row">
                            <div class="col">
                                <span class="fas fa-fw fa-coins"></span> Balance :
                            </div>
                            <div :class="'col text-' + (summary.used_balance > 0 ? 'success' : 'danger')">
                                ${{ numberFormat(-summary.used_balance) }}
                            </div>
                        </div>
                        <div class="d-flex flex-row flew-nowrap mt-3 mb-2 p-3 rounded bg-primary">
                            <div class="flex-grow-1 text-uppercase">
                                <span class="text-secondary mr-1">
                                    <span class="fas fa-coins"></span> Total :
                                </span>
                                <span class="text-white font-weight-bold">${{ numberFormat(summary.total) }}</span>
                            </div>

                            <div>
                                <button v-if="!this.couponSuccess" id="couponBtn" class="btn btn-sm btn-info mr-1" @click="haveCoupon">
                                    <span class="fas fa-ticket-alt"></span> Have a Coupon?
                                </button>

                                <button class="btn btn-sm btn-secondary mr-1" @click="$emit('back-button')">
                                    <span class="fas fa-long-arrow-alt-left"></span> Back
                                </button>

                                <div class="d-inline-block">
                                    <button class="btn btn-sm btn-success"
                                        @click="submitRegistration">
                                        <span class="fas fa-file-invoice-dollar"></span> Proceed To Payment
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div v-if="(summary.total == 0)">
                            <span class="text-success"><strong>You have a zero balance. Please continue with payment steps to complete the transaction, you will not be charged</strong></span>
                        </div>
                    </div>
                    <div v-else>
                        <button class="btn btn-sm btn-primary" @click="$emit('back-button')">
                            <span class="fas fa-long-arrow-alt-left"></span> Back
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
    .clickable {
        cursor: pointer;
    }

    .hoverable:hover {
        background-color: var(--light) !important;
    }
</style>

<script>
    export default {
        name: 'RegistrationEditPayment',
        props: {
            managed: {
                default: null,
                type: Number
            },
            gymId: {
                type: Number,
                default: null,
            },
            meetId: {
                type: Number,
                default: null,
            },
            registrationId: {
                type: Number,
                default: null,
            },
            registrationData: {
                type: Object,
                default: null,
            },
            paymentOptions: {
                type: Object,
                default: null,
            },
            previous_remaining: {
                type: Number,
                default: null
            },
            previous_registration_credit_amount: {
                type: Number,
                default: 0
            }
            
        },
        computed: {
            constants() {
                return {
                };
            },
        },
        data() {
            return {
                isLoading: false,
                isError: false,
                optionsExpanded: null,
                errorMessage: '',
                chosenMethod: null,
                summary: null,
                isProcessingPayment: false,
                paymentProcessedMessage: null,
                useBalance: false,
                checkNo: null,
                meet: null,
                subtotal: 0,
                gymDetails: null,
                coupon: "",
                couponSuccess : false,
                couponValue: 0,
                display_div: false,
                competitions: null,
                enable_travel_arrangements: 0,
                onetimeach: null,
                routingNumber: '',
                accountNumber: '',
                accountType: 's', // Default to savings
                accountName: ''
            }
        },
        watch: {
            paymentOptions() {
                this.extractPaymentOptions();
            },

            registrationData() {
                this.registrationDataChanged();
            }
        },
        methods: {
            getCompetitions: function(){
                axios.get('/api/competitions-info/').then(result => {
                    this.competitions = result.data;
                });
            },
            toggleDiv: function() {
                this.display_div = !this.display_div;
                if(!this.display_div)
                    $("#caret-div").removeClass("fa-caret-up").addClass("fa-caret-down");
                else
                    $("#caret-div").removeClass("fa-caret-down").addClass("fa-caret-up");
            },
            registrationDataChanged() {
                if (this.registrationData == null)
                    return;
                this.meet = this.registrationData.meet;
                this.recalculateTotals();
                axios.get('/api/gym-info/'+this.registrationData.gym).then(result => {
                    this.gymDetails = result.data;
                });
            },

            showCheckSendingModel() {
                $('#modal-check-sending-details').modal('show');
            },

            printCheckDetails(){
                window.open('/meet/'+ this.meet.id +'/gym/'+this.registrationData.gym+'/print-check-sending-details','blank')
            },

            extractPaymentOptions() {
                this.chosenMethod = null;
            },
            isNumeric(n) {
                return !isNaN(parseFloat(n)) && isFinite(n);
            },
            recalculateTotals(coupon = 0) {
                console.log(this.registrationData.changes_fees);

                if(this.couponValue != 0 && this.isNumeric(this.couponValue))
                    coupon = this.couponValue;

                if ((this.paymentOptions == null) || (this.chosenMethod == null))
                    return;

                this.summary = {
                    subtotal: this.registrationData.total + this.previous_remaining,
                    own_meet_refund: (this.paymentOptions.is_own ? this.registrationData.total : 0),
                    handling: 0,
                    used_balance: 0,
                    processor: 0,
                    total: 0,
                    saving: ''
                };

                if (this.paymentOptions.defer.handling || this.paymentOptions.is_own) {
                    this.summary.handling = this.applyFeeMode(
                        this.summary.subtotal,
                        this.paymentOptions.handling.fee,
                        this.paymentOptions.handling.mode
                    );
                }

                let localTotal = this.summary.subtotal - this.summary.own_meet_refund + this.summary.handling;
                let currentBalance = Utils.toFloat(this.paymentOptions.methods.balance.current);

                if (this.paymentOptions.methods.balance && (this.chosenMethod.type != 'check')) {
                    if (currentBalance < 0) {
                        this.summary.used_balance = currentBalance;
                    } else if (this.useBalance) {
                        this.summary.used_balance = (
                            currentBalance >= localTotal ?
                            localTotal : currentBalance
                        );
                    }
                }

                localTotal -= this.summary.used_balance;
                if (localTotal > 0) {
                    if (this.paymentOptions.defer.processor || this.paymentOptions.is_own) {
                        this.summary.processor = this.applyFeeMode(
                            localTotal,
                            this.chosenMethod.fee,
                            this.chosenMethod.mode
                        );
                    } else if (this.summary.used_balance < 0) {
                        this.summary.processor = this.applyFeeMode(
                            -this.summary.used_balance,
                            this.chosenMethod.fee,
                            this.chosenMethod.mode
                        );
                    }
                }
                
                this.summary.total = localTotal + this.summary.processor;

                if(this.summary.total - coupon < 0)
                {
                    this.showAlert("Coupon cannot be used if value is greater than total", 'Whoops', 'red', 'fas fa-exclamation-triangle');
                }
                else
                {
                    this.summary.total = this.summary.total - coupon;
                }

                let sum_h_p = this.summary.handling + this.summary.processor;
                var flg = 0;
                if (sum_h_p > 0) {
                    let totalsave = 0;
                    this.summary.saving += 'Saved ' ;
                    for(let key in this.competitions){
                        let values = this.competitions[key];
                        let _cc = values[0];
                        let _af = values[1];
                        let _sf = _cc + _af;
                        let _saved_total_fee = (this.summary.subtotal * _sf) / 100; 
                        if(_saved_total_fee > sum_h_p)
                        {
                            totalsave += _saved_total_fee - sum_h_p;
                            // this.summary.saving += '$'+(_saved_total_fee - sum_h_p).toFixed(2) + ' than ' + key +',';
                            flg = 1;
                        }
                    }
                    if(flg == 1 && totalsave > 0)
                    {
                        this.summary.saving += '$'+totalsave.toFixed(2) + ' compared to competitors';
                        // this.summary.saving = this.summary.saving.slice(0, -1);
                    }
                    else
                    this.summary.saving = '';
                }
            },

            applyFeeMode(amount, fee, mode) {
                return (
                    mode == 'flat' ?            // flat || percent
                    fee :
                    amount * (fee / 100)
                );
            },

            useCard(card) {
                this.chosenMethod = {
                    ...card,
                    fee: this.paymentOptions.methods.card.fee,
                    mode: this.paymentOptions.methods.card.mode,
                    type: 'card'
                };
                this.recalculateTotals();
            },

            useACH(bank) {
                this.chosenMethod = {
                    ...bank,
                    accountType: bank.type,
                    fee: this.paymentOptions.methods.ach.fee,
                    mode: this.paymentOptions.methods.ach.mode,
                    type: 'ach'
                };
                this.recalculateTotals();
            },
            useOneTimeACH(){
                this.chosenMethod = {
                    accountType: '',
                    name: 'One Time Payment',
                    fee: this.paymentOptions.methods.ach.fee,
                    mode: this.paymentOptions.methods.ach.mode,
                    type: 'onetimeach'
                };
                this.recalculateTotals();
            },
            // usePaypal() {
            //     this.chosenMethod = {
            //         fee: this.paymentOptions.methods.paypal.fee,
            //         mode: this.paymentOptions.methods.paypal.mode,
            //         type: 'paypal'
            //     };
            //     this.recalculateTotals();
            // },

            useCheck() {
                this.chosenMethod = {
                    fee: this.paymentOptions.methods.check.fee,
                    mode: this.paymentOptions.methods.check.mode,
                    type: 'check'
                };
                this.useBalance = false;
                this.recalculateTotals();
            },
            haveCoupon()
            {
                let ocheck = $('input[name="paymentType"]:checked').val() == 1 ? this.registrationData.meet.deposit_ratio : 100;
                if(ocheck != 100)
                {
                    this.showAlert("Coupon cannot be used in deposit payment", 'Whoops', 'red', 'fas fa-exclamation-triangle');
                    return false;
                }
                $('#modal-coupon').modal('show');
            },
            checkCoupon()
            {
                axios.post(
                    '/api/registration/register/coupon',
                    {
                        '__managed': this.managed,
                        meet_id:this.registrationData.meet.id,
                        gym_id:this.registrationData.gym,
                        coupon:this.coupon.trim().toUpperCase()
                    }
                ).then(result => {
                    this.couponValue = result.data.value;
                    this.recalculateTotals();
                    this.showAlert("Coupon Successfully Applied", 'Success', 'green', 'fas fa-check');
                    this.couponSuccess = true;
                    $('#couponBtn').hide();
                }).catch(error => {
                    let msg = '';
                    if (error.response) {
                        msg = error.response.data.message;
                    } else if (error.request) {
                        msg = 'No server response.';
                    } else {
                        msg = error.message;
                    }
                    this.showAlert(msg, 'Whoops', 'red', 'fas fa-exclamation-triangle');
                }).finally(() => {
                    $('#modal-coupon').modal('hide');
                });
            },

            submitRegistration() {
                if (this.chosenMethod.type == 'check') {
                    if (!this.checkNo) {
                        let msg = 'Please provide a check number';
                        this.showAlert(msg, 'Whoops', 'red', 'fas fa-exclamation-triangle');
                        return;
                    }

                    this.chosenMethod.id = this.checkNo;
                }
                if(this.chosenMethod.type == 'onetimeach')
                {
                    this.onetimeach = {
                        routingNumber: this.routingNumber,
                        accountNumber: this.accountNumber,
                        accountType: this.accountType,
                        accountName: this.accountName
                    }
                }
                this.confirmAction(
                    'Are you sure you want to proceed with the payment ?',
                    'orange',
                    'fas fa-question-circle',
                    () => {
                        this.isProcessingPayment = true;
                        axios.post(
                            '/api/gym/' + this.gymId +
                            '/registration/' + this.registrationId +'/edit/pay',
                            {
                                '__managed': this.managed,
                                summary: this.summary,
                                bodies: this.registrationData.bodies,
                                coaches: this.registrationData.coaches,
                                method: {
                                    type: this.chosenMethod.type,
                                    id: (this.chosenMethod.id ? this.chosenMethod.id : null)
                                },
                                use_balance: this.useBalance,
                                coupon: this.coupon.trim().toUpperCase(),
                                onetimeach: this.onetimeach,
                                changes_fees: this.registrationData.changes_fees,
                            }
                        ).then(result => {
                            this.paymentProcessedMessage = result.data.message;
                        }).catch(error => {
                            let msg = '';
                            if (error.response) {
                                msg = error.response.data.message;
                            } else if (error.request) {
                                msg = 'No server response.';
                            } else {
                                msg = error.message;
                            }

                            this.showAlert(msg, 'Whoops', 'red', 'fas fa-exclamation-triangle');
                        }).finally(() => {
                            this.isProcessingPayment = false;
                        });
                    },
                    this
                );

            },

            confirmAction(msg, color, icon, callback, context) {
                $.confirm({
                    title: 'Are you sure ?',
                    content: msg,
                    icon: icon,
                    type: color,
                    typeAnimated: true,
                    buttons: {
                        no: function () {
                            this.close();
                        },
                        confirm:  {
                            text: 'Yes',
                            btnClass: 'btn-' + color,
                            action: function () {
                                callback();
                            }
                        }
                    }
                });
            },

            showAlert(msg, title, color, icon) {
                $.alert({
                    title: title,
                    content: msg,
                    icon: icon,
                    type: color,
                    typeAnimated: true
                });
            },

            numberFormat(n) {
                try {
                    let fee = Utils.toFloat(n);
                    return (fee === null ? n : fee.toFixed(2));
                } catch (e) {
                    return n;
                }
            },

            capitalize(s) {
                if (typeof s !== 'string') return ''
                return s.charAt(0).toUpperCase() + s.slice(1)
            }
        },
        beforeMount(){
            this.getCompetitions();
        },
        mounted() {
            if (this.registrationData)
            {
                subtotal = this.registrationData.total;
            }
        }
    }
</script>
