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
                order: [[3, 'desc']]
            },
            {
                "targets": [1],
                'width': '20%',
            },
            {
                "targets": [2],
                'width': '20%',
            },
            {
                "targets": [3],
                'width': '13%',
            },
            {
                "targets": [4],
                'width': '13%',
            },
            {
                "targets": [5],
                'width': '13%',
            },
            {
                "targets": [6],
                'width': '13%',
            },
      
        ],
        columns: [
            {
                data: 'id',
                name: 'id',
            },
            {
                data: 'name',
                name: 'name',
            },
            {
                data: 'email',
                name: 'email',
            },
            {
                data: function data(row) {
                    return addCommas(parseFloat(row.cleared_balance).toFixed(2));
                },
                name: 'cleared_balance',
            },
            {
                data: function data(row) {
                  row.total == 	'null' ? 0 : row.total;
                    return addCommas(parseFloat(row.pending_balance).toFixed(2));
                },
                name: 'pending_balance',
            },
            {
                data: function data(row) {
                    if(row.total == null)
                       return 0;
                    return addCommas(parseFloat(row.total).toFixed(2));
                },
                name: 'total',
            },
            {
                data: function data(row){
                  row.total == NaN ? 0 : row.total;
                  return row.cleared_balance - row.total;
                }
            }
        ],
    });
});
