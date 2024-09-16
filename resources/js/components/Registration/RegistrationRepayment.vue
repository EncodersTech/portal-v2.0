<template>
    <div>
        <div v-if="paymentProcessedMessage != null">
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
                        
                        <div v-if="paymentOptions.methods.onetimecc" class="py-1 px-2 mb-2 border bg-white rounded" @click="useOneTimeCC()">

                            <h6 class="clickable m-0 py-2" :class="{'border-bottom': (optionsExpanded == 'onetimecc')}"
                                @click="optionsExpanded = 'onetimecc'">
                                <span class="fas fa-fw fa-money-check-alt"></span> One Time Card Payment
                                <span :class="'fas fa-fw fa-caret-' + (optionsExpanded == 'onetimecc' ? 'down' : 'right')"></span>
                            </h6>

                            <div v-if="optionsExpanded == 'onetimecc'">
                                <div>
                                    <div>
                                        <label for="card_name">Name on Card:</label>
                                        <input type="text" class="form-control" id="card_name" v-model="card_name" required>
                                    </div>

                                    <div>
                                        <label for="card_number">Card Number:</label>
                                        <input type="text"  class="form-control" id="card_number" v-model="card_number" maxlength="19" @input="cardNumberInput()" required>
                                    </div>

                                    <div>
                                        <label for="card_expiry">Expiry Date:</label>
                                        <input type="text"  class="form-control" id="card_expiry" v-model="card_expire" placeholder="mm/yy" maxlength="5" @input="cardExpiryInput()" required>
                                    </div>
                                    <div>
                                        <label for="card_cvc">CVV</label>
                                        <input type="text"  class="form-control" id="card_cvc" v-model="card_cvc" maxlength="4" required>
                                    </div>
                                    <div class="mt-2">
                                        <input type="checkbox" id="save_cc_card" v-model="save_cc_card">
                                        <label for="save_cc_card">Check - if you would like to save this Credit Card for future transactions. Please note, we can only store one Credit Card per account</label>
                                    </div>
                                </div>
                            </div>
                        </div>
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
                                <h6 class="clickable m-0 py-2"  :class="{'border-bottom': (optionsExpanded == 'check')}"
                                    @click="optionsExpanded = 'check'">
                                    <span class="fas fa-fw fa-money-check-alt"></span> Mailed Check
                                </h6>

                                <div v-if="optionsExpanded == 'check'">
                                    <div class="form-group small mt-1 ml-3">
                                        <label class="control-label" for="check_no">
                                            <span class="fas fa-fw fa-money-check-alt"></span>
                                            Check # <span class="text-danger">*</span>
                                        </label>
                                        <input type="text" class="form-control form-control-sm"
                                            v-model="checkNo" id="check_no">
                                    </div>

                                    <div class="small ml-3">
                                        <strong>
                                            <span class="fas fa-info-circle"></span> Instructions Provided By Host :
                                        </strong>
                                        <p class="preserve-new-lines m-0">{{ meet.mailed_check_instructions }}</p>
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
                                    v-model="useBalance" @change="recalculateTotals">
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

                                <div v-else-if="chosenMethod.type == 'paypal'">
                                    PayPal
                                </div>

                                <div v-else>
                                    Mailed Check #{{ checkNo }}
                                </div>
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

                        <div v-if="summary.handling > 0" class="row">
                            <div class="col">
                                <span class="fas fa-fw fa-server"></span> Handling Fee :
                            </div>
                            <div class="col">
                                ${{ numberFormat(summary.handling) }}
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

                        <div v-if="summary.processor > 0" class="row">
                            <div class="col">
                                <span class="fas fa-fw fa-file-invoice"></span> Payment Processor Fee :
                            </div>
                            <div class="col">
                                ${{ numberFormat(summary.processor) }}
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
                                <div class="d-inline-block">
                                    <button class="btn btn-sm btn-success"
                                        @click="submitRegistration">
                                        <span class="fas fa-file-invoice-dollar"></span> Proceed To Payment
                                    </button>
                                </div>
                            </div>
                        </div>
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
        name: 'RegistrationPayment',
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
            transactionId: {
                type: Number,
                default: null,
            },
            subtotal: {
                type: Number,
                default: null,
            },
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
                paymentOptions: null,
                meet: null,
                onetimeach: null,
                routingNumber: '',
                accountNumber: '',
                accountType: 's', // Default to savings
                accountName: '',
                card_name: '',
                card_number: '',
                card_expire: '',
                card_cvc: '',
                save_cc_card: false,
                onetimecc: null
            }
        },
        watch: {
        },
        methods: {
            recalculateTotals() {
                if ((this.paymentOptions == null) || (this.chosenMethod == null))
                    return;

                this.summary = {
                    subtotal: this.subtotal,
                    own_meet_refund: (this.paymentOptions.is_own ? this.subtotal : 0),
                    handling: 0,
                    used_balance: 0,
                    processor: 0,
                    total: 0,
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
            useOneTimeCC() {
                this.chosenMethod = {
                    fee: this.paymentOptions.methods.card.fee,
                    mode: this.paymentOptions.methods.card.mode,
                    type: 'onetimecc'
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
            usePaypal() {
                this.chosenMethod = {
                    fee: this.paymentOptions.methods.paypal.fee,
                    mode: this.paymentOptions.methods.paypal.mode,
                    type: 'paypal'
                };
                this.optionsExpanded = 'paypal';
                this.recalculateTotals();
            },

            useCheck() {
                this.chosenMethod = {
                    fee: this.paymentOptions.methods.check.fee,
                    mode: this.paymentOptions.methods.check.mode,
                    type: 'check'
                };
                this.useBalance = false;
                this.recalculateTotals();
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
                if(this.chosenMethod.type == 'onetimecc')
                {
                    this.onetimecc = {
                        card_name: this.card_name,
                        card_number: this.card_number,
                        card_expire: this.card_expire,
                        card_cvc: this.card_cvc,
                        save_cc_card: this.save_cc_card
                    }
                }
                this.confirmAction(
                    'Are you sure you want to proceed with the payment ?',
                    'orange',
                    'fas fa-question-circle',
                    () => {
                        this.isProcessingPayment = true;
                        axios.post(
                            '/api/gym/' + this.gymId + '/registration/' + this.registrationId +
                            '/pay/' + this.transactionId,
                            {
                                '__managed': this.managed,
                                summary: this.summary,
                                method: {
                                    type: this.chosenMethod.type,
                                    id: (this.chosenMethod.id ? this.chosenMethod.id : null)
                                },
                                use_balance: this.useBalance,
                                onetimeach: this.onetimeach,
                                onetimecc: this.onetimecc
                            }
                        ).then(result => {
                            this.paymentProcessedMessage = result.data.message;
                        }).catch(error => {
                            //console.log(this.registrationId);
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
            },

            cardNumberInput() {
                this.card_number = this.card_number.replace(/ /g,'');
                this.card_number = this.card_number ? this.card_number.match(/.{1,4}/g).join(' ') : '';
            },
            cardExpiryInput() {
                this.card_expire = this.card_expire.replace(/\//g,'');
                this.card_expire = this.card_expire ? this.card_expire.match(/.{1,2}/g).join('/') : '';
                let Nowyear = new Date().getFullYear();
                // month cannot be greater than 12 - if it is, set it to 12
                if (this.card_expire.length > 4) {
                    let month = this.card_expire.slice(0, 2);
                    let year = this.card_expire.slice(3, 5);
                    if (parseInt(month) > 12) {
                        month = '12';
                    }
                    if (parseInt(year) < parseInt(Nowyear.toString().slice(2, 4))) {
                        year = Nowyear.toString().slice(2, 4);
                    }
                    this.card_expire = month +'/'+ year;
                }
            }
        },
        beforeMount() {
            this.isLoading = true;
            axios.get('/api/registration/payment/options/' + this.meetId + '/' + this.gymId, {
                'params': {
                    '__managed': this.managed
                }
            }).then(result => {
                this.isLoading = true;
                axios.get('/api/app/meet/' + this.meetId).then(result => {
                    if (result.data.meets.length != 1)
                        throw 'Something went wrong while loading this meet\'s details.';

                    this.meet = result.data.meets[0];
                }).catch(error => {
                    let msg = '';
                    if (error.response) {
                        msg = error.response.data.message;
                    } else if (error.request) {
                        msg = 'No server response.';
                    } else {
                        msg = error.message;
                    }
                    this.errorMessage = msg + '<br/>Please reload this page.';
                    this.isError = true;
                }).finally(() => {
                    this.isLoading = false;
                });
                this.paymentOptions = result.data;
                this.chosenMethod = null;
            }).catch(error => {
                let msg = '';
                if (error.response) {
                    msg = error.response.data.message;
                } else if (error.request) {
                    msg = 'No server response.';
                } else {
                    msg = error.message;
                }

                this.errorMessage = msg;
                this.isError = true;
            }).finally(() => {
                this.isLoading = false;
            });
        }
    }
</script>
