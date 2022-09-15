require('../main');

window.Vue = require('vue');

Vue.component('ag-joined-meet-list', require('../components/Gym/JoinedMeetList.vue').default);

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