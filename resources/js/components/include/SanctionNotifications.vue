<template>
    <div>
        <div v-if="isLoading" class="text-center p-3">
            <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
            Loading sanction notifications, please wait ...
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
                    <span class="fas fa-fw fa-receipt"></span> Sanctions
                </h5>

                <div class="row pt-3">
                    <div class="col-12 col-xs-12 col-sm-3 col-md-12 col-lg-3 mb-1" style="font-size:12px;" v-for="item  in items[current.body]">
                    <div class="small-box text-white" :class="[item.is_new ? 'bg-success' : 'bg-danger']">
                        <div class="inner">
                            <h5 class="mb-3" style="font-size: 15px;">
                                <span v-if="item.is_new">
                                    <span class="fas fa-fw fa-plus-square"></span>
                                        New {{ constants.bodies[item.body] }} Sanction
                                </span>
                                <span v-else>
                                    <span class="fas fa-fw fa-pen-square"></span>
                                        {{ constants.bodies[item.body] }} Sanction Update
                                </span>
                            </h5>
                            <div class="" style="letter-spacing: 0.5px">
                                <div class="">
                                    <strong>Sanction No.:</strong> {{ item.number }}
                                </div>
                                <div class="">
                                    <strong>Gym:</strong> {{ item.gym.name }}
                                </div>
                                <div class="">
                                    <strong>Meet:</strong>
                                    <span v-if="item.meet !== null">
                                    {{ item.meet.name }}
                                </span>
                                    <span v-else-if="item.usag_meet_name !== null">
                                    {{ item.usag_meet_name }} (received from USAG)
                                </span>
                                </div>
                                <div class="">
                                    <strong>Category:</strong> {{ item.category.name }}
                                </div>
                                <div class="">
                                    <strong>Type:</strong>
                                    <span class="badge badge-pill badge-success" v-if="item.is_new">New Sanction</span>
                                    <span class="badge badge-pill badge-warning"
                                          v-if="item.has_update">Details Updated</span>
                                    <span class="badge badge-pill badge-danger" v-if="item.has_deletion">Sanction Removed</span>
                                    <span class="badge badge-pill badge-dark" v-if="item.has_vender_change">Vendor Change</span>
                                </div>
                                <div class="">
                                    <strong>Last updated:</strong> {{ item.readable.last_updated }}
                                </div>
                            </div>
                        </div>

                        <a :href='item.url' class="small-box-footer mt-2">
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
        name: 'SanctionNotifications',
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
                    sanction: 0,
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
                    this.current.sanction = this.next(this.items[this.current.body], this.current.sanction);
                    this.shown = this.items[this.current.body][this.current.sanction];
                }
            },

            showPrevious() {
                if (this.current.body !== null) {
                    this.current.sanction = this.prev(this.items[this.current.body], this.current.sanction);
                    this.shown = this.items[this.current.body][this.current.sanction];
                }
            },

            initialize() {
                try {
                    let allSanctions = {
                        count: 0,
                    };
                    for (const g in this.gyms) {
                        if (this.gyms.hasOwnProperty(g)) {
                            const gym = this.gyms[g];
                            let usag_sanctions = {};
                            let has_usag = false;
                            for (const s in gym.usag_sanctions) {
                                if (gym.usag_sanctions.hasOwnProperty(s)) {
                                    const item = gym.usag_sanctions[s];

                                    has_usag = true;

                                    let sanction = _.cloneDeep(item);

                                    sanction.readable = {};

                                    sanction.timestamp = Moment(sanction.timestamp);
                                    sanction.readable.timestamp = sanction.timestamp.format('MM/DD/YYYY h:m:s a');

                                    sanction.created_at = Moment(sanction.created_at);
                                    sanction.readable.created_at = sanction.created_at.format('MM/DD/YYYY h:m:s a');

                                    sanction.updated_at = Moment(sanction.updated_at);
                                    sanction.readable.updated_at = sanction.updated_at.format('MM/DD/YYYY h:m:s a');

                                    delete sanction.number;
                                    delete sanction.gym_id;
                                    delete sanction.parent_id;
                                    delete sanction.level_category_id;
                                    delete sanction.level_category;
                                    delete sanction.meet_id;
                                    delete sanction.meet;

                                    if (!usag_sanctions.hasOwnProperty(item.number)) {
                                        usag_sanctions[item.number] = {
                                            number: item.number,
                                            usag_meet_name: item.usag_meet_name,
                                            is_new: false,
                                            has_update: false,
                                            has_deletion: false,
                                            // status: item.status,
                                            has_vender_change: false,
                                            body: this.constants.bodies.USAG,
                                            category: _.cloneDeep(item.level_category),
                                            gym: {
                                                id: gym.id,
                                                name: gym.name,
                                                picture: gym.profile_picture
                                            },
                                            meet: _.cloneDeep(item.meet),
                                            items: [],
                                            last_updated: sanction.timestamp,
                                            readable: {
                                                last_updated: sanction.timestamp.format('MM/DD/YYYY h:m:s a'),
                                            },
                                            url: '/gyms/' + gym.id + '/sanctions/usag/' + item.number,
                                        };
                                    } else {
                                        if (sanction.timestamp > usag_sanctions[item.number].last_updated) {
                                            usag_sanctions[item.number].last_updated = sanction.timestamp;
                                            usag_sanctions[item.number].readable.last_updated = sanction.timestamp.format('MM/DD/YYYY h:m:s a');
                                        }
                                    }

                                    switch (sanction.action) {
                                        case this.constants.sanctions.actions.ADD:
                                            usag_sanctions[item.number].is_new = true;
                                            break;

                                        case this.constants.sanctions.actions.UPDATE:
                                            usag_sanctions[item.number].has_update = true;
                                            break;

                                        case this.constants.sanctions.actions.DELETE:
                                            usag_sanctions[item.number].has_deletion = true;
                                            break;

                                        case this.constants.sanctions.actions.CHANGE_VENDOR:
                                            usag_sanctions[item.number].has_vender_change = true;
                                            break;
                                    }

                                    usag_sanctions[item.number].items.push(sanction);
                                }
                            }

                            usag_sanctions = Object.values(usag_sanctions);
                            usag_sanctions.sort((a, b) => {
                                if (a.last_updated < b.last_updated)
                                    return -1;

                                if (a.last_updated > b.last_updated)
                                    return 1;

                                return 0;
                            });
                            for (const s in usag_sanctions) {
                                usag_sanctions[s].items.sort((a, b) => {
                                    if (a.timestamp < b.timestamp)
                                        return -1;

                                    if (a.timestamp > b.timestamp)
                                        return 1;

                                    return 0;
                                });
                            }

                            allSanctions[this.constants.bodies.USAG] = usag_sanctions;
                            allSanctions.count += usag_sanctions.length;
                        }
                    }
                    this.items = allSanctions;

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
                    this.current.sanction = 0;
                    this.shown = (
                        this.current.body === null ?
                        null :
                        this.items[this.current.body][this.current.sanction]
                    );

                } catch (error) {
                    this.errorMessage = 'Something went wrong while processing your sanction notification.'
                                        + 'Please contact us.<br/>' +
                                        '<span class="small"><strong>Error details:</strong> ' + error + '</span>';
                }
            }
        },
        beforeMount() {
            this.isLoading = true;
            axios.get('/api/user/sanctions', {
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
                this.errorMessage = 'Failed to load sanction notification. ' + msg;
            }).finally(() => {
                this.isLoading = false;
            });
        }
    }
</script>
