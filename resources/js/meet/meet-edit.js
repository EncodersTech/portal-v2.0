require('../main');

window.Vue = require('vue');

Vue.component('datepicker', require('vuejs-datepicker').default);
Vue.component('admissions', require('../components/Meet/Admissions.vue').default);
Vue.component('ag-meet-categories', require('../components/Meet/MeetCategories.vue').default);
Vue.component('ag-meet-levels', require('../components/Meet/MeetLevels.vue').default);
Vue.component('ag-meet-files', require('../components/Meet/MeetFiles.vue').default);

$(document).ready(e => {
    let _busy = false;
    let restricted_edit = ($('#restricted-mode-enabled').length > 0);

    const app = new Vue({
        el: '#app',
        data: {
            today: Moment().tz("America/New_York").toDate(),
            startDate: this.today,
            endDate: this.today,
            registrationFirstDiscountEndDate: this.today,

            registrationStartDate: this.today,
            registrationEndDate: this.today,
            registrationScratchDate: this.today,
            lateDisabled: false,
            lateStartDate: this.today,
            lateEndDate: this.today,
            
            registration_first_discount_end_date: this.today,
            registration_second_discount_end_date: this.today,
            registration_third_discount_end_date: this.today,

            registration_first_discount_end_date_disabled: true,
            registration_first_discount_amount_disabled: true,
            registration_second_discount_end_date_disabled: true,
            registration_second_discount_amount_disabled: true,
            registration_third_discount_end_date_disabled: true,
            registration_third_discount_amount_disabled: true,

            allow_second_discount_disabled: true,
            allow_third_discount_disabled: true,
            
            isError: false,
            isLoading: false,
            errorMessage: '',
            bodies: [],
            selected_categories: [],
            restricted: restricted_edit,
            sanc: [],
            sanc_body_no:[],
            is_selected_body: []
        },
        computed: {
            bodyCategories() {
                let result = {};
                let locked = false;
                for (const i in this.bodies) {
                    const body = this.bodies[i];
                    entries = [];
                    locked = false;
                    for (const j in body.categories) {
                        const category = body.categories[j];
                        if(category.locked == true)
                        {
                            locked = true;
                        }
                        entries.push({
                            id: category.id,
                            name: category.name,
                            path: category.path
                        });
                    }
                    result[body.name] = {
                        name: body.name,
                        id: body.id,
                        categories: entries,
                        sanction_name: this.sanc_body_no,
                        islocked: locked
                    };
                }
                return result;
            },
        },
        methods: {
            startDateChanged(v) {
                this.endDate = Moment(v).add(1, 'days').toDate();
            },

            registrationStartDateChanged(v) {
                this.registrationEndDate = Moment(v).add(1, 'days').toDate();
            },
            lateStartDateChanged(v) {
                this.lateEndDate = Moment(v).add(1, 'days').toDate();
            },
            registration_firstDateChanged(v){
                this.registration_second_discount_end_date=Moment(v).add(1, 'days').toDate();
            },
            registration_secondDateChanged(v){
                this.registration_third_discount_end_date=Moment(v).add(1, 'days').toDate();
            },
            onMeetCategoriesChanged(val) {
                this.selected_categories = val;

                this.sanc.forEach(function (e) {
                    $('#sanc-' + e).hide();
                }); 
                this.sanc = []

                this.selected_categories.forEach(e=>{
                    if(e.body_id != 1)
                    {
                        this.sanc.push(e.body_id);
                    }
                });

                this.sanc.forEach(e=>{
                    $('#sanc-'+e).show();
                });
            },
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


            let oldRegistrationStart = this.$refs.oldRegistrationStartDate.dataset.value;
            if (oldRegistrationStart)
                this.registrationStartDate = Moment.tz(oldRegistrationStart, formats, "America/New_York").toDate();
            else
                this.registrationStartDate = Moment(this.today).add(1, 'days').toDate();

            let oldRegistrationEnd = this.$refs.oldRegistrationEndDate.dataset.value;
            if (oldRegistrationEnd)
                this.registrationEndDate = Moment.tz(oldRegistrationEnd, formats, "America/New_York").toDate();
            else
                this.registrationEndDate = Moment(this.registrationStartDate).add(1, 'days').toDate();

            let oldRegistrationScratch = this.$refs.oldRegistrationScratchDate.dataset.value;
            if (oldRegistrationScratch)
                this.registrationScratchDate = Moment.tz(oldRegistrationScratch, formats, "America/New_York").toDate();
            else
                this.registrationScratchDate = Moment(this.registrationEndDate).add(1, 'days').toDate();

            let oldLateStart = this.$refs.oldLateStartDate.dataset.value;
            if (oldLateStart)
                this.lateStartDate = Moment.tz(oldLateStart, formats, "America/New_York").toDate();
            else
                this.lateStartDate = Moment(this.registrationEndDate).add(1, 'days').toDate();

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
                
            let registration_second_discount_end_date = this.$refs.registration_second_discount_end_date.dataset.value;
            if (registration_second_discount_end_date)
                this.registration_second_discount_end_date = Moment.tz(registration_second_discount_end_date, formats, "America/New_York").toDate();
            else
                this.registration_second_discount_end_date = Moment(this.registration_first_discount_end_date).add(1, 'days').toDate();

            let registration_third_discount_end_date = this.$refs.registration_third_discount_end_date.dataset.value;
            if (registration_third_discount_end_date)
                this.registration_third_discount_end_date = Moment.tz(registration_third_discount_end_date, formats, "America/New_York").toDate();
            else
                this.registration_third_discount_end_date = Moment(this.registration_second_discount_end_date).add(1, 'days').toDate();

            this.isLoading = true;
            axios.get('/api/app/levels').then(result => {
                for (let bodyInitialism in result.data.levels) {
                    let body = result.data.levels[bodyInitialism];
                    body.name = bodyInitialism;

                    for (let categoryName in body.categories) {
                        let category = body.categories[categoryName];
                        category.name = categoryName;
                    }
                    this.bodies.push(body);
                }
            }).catch(error => {
                let msg = '';
                if (error.response) {
                    msg = error.response.data.message;
                } else if (error.request) {
                    msg = 'No server response.';
                } else {
                    msg = error.message;
                }
                this.errorMessage = msg + '<br/>Please reload this page.';
                this.isError = true;
            }).finally(() => {
                this.isLoading = false;
            });
        }
    });

    setupMeetPublishUnpublishBehavior();
    setupVenueAddressBehavior();
    setupClothingSizeBehavior();
    setupMeetPictureHandler();
    setupLateRegistrationBehavior();
    setupAthleteLimitBehavior();
    setupCompetitionFormatBehavior();
    setupPrimaryInfoBehavior();
    setupSecondaryInfoBehavior();
    setupMailedChecksBehavior();

    function setupClothingSizeBehavior() {
        if (restricted_edit)
            return;

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

    function setupVenueAddressBehavior() {
        let venueButton = $('#venue_use_gym_address');
        let venueGymInputs = $('input[data-venue-gym], select[data-venue-gym]');

        venueButton.click(e => {
            venueGymInputs.each((i, item) => {
                item.value = item.dataset.venueGym;
            });
        });
    }

    function setupMeetPictureHandler() {
        let profilePictureInput = $('#profile-picture');

        $('#profile-picture-change').click(e => {
            changeProfilePicture();
        });

        $('#profile-picture-display').click(e => {
            changeProfilePicture();
        });

        profilePictureInput.change(e => {
            if (profilePictureInput.val())
                $('#profile-picture-change-form').submit();
        });

        function changeProfilePicture() {
            if (_busy)
                return;
            profilePictureInput.click();
        }
    }

    function setupMeetPublishUnpublishBehavior() {
        let publishButton = $('#meet-publish-button');
        let unpublishButton = $('#meet-unpublish-button');
        let publishForm = $('#meet-publish-form');
        let unpublishForm = $('#meet-unpublish-form');

        publishButton.click(e => {
            confirmAction(
                'After it\'s published, this meet cannot be unpublished once it has registrations.<br/><br/>' +
                '<strong>Do you really want to publish this meet ?</strong>',
                'red',
                'fas fa-eye',
                () => {
                    if (publishButton.data('past') > 0) {
                        $.confirm({
                            title: '',
                            content: 'Would you like to alert past registrants that this meet is open?<br/><br/>',
                            // icon: 'fas fa-eye',
                            type: 'red',
                            typeAnimated: true,
                            buttons: {
                                no: function () {
                                    _busy = false;
                                    this.close();
                                    publishForm.submit();
                                },
                                confirm:  {
                                    text: 'Yes',
                                    btnClass: 'btn-red',
                                    action: function () {
                                        _busy = false;
                                        getPastMeets('#modal-show-past-meets',publishForm)

                                    }
                                }
                            }
                        });
                    } else {
                        publishForm.submit();
                    }
                }
            );
        });

        unpublishButton.click(e => {
            confirmAction(
                'Once unpublished, this meet wil not show up in the browse meet seciton anymore.<br/><br/>' +
                '<strong>Do you really want to unpublish this meet ?</strong>',
                'red',
                'fas fa-eye-slash',
                () => {
                    unpublishForm.submit();
                }
            );
        });
    }

    function getPastMeets(modal,publish){
        $(modal).modal('show');
        let spinner = $('#modal-show-past-meets-spinner');
        let pastMeetsForm = $('#past-meets-form');
        $('.modal-show-past-meets-close').click(e => {
            if (_busy)
                return;
            $(modal).modal('hide');
        });

        $('.send-mail').click(e => {
            if (_busy)
                return;
            if ($('#modal-show-past-meets-element input:checkbox:checked').length) {
                spinner.show();
                $.ajax({
                    url : '/send-mail/past-meets',
                    method : 'post',
                    data : pastMeetsForm.serialize(),
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success : function (result){
                        if (result.success){
                            publish.submit();
                            spinner.hide();
                        }
                    }
                })
            } else {
                $('#modal-show-past-meets-errors').show().html('')
                $('#modal-show-past-meets-errors').text('Please select one meet')
            }
        });

        setInterval(function (){
            $('#modal-show-past-meets-errors').hide().html('')
        },5000)
    }

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
        if (restricted_edit)
            return;

        let checkbox = $('#athlete_limit_checkbox');
        let athlete_limit = $('#athlete_limit');

        checkbox.change(e => {
            athlete_limit.prop('disabled', !checkbox.prop('checked'));
        });

        checkbox.change();
    }

    function setupCompetitionFormatBehavior() {
        let select = $('#meet_competition_format_id');
        let otherField = $('#meet_competition_format_other');

        select.change(e => {
            let disable = select.val() != select.data('other');
            otherField.prop('disabled', disable);

            if (disable)
                otherField.val('');
        });

        select.change();
    }

    function setupPrimaryInfoBehavior() {
        let primaryButton = $('#primary_use_own_info');
        let primaryInputs = $('input[data-primary-info], select[data-primary-info]');

        primaryButton.click(e => {
            primaryInputs.each((i, item) => {
                item.value = item.dataset.primaryInfo;
            });
        });
    }

    function setupSecondaryInfoBehavior() {
        let checkbox = $('#secondary_contact');
        let secondaryFields = $('.secondary-info-fields input, .secondary-info-fields select');
        let secondarySelect = $('#secondary_use_own_info');

        checkbox.change(e => {
            secondaryFields.prop('disabled', !checkbox.prop('checked'));
        });
        checkbox.change();

        secondarySelect.change(e => {
            if (secondarySelect.val()) {
                let option = secondarySelect.find(':selected').first()[0];
                if (option) {
                    $('#secondary_contact_first_name').val(option.dataset.secondaryFirst);
                    $('#secondary_contact_last_name').val(option.dataset.secondaryLast);
                    $('#secondary_contact_email').val(option.dataset.secondaryEmail);
                    $('#secondary_contact_job_title').val(option.dataset.secondaryJob);
                    $('#secondary_contact_phone').val(option.dataset.secondaryPhone);
                }
            }
        });
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

    function confirmAction(msg, color, icon, callback) {
        if (_busy)
            return;
        _busy = true;

        $.confirm({
            title: 'Are you sure ?',
            content: msg,
            icon: icon,
            type: color,
            typeAnimated: true,
            buttons: {
                no: function () {
                    _busy = false;
                    this.close();
                },
                confirm:  {
                    text: 'Yes',
                    btnClass: 'btn-' + color,
                    action: function () {
                        _busy = false;
                        callback();
                    }
                }
            }
        });
    };

    setupFirstDiscountRegistrationBehavior();
    setupSecondDiscountRegistrationBehavior();
    setupThirdDiscountRegistrationBehavior();
    function setupFirstDiscountRegistrationBehavior() {
        let checkbox = $('#registration_first_discount_is_enable');
        let registration_first_discount_end_date = $('#registration_first_discount_end_date');
        let registration_first_discount_amount = $('#registration_first_discount_amount_id');
        let allow_second_discount_disabled = $('#registration_second_discount_is_enable');
        checkbox.change(e => {
            let checked = checkbox.prop('checked');
            if(!checked)
            {
                disableSecond(checked);
                disableThird(checked);
            }

            registration_first_discount_end_date.prop('disabled', !checked);
            registration_first_discount_amount.prop('disabled', !checked);
            allow_second_discount_disabled.prop('disabled', !checked);
            app.registration_first_discount_end_date_disabled = !checked;
            app.registration_first_discount_amount_disabled = !checked;
            app.allow_second_discount_disabled = !checked;
        });
        checkbox.change();
    }
    
    function setupSecondDiscountRegistrationBehavior() {
        let checkbox = $('#registration_second_discount_is_enable');
        let registration_second_discount_end_date_disabled = $('#registration_second_discount_end_date_disabled');
        let registration_second_discount_amount = $('#registration_second_discount_amount_id');
        let allow_third_discount_disabled = $('#registration_third_discount_is_enable');
        checkbox.change(e => {
            let checked = checkbox.prop('checked');
            if(!checked)
            {
                disableThird(checked);
            }
            registration_second_discount_end_date_disabled.prop('disabled', !checked);
            registration_second_discount_amount.prop('disabled', !checked);
            allow_third_discount_disabled.prop('disabled', !checked);
            app.registration_second_discount_end_date_disabled = !checked;
            app.registration_second_discount_amount_disabled = !checked;
            app.allow_third_discount_disabled = !checked;
        });
        checkbox.change();
    }
    
    function setupThirdDiscountRegistrationBehavior() {
        let checkbox = $('#registration_third_discount_is_enable');
        let registration_third_discount_end_date_disabled = $('#registration_third_discount_end_date_disabled');
        let registration_third_discount_amount = $('#registration_third_discount_amount_id');
        
        checkbox.change(e => {
            let checked = checkbox.prop('checked');
            registration_third_discount_end_date_disabled.prop('disabled', !checked);
            registration_third_discount_amount.prop('disabled', !checked);
            
            app.registration_third_discount_end_date_disabled = !checked;
            app.registration_third_discount_amount_disabled = !checked;
            
        });
        checkbox.change();
    }
    function disableSecond(checked)
    {
        let allow_second_discount_disabled = $('#registration_second_discount_is_enable');
        $('#registration_second_discount_is_enable').prop('checked', false); 
        let registration_second_discount_end_date_disabled = $('#registration_second_discount_end_date_disabled');
        let registration_second_discount_amount = $('#registration_second_discount_amount_id');
        registration_second_discount_end_date_disabled.prop('disabled', !checked);
        registration_second_discount_amount.prop('disabled', !checked);
        allow_second_discount_disabled.prop('disabled', !checked);
        app.registration_second_discount_end_date_disabled = !checked;
        app.registration_second_discount_amount_disabled = !checked;
        app.allow_second_discount_disabled = !checked;
    }
    function disableThird(checked)
    {
        let allow_third_discount_disabled = $('#registration_third_discount_is_enable');
        $('#registration_third_discount_is_enable').prop('checked', false);
        let registration_third_discount_end_date_disabled = $('#registration_third_discount_end_date_disabled');
        let registration_third_discount_amount = $('#registration_third_discount_amount_id');
        registration_third_discount_end_date_disabled.prop('disabled', !checked);
        registration_third_discount_amount.prop('disabled', !checked);
        allow_third_discount_disabled.prop('disabled', !checked);
        
        app.registration_third_discount_end_date_disabled = !checked;
        app.registration_third_discount_amount_disabled = !checked;
        app.allow_third_discount_disabled = !checked;

    }
});
