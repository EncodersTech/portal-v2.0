<template>
    <div>
        <div v-if="isLoading" class="text-center p-3">
            <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
            Loading reservation notifications, please wait ...
        </div>
        <div v-else-if="errorMessage !== null" class="alert alert-danger">
            <strong>
                <span class="fas fa-fw fa-times-circle"></span> Whoops !<br/>
            </strong>
            <div v-html="errorMessage"></div>
        </div>
        <div v-else-if="(items !== null) && (items.count > 0)">
            <div v-if="(items[current.body].length > 0)">
                <h5 class="border-bottom">
                    <span class="fas fa-fw fa-receipt"></span> Reservations
                </h5>

                <div class="row pt-3">
                    <div class="col-12 col-xs-12 col-sm-4 col-md-12 col-lg-4 mb-1" v-for="item  in items[current.body]">
                        <div class="small-box text-white" :class="[item.is_un_assigned===true ? 'bg-warning' : item.is_new ? 'bg-success' : 'bg-danger']">
                            <div class="inner">
                                <h5 class="mb-3">
                                     <span v-if="item.is_new">
                                        <span class="fas fa-fw fa-plus-square"></span>
                                        New {{ constants.bodies[item.body] }} Reservation  - {{ item.meet ? item.meet.name : item.meetName}}
                                    </span>
                                    <span v-else>
                                        <span class="fas fa-fw fa-pen-square"></span>
                                        {{ constants.bodies[item.body] }} Reservation Update - {{ shown.meet ? shown.meet.name : item.meetName }}
                                    </span>
                                </h5>
                                <div class="" style="letter-spacing: 0.5px">
                                    <div class="">
                                        <strong>Sanction No.:</strong> {{ item.sanction }}
                                    </div>
                                    <div class="">
                                        <strong>Gym:</strong> {{ item.gym.name }}
                                    </div>
                                    <div class="">
                                        <strong>Category:</strong> {{ item.category.name }}
                                    </div>
                                    <div class="">
                                        <strong>Type:</strong>
                                        <span class="badge badge-pill badge-success" v-if="item.is_new">New Reservation</span>
                                        <span class="badge badge-pill badge-warning" v-if="item.has_update">Details Updated</span>
                                        <span class="badge badge-pill badge-danger" v-if="item.has_deletion">Reservation Removed</span>
                                    </div>
                                    <div class="">
                                        <strong>Last updated:</strong> {{ item.readable.last_updated }}
                                    </div>
                                </div>
                            </div>
                            <a href="#" v-if="item.is_un_assigned===true" class="small-box-footer mt-2 small-box-footer-font">
                                <b>Successfully received your USAG Reservation, however the host has not made this meet active on AllGym. You cannot complete registration until this is done.</b>
                            </a>
                            <a :href='item.url' v-else class="small-box-footer mt-2">
                                <b>View Details &amp; Merge</b> <i class="fas fa-arrow-circle-right"></i>
                            </a>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</template>

<style scoped>
    .icon-button {
        color: var(--white);
        border: none;
        background-color: transparent;
        margin: 0;
        padding: 0;
        border-radius: 6px;
        border: 1px solid transparent;
        transition-duration: 100ms;
        outline: none;
    }

    .icon-button:hover {
        color: var(--gray);
        background-color: var(--white);
        border: 1px solid var(--white);
    }

    .icon-button:active {
        color: var(--dark);
        background-color: var(--light);
        border: 1px solid var(--light);
    }

    .type-list-item::after {
        content: ', ';
    }

    .type-list-item:last-child::after {
        content: '';
    }
</style>

