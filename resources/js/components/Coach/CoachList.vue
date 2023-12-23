<template>
    <div>
        <div class="alert alert-danger small" :class="{ 'd-none': !isError}">
                <span class="fas fa-fw fa-times-circle"></span> <span v-html="errorMessage"></span>
        </div>

        <div class="row" :class="{ 'd-none': isError}">
            <div class="col-lg-auto pr-lg-0">
                <div class="d-flex flex-row flex-nowrap align-bodyItems-center my-2 border-bottom">
                    <h6 class="flex-grow-1 font-weight-bold mb-0">
                        <span class="fas fa-fw fa-filter"></span> Filters
                    </h6>
                    <a href="#" class="small text-danger px-1" @click="clearFilters">
                        <span class="fas fa-fw fa-eraser"></span> Clear
                    </a>
                </div>

                <div class="mb-3">
                    <label for="filter_gender" class="small mb-1">
                        <span class="fas fa-fw fa-venus-mars"></span> Gender
                    </label>
                    <select id="filter_gender" v-model="filters.gender" @change="filtersChanged"
                        class="form-control form-control-sm">
                        <option value="all">All</option>
                        <option value="female">Female</option>
                        <option value="male">Male</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label for="filter_gender" class="small mb-1">
                        <span class="fas fa-fw fa-file-signature"></span> Sanctioning Body
                    </label>

                    <div v-for="body in sanctioningBodies" :key="body.id" class="border-bottom border-secondary mb-2 pb-1">
                        <div class="form-check">
                            <input :id="'filter-body-' + body.id" class="form-check-input" type="checkbox"
                                v-model="body.checked"
                                @change="bodyCheckChanged($event, body)">
                            <label class="form-check-label" :for="'filter-body-' + body.id">
                                {{ body.initialism }}
                            </label>
                        </div>
                    </div>

                </div>
            </div>
            <div class="col">
                <div class="small" :class="{ 'd-none': !isLoading }">
                    <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true">
                    </span> Loading coaches, please wait ...
                </div>

                <div :class="{'d-none': isLoading}">
                    <div class="d-flex flex-row flex-nowrap mb-1">
                        <div class="flex-grow-1 small text-gray-600 mr-1">{{ statusText }}</div>
                        <div :class="{'d-none': showAll }" class="mr-1">
                            <select class="form-control form-control-sm" v-model="limit" @change="onLimitChanged">
                                <option v-for="n in 5" :key="n" :value="n * limitMultiplier">
                                    {{ n * limitMultiplier }}
                                </option>
                            </select>
                        </div>
                        <div>
                            <button class="btn btn-sm btn-info" @click="toggleShowAll">
                                <span :class="{'d-none': !showAll}">
                                    <span class="fas fa-copy"></span> Paginate
                                </span>
                                <span :class="{'d-none': showAll}">
                                    <span class="fas fa-eye"></span> Show All
                                </span>
                            </button>
                        </div>
                    </div>

                    <div class="alert alert-info" :class="{ 'd-none': !hasNoCoaches }">
                        <span class="fas fa-info-circle"></span> You do not have any active coaches
                        <span v-if="filtersApplied">matching the selected criteria</span>.
                    </div>

                    <div class="table-responsive-lg" :class="{ 'd-none': hasNoCoaches }">
                        <table class="table table-sm table-striped">
                            <thead class="thead-dark">
                                <tr>
                                    <th scope="col" class="align-middle">
                                        <input type="checkbox" id="coach-select-all" @change="selectAllCheckChanged()"
                                            :indeterminate.prop="selectAll.intermediate" v-model="selectAll.checked">
                                    </th>
                                    <th scope="col" class="align-middle clickable" @click="sortBy('first_name')">
                                        First Name
                                        <span v-if="sortColumn == 'first_name'">
                                            <span :class="'fas fa-fw fa-caret-' + sortDirection"></span>
                                        </span>
                                    </th>
                                    <th scope="col" class="align-middle clickable" @click="sortBy('last_name')">
                                        Last Name
                                        <span v-if="sortColumn == 'last_name'">
                                            <span :class="'fas fa-fw fa-caret-' + sortDirection"></span>
                                        </span>
                                    </th>
                                    <th scope="col" class="align-middle clickable" @click="sortBy('gender')">
                                        Gender
                                        <span v-if="sortColumn == 'gender'">
                                            <span :class="'fas fa-fw fa-caret-' + sortDirection"></span>
                                        </span>
                                    </th>
                                    <th scope="col" class="align-middle clickable" @click="sortBy('dob')">
                                        Date Of Birth
                                        <span v-if="sortColumn == 'dob'">
                                            <span :class="'fas fa-fw fa-caret-' + sortDirection"></span>
                                        </span>
                                    </th>
                                    <th scope="col" class="align-middle">Memberships</th>
                                    <th scope="col" class="text-right align-middle"></th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="coach in visibleCoaches" :key="coach.id">
                                    <td class="align-middle">
                                        <input type="checkbox" class="coach-select-checkbox"
                                        v-model="coach.checked" @change="coachCheckChanged(coach)">
                                    </td>

                                    <td class="align-middle">
                                        {{ coach.first_name }}
                                    </td>

                                    <td class="align-middle">
                                        {{ coach.last_name }}
                                    </td>

                                    <td class="align-middle">
                                        {{ coach.gender_display }}
                                    </td>

                                    <td class="align-middle">
                                        {{ coach.dob_display }}
                                    </td>

                                    <td class="align-middle membership-list">
                                        <div v-if="coach.usag_no != null" class="small membership">
                                            <strong>USAG</strong>
                                            <span v-if="coach.usag_active" class="text-gray-600">(Active)</span>
