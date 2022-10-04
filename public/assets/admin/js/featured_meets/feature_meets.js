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
/******/ 	return __webpack_require__(__webpack_require__.s = 64);
/******/ })
/************************************************************************/
/******/ ({

/***/ "./resources/assets/admin/js/featured_meets/feature_meets.js":
/*!*******************************************************************!*\
  !*** ./resources/assets/admin/js/featured_meets/feature_meets.js ***!
  \*******************************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


$(document).ready(function () {
  var tbl = $('#featuredMeetsTbl').DataTable({
    processing: true,
    serverSide: true,
    ajax: {
      url: featuredMeetsUrl
    },
    "order": [[0, "asc"]],
    "columnDefs": [{
      //sanctioning_bodies
      "targets": [5],
      'width': '15%'
    }, {
      //registration_status
      "targets": [7],
      'class': 'text-center',
      'width': '10%'
    }, {
      "targets": [6],
      'class': 'text-center',
      'width': '8%'
    }, {
      targets: '_all',
      defaultContent: 'N/A'
    }],
    columns: [{
      data: function data(row) {
        var url = meetsUrl + '/' + row.id + '/dashboard';
        return '<a href="' + url + '" class="">' + row.name + '</a>';
      },
      name: 'name'
    }, {
      data: 'gym.name',
      name: 'gym.name'
    }, {
      data: function data(row) {
        return moment(row.start_date, 'YYYY-MM-DD').format('DD/MM/YYYY');
      },
      name: 'start_date'
    }, {
      data: function data(row) {
        return moment(row.end_date, 'YYYY-MM-DD').format('DD/MM/YYYY');
      },
      name: 'end_date'
    }, {
      data: function data(row) {
        return moment(row.created_at, 'YYYY-MM-DD').format('DD/MM/YYYY');
      },
      name: 'created_at'
    }, {
      data: function data(row) {
        if (!isEmpty(row.venue_state_id)) {
          return row.venue_state.name + ', ' + row.venue_state.code;
        }

        return 'N/A';
      },
      name: 'name'
    }, {
      data: 'sanction_bodies[, ]',
      name: 'gym.name'
    }, {
      data: function data(row) {
        var now = moment(new Date()).format("YYYY-MM-DD 00:00:00");

        if (now < row.end_date) {
          var checked = row.is_featured != 0 ? 'checked' : '';
          var data = [{
            'id': row.id,
            'checked': checked
          }];
          return prepareTemplateRender('#featuredMeetTemplate', data);
        }
      },
      name: 'is_featured'
    }, {
      data: function data(row) {
        var statusColor = {
          1: 'danger',
          2: 'success',
          3: 'warning',
          4: 'info'
        };
        var statusArr = {
          1: 'Closed',
          2: 'Open',
          3: 'Late',
          4: 'Opening Soon'
        };
        return '<span title="Edit" class="font-size-15 badge badge-' + statusColor[row.registration_status] + '">' + statusArr[row.registration_status] + '</span>';
      },
      name: 'id'
    }]
  });
}); //Make Feature Meet

$(document).on('click', '.makeFeature', function (event) {
  $(this).html('<i class="fas fa-sync font-size-12px fa-spin"></i>');
  var meetId = $(event.currentTarget).attr('data-id');
  $.ajax({
    url: meetsUrl + '/' + meetId + '/meet-featured',
    method: 'post',
    cache: false,
    success: function success(result) {
      if (result.success) {
        $('#meetsTbl').DataTable().ajax.reload(null, false);
      }
    },
    error: function error(result) {
      manageAjaxErrors(result);
    }
  });
});

/***/ }),

/***/ 64:
/*!*************************************************************************!*\
  !*** multi ./resources/assets/admin/js/featured_meets/feature_meets.js ***!
  \*************************************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

module.exports = __webpack_require__(/*! E:\xampp_7.2\htdocs\AllGym\new\portal-v2.0\resources\assets\admin\js\featured_meets\feature_meets.js */"./resources/assets/admin/js/featured_meets/feature_meets.js");


/***/ })

/******/ });