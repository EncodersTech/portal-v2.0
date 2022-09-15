<template>
    <div>
        <div class="small" :class="{ 'd-none': !isLoading }">
            <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true">
            </span> Loading {{ plural }}, please wait ...
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

            <div class="alert alert-info" :class="{ 'd-none': !hasNoItems }">
                <span class="fas fa-info-circle"></span> You do not have any active {{ plural }}
                <span v-if="filtersApplied">matching the selected criteria</span>.
            </div>

            <div class="table-responsive-lg" :class="{ 'd-none': hasNoItems }">
                <table class="table table-sm table-striped">
                    <thead class="thead-dark">
                        <tr>
                            <th scope="col" class="align-middle">
                                <input type="checkbox" @change="selectAllCheckChanged()"
                                    :indeterminate.prop="selectAll.intermediate" v-model="selectAll.checked">
                            </th>
                            <th scope="col" class="align-middle clickable" @click="sortBy('column')">
                                Column
                                <span v-if="sortColumn == 'column'">
                                    <span :class="'fas fa-fw fa-caret-' + sortDirection"></span>
                                </span>
                            </th>
                            <th scope="col" class="text-right align-middle"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="item in visibleItems" :key="item.id">
                            <td class="align-middle">
                                <input type="checkbox" class="item-select-checkbox"
                                v-model="item.checked" @change="itemCheckChanged(item)">
                            </td>

                            <td class="align-middle">
                                {{ item.column }}
                            </td>

                            <td class="text-right align-middle">
                                <div class="mb-1 mr-1 d-inline-block">
                                    <a :href="'/items/' + item.id + '/edit'"
                                        class="btn btn-sm btn-success" title="Edit">
                                            <span class="fas fa-fw fa-edit"></span>
                                    </a>
                                </div>

                                <div class="mb-1 mr-1 d-inline-block">
                                    <button class="btn btn-sm btn-danger" :class="{ 'd-none': item.removing }"
                                        title="Remove" @click="removeItem(item)">
                                        <span class="fas fa-fw fa-trash"></span>
                                    </button>

                                    <button class="btn btn-sm btn-outline-danger" :class="{ 'd-none': !item.removing }"
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
        name: 'ItemList',
        components: {
            'ag-pager': Pager
        },
        props: {
            filters: {
                search: '',
            },
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
        },
        watch: {
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
                hasNoItems: false,
                filtersApplied: false,
                showAll: false,
                statusText: '',
                errorMessage: '',
                selectAll: {
                    checked: false,
                    intermediate: false
                },
                visibleItems: [],
                filteredItems: [],
                items: [],
                selectedItems: [],
                paging: {
                    current: 1,
                    total: 1
                },
                limit: 5,
                limitMultiplier: 5,
                debounce_delay: 500,
                sortColumn: '',
                sortDirection: 'up'
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
                if (this.filteredItems.length < 1)
                    return

                this.filteredItems.sort((a, b) => {
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

            itemCheckChanged(item) {
                this.addRemoveSelectedItem(item);
                this.updateSelectAllStatus();
                this.$emit(this.singular + '-selected-changed', this.selectedItems);
            },

            selectAllCheckChanged() {
                for (let i in this.visibleItems) {
                    let item = this.visibleItems[i];
                    item.checked = this.selectAll.checked;
                    this.addRemoveSelectedItem(item);
                }
                this.$emit(this.singular + '-selected-changed', this.selectedItems);
            },

            updateSelectAllStatus() {
                let allChecked = true;
                let noneChecked = true;
                for (let i in this.visibleItems) {
                    let item = this.visibleItems[i];
                    noneChecked &= !item.checked;
                    allChecked &= item.checked;
                }

                this.selectAll.checked = allChecked;
                this.selectAll.intermediate = (!noneChecked && !allChecked);
            },

            addRemoveSelectedItem(item) {
                let itemIndex = this.selectedItems.indexOf(item.id);
                let hasItem = (itemIndex > -1)

                if (item.checked) {
                    if (!hasItem)
                        this.selectedItems.push(item.id);
                } else {
                    if (hasItem)
                        this.selectedItems.splice(itemIndex, 1);
                }
            },

            removeItem(item) {
                if (this.isBusy)
                    return;

                this.confirmAction(
                    'Do you really want to remove ' + item.column + ' ?',
                    'red',
                    'fas fa-trash',
                    () => {
                        this.isBusy = true;
                        item.removing = true;
                        axios.post(
                            '/api/items/' + item.id + '/delete',
                            {
                                '__managed': this.managed
                            }
                        ).then(result => {
                            let itemIndex = this.items.indexOf(item);
                            this.items.splice(itemIndex, 1);
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
                            item.removing = false;
                            this.isBusy = false;
                        });
                    },
                    this
                );
            },

            showPage(page) {
                if (page != this.paging.current) {
                    for (let i in this.visibleItems) {
                        let item = this.visibleItems[i];
                        item.checked = false;
                        this.addRemoveSelectedItem(item);
                    }
                    this.$emit(this.singular + '-selected-changed', this.selectedItems);
                }

                this.paging.current = page;

                let start = 0;
                let end = this.filteredItems.length;

                if (!this.showAll) {
                    start = (this.paging.current - 1) * this.limit;
                    end = start + this.limit;
                }

                this.visibleItems = this.filteredItems.slice(
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
                let result = this.items;
                this.filtersApplied = false;
                this.isLoading = true;

                if (this.items.length > 0) {
                    if (this.filters.search !== '') {
                        result = result.filter(item => {
                            let flag = false;

                            if (item.column)
                                flag = flag || item.column.toLowerCase().includes(this.filters.search.toLowerCase());

                            return flag;
                        });

                        this.filtersApplied = true;
                    }
                }

                this.filteredItems = result;
                this.hasNoItems = (this.filteredItems.length < 1);

                this.paging.total = (this.showAll ? 1 : Math.ceil(this.filteredItems.length / this.limit));

                let page = this.paging.current > this.paging.total ? this.paging.total : this.paging.current;
                page = (page < 1 ? 1 : page);

                if (this.filtersApplied) // force deselect
                    this.paging.current = undefined;

                this.showPage(page);

                let entriesCount = this.filteredItems.length + ' entries';
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
            },
        },
        mounted() {
            this.isLoading = true;
            this.loadSavedSettings();
            axios.get('/api/items', {
                'params': {
                    '__managed': this.managed
                }
            }).then(result => {
                let items = result.data.items;
                this.hasNoItems = (items.length < 1);
                for (let i in items) {
                    let item = items[i];

                    item.checked = false;
                    item.removing = false;

                    this.items.push(item);
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
