require('./main');

window.Vue = require('vue');

Vue.component('ag-browse-meet-list', require('./components/BrowseMeetList.vue').default);

$(document).ready(e => {

    let _busy = false;

    const app = new Vue({
        el: '#app',
        data: {    
            
        },
        computed: {
            
        },
        methods: {
            
        }
    });
});