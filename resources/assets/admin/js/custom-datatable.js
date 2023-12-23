'use strict';

$.extend($.fn.dataTable.defaults, {
    "responsive": true,
    'paging': true,
    'info': true,
    'ordering': true,
    'autoWidth': false,
    'pageLength': 10,
    'language': {
        'search': '',
        'sSearch': 'Search',
        'processing': '<i class="fas fa-circle-notch fa-spin"></i> <small> Loading...</small>',
        'paginate': {
            'previous': '<i class="fas fa-chevron-left"></i>',
            'next': '<i class="fas fa-chevron-right"></i>',
        },
    },
    "preDrawCallback": function () {
        customSearch()
    }
});

function customSearch() {
    $('.dataTables_filter input').addClass("form-control");
    $('.dataTables_filter input').attr("placeholder", "Search");
}
