<template>
    <div>
        <div class="small" :class="{ 'd-none': !isLoading }">
            <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true">
            </span> Loading coaches, please wait ...
        </div>

        <div class="alert alert-danger" :class="{ 'd-none': !isError }">
            <span class="fas fa-fw fa-times-circle"></span> Whoops !<br/>
            <div class="mt-1" v-html="errorMessage"></div>
        </div>

        <div :class="{'d-none': isLoading || isError }">
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
                <span class="fas fa-info-circle"></span> You do not have any failed imports
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
                            <th scope="col" class="align-middle">Duplicate</th>
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
                                    No. <span class="text-info font-weight-bold">{{ coach.usag_no }}</span>
                                </div>

                                <div v-if="coach.usaigc_no != null" class="small membership">
                                    <strong>USAIGC</strong>
                                    <span v-if="coach.usaigc_background_check" class="text-gray-600">(Background Checked)</span>
                                    No. <span class="text-info font-weight-bold">{{ coach.usaigc_no }}</span>
                                </div>

                                <div v-if="coach.aau_no != null" class="small membership">
                                    <strong>AAU</strong>
                                    No. <span class="text-info font-weight-bold">{{ coach.aau_no }}</span>
                                </div>
                            </td>
                            
                            <td class="align-middle">
                                {{ coach.error_code == -9999 /* ERROR_CODE_DUPLICATE */ ? 'Yes' : '—' }}
                            </td>
                            
                            <td class="text-right align-middle">                                            
                                <div class="mb-1 mr-1 d-inline-block">
                                    <a :href="'/gyms/' + gym + '/coaches/import/failed/' + coach.id + '/edit'"
                                        class="btn btn-sm btn-success" title="Edit">
                                            <span class="fas fa-fw fa-edit"></span> Review
                                    </a>
                                </div>

                                <div class="mb-1 mr-1 d-inline-block">
                                    <button class="btn btn-sm btn-danger" :class="{ 'd-none': coach.removing }"
                                        title="Remove" @click="removeCoache(coach)">
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
        name: 'FailedCoacheImportList',
        components: {
            'ag-pager': Pager
        },
        props: {
            gym: Number,
            managed: {
                default: null,
                type: Number
            },
            search: '',
        },
        computed: {
        },
        watch: {
            search() {
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
                sortColumn: 'created_at',
                sortDirection: 'up',
                prefix: 'failed-coach-import-list-',
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

            coachCheckChanged(coach) {
                this.addRemoveSelectedLevel(coach);
                this.updateSelectAllStatus();
                this.$emit('failed-coach-selected-changed', this.selectedCoaches);
            },

            selectAllCheckChanged() {
                for (let i in this.visibleCoaches) {
                    let coach = this.visibleCoaches[i];
                    coach.checked = this.selectAll.checked;
                    this.addRemoveSelectedLevel(coach);
                }
                this.$emit('failed-coach-selected-changed', this.selectedCoaches);
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

            addRemoveSelectedLevel(coach) {
                let coachIndex = this.selectedCoaches.indexOf(coach.id);
                let hasCoache = (coachIndex > -1)

                if (coach.checked) {
                    if (!hasCoache)
                        this.selectedCoaches.push(coach.id);
                } else {
                    if (hasCoache)
                        this.selectedCoaches.splice(coachIndex, 1);
                }
            },

            removeCoache(coach) {
                if (this.isBusy)
                    return;
                
                this.confirmAction(
                    'Do you really want to remove this entry ?',
                    'red',
                    'fas fa-trash',
                    () => {                      
                        this.isBusy = true;
                        coach.removing = true;
                        axios.post(
                            '/api/gyms/' + this.gym + '/coaches/import/faulty/' + coach.id + '/delete',
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
                        this.addRemoveSelectedLevel(coach);
                    }
                    this.$emit('failed-coach-selected-changed', this.selectedCoaches);
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
                    if (this.search !== '') {
                        result = result.filter(coach => {
                            let flag = false;

                            if (coach.first_name)
                                flag = coach.first_name.toLowerCase().includes(this.search.toLowerCase());

                            if (coach.last_name)
                                flag = flag || coach.last_name.toLowerCase().includes(this.search);

                            if (coach.gender)
                                flag = flag || coach.gender.toLowerCase() == this.search.toLowerCase();

                            if (coach.dob_display)
                                flag = flag || coach.dob_display.includes(this.search);

                            if (coach.usag_no)
                                flag = flag || coach.usag_no.includes(this.search);

                            if (coach.usaigc_no)
                                flag = flag || ('igc' + coach.usaigc_no).includes(this.filters.search.toLowerCase());

                            if (coach.aau_no)
                                flag = flag || coach.aau_no.includes(this.search);

                            return flag;
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
            axios.get('/api/gyms/' + this.gym + '/coaches/import/faulty', {
                'params': {
                    '__managed': this.managed
                }
            }).then(result => {
                this.hasNoCoaches = (result.data.failed_imports.length < 1);
                for (let i in result.data.failed_imports) {
                    let coach = result.data.failed_imports[i];
                    
                    coach.gender_display = (
                        coach.gender != null ?
                        coach.gender.charAt(0).toUpperCase() + coach.gender.slice(1) :
                        null
                    );
                    coach.dob = (coach.dob != null ? Moment(coach.dob) : null);
                    coach.dob_display = (coach.dob != null ? coach.dob.format('MM/DD/YYYY') : null);
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
        }
    }
</script>
