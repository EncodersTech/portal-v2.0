require('../main');

window.Vue = require('vue');

$(document).ready(e => {

    const app = new Vue({
        el: '#app',
        data: {
            gymId: window._gym,
            registrationId: window._registration,
            isLoading: false,
            errorMessage: null,
            managed: window._managed_account,
            transaction: null,
            tab: 'details',
            specialist_events: null,
            registration: null,
            bodies: {},
            coaches: [],
            transactions: [],
            showAthletes: true,
            showCoaches: true,
            hadDisabledLevels: false,
        },
        computed: {
            constants() {
                return {
                    bodies: {
                        USAG: 1,
                        USAIGC: 2,
                        AAU: 3
                    },
                    categories: {
                        GYMNASTICS_WOMEN: 1,
                        GYMNASTICS_MEN: 2,
                        TRAMPOLINE_TUMBLING: 3,
                        RHYTHMIC: 4,
                        ACROBATIC: 5,
                        GYMNASTICS_FOR_ALL: 6,
                        TUMBLING: 7,
                    },
                    transactions: {
                        methods: {
                            1: 'Credit Card',
                            2: 'PayPal',
                            3: 'ACH',
                            4: 'Mailed Check',
                            5: 'Allgymnastics.com Balance',
                            Card: 1,
                            Paypal: 2,
                            Ach: 3,
                            Check: 4,
                            Balance: 5,
                        },
                        statuses: {
                            1: 'Pending',
                            2: 'Completed',
                            3: 'Canceled / Rejected',
                            4: 'Failed',
                            5: 'Waitlist Pending',
                            6: 'Waitlist Confirmed',
                            Pending: 1,
                            Completed: 2,
                            Canceled: 3,
                            Failed: 4,
                            WaitlistPending: 5,
                            WaitlistConfirmed: 6,
                        },
                    },
                    athletes: {
                        statuses: {
                            1: 'Registered',
                            2: 'Pending (Non-Reserved)',
                            3: 'Pending (Reserved)',
                            4: 'Scratched',
                            Registered: 1,
                            NonReserved: 2,
                            Reserved: 3,
                            Scratched: 4,
                        }
                    },
                    specialists: {
                        statuses: {
                            1: 'Registered',
                            2: 'Pending',
                            4: 'Scratched',
                            Registered: 1,
                            Pending: 2,
                            Scratched: 4,
                        }
                    },
                    coaches: {
                        statuses: {
                            1: 'Registered',
                            2: 'Pending (Non-Reserved)',
                            3: 'Pending (Reserved)',
                            4: 'Scratched',
                            Registered: 1,
                            NonReserved: 2,
                            Reserved: 3,
                            Scratched: 4,
                        }
                    },
                    reports: {
                        types: {
                            Summary: 'summary',
                            Entry: 'participation',
                            EntryNonAthletes: 'participation-not-athlete',
                            Coaches: 'coaches',
                            Specialists: 'specialists',
                            Refunds: 'refunds',
                            RegistrationDetail: 'registration-detail',
                            Scratch: 'scratch',
                            MeetEntry: 'meet-entry',
                        }
                    }
                };
            }
        },
        methods: {
            generateReport(report, gym) {
                try {
                    switch (report) {
                        case this.constants.reports.types.Summary:
                        case this.constants.reports.types.Entry:
                        case this.constants.reports.types.EntryNonAthletes:
                        case this.constants.reports.types.Coaches:
                        case this.constants.reports.types.Specialists:
                        case this.constants.reports.types.Refunds:
                        case this.constants.reports.types.RegistrationDetail:
                        case this.constants.reports.types.Scratch:
                        case this.constants.reports.types.MeetEntry:
                            break;

                        default:
                            throw 'Invalid report type `' + report + '`';
                    }


                    let link = '/gym/' + this.gymId +
                                '/registration/' + this.registrationId +
                                '/report/' + report;
                    window.open(link);
                } catch (error) {
                    let msg = '';
                    if (error.response) {
                        msg = error.response.data.message;
                    } else if (error.request) {
                        msg = 'No server response.';
                    } else if (error.message){
                        msg = error.message;
                    } else {
                        msg = error
                    }

                    this.showAlert(
                        msg,
                        'Generate Report',
                        'red',
                        'fas fa-exclamation-triangle'
                    );
                }
            },

            showTransactionDetails(tx) {
                this.transaction = tx;
                $('#modal-transaction-details').modal('show');
            },

            levelUniqueId(level) {
                return level.id + (level.male ? '-m' : '') + (level.female ? '-f' : '');
            },

            hasSpecialist(body, category) {
                return (body.id == this.constants.bodies.USAIGC)
                    && (category.id == this.constants.categories.GYMNASTICS_WOMEN);
            },

            showAlert(msg, title, color, icon) {
                $.alert({
                    title: title,
                    content: msg,
                    icon: icon,
                    type: color,
                    typeAnimated: true
                });
            },

            numberFormat(n) {
                try {
                    let fee = Utils.toFloat(n);
                    return (fee === null ? n : fee.toFixed(2));
                } catch (e) {
                    return n;
                }
            },

            toggleItems(toggle) {
                this.bodies.forEach(b => {
                    b.expanded = toggle;
                    b.categories.forEach(c => {
                        c.expanded = toggle;
                        c.levels.forEach(l => l.expanded = toggle);
                    });
                });
            },
        },
        beforeMount() {
            this.isLoading = true;
            axios.get('/api/app/specialist').then(result => {
                this.specialist_events = {};
                result.data.events.forEach(evt => this.specialist_events[evt.id] = evt);

                axios.get('/api/registration/' + window._registration, {
                    'params': {
                        '__managed': this.managed
                    }
                }).then(result => {
                    let registration = result.data;

                    registration.has_tshirts = false;
                    registration.has_leos = false;

                    for (let i in registration.transactions) {
                        let tx = registration.transactions[i];

                        tx.created_at = Moment(tx.created_at);
                        tx.created_at_display = tx.created_at.format('MM/DD/YYYY hh:mm:ss A');

                        tx.updated_at = Moment(tx.updated_at);
                        tx.updated_at_display = tx.updated_at.format('MM/DD/YYYY hh:mm:ss A');

                        tx.repayable = !tx.was_replaced && (
                            (tx.status == this.constants.transactions.statuses.Canceled)
                            || (tx.status == this.constants.transactions.statuses.Failed)
                            || (tx.status == this.constants.transactions.statuses.WaitlistConfirmed)
                        );

                        tx.waitlist = (tx.status == this.constants.transactions.statuses.WaitlistConfirmed)
                            || (tx.status == this.constants.transactions.statuses.WaitlistPending);

                        this.transactions.push(tx);
                    }

                    for (let i in registration.levels) {
                        let level = registration.levels[i];

                        let body = {
                            ... _.cloneDeep(level.sanctioning_body),
                            name: level.sanctioning_body.initialism,
                            categories: {},
                            expanded: true,
                            path: 'b' + level.sanctioning_body.id
                        };
                        if (this.bodies.hasOwnProperty(body.id))
                            body = this.bodies[body.id];

                        let category = {
                            ... _.cloneDeep(level.level_category),
                            levels: [],
                            expanded: true,
                            path: 'b' + body.id + '-c' + level.level_category.id
                        };
                        if (body.categories.hasOwnProperty(category.id))
                            category = body.categories[category.id];

                        level = {
                            ... _.cloneDeep(level),
                            disabled: level.pivot.disabled,
                            male: level.pivot.allow_men,
                            female: level.pivot.allow_women,
                            registration_fee: Utils.toFloat(level.pivot.registration_fee),
                            late_registration_fee: Utils.toFloat(level.pivot.late_registration_fee),
                            allow_specialist: level.pivot.allow_specialist,
                            specialist_registration_fee: Utils.toFloat(level.pivot.specialist_registration_fee),
                            specialist_late_registration_fee: Utils.toFloat(level.pivot.specialist_late_registration_fee),
                            allow_team: level.pivot.allow_teams,
                            team_registration_fee: Utils.toFloat(level.pivot.team_registration_fee),
                            team_late_registration_fee: Utils.toFloat(level.pivot.team_late_registration_fee),
                            enable_athlete_limit: level.pivot.enable_athlete_limit,
                            athlete_limit: level.pivot.athlete_limit,
                            has_team: level.pivot.has_team,
                            was_late: level.pivot.was_late,
                            team_fee: level.pivot.team_fee,
                            team_late_fee: level.pivot.team_late_fee,
                            team_refund: level.pivot.team_refund,
                            team_late_refund: level.pivot.team_late_refund,
                            athletes: [],
                            expanded: true,
                        };

                        level.uid = this.levelUniqueId(level);
                        level.has_specialist = this.hasSpecialist(body, category);
                        level.team_paid_for = (Utils.toFloat(level.team_fee) + Utils.toFloat(level.team_late_fee)
                                                - Utils.toFloat(level.team_refund) - Utils.toFloat(level.team_late_refund)) > 0;

                        delete level.sanctioning_body;
                        delete level.level_category;

                        if (level.disabled)
                            this.hadDisabledLevels = true;

                        category.levels.push(level);
                        body.categories[category.id] = category;
                        this.bodies[body.id] = body;
                    }

                    for (let i in registration.athletes) {
                        let athlete = _.cloneDeep(registration.athletes[i]);

                        athlete.is_specialist = false;
                        athlete.gender_display = athlete.gender.charAt(0).toUpperCase() + athlete.gender.slice(1)
                        athlete.dob = Moment(athlete.dob);
                        athlete.dob_display = athlete.dob.format('MM/DD/YYYY');

                        if (athlete.tshirt)
                            registration.has_tshirts = true;

                        if (athlete.leo)
                            registration.has_leos = true;

                        let athleteLevel = this.bodies[athlete.registration_level.level.sanctioning_body_id]
                                            .categories[athlete.registration_level.level.level_category_id]
                                            .levels.filter(l => {
                                                return (l.pivot.id == athlete.registration_level.id);
                                            });
                        if (athleteLevel.length != 1)
                            throw "Something went wrong while loading your athlete details."
                        
                        //need to check - scratch + move problem

                        athlete.total = Utils.toFloat(athlete.fee) + Utils.toFloat(athlete.late_fee);
                                    - Utils.toFloat(athlete.refund) - Utils.toFloat(athlete.late_refund);
                        athleteLevel[0].athletes.push(athlete);
                    }

                    for (let i in registration.specialists) {
                        let specialist = _.cloneDeep(registration.specialists[i]);

                        specialist.is_specialist = true;
                        specialist.gender_display = specialist.gender.charAt(0).toUpperCase() + specialist.gender.slice(1)
                        specialist.dob = Moment(specialist.dob);
                        specialist.dob_display = specialist.dob.format('MM/DD/YYYY');

                        if (specialist.tshirt)
                            registration.has_tshirts = true;

                        if (specialist.leo)
                            registration.has_leos = true;

                        let specialistLevel = this.bodies[specialist.registration_level.level.sanctioning_body_id]
                                            .categories[specialist.registration_level.level.level_category_id]
                                            .levels.filter(l => {
                                                return (l.pivot.id == specialist.registration_level.id);
                                            });

                        if (specialistLevel.length != 1)
                            throw "Something went wrong while loading your specialist details."

                        specialist.total = 0;
                        specialist.events.forEach(evt => {
                            specialist.total += Utils.toFloat(evt.fee) + Utils.toFloat(evt.late_fee)
                                                - Utils.toFloat(evt.refund) - Utils.toFloat(evt.late_refund);
                        });
                        specialistLevel[0].athletes.push(specialist);
                    }

                    let cleanedBodies = [];
                    for (let i in this.bodies) {
                        let b = this.bodies[i];
                        let cleanedCategories = [];

                        for (let i in b.categories) {
                            let c = b.categories[i];
                            let cleanedLevels = [];

                            for (let i in c.levels) {
                                let l = c.levels[i];
                                if (l.athletes.length > 0)
                                    cleanedLevels.push(l);
                            };

                            c.levels = cleanedLevels;
                            if (c.levels.length > 0)
                                    cleanedCategories.push(c);
                        };

                        b.categories = cleanedCategories;
                        if (b.categories.length > 0)
                            cleanedBodies.push(b);
                    };
                    this.bodies = cleanedBodies;

                    for (let i in registration.coaches) {
                        let coach = registration.coaches[i];

                        coach.gender_display = coach.gender.charAt(0).toUpperCase() + coach.gender.slice(1)
                        coach.dob = Moment(coach.dob);
                        coach.dob_display = coach.dob.format('MM/DD/YYYY');

                        if (coach.usag_expiry != null) {
                            coach.usag_expiry = Moment(coach.usag_expiry);
                            coach.usag_expiry_display = coach.usag_expiry.format('MM/DD/YYYY');
                        }

                        if (coach.usag_safety_expiry != null) {
                            coach.usag_safety_expiry = Moment(coach.usag_safety_expiry);
                            coach.usag_safety_expiry_display = coach.usag_safety_expiry.format('MM/DD/YYYY');
                        }

                        if (coach.usag_safesport_expiry != null) {
                            coach.usag_safesport_expiry = Moment(coach.usag_safesport_expiry);
                            coach.usag_safesport_expiry_display = coach.usag_safesport_expiry.format('MM/DD/YYYY');
                        }

                        if (coach.usag_background_expiry != null) {
                            coach.usag_background_expiry = Moment(coach.usag_background_expiry);
                            coach.usag_background_expiry_display = coach.usag_background_expiry.format('MM/DD/YYYY');
                        }
                        this.coaches.push(coach);
                    }

                    this.registration  = registration;
                });
            }).catch(error => {
                let msg = '';
                if (error.response) {
                    msg = error.response.data.message;
                } else if (error.request) {
                    msg = 'No server response.';
                } else if (error.message){
                    msg = error.message;
                } else {
                    msg = error
                }
                this.errorMessage = msg + '<br/>Please reload this page.';
                this.isError = true;
            }).finally(() => {
                this.isLoading = false;
            });
        }
    });

    if ($('#meet-public-url-copy').length > 0) {
        var meetUrl = new ClipboardJS('#meet-public-url-copy');

        meetUrl.on('success', function(e) {
            switchCopySuccessMessage(true, 'Copied !');
            e.clearSelection();
            _.debounce(switchCopySuccessMessage, 1500)(false, '');
        });

        meetUrl.on('error', function(e) {
            switchCopySuccessMessage(true, 'Ctrl+C to copy !');
            _.debounce(switchCopySuccessMessage, 2500)(false, '');
        });

        function switchCopySuccessMessage(shown, msg) {
            let text = $('#meet-public-url-copy-success')
            let elem = $('#meet-public-url-copy-success-message');

            text.html(msg);
            elem.css('visibility', shown ? 'visible' : 'hidden');
        }
    }
});
