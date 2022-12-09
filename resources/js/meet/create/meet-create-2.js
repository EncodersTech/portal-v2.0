require('../../main');

window.Vue = require('vue');

Vue.component('datepicker', require('vuejs-datepicker').default);

$(document).ready(e => {   

    const app = new Vue({
        el: '#app',
        data: {
            today: Moment().tz("America/New_York").toDate(),
            startDate: this.today,
            endDate: this.today,
            scratchDate: this.today,
            lateDisabled: false,
            lateStartDate: this.today,
            lateEndDate: this.today,

            registration_first_discount_end_date: this.today,

            registration_first_discount_end_date_disabled: true,
            registration_first_discount_amount_disabled: true,

            allow_second_discount_disabled: true,
            allow_third_discount_disabled: true,
        },
        methods: {
            startDateChanged(v) {
                this.endDate = Moment(v).add(1, 'days').toDate();
            },
            lateStartDateChanged(v) {
                this.lateEndDate = Moment(v).add(1, 'days').toDate();
            },
        },
        mounted() {
            let formats = ['MM/DD/YYYY', 'YYYY-MM-DD HH::mm:ss'];
            if(typeof(this.$refs.oldStartDate) !== 'undefined')
            {
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
    
                let oldScratch = this.$refs.oldScratchDate.dataset.value;
                if (oldScratch)
                    this.scratchDate = Moment.tz(oldScratch, formats, "America/New_York").toDate();
                else
                    this.scratchDate = Moment(this.endDate).add(1, 'days').toDate();
    
                let oldLateStart = this.$refs.oldLateStartDate.dataset.value;
                if (oldLateStart)
                    this.lateStartDate = Moment.tz(oldLateStart, formats, "America/New_York").toDate();
                else
                    this.lateStartDate = Moment(this.endDate).add(1, 'days').toDate();
    
                let oldLateEnd = this.$refs.oldLateEndDate.dataset.value;
                if (oldLateEnd)
                    this.lateEndDate = Moment.tz(oldLateEnd, formats, "America/New_York").toDate();
                else
                    this.lateEndDate = Moment(this.lateStartDate).add(1, 'days').toDate();
    
                let registration_first_discount_end_date = this.$refs.registration_first_discount_end_date.dataset.value;
                if (registration_first_discount_end_date)
                    this.registration_first_discount_end_date = Moment.tz(registration_first_discount_end_date, formats, "America/New_York").toDate();
                else
                    this.registration_first_discount_end_date = Moment(this.today).add(1, 'days').toDate();
            }
            
        }
    });

    setupLateRegistrationBehavior();
    setupAthleteLimitBehavior();
    setupMailedChecksBehavior();

    function setupLateRegistrationBehavior() {
        let checkbox = $('#allow_late_registration');
        let late_reg_fee = $('#late_registration_fee');

        checkbox.change(e => {
            let checked = checkbox.prop('checked');
        
            late_reg_fee.prop('disabled', !checked);
            app.lateDisabled = !checked;
        });

        checkbox.change();
    }

    function setupAthleteLimitBehavior() {
        let checkbox = $('#athlete_limit_checkbox');
        let athlete_limit = $('#athlete_limit');

        checkbox.change(e => {
            let checked = checkbox.prop('checked');
        
            athlete_limit.prop('disabled', !checked);
        });

        checkbox.change();
    }

    function setupMailedChecksBehavior() {
        let instructions = $('#mailed_check_instructions');
        let checkbox = $('#accept_mailed_check');

        checkbox.change(e => {
            let checked = checkbox.prop('checked');
        
            instructions.prop('disabled', !checked);
        });

        checkbox.change();
    }
    setupFirstDiscountRegistrationBehavior();
    function setupFirstDiscountRegistrationBehavior() {
        let checkbox = $('#registration_first_discount_is_enable');
        let registration_first_discount_end_date = $('#registration_first_discount_end_date');
        let registration_first_discount_amount = $('#registration_first_discount_amount_id');
        checkbox.change(e => {
            let checked = checkbox.prop('checked');
            registration_first_discount_end_date.prop('disabled', !checked);
            registration_first_discount_amount.prop('disabled', !checked);
            app.registration_first_discount_end_date_disabled = !checked;
            app.registration_first_discount_amount_disabled = !checked;
        });
        checkbox.change();
    }
});