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

/***/ "./resources/assets/admin/js/meet/dashboard.js":
/*!*****************************************************!*\
  !*** ./resources/assets/admin/js/meet/dashboard.js ***!
  \*****************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


$(document).ready(function () {
  var tbl = $('#meetGymsTbl').DataTable({
    processing: true,
    serverSide: true,
    "ajax": {
      "url": meetGymUrl,
      data: function data(_data) {
        _data.meet_id = meetId;
      }
    },
    "order": [[0, "asc"]],
    "columnDefs": [{
      "targets": [4, 5],
      'class': 'text-center',
      'width': '5%'
    }, {
      "targets": [7],
      'class': 'text-center',
      'width': '10%'
    }, {
      "targets": [2],
      'class': 'text-center',
      'width': '13%'
    }, {
      "targets": [1],
      'width': '15%'
    }, {
      "targets": [3],
      "searchable": false,
      'class': 'text-center',
      'width': '10%'
    }, {
      "targets": [6, 8],
      'class': 'text-center',
      'width': '3%'
    }],
    columns: [{
      data: 'gym.name',
      name: 'gym.name'
    }, {
      data: function data(row) {
        return moment(row.created_at, 'YYYY-MM-DD').format('Do MMM, YYYY');
      },
      name: 'gym.name'
    }, {
      data: function data(row) {
        return '<span class="text-danger">WIP</span>';
      },
      name: 'ach_fee_override'
    }, {
      data: function data(row) {
        return '<span>&#36;</span> ' + addCommas(parseFloat(row.total_fee).toFixed(2));
      },
      name: 'total_fee'
    }, {
      data: 'athletes_count',
      name: 'athletes_count'
    }, {
      data: 'coaches_count',
      name: 'coaches_count'
    }, {
      data: 'teams_count',
      name: 'teams_count'
    }, {
      data: function data(row) {
        var paymentStatus = !row.payment_status ? 'Pending Deposit' : 'available';

        if (row.payment_status) {
          if (row.gym.user.cleared_balance == 0) {
            return '--';
          }

          return '<span class="badge badge-success">' + paymentStatus + '</span>';
        } else {
          return '<span class="badge badge-danger">' + paymentStatus + '</span>';
        }
      },
      name: 'paypal_fee_override'
    }, {
      data: function data(row) {
        return '<span>&#36;</span> ' + addCommas(parseFloat(row.gym.user.cleared_balance).toFixed(2));
      },
      name: 'gym.user.cleared_balance'
    }]
  });
  $(document).on('click', '.copy-meet-url', function (e) {
    e.preventDefault();
    var copyText = $(this).attr('data-url');
    copyTextValue(copyText);
  });
  $(document).on('click', '#meet-public-url-copy', function (event) {
    event.preventDefault();
    var copyText = $('#meet-public-url').val();
    copyTextValue(copyText);
  });

  function copyTextValue(copyText) {
    var textarea = document.createElement("textarea");
    textarea.textContent = copyText;
    textarea.style.position = "fixed"; // Prevent scrolling to bottom of page in MS Edge.

    document.body.appendChild(textarea);
    textarea.select();
    document.execCommand("copy");
    document.body.removeChild(textarea);
  }
});
$(document).on('keyup keydown keypress', '#customHandlingFee', function () {
  var regex = /^\d{0,5}(\.\d{0,2})?$/i;

  if (!regex.test($(this).val())) {
    $(this).val('');
    displayErrorMessage('Please enter a correct fee, format 0.00.');
  }
});
$(document).on('submit', '#updateHandlingFee', function (e) {
  $('#saveBtn').prop('disabled', true);
  $.ajax({
    url: updateHandlingFeeRoute,
    cache: false,
    method: 'post',
    data: $(this).serialize(),
    success: function success(result) {
      if (result.success) {
        displaySuccessMessage(result.message);
        $('#customHandlingFee').blur();
        $('#saveBtn').prop('disabled', false);
        location.reload();
      }
    },
    error: function error(result) {
      displayErrorMessage(result.responseJSON.message);
      $('#saveBtn').prop('disabled', false);
    }
  });
  return false;
});

/***/ }),

/***/ 59:
/*!***********************************************************!*\
  !*** multi ./resources/assets/admin/js/meet/dashboard.js ***!
  \***********************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

module.exports = __webpack_require__(/*! E:\xampp_7.2\htdocs\AllGym\new\portal-v2.0\resources\assets\admin\js\meet\dashboard.js */"./resources/assets/admin/js/meet/dashboard.js");


/***/ })

/******/ });