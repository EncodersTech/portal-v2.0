require('../main');

import Datepicker from 'vuejs-datepicker';

window.Vue = require('vue');

const app = new Vue({
    el: '#app',
    data: {
        state: {
            date: new Date()
        }
    },
    components: {
        'datepicker': Datepicker
    }
});


$(document).ready(e => {

    let _busy = false;

    require('./include/athlete-memberhsip-checkboxes');

    $('#gender').change(e => {
        let leo_size = $('#leo_size_id');
        let selected = e.currentTarget.value;
        let enable = (selected == 'female');
        // leo_size.attr('disabled', !enable);

        if (selected != '') {
            let disallowedCategories = $('optgroup[data-' + selected +  '="0"]');
            let allowedCategories = $('optgroup[data-' + selected +  '="1"]');

            let disallowedLevels = $('option[data-' + selected +  '="0"]');
            let allowedLevels = $('option[data-' + selected +  '="1"]');

            disallowedCategories.hide();
            allowedCategories.show();

            disallowedLevels.hide();
            allowedLevels.show();

            disallowedLevels.each((e,itm) => {
                if (itm.selected)
                    itm.parentElement.value = '';
            });
        }
    });

    $('#gender').change();
});
