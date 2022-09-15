require('../main');

window.Vue = require('vue');

Vue.component('ag-sanction-details', require('../components/USAG/SanctionDetails.vue').default);
Vue.component('ag-reservation-details', require('../components/USAG/ReservationDetails.vue').default);

$(document).ready(e => {
    const app = new Vue({
        el: '#app',
    });
});