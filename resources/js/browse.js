require('./main');

window.Vue = require('vue');

Vue.component('ag-browse-meet-list', require('./components/BrowseMeetList.vue').default);
// Vue.component('Calendar', require('./components/Meet/Calendar.vue').default);
import Calendar from './components/Meet/Calendar.vue';
$(document).ready(e => {

    let _busy = false;

    const app = new Vue({
        el: '#app',
        components: {
            Calendar,
        },
        data: {    
            
        },
        computed: {
            
        },
        methods: {
            
        }
    });
});