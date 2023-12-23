'use strict';
$(document).ready(function () {
    let tbl = $('#featuredMeetsTbl').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: featuredMeetsUrl,
        },
        "order": [[0, "asc"]],
        "columnDefs": [
            {
                //sanctioning_bodies
                "targets": [5],
                'width': '15%',
            },
            {
                //registration_status
                "targets": [7],
                'class': 'text-center',
                'width': '10%',
            },
            {
                "targets": [6],
                'class': 'text-center',
                'width': '8%',
            },
            {
                targets: '_all',
                defaultContent: 'N/A',
            },
        ],
        columns: [
            {
                data: function data(row) {
                    let url = meetsUrl + '/' + row.id + '/dashboard';
                    return '<a href="' + url + '" class="">' + row.name + '</a>';
                },
                name: 'name',
            },
            {
                data: 'gym.name',
                name: 'gym.name',
            },
            {
                data: function data(row) {
                    return moment(row.start_date, 'YYYY-MM-DD').format('DD/MM/YYYY');
                },
                name: 'start_date',
            },
            {
                data: function data(row) {
                    return moment(row.end_date, 'YYYY-MM-DD').format('DD/MM/YYYY');
                },
                name: 'end_date',
            },
            {
                data: function data(row) {
                    return moment(row.created_at, 'YYYY-MM-DD').format('DD/MM/YYYY');
                },
                name: 'created_at',
            },
            {
                data: function data(row) {
                    if (!isEmpty(row.venue_state_id)) {
                        return row.venue_state.name + ', ' + row.venue_state.code;
                    }
                    return 'N/A';
                },
                name: 'name',
            },
            {
                data: 'sanction_bodies[, ]',
                name: 'gym.name',
            },
            {
                data: function (row) {
                    let now = moment(new Date()).format("YYYY-MM-DD 00:00:00");
                    if (now < row.end_date) {
                        let checked = row.is_featured != 0 ? 'checked' : '';
                        let data = [{'id': row.id, 'checked': checked}];
                        return prepareTemplateRender('#featuredMeetTemplate', data);
                    }
                },
                name: 'is_featured',
            },
            {
                data: function (row) {
                    let statusColor = {1: 'danger', 2: 'success', 3: 'warning', 4: 'info'};
                    let statusArr = {1: 'Closed', 2: 'Open', 3: 'Late', 4: 'Opening Soon'};

                    return '<span title="Edit" class="font-size-15 badge badge-' + statusColor[row.registration_status] + '">' + statusArr[row.registration_status] + '</span>';
                },
                name: 'id',
            },
        ],
    })
});

//Make Feature Meet
$(document).on('click', '.makeFeature', function (event) {
    $(this).html('<i class="fas fa-sync font-size-12px fa-spin"></i>');
    let meetId = $(event.currentTarget).attr('data-id');
    $.ajax({
        url: meetsUrl + '/' + meetId + '/meet-featured',
        method: 'post',
        cache: false,
        success: function (result) {
            if (result.success) {
                $('#meetsTbl').DataTable().ajax.reload(null, false);
            }
        },
        error: function (result) {
            manageAjaxErrors(result);
        },
    })
});
