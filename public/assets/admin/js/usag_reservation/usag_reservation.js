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
/******/ 	return __webpack_require__(__webpack_require__.s = 62);
/******/ })
/************************************************************************/
/******/ ({

/***/ "./resources/assets/admin/js/usag_reservation/usag_reservation.js":
/*!************************************************************************!*\
  !*** ./resources/assets/admin/js/usag_reservation/usag_reservation.js ***!
  \************************************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


$(document).ready(function () {
  var page = $('#hiddenPage').val();
  $(document).on('click', '.pagination li > a', function (event) {
    event.preventDefault();
    var page = $(this).attr('href').split('page=')[1];
    var query = $('#searchData').val();
    $('li').removeClass('active');
    $(this).parent().addClass('active');
    fetchData(page, query);
  });

  function fetchData(page, searchData) {
    $.ajax({
      url: 'search-usag-reservations?page=' + page + '&searchData=' + searchData,
      type: 'GET',
      cache: false,
      success: function success(result) {
        if (result.success) {
          $('.searchUsag').hide();
          $('.usagReservationDiv').html('').html(result.data);
        }
      },
      error: function error(result) {
        displayErrorMessage(result.responseJSON.message);
      }
    });
  }

  $(document).on('keyup', '#searchData', function () {
    $('.searchUsag').removeClass('d-none').show();
    $('.no-record-found').addClass('d-none').hide();
    var searchData = $(this).val();
    fetchData(page, searchData);
  });
  $(document).on('click', '#usagReservationHide', function (event) {
    var reservationId = $(event.currentTarget).attr('data-id');
    $(this).html('<i class="fas fa-sync font-size-12px fa-spin"></i>');
    $.ajax({
      url: usagReservations + '/' + reservationId + '/usag-reservation-hide',
      method: 'post',
      cache: false,
      success: function success(result) {
        if (result.success) {
          var query = $('#searchData').val();
          fetchData(page, query);
        }
      },
      error: function error(result) {
        UnprocessableInputError(result);
        $(this).html('<i class="fas fa-eye-slash"></i>');
      }
    });
  });
  $(document).on('click', '#usagReservationDelete', function (event) {
    var reservationId = $(event.currentTarget).attr('data-id');
    swal({
      title: 'Delete !',
      text: 'Are you sure want to delete this USAG Reservation ?',
      type: 'warning',
      showCancelButton: true,
      closeOnConfirm: false,
      showLoaderOnConfirm: true,
      confirmButtonColor: '#007bff',
      cancelButtonColor: '#d33',
      cancelButtonText: 'No',
      confirmButtonText: 'Yes'
    }, function () {
      $.ajax({
        url: usagReservations + '/' + reservationId,
        type: 'DELETE',
        dataType: 'json',
        success: function success(obj) {
          if (obj.success) {
            var query = $('#searchData').val();
            fetchData(page, query);
          }

          swal({
            title: 'Deleted!',
            text: 'USAG Reservation has been deleted.',
            type: 'success',
            confirmButtonColor: '#007bff',
            timer: 2000
          });
        },
        error: function error(data) {
          swal({
            title: '',
            text: data.responseJSON.message,
            type: 'error',
            confirmButtonColor: '#007bff',
            timer: 5000
          });
        }
      });
    });
  });
});

/***/ }),

/***/ 62:
/*!******************************************************************************!*\
  !*** multi ./resources/assets/admin/js/usag_reservation/usag_reservation.js ***!
  \******************************************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

module.exports = __webpack_require__(/*! E:\xampp_7.2\htdocs\AllGym\new\portal-v2.0\resources\assets\admin\js\usag_reservation\usag_reservation.js */"./resources/assets/admin/js/usag_reservation/usag_reservation.js");


/***/ })

/******/ });