require('../main');

import Datepicker from 'vuejs-datepicker';

window.Vue = require('vue');

const app = new Vue({
    el: '#app',
    data: {
        date: new Date(),
        usagDatesDisabled: true,
    },
    components: {
        'datepicker': Datepicker
    }
});


$(document).ready(e => {

    let _busy = false;

    setupCoachForm();

    function setupCoachForm() {
        let checkboxes = $('.coach-membership-checkbox');

        checkboxes.each((e, v) => {
            let checkbox = $(v);
            let input = $('input[name="' + checkbox.data('body') + '_no"]');
            let fieldContainer = $('#' + checkbox.data('body') + '-membership-fields');

            if (input.val()) {
                checkbox.prop('checked', true);
                app.usagDatesDisabled = false;

                fieldContainer.find('input[data-body], select[data-body], checkbox[data-body]')
                                .prop('disabled', false);
            }
        });

        checkboxes.click(e => {
            let checkbox = $(e.currentTarget);
            let fieldContainer = $('#' + checkbox.data('body') + '-membership-fields');

            app.usagDatesDisabled = !checkbox.prop('checked');
            fieldContainer.find('input[data-body], select[data-body], checkbox[data-body]')
                            .prop('disabled', !checkbox.prop('checked'));
        });
    }
});
