require('../main');

window.Vue = require('vue');

Vue.component('ag-registration-repayment', require('../components/Registration/RegistrationRepayment.vue').default);

$(document).ready(e => {

    const app = new Vue({
        el: '#app',
        data: {
        },
        watch: {
        },
        methods: {
            

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
        beforeMount() {
            //this.loadPaymentOptions();
        }
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