<script>
    export default {
        name: 'ReservationsNotifications',
        props: {
            managed: {
                default: null,
                type: Number
            },
        },
        computed: {
            constants() {
                return {
                    bodies: {
                        1: 'USAG',
                        2: 'USAIGC',
                        3: 'AAU',
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
                    sanctions: {
                        actions: {
                            ADD: 1,
                            UPDATE: 2,
                            DELETE: 3,
                            CHANGE_VENDOR: 4,
                        },
                        statuses: {
                            PENDING: 1,
                            DISMISSED: 2,
                            MERGED: 3,
                        }
                    },
                    reservations: {
                        actions: {
                            ADD: 1,
                            UPDATE: 2,
                        },
                        statuses: {
                            PENDING: 1,
                            DISMISSED: 2,
                            MERGED: 3,
                        }
                    }
                };
            },
        },
        watch: {
        },
        data() {
            return {
                isLoading: false,
                errorMessage: null,
                gyms: null,
                items: null,
                shown: null,
                current: {
                    reservation: 0,
                    body: 0
                },
            }
        },
        methods: {
            next(arr, cur) {
                return ((cur + 1) % arr.length);
            },

            prev(arr, cur) {
                return ((cur == 0 ? arr.length : cur) - 1);
            },

            showNext() {
                if (this.current.body !== null) {
                    this.current.reservation = this.next(this.items[this.current.body], this.current.reservation);
                    this.shown = this.items[this.current.body][this.current.reservation];
                }
            },

            showPrevious() {
                if (this.current.body !== null) {
                    this.current.reservation = this.prev(this.items[this.current.body], this.current.reservation);
                    this.shown = this.items[this.current.body][this.current.reservation];
                }
            },

            initialize() {
                try {
                    let reservation = [];
                    let allReservations = {
                        count: 0,
                    };

                    for (const g in this.gyms) {
                        if (this.gyms.hasOwnProperty(g)) {
                            const gym = this.gyms[g];

                            let usag_reservations = {};
                            let has_usag = false;

                            for (const s in gym.usag_reservations) {

                                if (gym.usag_reservations.hasOwnProperty(s)) {
                                    const item = gym.usag_reservations[s];

                                    has_usag = true;
                                    let reservation = _.cloneDeep(item);

                                    reservation.readable = {};

                                    reservation.timestamp = Moment(reservation.timestamp);
                                    reservation.readable.timestamp = reservation.timestamp.format('MM/DD/YYYY h:m:s a');

                                    reservation.created_at = Moment(reservation.created_at);
                                    reservation.readable.created_at = reservation.created_at.format('MM/DD/YYYY h:m:s a');

                                    reservation.updated_at = Moment(reservation.updated_at);
                                    reservation.readable.updated_at = reservation.updated_at.format('MM/DD/YYYY h:m:s a');

                                    delete reservation.gym_id;
                                    delete reservation.parent_id;
                                    delete reservation.usag_sanction_id;
                                    delete reservation.usag_sanction;

                                    if (!usag_reservations.hasOwnProperty(item.usag_sanction.number)) {
                                        usag_reservations[item.usag_sanction.number] = {
                                            sanction: item.usag_sanction.number,
                                            is_un_assigned: item.usag_sanction.status == 4 || item.usag_sanction.status == 1  ? true  :false,
                                            is_new: false,
                                            has_update: false,
                                            body: this.constants.bodies.USAG,
                                            // status: item.status,
                                            category: _.cloneDeep(item.usag_sanction.level_category),
                                            gym: {
                                                id: gym.id,
                                                name: gym.name,
                                                picture: gym.profile_picture
                                            },
                                            meet: _.cloneDeep(item.usag_sanction.meet),
                                            meetName: _.cloneDeep(item.usag_sanction.usag_meet_name),
                                            items: [],
                                            last_updated: reservation.timestamp,
                                            readable: {
                                                last_updated: reservation.timestamp.format('MM/DD/YYYY h:m:s a'),
                                            },
                                            url: '/gyms/' + gym.id + '/sanctions/usag/' + item.usag_sanction.number + '/reservation',
                                        };
                                    } else {
                                        if (reservation.timestamp > usag_reservations[item.usag_sanction.number].last_updated) {
                                            usag_reservations[item.usag_sanction.number].last_updated = reservation.timestamp;
                                            usag_reservations[item.usag_sanction.number].readable.last_updated = reservation.timestamp.format('MM/DD/YYYY h:m:s a');
                                        }
                                    }

                                    switch (reservation.action) {
                                        case this.constants.reservations.actions.ADD:
                                            usag_reservations[item.usag_sanction.number].is_new = true;
                                            break;

                                        case this.constants.reservations.actions.UPDATE:
                                            usag_reservations[item.usag_sanction.number].has_update = true;
                                            break;
                                    }

                                    usag_reservations[item.usag_sanction.number].items.push(reservation);
                                }
                            }

                            usag_reservations = Object.values(usag_reservations);
                            usag_reservations.sort((a, b) => {
                                if (a.last_updated < b.last_updated)
                                    return -1;

                                if (a.last_updated > b.last_updated)
                                    return 1;

                                return 0;
                            });
                            for (const s in usag_reservations) {
                                reservation.push(usag_reservations[s]);
                                usag_reservations[s].items.sort((a, b) => {
                                    if (a.timestamp < b.timestamp)
                                        return -1;

                                    if (a.timestamp > b.timestamp)
                                        return 1;

                                    return 0;
                                });
                            }

                            allReservations.count += usag_reservations.length;
                        }
                    }
                    allReservations[this.constants.bodies.USAG] = reservation;

                    this.items = allReservations;

                    this.current.body = null;
                    let possibleBodies = [
                        this.constants.bodies.USAG,
                        this.constants.bodies.USAIGC,
                        this.constants.bodies.AAU,
                    ];
                    possibleBodies.forEach(b => {
                        if (this.items.hasOwnProperty(b)) {
                            this.current.body = b;
                            return true;
                        }
                    });
                    this.current.reservation = 0;
                    this.shown = (
                        this.current.body === null ?
                        null :
                        this.items[this.current.body][this.current.reservation]
                    );
                } catch (error) {
                    this.errorMessage = 'Something went wrong while processing your reservation notification.'
                                        + 'Please contact us.<br/>' +
                                        '<span class="small"><strong>Error details:</strong> ' + error + '</span>';
                }
            }
        },
        beforeMount() {
            this.isLoading = true;
            axios.get('/api/user/reservations', {
                'params': {
                    '__managed': this.managed
                }
            }).then(result => {
                this.gyms = result.data.gyms;
                this.initialize();
            }).catch(error => {
                let msg = '';
                if (error.response) {
                    msg = error.response.data.message;
                } else if (error.request) {
                    msg = 'No server response.';
                } else {
                    msg = error.message;
                }
                this.errorMessage = 'Failed to load reservation notification. ' + msg;
            }).finally(() => {
                this.isLoading = false;
            });
        }
    }
</script>
