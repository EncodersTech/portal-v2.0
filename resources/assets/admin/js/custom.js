'use strict';

let jsrender = require('jsrender');
$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
});

window.addCommas = function (nStr) {
    nStr += '';
    let x = nStr.split('.');
    let x1 = x[0];
    let x2 = x.length > 1 ? '.' + x[1] : '';
    var rgx = /(\d+)(\d{3})/;
    while (rgx.test(x1)) {
        x1 = x1.replace(rgx, '$1' + ',' + '$2');
    }
    return x1 + x2;
};


window.manageAjaxErrors = function (
    data, errorDivId = 'editValidationErrorsBox') {
    if (data.status == 404) {
        iziToast.error({
            title: 'Error!',
            message: data.responseJSON.message,
            position: 'topRight',
        });
    } else {
        printErrorMessage('#' + errorDivId, data)
    }
}

window.printErrorMessage = function (selector, errorResult) {
    $(selector).show().html('')
    $(selector).text(errorResult.responseJSON.message)
}

window.UnprocessableInputError = function (data) {
    iziToast.error({
        title: 'Error',
        message: data.responseJSON.message,
        position: 'topRight',
    });
};

window.prepareTemplateRender = function (templateSelector, data) {
    let template = jsrender.templates(templateSelector);
    return template.render(data);
};

// matches screen pixels for media queries and applied the supplied css to the same
window.matchWindowScreenPixels = function (selectorObj, modulePrefix) {
    if (typeof selectorObj != 'undefined') {
        const windowWidth = $(window).innerWidth();
        if (windowWidth === 375) {
            $.each(selectorObj, function (key, val) {
                $(val + ' + .bootstrap-datetimepicker-widget.dropdown-menu').
                addClass('dtPicker375-' + modulePrefix);
            });
        }
        if (windowWidth === 360) {
            $.each(selectorObj, function (key, val) {
                $(val + ' + .bootstrap-datetimepicker-widget.dropdown-menu').
                addClass('dtPicker360-' + modulePrefix);
            });
        } else if (windowWidth === 320) {
            $.each(selectorObj, function (key, val) {
                $(val + ' + .bootstrap-datetimepicker-widget.dropdown-menu').
                addClass('dtPicker320-' + modulePrefix);
            });
        }
    }
};

window.isEmpty = (value) => {
    return value === undefined || value === null || value === '';
};

window.addCommas = function (nStr) {
    nStr += '';
    let x = nStr.split('.');
    let x1 = x[0];
    let x2 = x.length > 1 ? '.' + x[1] : '';
    var rgx = /(\d+)(\d{3})/;
    while (rgx.test(x1)) {
        x1 = x1.replace(rgx, '$1' + ',' + '$2');
    }
    return x1 + x2;
};

window.resetModalForm = function (formId, validationBox) {
    $(formId)[0].reset();
    $('select.select2Selector').each(function (index, element) {
        let drpSelector = '#' + $(this).attr('id');
        $(drpSelector).val('');
        $(drpSelector).trigger('change');
    });
    $(validationBox).hide();
};

$(document).ready(function () {
    // script to active parent menu if sub menu has currently active
    let hasActiveMenu = $(document).find('.nav-item.has-treeview ul li a').hasClass('active');
    if (hasActiveMenu) {
        $(document).find('.nav-item.has-treeview ul li a.active').closest('ul').parent('li').addClass('menu-open');
    }
});

window.displayErrorMessage = function (message) {
    iziToast.error({
        title: 'Error',
        message: message,
        position: 'topRight',
    });
};

window.displaySuccessMessage = function (message) {
    iziToast.success({
        title: 'Success',
        message: message,
        position: 'topRight',
    });
};


window.deleteItem = function (url, tableId, header, callFunction = null) {
    swal({
            title: 'Delete !',
            text: 'Are you sure want to delete this "' + header + '" ?',
            type: 'warning',
            showCancelButton: true,
            closeOnConfirm: false,
            showLoaderOnConfirm: true,
            confirmButtonColor: '#007bff',
            cancelButtonColor: '#d33',
            cancelButtonText: 'No',
            confirmButtonText: 'Yes',
        },
        function () {
            deleteItemAjax(url, tableId, header, callFunction = null);
        });
};


function deleteItemAjax (url, tableId, header, callFunction = null) {
    $.ajax({
        url: url,
        type: 'DELETE',
        dataType: 'json',
        success: function (obj) {
            if (obj.success) {
                $(tableId).DataTable().ajax.reload(null, true);
            }
            swal({
                title: 'Deleted!',
                text: header + ' has been deleted.',
                type: 'success',
                confirmButtonColor: '#007bff',
                timer: 2000,
            });
            if (callFunction) {
                eval(callFunction);
            }
        },
        error: function (data) {
            swal({
                title: '',
                text: data.responseJSON.message,
                type: 'error',
                confirmButtonColor: '#007bff',
                timer: 5000,
            });
        },
    });
}

