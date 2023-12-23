<template>
    <div>
        <div class="alert alert-danger" :class="{ 'd-none': !isError }">
            <span class="fas fa-fw fa-times-circle"></span> Whoops !<br/>
            <div class="mt-1" v-html="errorMessage"></div>
        </div>

        <div class="row">
            <div class="col-lg-3 mb-3">
                <div class="d-flex flex-row flex-nowrap align-bodyItems-center my-2 border-bottom">
                    <h6 class="flex-grow-1 font-weight-bold mb-0">
                        <span class="fas fa-fw fa-filter"></span> Filters
                    </h6>
                    <a href="#" class="small text-danger px-1" @click="clearFilters">
                        <span class="fas fa-fw fa-eraser"></span> Clear
                    </a>
                </div>

                <div>
                    <label for="filter_name" class="small mb-1">
                        <span class="fas fa-fw fa-calendar-week"></span> Name
                    </label>
                    <input type="text" class="form-control form-control-sm" id="filter_name"
                        v-model="filters.name">
                </div>

                <div class="mt-2">
                    <label for="filter_status" class="small mb-1">
                        <span class="fas fa-fw fa-question-circle"></span> Status
                    </label>
                    <select class="form-control form-control-sm" id="filter_status"
                        v-model="filters.status">
                        <option value="">All</option>
                        <option v-for="s in constants.registrations.statuses._array" :key="s"
                            :value="s">{{ constants.registrations.statuses[s] }}</option>
                    </select>
                </div>

                <div class="mt-2">
                    <label for="filter_state" class="small mb-1">
                        <span class="fas fa-fw fa-map-marked"></span> State
                    </label>
                    <select type="text" class="form-control form-control-sm" id="filter_state"
                        v-model="filters.state">
                        <option value="">(all)</option>
                        <option v-for="state in states" :key="state.id" :value="state.code">
                            {{ state.name }}
                        </option>
                    </select>
                </div>

                <div class="mt-2">
                    <label for="filter_from" class="small mb-1">
                        <span class="fas fa-fw fa-calendar-alt"></span> From
                    </label>
                    <datepicker :input-class="'form-control form-control-sm bg-white'" format="MM/dd/yyyy"
                        v-model="filters.from" :bootstrap-styling="true" :typeable="true">
                    </datepicker>
                </div>

                <div class="mt-2">
                    <label for="filter_to" class="small mb-1">
                        <span class="fas fa-fw fa-calendar-alt"></span> To
                    </label>
                    <datepicker :input-class="'form-control form-control-sm bg-white'" format="MM/dd/yyyy"
                        v-model="filters.to" :bootstrap-styling="true" :typeable="true">
                    </datepicker>
                </div>

                <div class="form-check mt-2">
                    <input class="form-check-input" type="checkbox" id="filter_usag"
                        v-model="filters.bodies.usag" >
                    <label class="form-check-label" for="filter_usag">
                        USAG
                    </label>
                </div>

                <div class="form-check mt-2">
                    <input class="form-check-input" type="checkbox" id="filter_usaigc"
                        v-model="filters.bodies.usaigc" >
                    <label class="form-check-label" for="filter_usaigc">
                        USAIGC
                    </label>
                </div>

                <div class="form-check mt-2">
                    <input class="form-check-input" type="checkbox" id="filter_aau"
                        v-model="filters.bodies.aau" >
                    <label class="form-check-label" for="filter_aau">
                        AAU
                    </label>
                </div>

                <div class="form-check mt-2">
                    <input class="form-check-input" type="checkbox" id="filter_nga"
                           v-model="filters.bodies.nga" >
                    <label class="form-check-label" for="filter_nga">
                        NGA
                    </label>
                </div>
            </div>

            <div class="col">
                <div v-if="!isError">
                    <div class="d-flex flex-row flex-nowrap mb-1">
                        <div class="flex-grow-1 small text-gray-600 mr-1">{{ statusText }}</div>
                        <div class="mr-1">
                            <select class="form-control form-control-sm" v-model="limit" @change="onLimitChanged">
                                <option v-for="n in 5" :key="n" :value="n * limitMultiplier">
                                    {{ n * limitMultiplier }}
                                </option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <div class="small" v-if="isLoading">
                            <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true">
                            </span> Loading {{ plural }}, please wait ...
                        </div>
                        <div v-else>
                            <div class="alert alert-info" v-if="hasNoItems">
                                <span class="fas fa-info-circle"></span> There are no {{ plural }}.
                            </div>
                            <div class="table-responsive-lg" v-else>
                                <table class="table table-sm table-striped">
                                    <thead class="thead-dark">
                                        <tr>
                                            <th scope="col" class="meet-picture-column text-center align-middle">
                                                <span class="fas fa-fw fa-image"></span>
                                            </th>
                                            <th scope="col" class="align-middle">
                                                Name
                                            </th>
                                            <th scope="col" class="align-middle">
                                                Status
                                            </th>
                                            <th scope="col" class="align-middle">
                                                Start Date
                                            </th>
                                            <th scope="col" class="align-middle">
                                                End Date
                                            </th>
                                            <th scope="col" class="align-middle">
                                                Location
                                            </th>
                                            <th scope="col" class="align-middle">
                                                Host
                                            </th>
                                            <th scope="col" class="align-middle">
                                                Sanctioning Bodies
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="item in items" :key="item.meet.id">
                                            <td class="meet-picture-column align-middle">
                                                <img class="meet-picture rounded-circle" alt="Meet Picture"
                                                        :src="item.meet.profile_picture" title="Meet Picture">
                                            </td>

                                            <td class="align-middle">
                                                <a :href="'/gyms/' + gymId + '/registration/' + item.id " class="font-weight-bold">
                                                    {{ item.meet.name }}
                                                </a>
                                            </td>

                                            <td class="align-middle">
                                                <span v-if="item.status == constants.registrations.statuses.Canceled" class="badge badge-danger">
                                                    Canceled / Rejected
                                                </span>
                                                <span v-else-if="item.status == constants.registrations.statuses.Registered" class="badge badge-success">
                                                    Registered
                                                    <span v-if="item.has_pending_transactions"
                                                        class="text-warning fas fa-exclamation-triangle">
                                                    </span>
                                                    <span v-if="item.has_repayable_transactions"
                                                        class="text-danger fas fa-exclamation-triangle">
                                                    </span>
                                                </span>
                                                <span v-else-if="item.status == constants.registrations.statuses.Waitlist" class="badge badge-warning">
                                                    Waitlist (Pending)
                                                </span>
                                                <span v-else class="badge badge-info">
                                                    Waitlist (Confirmed)
                                                </span>
                                            </td>

                                            <td class="align-middle">
                                                {{ item.meet.start_date_display }}
                                            </td>

                                            <td class="align-middle">
                                                {{ item.meet.end_date_display }}
                                            </td>

                                            <td class="align-middle">
                                                {{ item.meet.venue_city }}, {{ item.meet.venue_state.code }}
                                            </td>

                                            <td class="align-middle">
                                                {{ item.meet.gym.name }}
                                            </td>

                                            <td class="align-middle">
                                                <span v-for="b in item.meet.bodies" :key="b"
                                                    class="badge badge-secondary ml-1 mt-1">
                                                    {{ constants.bodies[b] }}
                                                </span>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <ag-pager v-bind="paging" @pager-page-changed="onPageChanged"
                                @pager-request-page-change="onPageChangeRequest"></ag-pager>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style lang="css" scoped>
    .membership {
        margin-bottom: 0.25rem;
        border-bottom: 1px solid #CED4DA

    }

    .membership:last-child {
        margin-bottom: 0;
        border-bottom: none;
    }

    .clickable {
        cursor: pointer;
    }
