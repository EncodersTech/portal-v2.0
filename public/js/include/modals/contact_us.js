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
/******/ 	return __webpack_require__(__webpack_require__.s = 53);
/******/ })
/************************************************************************/
/******/ ({

/***/ "./resources/js/include/modals/contact_us.js":
/*!***************************************************!*\
  !*** ./resources/js/include/modals/contact_us.js ***!
  \***************************************************/
/*! no static exports found */
/***/ (function(module, exports) {

$(document).ready(function () {
  var prefix = 'modal-contact-us';
  var modal = $('#' + prefix);
  var _busy = false;
  modal.on('show.bs.modal', function (e) {
    clearForm();
  });
  el('email').bind('input propertychange', function () {
    el('email').removeClass('is-invalid');
  });
  el('message').bind('input propertychange', function () {
    el('message').removeClass('is-invalid');
    el('message-char-count').html(750 - el('message').val().length);
  });
  el('close').click(function () {
    if (!_busy) modal.modal('hide');
  });
  el('submit').click(function () {
    if (_busy) return;
    _busy = true;
    el('spinner').show();
    el('email').removeClass('is-invalid');
    el('message').removeClass('is-invalid');
    hideAlert();
    email = el('email').val();
    message = el('message').val();
    axios.post('/api/contact', {
      'email': email,
      'message': message
    }).then(function (response) {
      clearForm();
      showAlert('Thank you ! Your message was sent, we will get back to you soon', 'success');
    })["catch"](function (error) {
      response = error.response.data;

      try {
        if ('errors' in response) {
          if ('email' in response.errors) el('email').addClass('is-invalid');
          if ('message' in response.errors) el('message').addClass('is-invalid');
        }
      } catch (ex) {}

      showAlert(response.message, 'error');
    })["finally"](function () {
      el('spinner').hide();
      _busy = false;
    });
  });

  function clearForm() {
    el('spinner').hide();
    el('email').val('');
    el('email').removeClass('is-invalid');
    el('message').val('');
    el('message').removeClass('is-invalid');
    hideAlert();
  }

  function showAlert(msg, type) {
    switch (type) {
      case 'error':
        el('alert-icon').attr('class', 'fas fa-times-circle');
        type = 'danger';
        break;

      case 'success':
        el('alert-icon').attr('class', 'fas fa-check-circle');
        break;

      case 'warning':
        el('alert-icon').attr('class', 'fas fa-exclamation-triangle');
        break;

      default:
        el('alert-icon').attr('class', 'fas fa-info-circle');
        type = 'info';
    }

    el('alert').attr('class', 'alert alert-' + type);
    el('alert-text').html(msg);
    el('alert-container').show();
  }

  function hideAlert(msg, type) {
    el('alert-container').hide();
    el('alert-icon').attr('class', '');
    el('alert').attr('class', '');
    el('alert-text').html('');
  }

  function el(el) {
    return $('#' + prefix + '-' + el);
  }
});

/***/ }),

/***/ 53:
/*!*********************************************************!*\
  !*** multi ./resources/js/include/modals/contact_us.js ***!
  \*********************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

module.exports = __webpack_require__(/*! E:\xampp_7.2\htdocs\AllGym\new\portal-v2.0\resources\js\include\modals\contact_us.js */"./resources/js/include/modals/contact_us.js");


/***/ })

/******/ });