require('../main');

window.Vue = require('vue');

Vue.component('ag-sanction-notifications', require('../components/include/SanctionNotifications.vue').default);
Vue.component('ag-reservations-notifications', require('../components/include/ReservationsNotifications.vue').default);

$(document).ready(e => {

    const app = new Vue({
        el: '#app',
        data: {
        },
    });
});
