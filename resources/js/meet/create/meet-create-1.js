require('../../main');

window.Vue = require('vue');

Vue.component('datepicker', require('vuejs-datepicker').default);
Vue.component('admissions', require('../../components/Meet/Admissions.vue').default);

$(document).ready(e => {   

    const app = new Vue({
        el: '#app',
        data: {
            today: Moment().tz("America/New_York").toDate(),
            startDate: this.today,
            endDate: this.today,
        },
        methods: {
            startDateChanged(v) {
                this.endDate = Moment(v).add(1, 'days').toDate();
            }
        },
        mounted() {
            let formats = ['MM/DD/YYYY', 'YYYY-MM-DD HH::mm:ss'];
            let oldStart = this.$refs.oldStartDate.dataset.value;
            if (oldStart)
                this.startDate = Moment.tz(oldStart, formats, "America/New_York").toDate();
            else
                this.startDate = Moment(this.today).add(1, 'days').toDate();

            let oldEnd = this.$refs.oldEndDate.dataset.value;
            if (oldEnd)
                this.endDate = Moment.tz(oldEnd, formats, "America/New_York").toDate();
            else
                this.endDate = Moment(this.startDate).add(1, 'days').toDate();
        }
    });

    setupVenueAddressBehavior();
    setupClothingSizeBehavior();

    function setupVenueAddressBehavior() {
        let venueButton = $('#venue_use_gym_address');
        let venueGymInputs = $('input[data-venue-gym], select[data-venue-gym]');
        
        venueButton.click(e => {
            venueGymInputs.each((i, item) => {
                item.value = item.dataset.venueGym;
            });
        });
    }

    function setupClothingSizeBehavior() {

        let tshirtChartCheckbox = $('#tshirt_size_chart_checkbox');
        let tshirtChartSelect = $('#tshirt_size_chart_id');
        let leoChartCheckbox = $('#leo_size_chart_checkbox');
        let leoChartSelect = $('#leo_size_chart_id');

        tshirtChartCheckbox.change(e => {
            tshirtChartSelect.prop('disabled', !tshirtChartCheckbox.prop('checked'));
        });

        leoChartCheckbox.change(e => {
            leoChartSelect.prop('disabled', !leoChartCheckbox.prop('checked'));
        });

        tshirtChartCheckbox.change();
        leoChartCheckbox.change();
    }
});