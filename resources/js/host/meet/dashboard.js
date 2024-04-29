require('../../main');

window.Vue = require('vue');

$(document).ready(e => {
    var selectizeControl, selectizeControlEdit;
    const app = new Vue({
        el: '#app',
        data: {
            managed: window._managed_account,
            is_managed: window.is_managed,
            gymId: window.gym_id,
            meetId: window.meet_id,
            isLoading: false,
            errorMessage: null,
            tab: 'summary',
            meetDetails: null,

            allRegistrations: [],
            registrations: [],
            registrationFilters: {
                text: '',
                pending: false
            },
            registrationFiltering: false,

            waitlist: [],
            allWaitlist: [],
            waitlistFilters: {
                text: '',
                pending: false,
                confirmed: false,
            },
            waitlistFiltering: false,

            transaction: null,
            transactions: [],
            allTransactions: [],
            transactionsFilters: {
                text: '',
                method: '',
                status: ''
            },
            depositVar:{
                gymId: '',
                meetId: window.meet_id,
                amount: ''
            },
            depositVarEdit: {
                gymId: '',
                meetId: window.meet_id,
                amount: ''
            },
            allGym: [],
            depositGym:[],
            transactionsFiltering: false,

            transactionsSortColumn: 'created_at',
            transactionsSortDirection: 'down',

            check_confirmation: null,
            selected_card: '',

            specialist_events: {},
            bodies: {},
            cards: [],
            cardError: null,
            verification_details: null,
            sortColumn: 'name',
            sortDirection: 'down',
            selectedGymMailable: null
        },
        computed: {
            constants() {
                return {
                    bodies: {
                        USAG: 1,
                        USAIGC: 2,
                        AAU: 3,
                        NGA: 4,
                        1: 'USAG',
                        2: 'USAIGC',
                        3: 'AAU',
                        4: 'NGA',
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
                            _array: [1, 3, 4, 5],
                            1: 'Credit Card',
                            // 2: 'PayPal',
                            3: 'ACH',
                            4: 'Mailed Check',
                            5: 'Allgymnastics.com Balance',
                            Card: 1,
                            // Paypal: 2,
                            Ach: 3,
                            Check: 4,
                            Balance: 5,
                        },
                        statuses: {
                            _array: [1, 2, 3, 4, 5, 6],
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
                    registrations: {
                        statuses: {
                            1: 'Registered',
                            2: 'Pending Waitlist',
                            3: 'Confirmed Waitlist',
                            4: 'Canceled / Rejected',
                            Registered: 1,
                            Waitlist: 2,
                            ConfirmedWaitlist: 3,
                            Canceled: 4,
                        }
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
                    verifications: {
                        statuses: {
                            _array: [1, 2],
                            1: 'Processing',
                            2: 'Completed',
                            Processing: 1,
                            Done: 2,
                        },
                    },
                    reports: {
                        types: {
                            Summary: 'summary',
                            Entry: 'participation',
                            EntryNonAthletes: 'participation-not-athlete',
                            Coaches: 'coaches',
                            Specialists: 'specialists',
                            Refunds: 'refunds',
                            LeoTShirt: 'leo-t-shirt',
                            ProscoreExport: 'proscore-export',
                            RegistrationDetail: 'registration-detail',
                            LeoTShirtGym: 'leo-t-shirt-gym',
                            MeetEntry: 'meet-entry',
                            Scratch: 'scratch',
                            USAIGCCoachSignin: 'usaigc-coach-signin',
                            GymMailingLabel: 'gym-mailing-label',
                            CoachSignin: 'coach-signin',
                            GymNameLabel: 'gym-name-label',
                            CoachNameLabel: 'coaches-name-label',
                            MarketingQR: 'marketing-qr',
                            NGACoachSignin: 'nga-coach-signin',
                            SpecialistsByLevel: 'specialist-by-level',
                            EntryTeam: 'entry-team',
                        }
                    }
                };
            }
        },
        watch: {
            registrationFilters: {
                deep: true,
                handler() {
                    this.registrationFiltering = true;
                    this.filterRegistrations();
                }
            },

            waitlistFilters: {
                deep: true,
                handler() {
                    this.waitlistFiltering = true;
                    this.filterWaitlist();
                }
            },

            transactionsFilters: {
                deep: true,
                handler() {
                    this.transactionsFiltering = true;
                    this.filterTransactions();
                }
            },
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
                        case this.constants.reports.types.LeoTShirt:
                        case this.constants.reports.types.ProscoreExport:
                        case this.constants.reports.types.RegistrationDetail:
                        case this.constants.reports.types.LeoTShirtGym:
                        case this.constants.reports.types.MeetEntry:
                        case this.constants.reports.types.Scratch:
                        case this.constants.reports.types.USAIGCCoachSignin:
                        case this.constants.reports.types.NGACoachSignin:
                        case this.constants.reports.types.GymMailingLabel:
                        case this.constants.reports.types.CoachSignin:
                        case this.constants.reports.types.GymNameLabel:
                        case this.constants.reports.types.CoachNameLabel:
                        case this.constants.reports.types.MarketingQR:
                        case this.constants.reports.types.SpecialistsByLevel:
                        case this.constants.reports.types.EntryTeam:
                            break;

                        default:
                            throw 'Invalid report type `' + report + '`';
                    }

                    let link = '/host/' + this.gymId + '/meets/' + this.meetId + '/report/' +
                                report + '/create' + (gym !== undefined ? '/' + gym : '');
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

            structureLevels(input) {
                let bodies = {};
                for (let i in input) {
                    let level = input[i];

                    let body = {
                        ... _.cloneDeep(level.sanctioning_body),
                        name: level.sanctioning_body.initialism,
                        categories: {},
                        expanded: false,
                        path: 'b' + level.sanctioning_body.id,
                        hasPending: false
                    };
                    if (bodies.hasOwnProperty(body.id))
                        body = bodies[body.id];

                    let category = {
                        ... _.cloneDeep(level.level_category),
                        levels: [],
                        expanded: false,
                        path: 'b' + body.id + '-c' + level.level_category.id,
                        hasPending: false
                    };
                    if (body.categories.hasOwnProperty(category.id))
                        category = body.categories[category.id];

                    level = {
                        ... _.cloneDeep(level),
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
                        expanded: false,
                        hasPending: false
                    };

                    level.uid = this.levelUniqueId(level);
                    level.has_specialist = this.hasSpecialist(body, category);
                    level.team_paid_for = (Utils.toFloat(level.team_fee) + Utils.toFloat(level.team_late_fee)
                                            - Utils.toFloat(level.team_refund) - Utils.toFloat(level.team_late_refund)) > 0;

                    delete level.sanctioning_body;
                    delete level.level_category;

                    category.levels.push(level);
                    body.categories[category.id] = category;
                    bodies[body.id] = body;
                }
                return bodies;
            },

            processMeetDetails() {
                if (!this.meetDetails)
                    return;

                try {
                    let loadCards = false;

                    let txg = _.cloneDeep(this.meetDetails.allgym); // gymFilters1
                    let rawGym = [];
                    for (let i in txg) {
                        let tx = txg[i];
                        rawGym[tx['id']] = tx['name'];
                        this.allGym.push(tx);
                    }
                    
                    let txd = _.cloneDeep(this.meetDetails.depositGym); // gymFilters1
                    for (let i in txd) {
                        let tx = txd[i];
                        tx['gym_name'] = rawGym[tx['gym_id']];
                        tx['created_at'] = Moment(tx['created_at']);
                        tx['created_at_display'] = tx['created_at'].format('MM/DD/YYYY hh:mm:ss A');

                        tx['updated_at'] = Moment(tx['updated_at']);
                        tx['updated_at_display'] = tx['updated_at'].format('MM/DD/YYYY hh:mm:ss A'); 
                        tx['amount'] = Utils.toFloat(tx['amount']);
                        this.depositGym.push(tx);
                    }
                    // console.log(this.allGym);

                    for (let i in this.meetDetails.registrations) {
                        let r = _.cloneDeep(this.meetDetails.registrations[i]);
                        r.expanded = false;
                        r.coachesExpanded = false;
                        r.anyPending = false;
                        r.hasPendingCoaches = false;

                        r.has_tshirts = false;
                        r.has_leos = false;
                        r.bodies = this.structureLevels(r.levels);

                        for (let i in r.athletes) {
                            let athlete = _.cloneDeep(r.athletes[i]);

                            athlete.is_specialist = false;
                            athlete.gender_display = athlete.gender.charAt(0).toUpperCase() + athlete.gender.slice(1)
                            athlete.dob = Moment(athlete.dob);
                            athlete.dob_display = athlete.dob.format('MM/DD/YYYY');

                            if (athlete.tshirt)
                                r.has_tshirts = true;

                            if (athlete.leo)
                                r.has_leos = true;

                            let athleteHasPending = (athlete.status == this.constants.athletes.statuses.Reserved) ||
                                (athlete.status == this.constants.athletes.statuses.NonReserved);

                            let athleteLevel = r.bodies[athlete.registration_level.level.sanctioning_body_id]
                                                .categories[athlete.registration_level.level.level_category_id]
                                                .levels.filter(l => {
                                                    return (l.pivot.id == athlete.registration_level.id);
                                                });
                            if (athleteLevel.length != 1) {
                                throw "Something went wrong while loading your athlete details."
                            }
                            
                            athlete.total = Utils.toFloat(athlete.fee) + Utils.toFloat(athlete.late_fee)
                                            - Utils.toFloat(athlete.refund) - Utils.toFloat(athlete.late_refund);
                            athleteLevel[0].athletes.push(athlete);

                            if (athleteHasPending) {
                                r.bodies[athlete.registration_level.level.sanctioning_body_id].hasPending = true;

                                r.bodies[athlete.registration_level.level.sanctioning_body_id]
                                    .categories[athlete.registration_level.level.level_category_id]
                                    .hasPending = true;

                                athleteLevel[0].hasPending = true;
                                r.anyPending = true;
                            }
                        }

                        for (let i in r.specialists) {
                            let specialist = _.cloneDeep(r.specialists[i]);

                            specialist.is_specialist = true;
                            specialist.gender_display = specialist.gender.charAt(0).toUpperCase() + specialist.gender.slice(1)
                            specialist.dob = Moment(specialist.dob);
                            specialist.dob_display = specialist.dob.format('MM/DD/YYYY');

                            if (specialist.tshirt)
                                r.has_tshirts = true;

                            if (specialist.leo)
                                r.has_leos = true;

                            let specialistLevel = r.bodies[specialist.registration_level.level.sanctioning_body_id]
                                                .categories[specialist.registration_level.level.level_category_id]
                                                .levels.filter(l => {
                                                    return (l.pivot.id == specialist.registration_level.id);
                                                });
                            if (specialistLevel.length != 1) {
                                throw "Something went wrong while loading your specialist details."
                            }

                            specialist.total = 0;
                            specialist.events.forEach(evt => {
                                specialist.total += Utils.toFloat(evt.fee) + Utils.toFloat(evt.late_fee)
                                                    - Utils.toFloat(evt.refund) - Utils.toFloat(evt.late_refund);
                            });
                            specialistLevel[0].athletes.push(specialist);

                            if (specialist.has_pending_events) {
                                r.bodies[specialist.registration_level.level.sanctioning_body_id].hasPending = true;

                                r.bodies[specialist.registration_level.level.sanctioning_body_id]
                                    .categories[specialist.registration_level.level.level_category_id]
                                    .hasPending = true;

                                specialistLevel.hasPending = true;
                                r.anyPending = true;
                            }
                        }

                        for (let i in r.bodies) {
                            let b = r.bodies[i];
                            b.athlete_count = 0;
                            b.coach_count = 0;
                            for (let j in b.categories) {
                                let c = b.categories[j];
                                c.athlete_count = 0;
                                for (let k in c.levels) {
                                    let l = c.levels[k];
                                    c.athlete_count += l.athletes.length;
                                }
                                b.athlete_count += c.athlete_count;
                            }
                        }

                        for (let i in r.coaches) {
                            let c = r.coaches[i];

                            c.gender_display = c.gender.charAt(0).toUpperCase() + c.gender.slice(1)
                            c.dob = Moment(c.dob);
                            c.dob_display = c.dob.format('MM/DD/YYYY');

                            if (c.usag_expiry != null) {
                                c.usag_expiry = Moment(c.usag_expiry);
                                c.usag_expiry_display = c.usag_expiry.format('MM/DD/YYYY');
                            }

                            if (c.usag_safety_expiry != null) {
                                c.usag_safety_expiry = Moment(c.usag_safety_expiry);
                                c.usag_safety_expiry_display = c.usag_safety_expiry.format('MM/DD/YYYY');
                            }

                            if (c.usag_safesport_expiry != null) {
                                c.usag_safesport_expiry = Moment(c.usag_safesport_expiry);
                                c.usag_safesport_expiry_display = c.usag_safesport_expiry.format('MM/DD/YYYY');
                            }

                            if (c.usag_background_expiry != null) {
                                c.usag_background_expiry = Moment(c.usag_background_expiry);
                                c.usag_background_expiry_display = c.usag_background_expiry.format('MM/DD/YYYY');
                            }

                            if (c.usag_no && r.bodies[this.constants.bodies.USAG])
                                r.bodies[this.constants.bodies.USAG].coach_count++;

                            r.hasPendingCoaches = r.hasPendingCoaches || (
                                (c.status == this.constants.coaches.statuses.Reserved) ||
                                (c.status == this.constants.coaches.statuses.NonReserved)
                            );
                        }

                        r.anyPending = r.anyPending || r.hasPendingCoaches;

                        let txs = _.cloneDeep(r.transactions);
                        for (let i in txs) {
                            let tx = txs[i];

                            tx.gym = _.cloneDeep(r.gym);

                            tx.created_at = Moment(tx.created_at);
                            tx.created_at_display = tx.created_at.format('MM/DD/YYYY hh:mm:ss A');

                            tx.updated_at = Moment(tx.updated_at);
                            tx.updated_at_display = tx.updated_at.format('MM/DD/YYYY hh:mm:ss A');

                            tx.waitlist = (tx.status == this.constants.transactions.statuses.WaitlistConfirmed)
                            || (tx.status == this.constants.transactions.statuses.WaitlistPending);
                            if(tx.breakdown.length > 0)
                                tx.breakdown.host.subtotal = tx.breakdown.host.subtotal == 0 && tx.breakdown.host.coupon > 0 ? parseFloat(tx.breakdown.host.subtotal) + parseFloat(tx.breakdown.host.coupon) - (parseFloat(tx.breakdown.host.handling) + parseFloat(tx.breakdown.host.processor) - parseFloat(tx.breakdown.host.total)) : parseFloat(tx.breakdown.host.subtotal) + parseFloat(tx.breakdown.host.coupon);
                            if (!tx.waitlist) {
                                if (tx.method == this.constants.transactions.methods.Check) {
                                    let total_handling_fee = tx.breakdown.host.handling + tx.breakdown.gym.handling;
                                    let deposit_total_handling_fee =  tx.breakdown.host.deposit_handling + tx.breakdown.gym.deposit_handling;
                                    tx.total_handling_fee = (isNaN(deposit_total_handling_fee) || deposit_total_handling_fee == 0) ? total_handling_fee : deposit_total_handling_fee;
                                    // tx.total_handling_fee = tx.breakdown.host.handling + tx.breakdown.gym.handling;
                                    tx.check_total = tx.breakdown.gym.total;
                                    tx.is_pending_check = (tx.status == this.constants.transactions.statuses.Pending);
                                    loadCards = true;
                                } else {
                                    tx.is_pending_check = false;
                                }
                            }

                            this.allTransactions.push(tx);
                        }

                        this.allTransactions.sort((a, b) => {
                            let va = a[this.transactionsSortColumn];
                            let vb = b[this.transactionsSortColumn];

                            if (va < vb)
                                return -1 * (this.transactionsSortDirection == 'up' ? 1 : -1);

                            if (va > vb)
                                return 1 * (this.transactionsSortDirection == 'up' ? 1 : -1);

                            return 0;
                        });

                        r.verifications = {
                            [this.constants.bodies.USAG]: {
                                athletes: null,
                                coaches: null,
                            },
                            [this.constants.bodies.USAIGC]: {
                                athletes: null,
                            }
                        };
                        for (let i in r.athlete_verifications) {
                            let v = r.athlete_verifications[i];

                            r.verifications[v.sanctioning_body_id].athletes = {
                                id: v.id,
                                results: v.results,
                                status: v.status,
                            };
                        }
                        delete r.athlete_verifications;

                        for (let i in r.coach_verifications) {
                            let v = r.coach_verifications[i];

                            r.verifications[v.sanctioning_body_id].coaches = {
                                id: v.id,
                                results: v.results,
                                status: v.status,
                            };
                        }
                        delete r.coach_verifications;

                        if (r.status == this.constants.registrations.statuses.Registered) {
                            this.allRegistrations.push({...r});
                        } else {
                            this.allWaitlist.push({...r});
                        }
                    }

                    if (loadCards)
                        this.loadCards();
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

                    this.errorMessage = msg + '<br/>Please reload this page.';
                }

                this.filterRegistrations();
                this.filterWaitlist();
                this.filterTransactions();
            },

            handleVerificationRequest(body, type, v, r, modal) {
                try {
                    $('#modal-verification-details').modal('hide');
                    if (v && (modal !== true)) {
                        if (v.status == this.constants.verifications.statuses.Done) {
                            v = {
                                ... _.cloneDeep(v),
                                body: body,
                                type: type,
                                registration: r,
                            };

                            for (i in v.results.data) {
                                let entrant = v.results.data[i];
                                entrant.gender_display = entrant.gender.charAt(0).toUpperCase() + entrant.gender.slice(1)
                                if (!Array.isArray(entrant.issues)) {
                                    let filtered = _.pickBy(entrant.issues, (v, k) => v.length > 0);
                                    entrant.issues = filtered;
                                }
                            }
                            this.verification_details = v;
                            this.switchToTab('verifications', true);
                        }
                    } else {
                        this.isLoading = true;
                        axios.get(
                            '/api/host/' + this.gymId + '/meets/' + this.meetId +
                            '/registration/' + r.id + '/verify', {
                                'params': {
                                    '__managed': this.managed,
                                    body: body,
                                    type: type
                                }
                            }
                        ).then(result => {
                            r.verifications[body][type] = {
                                id: result.data.id,
                                results: result.data.results,
                                status: result.data.status,
                            };

                            if (modal === true)
                                this.switchToTab('participants');

                            this.showAlert(
                                'The verification is running in the background. You will receive an email when this is done.',
                                'Entrant Verification',
                                'green',
                                'fas fa-check-circle'
                            );
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
                            this.showAlert(
                                msg,
                                'Confirm Check',
                                'red',
                                'fas fa-exclamation-triangle'
                            );
                        }).finally(() => {
                            this.isLoading = false;
                        });
                    }
                } catch (error) {
                    console.error(error);
                    this.showAlert('Something went wrong', 'Oops !', 'red', 'fas fa-exclamation-triangle');
                }
            },

            loadCards() {
                this.isLoading = true;
                axios.get('/api/managed/cards', {
                    'params': {
                        '__managed': this.managed
                    }
                }).then(result => {
                    this.cards = result.data.cards;
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
                    this.cardError =  'We failed to load your linked credit cards.' +
                                        'You will not be able to accept checks in the transactions tab.' +
                                        '<br>Details: ' + msg;
                }).finally(() => {
                    this.isLoading = false;
                });
            },

            filterRegistrations: _.debounce(function () {
                let result = this.allRegistrations;

                if (this.allRegistrations.length > 0) {
                    if (this.registrationFilters.pending)
                        result = result.filter(r => r.anyPending);

                    if (this.registrationFilters.text !== '') {
                        result = result.filter(r => {
                            return r.gym.name.toLowerCase().includes(
                                this.registrationFilters.text.toLowerCase()
                            ) || r.gym.short_name.toLowerCase().includes(
                                this.registrationFilters.text.toLowerCase()
                            );
                        });
                    }
                }

                this.registrations = result;
               this.registrationFiltering = false;
            }, 250),

            filterWaitlist: _.debounce(function () {
                let result = this.allWaitlist;

                if (this.allWaitlist.length > 0) {
                    result = result.filter(r => r.status != this.constants.registrations.statuses.Canceled);

                    if (this.waitlistFilters.pending)
                        result = result.filter(r => r.anyPending);


                    if (this.waitlistFilters.confirmed)
                        result = result.filter(r => r.status != this.constants.registrations.statuses.ConfirmedWaitlist);

                    if (this.waitlistFilters.text !== '') {
                        result = result.filter(r => {
                            return r.gym.name.toLowerCase().includes(
                                this.waitlistFilters.text.toLowerCase()
                            ) || r.gym.short_name.toLowerCase().includes(
                                this.waitlistFilters.text.toLowerCase()
                            );
                        });
                    }
                }

                this.waitlist = result;
               this.waitlistFiltering = false;
            }, 250),

            filterTransactions: _.debounce(function () {
                let result = this.allTransactions;

                if (this.allTransactions.length > 0) {
                    if (this.transactionsFilters.method)
                        result = result.filter(tx => tx.method == this.transactionsFilters.method);

                    if (this.transactionsFilters.status)
                        result = result.filter(tx => tx.status == this.transactionsFilters.status);

                    if (this.transactionsFilters.text !== '') {
                        result = result.filter(tx => {
                            return (tx.processor_id == this.transactionsFilters.text)
                            || tx.gym.name.toLowerCase().includes(
                                this.transactionsFilters.text.toLowerCase()
                            ) || tx.gym.short_name.toLowerCase().includes(
                                this.transactionsFilters.text.toLowerCase()
                            );
                        });
                    }
                }

                this.transactions = result;
                this.transactionsFiltering = false;
                this.sortBy('name');
            }, 250),

            switchToTab(tab, skipCookie) {
                this.tab = tab;

                if (skipCookie == true)
                    return;

                Cookies.set('gym-' + this.gymId + '-meet-' + this.meetId + 'dashboard-tab', this.tab);
            },

            sendEmailTo(registration) {
                // this.selectedGymMailable = registration.gym.id;
                $('#sendMailable_'+registration.gym.id).prop('checked', true);
                this.switchToTab('mailer');
                // console.log(registration);
                // this.showAlert(
                //     'Transfer to mass mailing interface with prepopulated recipient.',
                //     'TODO',
                //     'red',
                //     'fas fa-exclamation-triangle'
                // );

            },
            openPendinReport(usagReservation){
                $('#general_info').html('<td>'+usagReservation.action_status+'</td><td>'+usagReservation.contact_email+'</td><td>'+usagReservation.contact_name+'</td>');
                if(usagReservation.gym)
                    $('#gym_info').html('<td>'+usagReservation.gym.name+'</td><td>'+usagReservation.gym.office_phone+'</td><td>'+usagReservation.gym.usag_membership+'</td>');
                else
                    $('#gym_info').html('<td colspan="3">Not Found</td>');

                $('#sanction_info').html('<td>'+usagReservation.usag_sanction.status_label+'</td><td>'+usagReservation.usag_sanction.contact_email+'</td><td>'+usagReservation.usag_sanction.contact_name+'</td><td>'+usagReservation.usag_sanction.usag_meet_name+'</td>');

                $('#athlete_update').html('');
                $('#coaches_add').html('');
                $('#athlete_add_div').hide();
                $('#athlete_update_div').hide();
                $('#coach_div').hide();
                if(usagReservation.payload.Reservation.Details.Gymnasts.Add !== undefined)
                {
                $('#athlete_add_div').show();
                let athletes_add = usagReservation.payload.Reservation.Details.Gymnasts.Add;
                let athlete_table = '';
                athletes_add.forEach(athletes => {
                    athlete_table += '<tr><td>'+athletes.ReservationID+'</td><td>'+athletes.Name+'</td><td>'+athletes.USAGID+'</td><td>'+athletes.Gender+'</td><td>'+athletes.DOB+'</td><td>'+athletes.MemberType+'</td><td>'+athletes.Apparatus+'</td><td>'+athletes.Level+'</td></tr>';
                });
                $('#athlete_add').html(athlete_table);
                }
                if(usagReservation.payload.Reservation.Details.Gymnasts.Update !== undefined)
                {
                $('#athlete_update_div').show();
                let athletes_update = usagReservation.payload.Reservation.Details.Gymnasts.Update;
                let athlete_table = '';
                athletes_update.forEach(athletes => {
                    //console.log(athletes);
                    athlete_table += '<tr><td>'+athletes.ReservationID+'</td><td>'+athletes.USAGID+'</td><td>'+athletes.Apparatus+'</td><td>'+athletes.Level+'</td><td>Update</td></tr>';
                });
                $('#athlete_update').html(athlete_table);
                }
                if(usagReservation.payload.Reservation.Details.Gymnasts.Scratch !== undefined)
                {
                $('#athlete_update_div').show();
                let athletes_update = usagReservation.payload.Reservation.Details.Gymnasts.Scratch;
                let athlete_table = '';
                athletes_update.forEach(athletes => {
                    //console.log(athletes);
                    athlete_table += '<tr><td>'+athletes.ReservationID+'</td><td>'+athletes.USAGID+'</td><td>'+athletes.Apparatus+'</td><td>'+athletes.Level+'</td><td>Scratch</td></tr>';
                });
                $('#athlete_update').html(athlete_table);
                }
                if(usagReservation.payload.Reservation.Details.Coaches !== undefined && usagReservation.payload.Reservation.Details.Coaches.Add !== undefined)
                {
                $('#coach_div').show();
                let coaches_add = usagReservation.payload.Reservation.Details.Coaches.Add;
                let coaches_table = '';
                coaches_add.forEach(coaches => {
                    coaches_table += '<tr><td>'+coaches.ReservationID+'</td><td>'+coaches.Name+'</td><td>'+coaches.USAGID+'</td><td>'+coaches.MemberType+'</td></tr>';
                });
                $('#coaches_add').html(coaches_table);
                }
                
                $('#payload_info').html('<pre>'+JSON.stringify(usagReservation.payload, null, 2)+'</pre>');
                $('#modal-usag-report').modal('show');
            
            },
            close_modal()
            {
                $('#modal-usag-report').modal('hide');
            },
            hidethis(e){
                if ($('#btns').html() == "Show")
                    $('#btns').html("Hide");
                else
                    $('#btns').html("Show");
                $('#payload_info').toggle();
            },

            confirmWaitlistRegistration(tx) {
                if (!tx)
                    throw 'Invalid registration';

                this.confirmAction(
                    'Are you sure you want to confirm this entry ?',
                    'red',
                    'fas fa-exclamation-triangle',
                    () => {
                        this.isLoading = true;
                        axios.get(
                            '/api/host/' + this.gymId + '/meets/' + this.meetId +
                            '/registration/' + tx.meet_registration_id + '/transaction/' +
                            tx.id + '/confirm', {
                                'params': {
                                    '__managed': this.managed,
                                }
                            }
                        ).then(result => {
                            this.showAlert(
                                'Waitlist Entry confirmed !',
                                'Confirm Waitlist Entry',
                                'green',
                                'fas fa-check-circle'
                            );
                            Utils.refresh();
                        }).catch(error => {
                            console.error([error]);
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
                                'Confirm Waitlist Entry',
                                'red',
                                'fas fa-exclamation-triangle'
                            );
                        }).finally(() => {
                            this.isLoading = false;
                        });
                    },
                    this
                );
            },

            rejectWaitlistRegistration(tx) {
                if (!tx)
                    throw 'Invalid registration';

                this.confirmAction(
                    'Are you sure you want to reject this entry ?',
                    'red',
                    'fas fa-exclamation-triangle',
                    () => {
                        this.isLoading = true;
                        axios.get(
                            '/api/host/' + this.gymId + '/meets/' + this.meetId +
                            '/registration/' + tx.meet_registration_id + '/transaction/' +
                            tx.id + '/reject', {
                                'params': {
                                    '__managed': this.managed,
                                }
                            }
                        ).then(result => {
                            this.showAlert(
                                'Waitlist Entry rejected !',
                                'Reject Waitlist Entry',
                                'green',
                                'fas fa-check-circle'
                            );
                            Utils.refresh();
                        }).catch(error => {
                            console.error([error]);
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
                                'Reject Waitlist Entry',
                                'red',
                                'fas fa-exclamation-triangle'
                            );
                        }).finally(() => {
                            this.isLoading = false;
                        });
                    },
                    this
                );
            },

            confirmCheck(tx,cc) {
                if (!tx || !tx.is_pending_check)
                    return;

                this.selected_card = '';
                this.transaction = tx;
                this.cc_fees = cc;
                $('#modal-confirm-check').modal('show');
            },
            confirmGeneral(tx){
                // console.log(selectizeControl);
                tx.gymId = selectizeControl.getValue();
                if (!tx)
                {
                    this.showAlert(
                            "Invalid Deposit",
                            'Confirm Deposit',
                            'red',
                            'fas fa-exclamation-triangle'
                        );
                        return false;
                }
                    
                if (tx.gymId == '')
                {
                    this.showAlert(
                        "Invalid Gym ID",
                        'Confirm Deposit',
                        'red',
                        'fas fa-exclamation-triangle'
                    );
                    return false;
                }
                    
                if (tx.amount == '' || parseInt(tx.amount) <= 0)
                {
                    this.showAlert(
                        "Please choose a valid amount.",
                        'Confirm Deposit',
                        'red',
                        'fas fa-exclamation-triangle'
                    );
                    return false;
                }
                this.depositVar = tx;
                $('#modal-confirm-deposit').modal('show');
            },
            sendDepositConfirmation(tx){
                try {
                    $('#modal-confirm-deposit').modal('hide');
                    if (!tx)
                        throw 'Invalid deposit';
                    

                    if (tx.amount == '' || parseInt(tx.amount) <= 0)
                        throw 'Please choose a valid amount.';

                    this.isLoading = true;
                    axios.get(
                        '/api/host/' + this.gymId + '/meets/' + this.meetId + '/deposit',
                        {
                            'params': {
                                '__managed': this.managed,
                                depositVar: tx
                            }
                        }
                    ).then(result => {
                        Utils.refresh();
                    }).catch(error => {
                        console.error([error]);
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
                            'Confirm Deposit',
                            'red',
                            'fas fa-exclamation-triangle'
                        );
                    }).finally(() => {
                        this.isLoading = false;
                    });
                } catch (error) {
                    this.showAlert(
                        error,
                        'Confirm Deposit',
                        'red',
                        'fas fa-exclamation-triangle'
                    );
                }
            },
            disableDeposit(tx){
                try {
                    // $('#modal-confirm-deposit').modal('hide');
                    if (!tx)
                        throw 'Invalid deposit';

                    this.isLoading = true;
                    axios.get(
                        '/api/host/' + this.gymId + '/meets/' + this.meetId + '/deposit/disable',
                        {
                            'params': {
                                '__managed': this.managed,
                                depositId: tx.id
                            }
                        }
                    ).then(result => {
                        Utils.refresh();
                    }).catch(error => {
                        console.error([error]);
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
                            'Confirm Deposit',
                            'red',
                            'fas fa-exclamation-triangle'
                        );
                    }).finally(() => {
                        this.isLoading = false;
                    });
                } catch (error) {
                    this.showAlert(
                        error,
                        'Confirm Deposit',
                        'red',
                        'fas fa-exclamation-triangle'
                    );
                }
            },
            editDeposit(tx)
            {
                this.depositVarEdit = tx;
                this.bindSelect2();
                $('#modal-confirm-deposit-edit').modal('show');
            },
            enableDeposit(tx)
            {
                try {
                    // $('#modal-confirm-deposit').modal('hide');
                    if (!tx)
                        throw 'Invalid deposit';

                    this.isLoading = true;
                    axios.get(
                        '/api/host/' + this.gymId + '/meets/' + this.meetId + '/deposit/enable',
                        {
                            'params': {
                                '__managed': this.managed,
                                depositId: tx.id
                            }
                        }
                    ).then(result => {
                        Utils.refresh();
                    }).catch(error => {
                        console.error([error]);
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
                            'Confirm Deposit',
                            'red',
                            'fas fa-exclamation-triangle'
                        );
                    }).finally(() => {
                        this.isLoading = false;
                    });
                } catch (error) {
                    this.showAlert(
                        error,
                        'Confirm Deposit',
                        'red',
                        'fas fa-exclamation-triangle'
                    );
                }
            },
            bindSelect2(){
                var select2 = $('#select3').selectize({
                  sortField: 'text'
                });
                selectizeControlEdit = select2[0].selectize.setValue(this.depositVarEdit.gym_id); 
                select2.selectedValue = this.depositVarEdit.gym_id;
            },
            sendDepositEdit(tx){
                // tx.gym_id = this.$refs.selectedItem.value;
                // tx.gym_id = selectizeControlEdit.getValue();
                tx.gym_id = $('#select3').val();
                try {
                    $('#modal-confirm-deposit-edit').modal('hide');
                    if (!tx)
                        throw 'Invalid deposit';
                    

                    if (tx.amount == '' || parseInt(tx.amount) <= 0)
                        throw 'Please choose a valid amount.';

                    this.isLoading = true;
                    axios.get(
                        '/api/host/' + this.gymId + '/meets/' + this.meetId + '/deposit/edit',
                        {
                            'params': {
                                '__managed': this.managed,
                                deposit: tx
                            }
                        }
                    ).then(result => {
                        Utils.refresh();
                    }).catch(error => {
                        console.error([error]);
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
                            'Confirm Deposit',
                            'red',
                            'fas fa-exclamation-triangle'
                        );
                    }).finally(() => {
                        this.isLoading = false;
                    });
                } catch (error) {
                    this.showAlert(
                        error,
                        'Confirm Deposit',
                        'red',
                        'fas fa-exclamation-triangle'
                    );
                }
            },

            sendCheckConfirmation(tx) {
                try {
                    $('#modal-confirm-check').modal('hide');
                    if (!tx)
                        throw 'Invalid transaction';

                    // if (!card)
                    //     throw 'Please choose a valid card.';

                    this.isLoading = true;
                    axios.get(
                        '/api/host/' + this.gymId + '/meets/' + this.meetId +
                        '/registration/' + tx.meet_registration_id + '/check/' + tx.id +
                        '/confirm/0', {
                            'params': {
                                '__managed': this.managed,
                                amount: tx.total_handling_fee
                            }
                        }
                    ).then(result => {
                        Utils.refresh();
                    }).catch(error => {
                        console.error([error]);
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
                            'Confirm Check',
                            'red',
                            'fas fa-exclamation-triangle'
                        );
                    }).finally(() => {
                        this.isLoading = false;
                    });
                } catch (error) {
                    this.showAlert(
                        error,
                        'Confirm Check',
                        'red',
                        'fas fa-exclamation-triangle'
                    );
                }
            },

            rejectCheck(tx) {
                if (!tx)
                    throw 'Invalid transaction';

                this.confirmAction(
                    'Are you sure you want to reject this check ?',
                    'red',
                    'fas fa-exclamation-triangle',
                    () => {
                        this.isLoading = true;
                        axios.get(
                            '/api/host/' + this.gymId + '/meets/' + this.meetId +
                            '/registration/' + tx.meet_registration_id + '/check/' + tx.id +
                            '/reject', {
                                'params': {
                                    '__managed': this.managed,
                                }
                            }
                        ).then(result => {
                            tx.is_pending_check = false;
                            tx.status = this.constants.transactions.statuses.Canceled;
                            this.showAlert(
                                'Check rejected !',
                                'Reject Check',
                                'green',
                                'fas fa-check-circle'
                            );
                        }).catch(error => {
                            console.error([error]);
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
                                'Reject Check',
                                'red',
                                'fas fa-exclamation-triangle'
                            );
                        }).finally(() => {
                            this.isLoading = false;
                        });
                    },
                    this
                );
            },

            showTransactionDetails(tx,cc) {
                this.transaction = tx;
                this.cc_fees = cc;
                $('#modal-transaction-details').modal('show');
            },

            showRelatedTransaction(txId) {
                if (!txId)
                    return;

                let tx = this.allTransactions.filter(tx => tx.id == txId);
                if (tx.length != 1)
                    return;

                tx = tx[0];
                this.transactionsFilters.text = tx.processor_id;
                this.switchToTab('transactions');
            },

            levelUniqueId(level) {
                return level.id + (level.male ? '-m' : '') + (level.female ? '-f' : '');
            },

            hasSpecialist(body, category) {
                return (body.id == this.constants.bodies.USAIGC)
                    && (category.id == this.constants.categories.GYMNASTICS_WOMEN);
            },

            numberFormat(n) {
                try {
                    let fee = Utils.toFloat(n);
                    return (fee === null ? n : fee.toFixed(2));
                } catch (e) {
                    return n;
                }
            },

            confirmAction(msg, color, icon, callback, context) {
                $.confirm({
                    title: 'Are you sure ?',
                    content: msg,
                    icon: icon,
                    type: color,
                    typeAnimated: true,
                    buttons: {
                        no: function () {
                            this.close();
                        },
                        confirm:  {
                            text: 'Yes',
                            btnClass: 'btn-' + color,
                            action: function () {
                                callback();
                            }
                        }
                    }
                });
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
            sortBy(column) {
                if (column == this.sortColumn) {
                    this.sortDirection = (this.sortDirection == 'up' ? 'down' : 'up');
                } else {
                    this.sortColumn = column;
                    this.sortDirection = 'up';
                }
                this.sortChanged();
            },
            sortChanged() {
                if (this.transactions.length < 1)
                    return

                this.transactions.sort((a, b) => {
                    let va = a[this.sortColumn];
                    let vb = b[this.sortColumn];
                    if(this.sortColumn == 'name'){
                        va = a.gym[this.sortColumn];
                        vb = b.gym[this.sortColumn];
                    }
                    if (va < vb)
                        return -1 * (this.sortDirection == 'up' ? 1 : -1);

                    if (va > vb)
                        return 1 * (this.sortDirection == 'up' ? 1 : -1);

                    return 0;
                });
            },
        },
        beforeMount() {
            this.isLoading = true;
            axios.get('/api/app/specialist').then(result => {
                let savedTab = Cookies.get('gym-' + this.gymId + '-meet-' + this.meetId + 'dashboard-tab');
                if (savedTab)
                    this.switchToTab(savedTab);

                this.specialist_events = {};
                result.data.events.forEach(evt => this.specialist_events[evt.id] = evt);

                axios.get('/api/host/' + this.gymId + '/meets/' + this.meetId + '/details', {
                    'params': {
                        '__managed': this.managed
                    }
                }).then(result => {
                    this.meetDetails = result.data;
                    this.processMeetDetails();
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
                    this.errorMessage = msg;
                }).finally(() => {
                    this.isLoading = false;
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
            }).finally(() => {
                this.isLoading = false;
            });
        },
        mounted() {
            var time = $('#select2 > option').length;
            
            var interval = setInterval(function() { 
                if (time <= 3) { 
                    time = $('#select2 > option').length;
                }
                else { 
                var select = $('#select2').selectize({
                    sortField: 'text'
                });
                selectizeControl = select[0].selectize;  
                clearInterval(interval);
                }
            }, 500);
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
