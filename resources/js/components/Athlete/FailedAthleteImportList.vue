<template>
    <div>
        <div class="small" :class="{ 'd-none': !isLoading }">
            <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true">
            </span> Loading athletes, please wait ...
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

            <div class="alert alert-info" :class="{ 'd-none': !hasNoAthletes }">
                <span class="fas fa-info-circle"></span> You do not have any failed imports
                <span v-if="filtersApplied">matching the selected criteria</span>.
            </div>

            <div class="table-responsive-lg" :class="{ 'd-none': hasNoAthletes }">
                <table class="table table-sm table-striped">
                    <thead class="thead-dark">
                        <tr>
                            <th scope="col" class="align-middle">
                                <input type="checkbox" id="athlete-select-all" @change="selectAllCheckChanged()"
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
                                <span v-if="sortColumn == 'gnder'">
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
                            <th scope="col" class="align-middle">US Cit.</th>
                            <th scope="col" class="align-middle">Duplicate</th>
                            <th scope="col" class="text-right align-middle"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="athlete in visibleAthletes" :key="athlete.id">
                            <td class="align-middle">
                                <input type="checkbox" class="athlete-select-checkbox"
                                v-model="athlete.checked" @change="athleteCheckChanged(athlete)">
                            </td>

                            <td class="align-middle">
                                {{ athlete.first_name }}
                            </td>

                            <td class="align-middle">
                                {{ athlete.last_name }}
                            </td>

                            <td class="align-middle">
                                {{ athlete.gender_display }}
                            </td>

                            <td class="align-middle">
                                {{ athlete.dob_display }}
                            </td>

                            <td class="align-middle membership-list">
                                <div v-if="athlete.usag_no != null" class="small membership">
                                    <strong>USAG</strong>
                                    <span v-if="athlete.usag_active" class="text-gray-600">(Active)</span>
                                    No. <span class="text-info font-weight-bold">{{ athlete.usag_no }}</span><br/>
                                    <span v-if="athlete.usag_level != null">
                                        {{ athlete.usag_level.level_category.name}} |
                                        <strong>
                                            <span class="fas fa-layer-group"></span>
                                            {{ athlete.usag_level.name }}
                                        </strong>
                                    </span>
                                </div>

                                <div v-if="athlete.usaigc_no != null" class="small membership">
                                    <strong>USAIGC</strong>
                                    <span v-if="athlete.usaigc_active" class="text-gray-600">(Active)</span>
                                    No. <span class="text-info font-weight-bold">IGC{{ athlete.usaigc_no }}</span><br/>
                                    <span v-if="athlete.usaigc_level != null">
                                        {{ athlete.usaigc_level.level_category.name}} |
                                        <strong>
                                            <span class="fas fa-layer-group"></span>
                                            {{ athlete.usaigc_level.name }}
                                        </strong>
                                    </span>
                                </div>

                                <div v-if="athlete.aau_no != null" class="small membership">
                                    <strong>AAU</strong>
                                    <span v-if="athlete.aau_active" class="text-gray-600">(Active)</span>
                                    No. <span class="text-info font-weight-bold">{{ athlete.aau_no }}</span><br/>
                                    <span v-if="athlete.usaigc_level != null">
                                        {{ athlete.aau_level.level_category.name}} |
                                        <strong>
                                            <span class="fas fa-layer-group"></span>
                                            {{ athlete.aau_level.name }}
                                        </strong>
                                    </span>
                                </div>
                            </td>

                            <td class="align-middle">
                                {{ athlete.is_us_citizen ? 'Yes' : 'No' }}
                            </td>

                            <td class="align-middle">
                                {{ athlete.error_code == -9999 /* ERROR_CODE_DUPLICATE */ ? 'Yes' : '—' }}
                            </td>
                            
                            <td class="text-right align-middle">                                            
                                <div class="mb-1 mr-1 d-inline-block">
                                    <a :href="'/gyms/' + gym + '/athletes/import/failed/' + athlete.id + '/edit'"
                                        class="btn btn-sm btn-success" title="Edit">
                                            <span class="fas fa-fw fa-edit"></span> Review
                                    </a>
                                </div>

                                <div class="mb-1 mr-1 d-inline-block">
                                    <button class="btn btn-sm btn-danger" :class="{ 'd-none': athlete.removing }"
                                        title="Remove" @click="removeAthlete(athlete)">
                                        <span class="fas fa-fw fa-trash"></span>
                                    </button>

                                    <button class="btn btn-sm btn-outline-danger" :class="{ 'd-none': !athlete.removing }"
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
        name: 'FailedAthleteImportList',
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
                hasNoAthletes: false,
                filtersApplied: false,
                showAll: false,
                statusText: '',
                errorMessage: '',
                selectAll: {
                    checked: false,
                    intermediate: false
                },
                visibleAthletes: [],
                filteredAthletes: [],
                athletes: [],
                selectedAthletes: [],
                paging: {
                    current: 1,
                    total: 1
                },
                limit: 5,
                limitMultiplier: 5,
                debounce_delay: 500,
                sortColumn: 'created_at',
                sortDirection: 'up',
                prefix: 'failed-athlete-import-list-',
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
                if (this.filteredAthletes.length < 1) 
                    return

                this.filteredAthletes.sort((a, b) => {
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

            athleteCheckChanged(athlete) {
                this.addRemoveSelectedLevel(athlete);
                this.updateSelectAllStatus();
                this.$emit('failed-athlete-selected-changed', this.selectedAthletes);
            },

            selectAllCheckChanged() {
                for (let i in this.visibleAthletes) {
                    let athlete = this.visibleAthletes[i];
                    athlete.checked = this.selectAll.checked;
                    this.addRemoveSelectedLevel(athlete);
                }
                this.$emit('failed-athlete-selected-changed', this.selectedAthletes);
            },

            updateSelectAllStatus() {
                let allChecked = true;
                let noneChecked = true;
                for (let i in this.visibleAthletes) {
                    let athlete = this.visibleAthletes[i];
                    noneChecked &= !athlete.checked;
                    allChecked &= athlete.checked;                        
                }

                this.selectAll.checked = allChecked;
                this.selectAll.intermediate = (!noneChecked && !allChecked);
            },

            addRemoveSelectedLevel(athlete) {
                let athleteIndex = this.selectedAthletes.indexOf(athlete.id);
                let hasAthlete = (athleteIndex > -1)

                if (athlete.checked) {
                    if (!hasAthlete)
                        this.selectedAthletes.push(athlete.id);
                } else {
                    if (hasAthlete)
                        this.selectedAthletes.splice(athleteIndex, 1);
                }
            },

            removeAthlete(athlete) {
                if (this.isBusy)
                    return;
                
                this.confirmAction(
                    'Do you really want to remove this entry ?',
                    'red',
                    'fas fa-trash',
                    () => {                      
                        this.isBusy = true;
                        athlete.removing = true;
                        axios.post(
                            '/api/gyms/' + this.gym + '/athletes/import/faulty/' + athlete.id + '/delete',
                            {
                                '__managed': this.managed
                            }
                        ).then(result => {
                            let athleteIndex = this.athletes.indexOf(athlete);
                            this.athletes.splice(athleteIndex, 1);
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
                            athlete.removing = false;
                            this.isBusy = false;            
                        });
                    },
                    this
                );
            },

            showPage(page) {
                if (page != this.paging.current) {
                    for (let i in this.visibleAthletes) {
                        let athlete = this.visibleAthletes[i];
                        athlete.checked = false;
                        this.addRemoveSelectedLevel(athlete);
                    }
                    this.$emit('failed-athlete-selected-changed', this.selectedAthletes);    
                }

                this.paging.current = page;

                let start = 0;
                let end = this.filteredAthletes.length;

                if (!this.showAll) {
                    start = (this.paging.current - 1) * this.limit;
                    end = start + this.limit;
                } 

                this.visibleAthletes = this.filteredAthletes.slice(
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
                let result = this.athletes;
                this.filtersApplied = false;
                this.isLoading = true;

                if (this.athletes.length > 0) {
                    if (this.search !== '') {
                        result = result.filter(athlete => {
                            let flag = false;

                            if (athlete.first_name)
                                flag = athlete.first_name.toLowerCase().includes(this.search.toLowerCase());

                            if (athlete.last_name)
                                flag = flag || athlete.last_name.toLowerCase().includes(this.search);

                            if (athlete.gender)
                                flag = flag || athlete.gender.toLowerCase() == this.search.toLowerCase();

                            if (athlete.dob_display)
                                flag = flag || athlete.dob_display.includes(this.search);

                            if (athlete.usag_no)
                                flag = flag || athlete.usag_no.includes(this.search);

                            if (athlete.usag_level) {
                                flag = flag || athlete.usag_level.level_category.name.toLowerCase().includes(this.search.toLowerCase());
                                flag = flag || athlete.usag_level.name.toLowerCase().includes(this.search.toLowerCase());
                            }

                            if (athlete.usaigc_no)
                                flag = flag || ('igc' + athlete.usaigc_no).includes(this.filters.search.toLowerCase());

                            if (athlete.usaigc_level) {
                                flag = flag || athlete.usaigc_level.level_category.name.toLowerCase().includes(this.search.toLowerCase());
                                flag = flag || athlete.usaigc_level.name.toLowerCase().includes(this.search.toLowerCase());
                            }

                            if (athlete.aau_no)
                                flag = flag || athlete.aau_no.includes(this.search);

                            if (athlete.aau_level) {
                                flag = flag || athlete.aau_level.level_category.name.toLowerCase().includes(this.search.toLowerCase());
                                flag = flag || athlete.aau_level.name.toLowerCase().includes(this.search.toLowerCase());
                            }

                            return flag;
                        });

                        this.filtersApplied = true;
                    }
                }
                
                this.filteredAthletes = result;
                this.hasNoAthletes = (this.filteredAthletes.length < 1);

                this.paging.total = (this.showAll ? 1 : Math.ceil(this.filteredAthletes.length / this.limit));
                
                let page = this.paging.current > this.paging.total ? this.paging.total : this.paging.current;
                page = (page < 1 ? 1 : page);

                if (this.filtersApplied) // force deselect
                    this.paging.current = undefined;

                this.showPage(page);

                let entriesCount = this.filteredAthletes.length + ' entries';
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
            axios.get('/api/gyms/' + this.gym + '/athletes/import/faulty', {
                'params': {
                    '__managed': this.managed
                }
            }).then(result => {
                this.hasNoAthletes = (result.data.failed_imports.length < 1);
                for (let i in result.data.failed_imports) {
                    let athlete = result.data.failed_imports[i];
                    
                    athlete.gender_display = (
                        athlete.gender != null ?
                        athlete.gender.charAt(0).toUpperCase() + athlete.gender.slice(1) :
                        null
                    );
                    athlete.dob = (athlete.dob != null ? Moment(athlete.dob) : null);
                    athlete.dob_display = (athlete.dob != null ? athlete.dob.format('MM/DD/YYYY') : null);
                    athlete.checked = false;
                    athlete.removing = false;

                    this.athletes.push(athlete);
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
