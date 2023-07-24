'use strict';

$(document).ready(function () {
    let tbl = $('#usersTbl').DataTable({
        processing: true,
        serverSide: true,
        "ajax": {
            "url": userUrl,
        },
        "order": [[2, "asc"]],
        "columnDefs": [
            {
                targets: '_all',
                defaultContent: 'N/A',
            },
            {
                "targets": [2],
                'width': '13%',
            },
            {
                "targets": [3],
                'width': '8%',
            },
            {
                "targets": [3],
                'width': '10%',
            },
            {
                "targets": [4],
                'width': '10%',
            },
            {
                "targets": [6],
                'width': '8%',
                'class': 'text-center',
                "orderable": false
            },
            {
                "targets": [5],
                'width': '8%',
                'class': 'text-center',
                "orderable": false
            },
            {
                "targets": [8],
                'width': '9%',
            },
            {
                "targets": [9],
                'width': '3%',
                'class': 'text-center',
                "orderable": false
            },
            {
                "targets": [11],
                'width': '8%',
                'class': 'text-center',
                "orderable": false
            },
            {
                "targets": [10],
                'width': '5%',
                'class': 'text-center',
                "orderable": false
            },
            {
                "targets": [7],
                "searchable": false,
                "visible": isImpersonate ? true : false,
                'class': 'text-center',
                'width': '5%',
                "orderable": false
            },
            {
                "targets": [0],
                "orderable": false,
                'width': '5%',
                'class': 'text-center',
            }
        ],
        columns: [
            {
                data: function (row) {
                    return '<img src="' + row.profile_picture +
                        '" class="user-picture-column rounded-circle" data-toggle="tooltip" title="' +
                        row.full_name + '">';
                },
                name: 'first_name',
            },
            {
                data: 'full_name',
                name: 'full_name',
            },
            {
                data: 'gname',
                name: 'gname',
            },
            {
                // data: 'email',
                data: function data(row) {
                    if(row.member_info != null)
                        return row.email + '<br>Connected: '+ row.member_info;
                    else
                        return row.email;
                },
                name: 'email',
            },
            {
                data: 'job_title',
                name: 'job_title',
            },
            {
                data: 'office_phone',
                name: 'office_phone',
            },
            {
                data: function data(row) {
                    let checked = row.mail_check_disable == 0 ? '' : 'checked';
                    let data = [{'id': row.id, 'checked': checked}];
                    return prepareTemplateRender('#UserMailCheckTemplate', data);
                },
                name: 'office_phone',
            },
            {
                data: function data(row) {
                    return '<a href="javascript:void(0);" class="btn btn-sm btn-info resetPassword" data-id="'+row.id+'"><span>Reset Password</span></a>'
                },
                name: 'office_phone',
            },
            {
                data: function data(row) {
                    if ((!row.is_admin) && (!row.is_disabled)) {
                        let url = impersonateUrl + '/' + row.id;
                        return '<a href="' + url + '" class="btn btn-sm btn-primary">Impersonate</a>'
                    }
                    return '<span class="btn-sm btn-warning">N/A</span>';
                },
                name: 'id',
            },
            {
                data: function data(row) {
                    if (!isEmpty(row.deactivate_at)) {
                        return moment(row.deactivate_at).format('Do MMM, YYYY hh:mm:ss a');
                    }

                    return 'N/A';
                },
                name: 'last_name',
            },
            {
                data: function (row) {
                    let checked = row.is_disabled == 0 ? 'checked' : '';
                    let data = [{'id': row.id, 'checked': checked}];
                    return prepareTemplateRender('#UserStatusTemplate', data);
                },
                name: 'user.status',
            },
            {
                data: function (row) {
                    let checked = row.email_verified_at !== null ? '<span style="color:green;">Verified</span>' : '<span style="color:red;">Unverified</span>';
                    // let data = [{'id': row.id, 'checked': checked}];
                    return checked;
                },
                name: 'user.isverified',
            },
            {
                data: function data(row) {
                    if (row.withdrawal_freeze) {
                        return '<a href="javascript:void(0);" class="btn btn-sm btn-danger withdrawalFreeze" data-id="' + row.id + '"><span>Withdrawal Frozen</span></a>'
                    }
                    return '<a href="javascript:void(0);" class="btn btn-sm btn-primary withdrawalFreeze" data-id="' + row.id + '"><span>Withdrawal Freeze</span></a>'
                },
                name: 'id',
            },
            {
                data: function (row) {
                    if (row.is_disabled == 0) {
                        let data = [
                            {
                                'id': row.id,
                                'isDisabled': row.is_disabled,
                                'url': userUrl + '/' + row.id + '/edit'
                            }];
                        return prepareTemplateRender('#usersActionTemplate', data);
                    }
                    return 'N/A';
                },
                name: 'last_name',
            },
        ],
    });

    // Send Reset password email
    $(document).on('click', '.resetPassword', function (event) {
        $(this).html('<i class="fas fa-sync font-size-12px fa-spin"></i>');
        let userId = $(event.currentTarget).attr('data-id');
        $.ajax({
            url: userUrl + '/' + userId + '/send-reset-email',
            type: 'post',
            cache: false,
            data: {
                "userId": userId
            },
            success: function (result) {
                if (result.success) {
                    displaySuccessMessage(result.message);
                    $('#usersTbl').DataTable().ajax.reload(null, true);
                }
            },
            error: function (error) {
                manageAjaxErrors(error)
            },
        })
    });

    //Deactive User
    $(document).on('click', '.status', function (event) {
        $(this).html('<i class="fas fa-sync font-size-12px fa-spin"></i>');
        let userId = $(event.currentTarget).attr('data-id');
        $.ajax({
            url: userUrl + '/' + userId + '/active-deactive',
            method: 'post',
            cache: false,
            success: function (result) {
                if (result.success) {
                    displaySuccessMessage(result.message);
                    $('#usersTbl').DataTable().ajax.reload(null, false);
                    $(this).html('<i class="as fa-user-lock"></i>');
                }
            },
            error: function (result) {
                UnprocessableInputError(result);
                $(this).html('<i class="as fa-user-lock"></i>');
            },
        })
    });

    // Withdrawal Freeze
    $(document).on('click', '.withdrawalFreeze', function (event) {
        $(this).html('<i class="fas fa-sync font-size-12px fa-spin"></i>');
        let userId = $(event.currentTarget).attr('data-id');
        $.ajax({
            url: userUrl + '/' + userId + '/withdrawal-freeze',
            type: 'post',
            cache: false,
            success: function (result) {
                if (result.success) {
                    displaySuccessMessage(result.message);
                    $('#usersTbl').DataTable().ajax.reload(null, true);
                }
            },
            error: function (error) {
                manageAjaxErrors(error)
            },
        })
    });

    // Withdrawal Money
    $(document).on('click', '.user-withdrawal-money', function (event) {
        let userId = $(event.currentTarget).attr('data-id');
        $('#withdrawalMoneyModal').find('#userId').val(userId);
        $('#withdrawalMoneyModal').modal('show');
    });

    $(document).on('submit', '#changeWithdrawalMoneyForm', function (event) {
        event.preventDefault();
        let loadingButton = $(this).find('#btnWithdrawMoneySave');
        loadingButton.button('loading');
        let userId = $('#userId').val();
        $.ajax({
            url: userUrl + '/' + userId + '/withdrawal-money',
            type: 'POST',
            data: $(this).serialize(),
            cache: false,
            success: function (result) {
                if (result.success) {
                    displaySuccessMessage(result.message);
                    $('#withdrawalMoneyModal').modal('hide');
                    $('#usersTbl').DataTable().ajax.reload(null, true);
                }
            },
            error: function (error) {
                displayErrorMessage(error.responseJSON.message);
            },
            complete: function () {
                loadingButton.button('reset');
            },
        });
    });

    $('#withdrawalMoneyModal').on('hidden.bs.modal', function () {
        resetModalForm('#changeWithdrawalMoneyForm', '#validationErrorsBox');
    });

    $('#clearsOn').datetimepicker({
        format: 'YYYY-MM-DD HH:mm:ss',
        useCurrent: true,
        icons: {
            up: 'fa fa-chevron-up',
            down: 'fa fa-chevron-down',
            next: 'fa fa-chevron-right',
            previous: 'fa fa-chevron-left',
        },
        sideBySide: true,
        widgetPositioning: {
            horizontal: 'left',
            vertical: 'bottom',
        },
    });

    // Mail-Check Disable/Enable
    $(document).on('click', '.mailCheckCheckBox', function (event) {
        $(this).attr("disabled", true);
        let checkBox = '';
        if($(this).is(':checked')) {
            checkBox = true;
        }else {
            checkBox = false;
        }
        let userId = $(event.currentTarget).attr('data-id');
        console.log(userId);
        $.ajax({
            url: userUrl + '/' + userId + '/mail-check-option',
            type: 'post',
            data: {check_box_value: checkBox},
            cache: false,
            success: function (result) {
                if (result.success) {
                    displaySuccessMessage(result.message);
                    $('#usersTbl').DataTable().ajax.reload(null, true);
                }
            },
            error: function (error) {
                manageAjaxErrors(error);
                $('.mailCheckCheckBox').attr("disabled", false).prop('checked', false);
            },
        })
    });
});
