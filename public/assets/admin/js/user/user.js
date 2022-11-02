/******/ (function(modules) { // webpackBootstrap
/******/ 	// The module cache
/******/ 	var installedModules = {};
/******/
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/
/******/ 		// Check if module is in cache
/******/ 		if(installedModules[moduleId]) {
/******/ 			return installedModules[moduleId].exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = installedModules[moduleId] = {
/******/ 			i: moduleId,
/******/ 			l: false,
/******/ 			exports: {}
/******/ 		};
/******/
/******/ 		// Execute the module function
/******/ 		modules[moduleId].call(module.exports, module, module.exports, __webpack_require__);
/******/
/******/ 		// Flag the module as loaded
/******/ 		module.l = true;
/******/
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/
/******/
/******/ 	// expose the modules object (__webpack_modules__)
/******/ 	__webpack_require__.m = modules;
/******/
/******/ 	// expose the module cache
/******/ 	__webpack_require__.c = installedModules;
/******/
/******/ 	// define getter function for harmony exports
/******/ 	__webpack_require__.d = function(exports, name, getter) {
/******/ 		if(!__webpack_require__.o(exports, name)) {
/******/ 			Object.defineProperty(exports, name, { enumerable: true, get: getter });
/******/ 		}
/******/ 	};
/******/
/******/ 	// define __esModule on exports
/******/ 	__webpack_require__.r = function(exports) {
/******/ 		if(typeof Symbol !== 'undefined' && Symbol.toStringTag) {
/******/ 			Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });
/******/ 		}
/******/ 		Object.defineProperty(exports, '__esModule', { value: true });
/******/ 	};
/******/
/******/ 	// create a fake namespace object
/******/ 	// mode & 1: value is a module id, require it
/******/ 	// mode & 2: merge all properties of value into the ns
/******/ 	// mode & 4: return value when already ns object
/******/ 	// mode & 8|1: behave like require
/******/ 	__webpack_require__.t = function(value, mode) {
/******/ 		if(mode & 1) value = __webpack_require__(value);
/******/ 		if(mode & 8) return value;
/******/ 		if((mode & 4) && typeof value === 'object' && value && value.__esModule) return value;
/******/ 		var ns = Object.create(null);
/******/ 		__webpack_require__.r(ns);
/******/ 		Object.defineProperty(ns, 'default', { enumerable: true, value: value });
/******/ 		if(mode & 2 && typeof value != 'string') for(var key in value) __webpack_require__.d(ns, key, function(key) { return value[key]; }.bind(null, key));
/******/ 		return ns;
/******/ 	};
/******/
/******/ 	// getDefaultExport function for compatibility with non-harmony modules
/******/ 	__webpack_require__.n = function(module) {
/******/ 		var getter = module && module.__esModule ?
/******/ 			function getDefault() { return module['default']; } :
/******/ 			function getModuleExports() { return module; };
/******/ 		__webpack_require__.d(getter, 'a', getter);
/******/ 		return getter;
/******/ 	};
/******/
/******/ 	// Object.prototype.hasOwnProperty.call
/******/ 	__webpack_require__.o = function(object, property) { return Object.prototype.hasOwnProperty.call(object, property); };
/******/
/******/ 	// __webpack_public_path__
/******/ 	__webpack_require__.p = "/";
/******/
/******/
/******/ 	// Load entry module and return exports
/******/ 	return __webpack_require__(__webpack_require__.s = 59);
/******/ })
/************************************************************************/
/******/ ({

/***/ "./resources/assets/admin/js/user/user.js":
/*!************************************************!*\
  !*** ./resources/assets/admin/js/user/user.js ***!
  \************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


$(document).ready(function () {
  var tbl = $('#usersTbl').DataTable({
    processing: true,
    serverSide: true,
    "ajax": {
      "url": userUrl
    },
    "order": [[2, "asc"]],
    "columnDefs": [{
      targets: '_all',
      defaultContent: 'N/A'
    }, {
      "targets": [2],
      'width': '13%'
    }, {
      "targets": [3],
      'width': '8%'
    }, {
      "targets": [4],
      'width': '10%'
    }, {
      "targets": [6],
      'width': '8%',
      'class': 'text-center',
      "orderable": false
    }, {
      "targets": [5],
      'width': '8%',
      'class': 'text-center',
      "orderable": false
    }, {
      "targets": [8],
      'width': '9%'
    }, {
      "targets": [9],
      'width': '3%',
      'class': 'text-center',
      "orderable": false
    }, {
      "targets": [11],
      'width': '8%',
      'class': 'text-center',
      "orderable": false
    }, {
      "targets": [10],
      'width': '5%',
      'class': 'text-center',
      "orderable": false
    }, {
      "targets": [7],
      "searchable": false,
      "visible": isImpersonate ? true : false,
      'class': 'text-center',
      'width': '5%',
      "orderable": false
    }, {
      "targets": [0],
      "orderable": false,
      'width': '5%',
      'class': 'text-center'
    }],
    columns: [{
      data: function data(row) {
        return '<img src="' + row.profile_picture + '" class="user-picture-column rounded-circle" data-toggle="tooltip" title="' + row.full_name + '">';
      },
      name: 'first_name'
    }, {
      data: 'full_name',
      name: 'full_name'
    }, {
      data: 'email',
      name: 'email'
    }, {
      data: 'job_title',
      name: 'job_title'
    }, {
      data: 'office_phone',
      name: 'office_phone'
    }, {
      data: function data(row) {
        var checked = row.mail_check_disable == 0 ? '' : 'checked';
        var data = [{
          'id': row.id,
          'checked': checked
        }];
        return prepareTemplateRender('#UserMailCheckTemplate', data);
      },
      name: 'office_phone'
    }, {
      data: function data(row) {
        return '<a href="javascript:void(0);" class="btn btn-sm btn-info resetPassword" data-id="' + row.id + '"><span>Reset Password</span></a>';
      },
      name: 'office_phone'
    }, {
      data: function data(row) {
        if (!row.is_admin && !row.is_disabled) {
          var url = impersonateUrl + '/' + row.id;
          return '<a href="' + url + '" class="btn btn-sm btn-primary">Impersonate</a>';
        }

        return '<span class="btn-sm btn-warning">N/A</span>';
      },
      name: 'id'
    }, {
      data: function data(row) {
        if (!isEmpty(row.deactivate_at)) {
          return moment(row.deactivate_at).format('Do MMM, YYYY hh:mm:ss a');
        }

        return 'N/A';
      },
      name: 'last_name'
    }, {
      data: function data(row) {
        var checked = row.is_disabled == 0 ? 'checked' : '';
        var data = [{
          'id': row.id,
          'checked': checked
        }];
        return prepareTemplateRender('#UserStatusTemplate', data);
      },
      name: 'user.status'
    }, {
      data: function data(row) {
        var checked = row.email_verified_at !== null ? '<span style="color:green;">Verified</span>' : '<span style="color:red;">Unverified</span>'; // let data = [{'id': row.id, 'checked': checked}];

        return checked;
      },
      name: 'user.isverified'
    }, {
      data: function data(row) {
        if (row.withdrawal_freeze) {
          return '<a href="javascript:void(0);" class="btn btn-sm btn-danger withdrawalFreeze" data-id="' + row.id + '"><span>Withdrawal Frozen</span></a>';
        }

        return '<a href="javascript:void(0);" class="btn btn-sm btn-primary withdrawalFreeze" data-id="' + row.id + '"><span>Withdrawal Freeze</span></a>';
      },
      name: 'id'
    }, {
      data: function data(row) {
        if (row.is_disabled == 0) {
          var data = [{
            'id': row.id,
            'isDisabled': row.is_disabled,
            'url': userUrl + '/' + row.id + '/edit'
          }];
          return prepareTemplateRender('#usersActionTemplate', data);
        }

        return 'N/A';
      },
      name: 'last_name'
    }]
  }); // Send Reset password email

  $(document).on('click', '.resetPassword', function (event) {
    $(this).html('<i class="fas fa-sync font-size-12px fa-spin"></i>');
    var userId = $(event.currentTarget).attr('data-id');
    $.ajax({
      url: userUrl + '/' + userId + '/send-reset-email',
      type: 'post',
      cache: false,
      data: {
        "userId": userId
      },
      success: function success(result) {
        if (result.success) {
          displaySuccessMessage(result.message);
          $('#usersTbl').DataTable().ajax.reload(null, true);
        }
      },
      error: function error(_error) {
        manageAjaxErrors(_error);
      }
    });
  }); //Deactive User

  $(document).on('click', '.status', function (event) {
    $(this).html('<i class="fas fa-sync font-size-12px fa-spin"></i>');
    var userId = $(event.currentTarget).attr('data-id');
    $.ajax({
      url: userUrl + '/' + userId + '/active-deactive',
      method: 'post',
      cache: false,
      success: function success(result) {
        if (result.success) {
          displaySuccessMessage(result.message);
          $('#usersTbl').DataTable().ajax.reload(null, false);
          $(this).html('<i class="as fa-user-lock"></i>');
        }
      },
      error: function error(result) {
        UnprocessableInputError(result);
        $(this).html('<i class="as fa-user-lock"></i>');
      }
    });
  }); // Withdrawal Freeze

  $(document).on('click', '.withdrawalFreeze', function (event) {
    $(this).html('<i class="fas fa-sync font-size-12px fa-spin"></i>');
    var userId = $(event.currentTarget).attr('data-id');
    $.ajax({
      url: userUrl + '/' + userId + '/withdrawal-freeze',
      type: 'post',
      cache: false,
      success: function success(result) {
        if (result.success) {
          displaySuccessMessage(result.message);
          $('#usersTbl').DataTable().ajax.reload(null, true);
        }
      },
      error: function error(_error2) {
        manageAjaxErrors(_error2);
      }
    });
  }); // Withdrawal Money

  $(document).on('click', '.user-withdrawal-money', function (event) {
    var userId = $(event.currentTarget).attr('data-id');
    $('#withdrawalMoneyModal').find('#userId').val(userId);
    $('#withdrawalMoneyModal').modal('show');
  });
  $(document).on('submit', '#changeWithdrawalMoneyForm', function (event) {
    event.preventDefault();
    var loadingButton = $(this).find('#btnWithdrawMoneySave');
    loadingButton.button('loading');
    var userId = $('#userId').val();
    $.ajax({
      url: userUrl + '/' + userId + '/withdrawal-money',
      type: 'POST',
      data: $(this).serialize(),
      cache: false,
      success: function success(result) {
        if (result.success) {
          displaySuccessMessage(result.message);
          $('#withdrawalMoneyModal').modal('hide');
          $('#usersTbl').DataTable().ajax.reload(null, true);
        }
      },
      error: function error(_error3) {
        displayErrorMessage(_error3.responseJSON.message);
      },
      complete: function complete() {
        loadingButton.button('reset');
      }
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
      previous: 'fa fa-chevron-left'
    },
    sideBySide: true,
    widgetPositioning: {
      horizontal: 'left',
      vertical: 'bottom'
    }
  }); // Mail-Check Disable/Enable

  $(document).on('click', '.mailCheckCheckBox', function (event) {
    $(this).attr("disabled", true);
    var checkBox = '';

    if ($(this).is(':checked')) {
      checkBox = true;
    } else {
      checkBox = false;
    }

    var userId = $(event.currentTarget).attr('data-id');
    console.log(userId);
    $.ajax({
      url: userUrl + '/' + userId + '/mail-check-option',
      type: 'post',
      data: {
        check_box_value: checkBox
      },
      cache: false,
      success: function success(result) {
        if (result.success) {
          displaySuccessMessage(result.message);
          $('#usersTbl').DataTable().ajax.reload(null, true);
        }
      },
      error: function error(_error4) {
        manageAjaxErrors(_error4);
        $('.mailCheckCheckBox').attr("disabled", false).prop('checked', false);
      }
    });
  });
});

/***/ }),

/***/ 59:
/*!******************************************************!*\
  !*** multi ./resources/assets/admin/js/user/user.js ***!
  \******************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

module.exports = __webpack_require__(/*! E:\xampp_7.2\htdocs\AllGym\portal-v2.0\resources\assets\admin\js\user\user.js */"./resources/assets/admin/js/user/user.js");


/***/ })

/******/ });