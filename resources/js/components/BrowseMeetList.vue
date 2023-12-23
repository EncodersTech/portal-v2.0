<template>
    <div>
        <div class="modal fade" id="modal-participating-gym" tabindex="-1" role="dialog" aria-labelledby="modal-participating-gym" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">

                    <div class="modal-header">
                        <h5 class="modal-title text-primary">
                            <span class="fas fa-fw fa-dumbbell"></span> Participating Gyms
                        </h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span class="fas fa-times" aria-hidden="true"></span>
                        </button>
                    </div>

                    <div class="modal-body">
                        <div>
                            <span v-if="gymFilters.length == 0" style="color:red">Have No Participating Club Yet</span>
                            <ul v-if="gymFilters.length > 0" v-for="gymFilter in gymFilters">
                                <li>{{ gymFilter.name }}</li>
                            </ul>
                            
                        </div>

                        <div class="text-right mt-3">
                            <button class="btn btn-sm btn-secondary" data-dismiss="modal">
                                <span class="far fa-fw fa-times-circle"></span> Close
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="alert alert-danger" :class="{ 'd-none': !isError }">
            <span class="fas fa-fw fa-times-circle"></span> Whoops !<br/>
            <div class="mt-1" v-html="errorMessage"></div>
        </div>
        <div class="alert alert-danger" :class="{ 'd-none': !isErrorSoft }">
            <span class="fas fa-fw fa-times-circle"></span> Whoops !<br/>
            <div class="mt-1" v-html="errorMessage"></div>
        </div>

        <div class="row">
            <div class="col-lg-3 mb-3">
                <div class="d-flex flex-row flex-nowrap align-bodyItems-center my-2 border-bottom">
                    <h6 class="flex-grow-1 font-weight-bold mb-0">
                        <span class="fas fa-fw fa-filter"></span> Meet Filters
                    </h6>
                    <a href="#" class="small text-danger px-1" @click="clearFilters">
                        <span class="fas fa-fw fa-eraser"></span> Clear
                    </a>
                </div>

                <div>
                    <label for="filter_name" class="small mb-1">
                        <span class="fas fa-fw fa-calendar-week"></span> Meet Name
                    </label>
                    <input type="text" class="form-control form-control-sm" id="filter_name"
                        v-model="filters.name">
                </div>

