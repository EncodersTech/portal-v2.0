'use strict';

$(document).ready(function () {
    let tbl = $('#transferReportsTbl').DataTable({
        processing: true,
        serverSide: true,
        "ajax": {
            "url": transferUrl,
        },
        "columnDefs": [
            {
                targets: '_all',
                defaultContent: 'N/A',
            },
        ],
        columns: [
            {
                data: function data(row) {
                    return row.source_user.full_name;
                },
                name: 'user.first_name',
            },
            {
                data: function data(row) {
                    return row.destination_user.full_name;
                },
                name: 'user.last_name',
            },
            {
                data: 'description',
                name: 'description',
            },
            {
                data: function data(row) {
                    let kl = "green";
                    let sig = "";
                    if(parseFloat(row.total) < 0)
                    {
                        kl = "red";
                        sig = '-';
                    }
                    return '<span style="color:'+kl+';"> $'+sig+''+ addCommas(parseFloat(Math.abs(row.total)).toFixed(2)) + "</span>";
                },
                name: 'total',
            },
            {
                data: function data(row) {
                    return transferReason[row.reason];
                },
                name: 'id',
            },
            {
                data: function data(row) {
                    if (row.status == 1) {
                        return '<span class="badge badge-warning">Pending</span>';
                    }
                    if (row.status == 2) {
                        return '<span class="badge badge-success">Cleared</span>';
                    }
                    if (row.status == 3) {
                        return '<span class="badge badge-warning">Pending (Unconfirmed)</span>';
                    }
                    if (row.status == 4) {
                        return '<span class="badge badge-danger">Failed</span>';
                    }
                },
                name: 'total',
            }
        ],
    });
});
