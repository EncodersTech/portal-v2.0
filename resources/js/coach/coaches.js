require('../main');

window.Vue = require('vue');

Vue.component('ag-coach-search-field', require('../components/Search/SearchField.vue').default);
Vue.component('ag-coach-list', require('../components/Coach/CoachList.vue').default);
Vue.component('ag-faulty-coach-list', require('../components/Coach/FailedCoachImportList.vue').default);
Vue.component('ag-coach-import', require('../components/Coach/CoachImport.vue').default);

$(document).ready(e => {
    let _busy = false;   

    const app = new Vue({
        el: '#app',
        data: {    
            gender: 'all',
            search: '',
            selectedCoaches: [],
            selectedFailedCoachImports: [],
            faultySearch: '',
        },
        computed: {
        },
        methods: {
            onFiltersChanged(v) {
                this.filters = v;
            },
    
            onSelectedCoachesChanged(v) {
                this.selectedCoaches = v;
            },

            onSearchTextChanged(v) {
                this.search = v;
            },

            onFailedCoachSelectChanged(v) {
                this.selectedFailedCoachImports = v;
            },

            onFaultySearchTextChanged(v) {
                this.faultySearch = v;
            }
        }
    });

    setupTabRemember();

    $('#remove-selected-coaches-button').click(e => {
        if (app.selectedCoaches.length < 1)
            return;

        confirmAction(
            'Do you really want to remove the selected Coaches ?',
            'red',
            'fas fa-trash',
            () => {
                $('input[name="selected_coaches_list"]').val(app.selectedCoaches);
                $('#remove-selected-coaches-form').submit();
            }
        );
    });

    $('#remove-selected-failed-coaches-button').click(e => {
        if (app.selectedFailedCoachImports.length < 1)
            return;

        confirmAction(
            'Do you really want to remove the selected Coaches ?',
            'red',
            'fas fa-trash',
            () => {
                $('input[name="selected_failed_coaches_list"]').val(app.selectedFailedCoachImports);
                $('#remove-selected-failed-coaches-form').submit();
            }
        );
    });

    $('.modal-coach-import-close').click(e => {
        if (_busy)
            return;
        $('#modal-coach-import').modal('hide');
    });

    function setupTabRemember() {
        let savedTab = $('#' + Cookies.get('coache-list-tab'));
        let tabs = $('#coach-list-tabs a[data-toggle="tab"]');

        if (savedTab.length > 0)
            savedTab.tab('show');
        else
            tabs.first().tab('show');

        tabs.on('shown.bs.tab', e => {
            let currentTab = $(e.target).attr('id');
            Cookies.set('coache-list-tab', currentTab);
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