</style>


<script>
    import Pager from '../Paging/Pager.vue';
    import DatePicker from 'vuejs-datepicker';

    export default {
        name: 'JoinedMeetList',
        components: {
            'ag-pager': Pager,
            'datepicker': DatePicker
        },
        props: {
            singular: {
                type: String,
                default: 'item'
            },
            plural: {
                type: String,
                default: 'items'
            },
            prefix: {
                type: String,
                default: 'items-'
            },
            gymId: {
                type: Number,
                default: null
            },
            managed: {
                type: Number,
                default: null
            }
        },
        computed: {
            hasNoItems() {
                return (this.items.length < 1);
            },
            constants() {
                return {
                    bodies: {
                        1: 'USAG',
                        2: 'USAIGC',
                        3: 'AAU',
                        4: 'NGA',
                        USAG: 1,
                        USAIGC: 2,
                        AAU: 3,
                        NGA: 4
                    },
                    registrations: {
                        statuses: {
                            _array: [1, 2, 3, 4],
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
                };
            },
        },
        watch: {
            filters: {
                deep: true,
                handler() {
                    this.isLoading = true;
                    _.debounce(this.filtersChanged, this.debounce_delay)(this);
                }
            }
        },
        data() {
            return {
                isLoading: false,
                isError: false,
                statusText: '',
                errorMessage: '',
                items: [],
                paging: {
                    current: 1,
                    total: 1
                },
                limit: 5,
                limitMultiplier: 5,
                debounce_delay: 1000,
                states: [],
                filters: {
                    name: '',
                    status: '',
                    from: null,
                    to: null,
                    state: '',
                    bodies: {
                        usag: false,
                        usaigc: false,
                        aau: false,
                        nga: false
                    }
                }
            }
        },
        methods: {
            clearFilters() {
                this.filters = {
                    name: '',
                    status: '',
                    from: null,
                    to: null,
                    state: '',
                    bodies: {
                        usag: false,
                        usaigc: false,
                        aau: false,
                        nga: false
                    }
                };
            },

            filtersChanged() {
                this.showPage(1);
            },

            showPage(page) {
                this.paging.current = page;
                this.fetchCurrentPage();
            },

            onPageChangeRequest(page) {
                this.showPage(page);
            },

            onPageChanged(val) {
                this.paging.current = val.current;
            },

            fetchCurrentPage() {
                this.isLoading = true;

                axios.get('/api/gym/' + this.gymId + '/joined', {
                    'params': {
                        '__managed': this.managed,
                        page: this.paging.current,
                        limit: this.limit,
                        state: this.filters.state,
                        from: this.filters.from != null ? Moment(this.filters.from).format('MM/DD/YYYY') : null,
                        to: this.filters.to != null ? Moment(this.filters.to).format('MM/DD/YYYY') : null,
                        usag: this.filters.bodies.usag ? 1 : 0,
                        usaigc: this.filters.bodies.usaigc ? 1 : 0,
                        aau: this.filters.bodies.aau ? 1 : 0,
                        nga: this.filters.bodies.nga ? 1 : 0,
                        name: this.filters.name,
                        status: this.filters.status,
                    }
                }).then(result => {
                    this.items = result.data.registrations;

                    for (let i in this.items) {
                        let item = this.items[i];

                        item.meet.start_date = Moment(item.meet.start_date);
                        item.meet.start_date_display = item.meet.start_date.format('MM/DD/YYYY');
                        item.meet.end_date = Moment(item.meet.end_date);
                        item.meet.end_date_display = item.meet.end_date.format('MM/DD/YYYY');

                        let bodies = [];
                        for (let j in item.meet.categories) {
                            let category = item.meet.categories[j];

                            if (!bodies.includes(category.pivot.sanctioning_body_id))
                                bodies.push(category.pivot.sanctioning_body_id);
                        }

                        item.meet.bodies = bodies;
                    }

                    this.paging.total = Math.ceil(result.data.total / this.limit);

                    let entriesCount = this.items.length + ' entries';
                    this.statusText = 'Showing page ' + this.paging.current + ' of ' + this.paging.total

                    this.isLoading = false;
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
            },

            onLimitChanged() {
                Cookies.set(this.prefix + 'items-per-page', this.limit);
                this.fetchCurrentPage();
            },

            loadSavedSettings() {
                let itemsPerPage = Utils.toInt(Cookies.get(this.prefix + 'items-per-page'));
                this.limit = ((itemsPerPage != NaN) && (itemsPerPage > 0) ? itemsPerPage : 5);
            }
        },
        mounted() {
            this.isLoading = true;
            this.loadSavedSettings();

            axios.get('/api/app/states').then(result => {
                this.states = result.data.states;
                this.isLoading = false;
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

            this.showPage(1);
        }
    }
</script>
