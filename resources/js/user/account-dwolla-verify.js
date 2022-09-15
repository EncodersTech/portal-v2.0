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

    $('#dwolla_document').change(e => {
        let input = e.currentTarget;
        let label = $('label[for="dwolla_document"]');

        if (input.files && (input.files.length > 0))
            label.html(input.files[0].name);
    });
});