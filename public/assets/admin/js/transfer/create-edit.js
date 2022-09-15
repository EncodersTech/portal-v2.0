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
/******/ 	return __webpack_require__(__webpack_require__.s = 67);
/******/ })
/************************************************************************/
/******/ ({

/***/ "./resources/assets/admin/js/transfer/create-edit.js":
/*!***********************************************************!*\
  !*** ./resources/assets/admin/js/transfer/create-edit.js ***!
  \***********************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


$(document).ready(function () {
  $('#sourceUser').select2({
    width: '100%'
  });
  $('#destinationUser').select2({
    width: '100%'
  });
});
$(document).on('change', '#sourceUser', function () {
  var userId = $(this).val();

  if (!isEmpty(userId)) {
    displaySourceUserBankAccount(userId);
  }
});
$(document).on('change', '#destinationUser', function () {
  var userId = $(this).val();

  if (!isEmpty($(this).val())) {
    displayDestinationUserBankAccount(userId);
  }
});
$(document).on('click', 'input:radio[name="source_wallet_bank"]', function () {
  if ($(this).is(':checked')) {
    $('#allGym_source_balance').prop('checked', false);
  }
});
$(document).on('click', 'input:checkbox[name="allGym_source_balance"]', function () {
  if ($(this).is(':checked')) {
    $("input:radio[name='source_wallet_bank']").prop('checked', false);
  }
});
$(document).on('click', 'input:radio[name="destination_wallet_bank"]', function () {
  if ($(this).is(':checked')) {
    $('#allGym_destination_balance').prop('checked', false);
  }
});
$(document).on('click', 'input:checkbox[name="allGym_destination_balance"]', function () {
  if ($(this).is(':checked')) {
    $("input:radio[name='destination_wallet_bank']").prop('checked', false);
  }
});

function displaySourceUserBankAccount(userId) {
  $.ajax({
    url: 'bank-accounts',
    type: 'POST',
    data: {
      'userId': userId
    },
    success: function success(result) {
      if (result.success) {
        var sourceBankAccounts = result.data;
        var data = [{
          'bankAccounts': sourceBankAccounts
        }];
        $('.source-user-bank').html('');
        $('.source-user-bank').append(prepareTemplateRender('#sourceUserBankTemplate', data));
      }
    },
    error: function error(result) {
      displayErrorMessage(result.responseJSON.message);
    }
  });
}

function displayDestinationUserBankAccount(userId) {
  $.ajax({
    url: 'bank-accounts',
    type: 'POST',
    data: {
      'userId': userId
    },
    success: function success(result) {
      if (result.success) {
        var destinationBankAccounts = result.data;
        var data = [{
          'bankAccounts': destinationBankAccounts
        }];
        $('.destination-user-bank').html('');
        $('.destination-user-bank').append(prepareTemplateRender('#destinationUserBankTemplate', data));
      }
    },
    error: function error(result) {
      displayErrorMessage(result.responseJSON.message);
    }
  });
}

$('#transferStore').on('submit', function (e) {
  e.preventDefault();
  var loadingButton = jQuery(this).find('#btnTransfer');
  loadingButton.button('loading');
  var formData = new FormData($(this)[0]);
  $.ajax({
    url: 'store',
    type: 'POST',
    data: formData,
    processData: false,
    contentType: false,
    success: function success(result) {
      if (result.success) {
        displaySuccessMessage(result.message);
        setTimeout(function () {
          window.location.href = transferUrl;
        }, 3000);
      }
    },
    error: function error(result) {
      displayErrorMessage(result.responseJSON.message);
    },
    complete: function complete() {
      loadingButton.button('reset');
    }
  });
});

/***/ }),

/***/ 67:
/*!*****************************************************************!*\
  !*** multi ./resources/assets/admin/js/transfer/create-edit.js ***!
  \*****************************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

module.exports = __webpack_require__(/*! E:\xampp_7.2\htdocs\AllGym\portal-v2.0\resources\assets\admin\js\transfer\create-edit.js */"./resources/assets/admin/js/transfer/create-edit.js");


/***/ })

/******/ });