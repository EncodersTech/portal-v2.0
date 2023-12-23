require('../main');

window.Vue = require('vue');

Vue.component('ag-registration-details', require('../components/Registration/register/RegistrationDetails.vue').default);
Vue.component('ag-registration-payment', require('../components/Registration/register/RegistrationPayment.vue').default);

$(document).ready(e => {

    const app = new Vue({
        el: '#app',
        data: {
            isError: false,
            errorMessage: '',
            managed: window._managed_account,
            meetId: window.meetId,
            gymId: '',
            step: 1,
            registrationData: null,
            paymentOptions: null,
            paymentOptionsLoading: false,

            hit: 0
        },
        watch: {
            gymId() {
                if (this.gymId) {
                    this.paymentOptionsLoading = true;
                    this.loadPaymentOptions();
                }
            }
        },
        methods: {
            loadPaymentOptions: _.debounce(function () {
                this.paymentOptionsLoading = true;
                axios.get('/api/registration/payment/options/' + this.meetId + '/' + this.gymId, {
                    'params': {
                        '__managed': this.managed
                    }
                }).then(result => {
                    this.paymentOptions = result.data;
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
                    this.paymentOptionsLoading = false;
                });
            }, 2500),

            firstStep(registrationData) {
                this.registrationData = registrationData;
                this.step++;
            },

            backToFirstStep() {
                this.step--;
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
        },
    });

    if ($('#meet-public-url-copy').length > 0) {
        var meetUrl = new ClipboardJS('#meet-public-url-copy');

        meetUrl.on('success', function(e) {
            switchCopySuccessMessage(true, 'Copied !');
            e.clearSelection();
            _.debounce(switchCopySuccessMessage, 1500)(false, '');
        });

        meetUrl.on('error', function(e) {
            switchCopySuccessMessage(true, 'Ctrl+C to copy !');
            _.debounce(switchCopySuccessMessage, 2500)(false, '');
        });

        function switchCopySuccessMessage(shown, msg) {
            let text = $('#meet-public-url-copy-success')
            let elem = $('#meet-public-url-copy-success-message');

            text.html(msg);
            elem.css('visibility', shown ? 'visible' : 'hidden');
        }
    }
});
