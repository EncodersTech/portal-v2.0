require('../main');

window.Vue = require('vue');

Vue.component('ag-athlete-filters', require('../components/Athlete/AthleteFilters.vue').default);
Vue.component('ag-athlete-search-field', require('../components/Search/SearchField.vue').default);
Vue.component('ag-athlete-list', require('../components/Athlete/AthleteList.vue').default);
Vue.component('ag-faulty-athlete-list', require('../components/Athlete/FailedAthleteImportList.vue').default);
Vue.component('ag-athlete-import', require('../components/Athlete/AthleteImport.vue').default);

$(document).ready(e => {
    let _busy = false;   

    const app = new Vue({
        el: '#app',
        data: {    
            gender: 'all',
            levels: {},
            search: '',
            selectedAthletes: [],
            selectedFailedAthleteImports: [],
            faultySearch: '',
        },
        computed: {
            filters: {
                get() {
                    return {
                        'gender': this.gender,
                        'levels': this.levels,
                        'search': this.search,
                    }
                },
    
                set(val) {
                    this.gender = val.gender;
                    this.levels = val.levels;
                }
            }
        },
        methods: {
            onFiltersChanged(v) {
                this.filters = v;
            },
    
            onSelectedAthletesChanged(v) {
                this.selectedAthletes = v;
            },

            onSearchTextChanged(v) {
                this.search = v;
            },

            onFailedAthleteSelectChanged(v) {
                this.selectedFailedAthleteImports = v;
            },

            onFaultySearchTextChanged(v) {
                // this.filters.search = v;
                this.faultySearch = v;
            }
        }
    });

    setupTabRemember();

    $('#remove-selected-athletes-button').click(e => {
        if (app.selectedAthletes.length < 1)
            return;

        confirmAction(
            'Do you really want to remove the selected athletes ?',
            'red',
            'fas fa-trash',
            () => {
                $('input[name="selected_athletes_list"]').val(app.selectedAthletes);
                $('#remove-selected-athletes-form').submit();
            }
        );
    });

    $('#remove-selected-failed-athletes-button').click(e => {
        if (app.selectedFailedAthleteImports.length < 1)
            return;

        confirmAction(
            'Do you really want to remove the selected athletes ?',
            'red',
            'fas fa-trash',
            () => {
                $('input[name="selected_failed_athletes_list"]').val(app.selectedFailedAthleteImports);
                $('#remove-selected-failed-athletes-form').submit();
            }
        );
    });

    $('.modal-athlete-import-close').click(e => {
        if (_busy)
            return;
        $('#modal-athlete-import').modal('hide');
    });

    function setupTabRemember() {
        let savedTab = $('#' + Cookies.get('athlete-list-tab'));
        let tabs = $('#athlete-list-tabs a[data-toggle="tab"]');

        if (savedTab.length > 0)
            savedTab.tab('show');
        else
            tabs.first().tab('show');

        tabs.on('shown.bs.tab', e => {
            let currentTab = $(e.target).attr('id');
            Cookies.set('athlete-list-tab', currentTab);
        });
    }

    function confirmAction(msg, color, icon, callback) {
        if (_busy)
            return;
        _busy = true;
    
        $.confirm({
            title: 'Are you sure ?',
            content: msg,
            icon: icon,
            type: color,
            typeAnimated: true,
            buttons: {
                no: function () {
                    _busy = false;
                    this.close();
                },
                confirm:  {
                    text: 'Yes',
                    btnClass: 'btn-' + color,
                    action: function () {
                        _busy = false;
                        callback();
                    }
                }
            }
        });
    };
});