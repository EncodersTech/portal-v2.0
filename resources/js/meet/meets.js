require('../main');

window.Vue = require('vue');

Vue.component('ag-search-field', require('../components/Search/SearchField.vue').default);
Vue.component('ag-meet-list', require('../components/Meet/MeetList.vue').default);
Vue.component('ag-archived-meet-list', require('../components/Meet/ArchivedMeetList.vue').default);

$(document).ready(e => {

    let _busy = false;

    const app = new Vue({
        el: '#app',
        data: {    
            search: '',
            archivedSearch:''
        },
        computed: {
            filters: {
                get() {
                    return {
                        search: this.search,
                    }
                },
            },
            archivedFilters: {
                get() {
                    return {
                        search: this.search,
                    }
                },
            }
        },
        methods: {
            onSearchTextChanged(v) {
                this.search = v;
            },
            
            onArchivedSearchTextChanged(v) {
                this.archivedSearch = v;
            },
        }
    });

    setupTabRemember();
    
    function setupTabRemember() {
        let savedTab = $('#' + Cookies.get('meet-list-tab'));
        let tabs = $('#meet-list-tabs a[data-toggle="tab"]');

        if (savedTab.length > 0)
            savedTab.tab('show');
        else
            tabs.first().tab('show');

        tabs.on('shown.bs.tab', e => {
            let currentTab = $(e.target).attr('id');
            Cookies.set('meet-list-tab', currentTab);
        });
    }
});