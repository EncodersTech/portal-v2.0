require('../main');

window.Vue = require('vue');
import VueCurrencyInput from 'vue-currency-input'

Vue.use(VueCurrencyInput)

$(document).ready(() => {

    let _busy = false;

    const app = new Vue({
        el: '#app',
        data: {
            isLoading: false,
            errorMessage: null,
            transaction: null,
            transactions: [],
            bankAccounts: null,
            withdrawalFees: {},
            withdrawal: {
                account: '',
                amount: 0.0,
                confirm: '',
                fee: 0.0,
                total: 0.0,
                featured_meet: featuredMeetFee,
                net_withdraw_amount: 0.0
            },
            sortColumn: 'created_at',
            sortDirection: 'up',
        },
        watch: {
            withdrawal: {
                deep: true,
                handler(withdrawal) {
                    // let lowest = null;
                    //
                    // if (withdrawal.amount > 0) {
                    //     for (let i in this.withdrawalFees) {
                    //         if (withdrawal.amount < i) {
                    //             if ((lowest === null) || (i < lowest))
                    //                 lowest = i;
                    //         }
                    //     }
                    // }

                    // if (lowest !== null)
                    //     withdrawal.fee = this.withdrawalFees[lowest];
                    // else
                    //     withdrawal.fee = 0;

                    withdrawal.total = withdrawal.amount + withdrawal.fee;
                    if (withdrawal.total > 0) {
                        withdrawal.net_withdraw_amount = withdrawal.total - withdrawal.featured_meet;
                    } else {
                        withdrawal.net_withdraw_amount = 0.0;
                    }
                }
            }
        },
        computed: {
            constants() {
                return {
                    balance: {
                        transactions: {
                            types: {
                                1: 'Registration Revenue',
                                2: 'Dwolla Verification Fee',
                                3: 'Admin Adjustment',
                                4: 'Registration Payment',
                                5: 'Check Confirmation — Handling Fee',
                                99: 'Withdrawal',
                                6: 'Admin',
                                Revenue: 1,
                                Dwolla: 2,
                                Adjustment: 3,
                                Payment: 4,
                                Check: 5,
                                Withdrawal: 99,
                            },
                            statuses: {
                                1: 'Pending',
                                2: 'Cleared',
                                3: 'Unconfirmed',
                                4: 'Failed',
                                Pending: 1,
                                Cleared: 2,
                                Unconfirmed: 3,
                                Failed: 4,
                            },
                        },
                    },
                };
            },
        },
        methods: {
            sortBy(column) {
                if (column == this.sortColumn) {
                    this.sortDirection = (this.sortDirection == 'up' ? 'down' : 'up');
                } else {
                    this.sortColumn = column;
                    this.sortDirection = 'up';
                }
                this.sortChanged();
            },

            sortChanged() {
                if (this.transactions.length < 1)
                    return

                this.transactions.sort((a, b) => {
                    let va = a[this.sortColumn];
                    let vb = b[this.sortColumn];
                    if (this.sortColumn == 'total'){
                        va = parseFloat(va);
                        vb = parseFloat(vb)
                    }
                    if (va < vb)
                        return -1 * (this.sortDirection == 'up' ? 1 : -1);

                    if (va > vb)
                        return 1 * (this.sortDirection == 'up' ? 1 : -1);

                    return 0;
                });

            },
            currencyFormat(v) {
                return Utils.toFloat(v).toLocaleString(
                    'us',
                    {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2
                    }
                );
            },

            loadWithdrawalFees(result) {
                if (!result)
                    return axios.get('/api/app/withdrawal/fees');
                this.withdrawalFees = result.data;
            },
            loadBankAccounts(result) {
                if (!result)
                    return axios.get('/api/user/bank/accounts');
                
                // let bankSt = [];
                // for (var i in result.data.bank_accounts) {
                //     if(result.data.bank_accounts[i].status == 'verified')
                //     bankSt[i] = result.data.bank_accounts[i];
                // }
                // this.bankAccounts = bankSt;
                this.bankAccounts = result.data.bank_accounts;
                this.bankAccounts = result.data.bank_accounts.filter(
                    ba => (ba.account_type != 'balance') && (ba.status == 'verified')
                );
            },

            loadBalanceTransactions(result) {
                if (!result)
                    return axios.get('/api/user/balance/transactions');

                this.transactions = result.data.transactions;
                for (let i in this.transactions) {
                    let tx = this.transactions[i];

                    tx.created_at = Moment(tx.created_at);
                    tx.created_at_display = tx.created_at.format('MM/DD/YYYY hh:mm:ss A');

                    tx.updated_at = Moment(tx.updated_at);
                    tx.updated_at_display = tx.updated_at.format('MM/DD/YYYY hh:mm:ss A');

                    tx.clears_on = Moment(tx.clears_on);
                    tx.clears_on_display = tx.clears_on.format('MM/DD/YYYY hh:mm:ss A');
                }
            },

            requestWithdrawal() {
               try {
                    $('#modal-withdraw-request').modal('hide');

                    let withdrawal = _.cloneDeep(this.withdrawal);
                    this.withdrawal.account = '';
                    this.withdrawal.amount = 0.0;
                    this.withdrawal.confirm = '';
                    this.withdrawal.fee = 0.0;
                    this.withdrawal.total = 0.0;

                    if (withdrawal.confirm != 'CONFIRM') {
                        this.showAlert(
                            'You need to type CONFIRM in the dedicated field.',
                            'Withdraw',
                            'red',
                            'fas fa-exclamation-triangle'
                        );
                        return;
                    }

                    this.isLoading = true;

                    axios.post(
                        '/api/user/balance/withdraw', {
                            account: withdrawal.account,
                            amount: withdrawal.amount,
                            total: withdrawal.total,
                        }
                    ).then(result => {
                        this.showAlert(
                            'Your withdrawal is being processed',
                            'Withdrawal',
                            'green',
                            'fas fa-check-circle',
                            () => Utils.refresh(),
                        );
                    }).catch(error => {
                        let msg = '';
                        if (error.response) {
                            msg = error.response.data.message;
                        } else if (error.request) {
                            msg = 'No server response.';
                        } else if (error.message){
                            msg = error.message;
                        } else {
                            msg = error
                        }
                        this.showAlert(
                            msg,
                            'Withdraw',
                            'red',
                            'fas fa-exclamation-triangle'
                        );
                    }).finally(() => {
                        this.isLoading = false;
                    });
                } catch (error) {
                    this.showAlert('Something went wrong', 'Oops !', 'red', 'fas fa-exclamation-triangle');
                }
            },

            showTransactionDetails(tx) {
                this.transaction = tx;
                if (tx.type == this.constants.balance.transactions.types.Withdrawal) {
                    $('#withdraw_info').show();
                }
                else
                {
                    $('#withdraw_info').hide();
                }
                $('#modal-transaction-details').modal('show');
            },

            numberFormat(n) {
                try {
                    let fee = Utils.toFloat(n);
                    return (fee === null ? n : fee.toFixed(2));
                } catch (e) {
                    return n;
                }
            },

            showAlert(msg, title, color, icon, callback) {
                $.alert({
                    title: title,
                    content: msg,
                    icon: icon,
                    type: color,
                    typeAnimated: true,
                    buttons: {
                        ok:  {
                            text: 'Got it !',
                            btnClass: 'btn-' + color,
                            action: function () {
                                if (callback)
                                    callback();
                            }
                        }
                    }
                });
            },
        },
        beforeMount() {
            this.isLoading = true;

            Promise.all([
                this.loadWithdrawalFees(),
                this.loadBankAccounts(),
                this.loadBalanceTransactions(),
            ]).then(results => {
                let i = 0;
                this.loadWithdrawalFees(results[i++]),
                this.loadBankAccounts(results[i++]);
                this.loadBalanceTransactions(results[i++]);
            }).catch(error => {
                let msg = '';
                if (error.response) {
                    msg = error.response.data.message;
                } else if (error.request) {
                    msg = 'No server response.';
                } else if (error.message) {
                    msg = error.message;
                } else {
                    msg = error;
                }
                this.errorMessage = msg + '<br/>Please reload this page.';
            }).finally(() => {
                this.isLoading = false;
                this.sortBy('created_at');
            });
        }
    });
});
