require('../main');

window.Vue = require('vue');
// Vue.component('YourComponent', require('../components/Meet/Calendar.vue').default);
// import Calendar from '../../../public/assets/admin/js/toast-ui-vue-calendar';
import Calendar from '../components/Meet/Calendar.vue';
$(document).ready(e => {  
    new Vue({
        el: '#app',
        components: {
            Calendar,
        },
      });
});