<!--                <div class="form-check mt-2">-->
<!--                    <input class="form-check-input" type="checkbox" id="filter_open"-->
<!--                        v-model="filters.open" >-->
<!--                    <label class="form-check-label" for="filter_open">-->
<!--                        Open registrations only-->
<!--                    </label>-->
<!--                </div>-->

                <div class="mt-2">
                    <label for="filter_state" class="small mb-1">
                        <span class="fas fa-fw fas fa-fw fa-map-marked"></span> State
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
                    <label for="filter_status" class="small mb-1">
                        <span class="fas fa-fw fas fa-fw fa-map-marked"></span> Registration Status
                    </label>
                    <select type="text" class="form-control form-control-sm" id="filter_status"
                            v-model="filters.status">
                        <option value="">(all)</option>
                        <option value="1">Close</option>
                        <option value="2">Open</option>
                        <option value="3">Late</option>
                        <option value="4" checked="checked">Opening Soon</option>
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
                    <div class="text-info small mb-1"><span class="fas fa-info-circle"></span> 
                        In the Host column, green color means you can click to view its participating clubs.
                    </div>
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
                                            <th scope="col" class="align-middle" @click="sortBy('name')">
                                                Meet Name
                                                <span v-if="sortColumn == 'name'">
                                                    <span :class="'fas fa-fw fa-caret-' + sortDirection"></span>
                                                </span>
                                            </th>
                                            <th scope="col" class="align-middle">
                                                Registration Status
                                            </th>
                                            <th scope="col" class="align-middle" @click="sortBy('start_date')">
                                                Start Date
                                                <span v-if="sortColumn == 'start_date'">
                                                    <span :class="'fas fa-fw fa-caret-' + sortDirection"></span>
                                                </span>
                                            </th>
                                            <th scope="col" class="align-middle" @click="sortBy('end_date')">
                                                End Date
                                                <span v-if="sortColumn == 'end_date'">
                                                    <span :class="'fas fa-fw fa-caret-' + sortDirection"></span>
                                                </span>
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
                                           <!-- <th scope="col" class="align-middle">
                                                Participating Clubs
                                            </th> -->
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="item in items" :key="item.id">
                                            <td class="meet-picture-column align-middle" v-bind:class="(item.is_featured)?'corner-cut corner-red background-feature':'' " v-bind:title="(item.is_featured)?'Featured Meet':''">
                                                <img class="meet-picture rounded-circle ml-2" alt="Meet Picture"
                                                        :src="item.profile_picture" title="Meet Picture">
                                            </td>

                                            <td class="align-middle">
                                                <a :href="'/meets/' + item.id " class="font-weight-bold">
                                                    {{ item.name }}
                                                </a>
                                            </td>

                                            <td class="align-middle">
                                                <span v-if="item.registration_status == constants.status.CLOSED" class="badge badge-danger">
                                                    Closed
                                                </span>
                                                <span v-else-if="item.registration_status == constants.status.OPEN" class="badge badge-success">
                                                    Open
                                                </span>
                                                <span v-else-if="item.registration_status == constants.status.LATE" class="badge badge-warning">
                                                    Late
                                                </span>
                                                <span v-else class="badge badge-info">
                                                    Opening Soon
                                                </span>
                                            </td>

                                            <td class="align-middle">
                                                {{ item.start_date_display }}
                                            </td>

                                            <td class="align-middle">
                                                {{ item.end_date_display }}
                                            </td>

                                            <td class="align-middle">
                                                {{ item.venue_city }}, {{ item.venue_state.code }}
                                            </td>

                                            <td class="align-middle">
                                                <span v-if="item.show_participate_clubs" @click="showClubs(item.id)" title="View Participating Clubs" style="color: green;cursor: pointer;font-weight: 800;">{{ item.gym.name }}</span>
                                                <span v-else >{{ item.gym.name }}</span>
                                                
                                            </td>

                                            <td class="align-middle">
                                                <span v-for="b in item.bodies" :key="b"
                                                    class="badge badge-secondary ml-1 mt-1">
                                                    {{ constants.bodyNames[b] }}
                                                </span>
                                            </td>
                                            <!-- <td class="align-middle">
                                                 <button v-if="item.show_participate_clubs" class="btn btn-sm btn-primary" @click="showClubs(item.id)">View</button>
                                                
                                             </td> -->
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
    import Pager from './Paging/Pager.vue';
    import DatePicker from 'vuejs-datepicker';

    export default {
        name: 'BrowseMeetList',
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
            }
        },
        computed: {
            hasNoItems() {
                return (this.items.length < 1);
            },
            constants() {
                return {
                    status: {
                        CLOSED: 1,
                        OPEN: 2,
                        LATE: 3,
                        SOON: 4,
                    },
                    bodyNames: {
                        1: 'USAG',
                        2: 'USAIGC',
                        3: 'AAU',
                        4: 'NGA'
                    }
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
                isErrorSoft: false,
                statusText: '',
                errorMessage: '',
                items: [],
                gymFilters: [],
                paging: {
                    current: 1,
                    total: 1
                },
                limit: 10,
                sortColumn: 'start_date',
                sortDirection: 'down',
                limitMultiplier: 10,
                debounce_delay: 1000,
                states: [],
                filters: {
                    name: '',
                    // open: false,
                    from: null,
                    to: null,
                    state: '',
                    status: '2',
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
                    // open: false,
                    from: null,
                    to: null,
                    state: '',
                    status: '4',
                    bodies: {
                        usag: false,
                        usaigc: false,
                        aau: false,
                        nga: false
                    }
                };
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
                if ( this.items.length < 1)
                    return

                this.items.sort((a, b) => {
                    let va = a[this.sortColumn];
                    let vb = b[this.sortColumn];

                    if (va < vb)
                        return -1 * (this.sortDirection == 'up' ? 1 : -1);

                    if (va > vb)
                        return 1 * (this.sortDirection == 'up' ? 1 : -1);

                    return 0;
                });
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

                axios.get('/api/app/meets', {
                    'params': {
                        '__managed': this.managed,
                        page: this.paging.current,
                        limit: this.limit,
                        state: this.filters.state,
                        status: this.filters.status,
                        from: this.filters.from != null ? Moment(this.filters.from).format('MM/DD/YYYY') : null,
                        to: this.filters.to != null ? Moment(this.filters.to).format('MM/DD/YYYY') : null,
                        usag: this.filters.bodies.usag ? 1 : 0,
                        usaigc: this.filters.bodies.usaigc ? 1 : 0,
                        aau: this.filters.bodies.aau ? 1 : 0,
                        nga: this.filters.bodies.nga ? 1 : 0,
                        name: this.filters.name,
                        // open: this.filters.open ? 1 : 0,
                    }
                }).then(result => {
                    this.items = result.data.meets;

                    for (let i in this.items) {
                        let item = this.items[i];

                        item.start_date = Moment(item.start_date);
                        item.start_date_display = item.start_date.format('MM/DD/YYYY');
                        item.end_date = Moment(item.end_date);
                        item.end_date_display = item.end_date.format('MM/DD/YYYY');

                        let bodies = [];
                        for (let j in item.categories) {
                            let category = item.categories[j];

                            if (!bodies.includes(category.pivot.sanctioning_body_id))
                                bodies.push(category.pivot.sanctioning_body_id);
                        }

                        item.bodies = bodies;
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
            },

            showClubs(id){
                console.log(id);
                this.isErrorSoft = false;
                // this.isLoading = true;
                axios.get('/api/app/meet/gym/participant/'+id, {
                    'params': {
                        '__managed': this.managed,
                    }
                    
                }).then(result => {
                    this.gymFilters = result.data.gym;
                    // console.log(this.gymFilters);
                     $('#modal-participating-gym').modal('show');

                }).catch(error => {
                    let msg = '';
                    if (error.response) {
                        msg = error.response.data.message;
                    } else if (error.request) {
                        msg = 'No server response.';
                    } else {
                        msg = error.message;
                    }
                    this.errorMessage = msg;
                    this.isErrorSoft = true;
                }).finally(() => {
                    // this.isLoading = false;
                });
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
