require('../../main');

window.Vue = require('vue');

Vue.component('ag-meet-files', require('../../components/Meet/MeetFiles.vue').default);

$(document).ready(e => {   

    const app = new Vue({
        el: '#app',
        data: {
        },
        computed: {
        },
        methods: {
        },
        mounted() {
        }
    });
});