<!--                                                No. <span class="text-info font-weight-bold">{{ coach.usag_no }}</span>-->
                                        </div>

                                        <div v-if="coach.usaigc_no != null" class="small membership">
                                            <strong>USAIGC</strong>
                                            <span v-if="coach.usaigc_background_check" class="text-gray-600">(Background Checked)</span>
<!--                                                No. <span class="text-info font-weight-bold">IGC{{ coach.usaigc_no }}</span>-->
                                        </div>

                                        <div v-if="coach.aau_no != null" class="small membership">
                                            <strong>AAU</strong>
<!--                                                No. <span class="text-info font-weight-bold">{{ coach.aau_no }}</span>-->
                                        </div>

                                        <div v-if="coach.nga_no != null" class="small membership">
                                            <strong>NGA</strong>
<!--                                                No. <span class="text-info font-weight-bold">{{ coach.nga_no }}</span>-->
                                        </div>
                                    </td>

                                    <!--
                                    <td class="align-middle">
                                        {{ coach.tshirt != null ? coach.tshirt.size : '—' }}
                                    </td>
                                    -->

                                    <td class="text-right align-middle">
                                        <div class="mb-1 mr-1 d-inline-block">
                                            <a :href="'/gyms/' + gym + '/coaches/' + coach.id"
                                                class="btn btn-sm btn-info" title="View">
                                                    <span class="fas fa-fw fa-eye"></span>
                                            </a>
                                        </div>

                                        <div class="mb-1 mr-1 d-inline-block">
                                            <a :href="'/gyms/' + gym + '/coaches/' + coach.id + '/edit'"
                                                class="btn btn-sm btn-success" title="Edit">
                                                    <span class="fas fa-fw fa-edit"></span>
                                            </a>
                                        </div>

                                        <div class="mb-1 mr-1 d-inline-block">
                                            <button class="btn btn-sm btn-danger" :class="{ 'd-none': coach.removing }"
                                                title="Remove" @click="removeCoach(coach)">
                                                <span class="fas fa-fw fa-trash"></span>
                                            </button>

                                            <button class="btn btn-sm btn-outline-danger" :class="{ 'd-none': !coach.removing }"
                                                title="Remove">
                                                <span class="spinner-border spinner-border-sm" role="status"
                                                    aria-hidden="true">
                                                </span>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <ag-pager :class="{'d-none': showAll }" v-bind="paging" @pager-page-changed="onPageChanged"
                        @pager-request-page-change="onPageChangeRequest"></ag-pager>
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

    export default {
        name: 'CoachList',
        components: {
            'ag-pager': Pager
        },
        props: {
            gym: Number,
            managed: {
                default: null,
                type: Number
            },
            search: {
                type: String,
                default: ''
            }
        },
        computed: {
        },
        watch: {
            search() {
                this.filters.search = this.search;
                this.isLoading = true;
                this.filtersChanged();
            },

            filters() {
                this.isLoading = true;
                this.filtersChanged();
            }
        },
        data() {
            return {
                isLoading: false,
                isError: false,
                isBusy: false,
                hasNoCoaches: false,
                filtersApplied: false,
                showAll: false,
                statusText: '',
                errorMessage: '',
                selectAll: {
                    checked: false,
                    intermediate: false
                },
                filters: {
                    gender: 'all',
                    bodies: [],
                    search: ''
                },
                sanctioningBodies: [],
                visibleCoaches: [],
                filteredCoaches: [],
                coaches: [],
                selectedCoaches: [],
                paging: {
                    current: 1,
                    total: 1
                },
                limit: 5,
                limitMultiplier: 5,
                debounce_delay: 500,
                sortColumn: 'first_name',
                sortDirection: 'up',
                prefix: 'coaches-list-',
            }
        },
        methods: {
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
                if (this.filteredCoaches.length < 1)
                    return

                this.filteredCoaches.sort((a, b) => {
                    let va = a[this.sortColumn];
                    let vb = b[this.sortColumn];

                    if (va < vb)
                        return -1 * (this.sortDirection == 'up' ? 1 : -1);

                    if (va > vb)
                        return 1 * (this.sortDirection == 'up' ? 1 : -1);

                    return 0;
                });

               this.showPage(this.paging.current);
            },

            clearFilters() {
                this.filters.gender = 'all';
                for (let i in this.filters.bodies) {
                    let body = this.filters.bodies[i];
                    body.checked = false;
                }
                this.bodyCheckChanged()
            },

            bodyCheckChanged(e, body) {
                this.filters.bodies = this.sanctioningBodies.filter(body => body.checked);
                this.isLoading = true;
                this.filtersChanged();
            },

            coachCheckChanged(coach) {
                this.addRemoveSelectedCoach(coach);
                this.updateSelectAllStatus();
                this.$emit('coach-selected-changed', this.selectedCoaches);
            },

            selectAllCheckChanged() {
                for (let i in this.visibleCoaches) {
                    let coach = this.visibleCoaches[i];
                    coach.checked = this.selectAll.checked;
                    this.addRemoveSelectedCoach(coach);
                }
                this.$emit('coach-selected-changed', this.selectedCoaches);
            },

            updateSelectAllStatus() {
                let allChecked = true;
                let noneChecked = true;
                for (let i in this.visibleCoaches) {
                    let coach = this.visibleCoaches[i];
                    noneChecked &= !coach.checked;
                    allChecked &= coach.checked;
                }

                this.selectAll.checked = allChecked;
                this.selectAll.intermediate = (!noneChecked && !allChecked);
            },

            addRemoveSelectedCoach(coach) {
                let coachIndex = this.selectedCoaches.indexOf(coach.id);
                let hasCoach = (coachIndex > -1)

                if (coach.checked) {
                    if (!hasCoach)
                        this.selectedCoaches.push(coach.id);
                } else {
                    if (hasCoach)
                        this.selectedCoaches.splice(coachIndex, 1);
                }
            },

            removeCoach(coach) {
                if (this.isBusy)
                    return;

                this.confirmAction(
                    'Do you really want to remove ' + coach.first_name + ' ' + coach.last_name + ' ?',
                    'red',
                    'fas fa-trash',
                    () => {
                        this.isBusy = true;
                        coach.removing = true;
                        axios.post(
                            '/api/gyms/' + this.gym + '/coaches/' + coach.id + '/delete',
                            {
                                '__managed': this.managed
                            }
                        ).then(result => {
                            let coachIndex = this.coaches.indexOf(coach);
                            this.coaches.splice(coachIndex, 1);
                            this.filtersChanged();
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
                            coach.removing = false;
                            this.isBusy = false;
                        });
                    },
                    this
                );
            },

            showPage(page) {
                if (page != this.paging.current) {
                    for (let i in this.visibleCoaches) {
                        let coach = this.visibleCoaches[i];
                        coach.checked = false;
                        this.addRemoveSelectedCoach(coach);
                    }
                    this.$emit('coach-selected-changed', this.selectedCoaches);
                }


                this.paging.current = page;

                let start = 0;
                let end = this.filteredCoaches.length;

                if (!this.showAll) {
                    start = (this.paging.current - 1) * this.limit;
                    end = start + this.limit;
                }

                this.visibleCoaches = this.filteredCoaches.slice(
                    start,
                    end
                );

                this.updateSelectAllStatus();
            },

            onPageChangeRequest(page) {
                this.showPage(page);
                this.paging.current = page;
            },

            onPageChanged(val) {
              this.paging.current = val.current;
            },

            filtersChanged: _.debounce(function () {
                let result = this.coaches;
                this.filtersApplied = false;
                this.isLoading = true;

                if (this.coaches.length > 0) {
                    if (this.filters.gender != 'all') {
                        result = result.filter(coach => coach.gender == this.filters.gender);
                        this.filtersApplied = true;
                    }

                    if (this.filters.bodies.length > 0) {
                        result = result.filter(coach => {
                            let flag = false;

                            for (let i in this.filters.bodies) {
                                let body = this.filters.bodies[i];
                                flag = flag || (coach[body.initialism.toLowerCase() + '_no'] != null);
                            }

                            return flag;
                        });

                        this.filtersApplied = true;
                    }

                    if (this.filters.search !== '') {
                        result = result.filter(coach => {
                            let flag = false;

                            if (coach.usag_no)
                                flag = coach.usag_no.includes(this.filters.search);

                            if (coach.usaigc_no)
                                flag = flag || ('igc' + coach.usaigc_no).includes(this.filters.search.toLowerCase());

                            if (coach.aau_no)
                                flag = flag || coach.aau_no.toLowerCase().includes(this.filters.search.toLowerCase());

                            return flag || coach.first_name.toLowerCase().includes(this.filters.search.toLowerCase()) ||
                            coach.last_name.toLowerCase().includes(this.filters.search.toLowerCase()) ||
                            coach.dob_display.includes(this.filters.search);
                        });

                        this.filtersApplied = true;
                    }
                }

                this.filteredCoaches = result;
                this.hasNoCoaches = (this.filteredCoaches.length < 1);

                this.paging.total = (this.showAll ? 1 : Math.ceil(this.filteredCoaches.length / this.limit));

                let page = this.paging.current > this.paging.total ? this.paging.total : this.paging.current;
                page = (page < 1 ? 1 : page);

                if (this.filtersApplied) // force deselect
                    this.paging.current = undefined;

                this.showPage(page);

                let entriesCount = this.filteredCoaches.length + ' entries';
                let pageCount = 'page ' + this.paging.current + ' of ' + this.paging.total;
                this.statusText = 'Showing ' + (this.showAll ? entriesCount : pageCount) +
                    ', ' + (this.filtersApplied ? 'Filters applied.' : 'No filters applied.');
                this.isLoading = false;
            }, 500),

            onLimitChanged() {
                Cookies.set(this.prefix + 'items-per-page', this.limit);
                this.filtersChanged();
            },

            toggleShowAll() {
                if (!this.showAll) {
                    this.confirmAction('Showing all entries can overwhelm your device and cause it to stop working.' +
                        '<br/><strong>Are you sure you want to proceed ?</strong>',
                        'orange', 'fas fa-exclamation-triangle', () => {
                            this.showAll = true;
                            this.filtersChanged();
                        },
                        this
                    );
                } else {
                    this.showAll = false;
                    this.filtersChanged();
                }
            },

            confirmAction(msg, color, icon, callback, context) {
                if (context.isBusy)
                    return;
                context.isBusy = true;

                $.confirm({
                    title: 'Are you sure ?',
                    content: msg,
                    icon: icon,
                    type: color,
                    typeAnimated: true,
                    buttons: {
                        no: function () {
                            context.isBusy = false;
                            this.close();
                        },
                        confirm:  {
                            text: 'Yes',
                            btnClass: 'btn-' + color,
                            action: function () {
                                context.isBusy = false;
                                callback();
                            }
                        }
                    }
                });
            },

            loadSavedSettings() {
                let itemsPerPage = Utils.toInt(Cookies.get(this.prefix + 'items-per-page'));

                this.limit = ((itemsPerPage != NaN) && (itemsPerPage > 0) ? itemsPerPage : 5);
            }
        },
        mounted() {
            this.isLoading = true;
            this.loadSavedSettings();
            axios.get('/api/app/bodies').then(result => {
                let bodies = result.data.bodies;

                for (let i in bodies) {
                    let body = bodies[i];
                    body.checked = false;
                    this.sanctioningBodies.push(body);
                }

                axios.get('/api/gyms/' + this.gym + '/coaches', {
                    'params': {
                        '__managed': this.managed
                    }
                }).then(result => {
                    this.hasNoCoaches = (result.data.coaches.length < 1);
                    for (let i in result.data.coaches) {
                        let coach = result.data.coaches[i];

                        coach.gender_display = coach.gender.charAt(0).toUpperCase() + coach.gender.slice(1)
                        coach.dob = Moment(coach.dob);
                        coach.dob_display = coach.dob.format('MM/DD/YYYY');
                        coach.checked = false;
                        coach.removing = false;

                        this.coaches.push(coach);
                    }

                    this.filtersChanged();
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
    }
</script>
