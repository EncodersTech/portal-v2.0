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
/******/ 	return __webpack_require__(__webpack_require__.s = 45);
/******/ })
/************************************************************************/
/******/ ({

/***/ "./resources/js/host/meet/meet-dashboard.js":
/*!**************************************************!*\
  !*** ./resources/js/host/meet/meet-dashboard.js ***!
  \**************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


$(document).ready(function () {
  setTimeout(function () {
    //message body summernote
    $('#messageBody').summernote({
      placeholder: 'Write message here...',
      minHeight: 200,
      toolbar: [['style', ['bold', 'italic', 'underline', 'clear']], ['font', ['strikethrough', 'superscript', 'subscript']], ['fontsize', ['fontsize']], ['insert', ['link']], ['color', ['color']], ['para', ['paragraph']], ['height', ['height']]]
    });
    checkBoxSelect(); //select all checkbox

    function checkBoxSelect() {
      $('#ckbCheckAll').click(function () {
        $('.gymCheck').prop('checked', $(this).prop('checked'));
      });
      $('.gymCheck').on('click', function () {
        if ($('.gymCheck:checked').length == $('.gymCheck').length) {
          $('#ckbCheckAll').prop('checked', true);
        } else {
          $('#ckbCheckAll').prop('checked', false);
        }
      });
    }
  }, 3200); //submit massmailer form

  $(document).on('submit', '#submitMassNotification', function () {
    var $description = $('<div />').html($('#messageBody').summernote('code'));
    var empty = $description.text().trim().replace(/ \r\n\t/g, '') === '';

    if ($('.gymCheck:checked').length === 0) {
      showError('Please select at least one gym.');
      return false;
    }

    if ($('#messageBody').summernote('isEmpty') || empty) {
      showError('Please write your message.');
      return false;
    }

    $('#sedMailNotification').prop('disabled', true);
    return true;
  }); //if add attachment then show attachment.

  $(document).on('change', '#documentImage', function () {
    var extension = isValidDocument($(this), '#validationErrorsBox');

    if (!isEmpty(extension) && extension != false) {
      $('#validationErrorsBox').html('').hide();
      displayDocument(this, '#previewImage', extension);
    }
  });

  window.isValidDocument = function (inputSelector, validationMessageSelector) {
    var ext = $(inputSelector).val().split('.').pop().toLowerCase(); //console.log('ext',ext);

    if (isEmpty(ext)) {
      $('#previewImage').attr('src', defaultImage);
      return false;
    }

    if ($.inArray(ext, ['png', 'jpg', 'jpeg', 'pdf', 'doc', 'docx', 'xlsx']) == -1) {
      $(inputSelector).val('');
      showError('The document must be a file of type: jpeg, jpg, png, pdf, doc, docx.');
      return false;
    }

    return ext;
  };

  function displayDocument(input, selector, extension) {
    var displayPreview = true;

    if (input.files && input.files[0]) {
      var reader = new FileReader();

      reader.onload = function (e) {
        var image = new Image();

        if ($.inArray(extension, ['pdf', 'doc', 'docx', 'xlsx']) == -1) {
          image.src = e.target.result;
        } else {
          if (extension == 'pdf') {
            image.src = pdfDocumentImageUrl;
          } else if (extension == 'xlsx') {
            image.src = excelDocumentImageUrl;
          } else {
            image.src = docxDocumentImageUrl;
          }
        }

        image.onload = function () {
          $(selector).attr('src', image.src);
          displayPreview = true;
        };
      };

      if (displayPreview) {
        reader.readAsDataURL(input.files[0]);
        $(selector).show();
      }
    }
  }

  ;

  function isEmpty(value) {
    return value === undefined || value === null || value === '';
  }

  ;

  function showError(msg) {
    $.alert({
      title: 'Whoops',
      content: msg,
      icon: 'fas fa-exclamation-triangle',
      type: 'red',
      typeAnimated: true
    });
  }
});

/***/ }),

/***/ 45:
/*!********************************************************!*\
  !*** multi ./resources/js/host/meet/meet-dashboard.js ***!
  \********************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

module.exports = __webpack_require__(/*! E:\xampp_7.2\htdocs\AllGym\portal-v2.0\resources\js\host\meet\meet-dashboard.js */"./resources/js/host/meet/meet-dashboard.js");


/***/ })

/******/ });