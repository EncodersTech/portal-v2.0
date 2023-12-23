'use strict';

$(document).ready(function () {
    let tbl = $('#gymBalanceReportsTbl').DataTable({
        processing: true,
        serverSide: true,
        "ajax": {
            "url": gymBalanceUrl,
        },
        "columnDefs": [
            {
                targets: '_all',
                defaultContent: 'N/A',
            },
            {
                "targets": [1],
                'width': '25%',
            },
            {
                "targets": [2],
                'width': '13%',
            }
        ],
        columns: [
            {
                data: 'name',
                name: 'name',
            },
            {
                data: 'user.email',
                name: 'user.email',
            },
            {
                data: function data(row) {
                    return '$ '+addCommas(parseFloat(row.user.cleared_balance).toFixed(2));
                },
                name: 'user.cleared_balance',
            }
        ],
    });
});
