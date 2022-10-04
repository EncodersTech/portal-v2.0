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
/******/ 	return __webpack_require__(__webpack_require__.s = 46);
/******/ })
/************************************************************************/
/******/ ({

/***/ "./resources/js/host/meet/meet_summary.js":
/*!************************************************!*\
  !*** ./resources/js/host/meet/meet_summary.js ***!
  \************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


$(document).ready(function () {// setTimeout(function () {
  //     loadMeetLineChart();
  //     loadMeetBarChart();
  //     loadMeetPieChart();
  // }, 1000)
  //
  // //Meet Line Chart
  // function loadMeetLineChart() {
  //     $.ajax({
  //         type: 'GET',
  //         url: meetChartURL + meet_id + '/line-chart-meet',
  //         dataType: 'json',
  //         cache: false,
  //     }).done(prepareMeetLineChartReport)
  // }
  //
  // function prepareMeetLineChartReport (result) {
  //     $('#meet-summary-line-chart-container').html('')
  //     let data = result.data
  //
  //     let athletes = false;
  //     let coaches = false;
  //     let gyms = false;
  //     $.each(data.athletes, function( index, value ) {
  //       if(value > 0){
  //           athletes = true;
  //           return false;
  //       }
  //     });
  //
  //     $.each(data.coaches, function( index, value ) {
  //         if(value > 0){
  //             athletes = true;
  //             return false;
  //         }
  //     });
  //
  //     $.each(data.gyms, function( index, value ) {
  //         if(value > 0){
  //             athletes = true;
  //             return false;
  //         }
  //     });
  //
  //
  //     if (!athletes && !coaches && !gyms) {
  //         $('#meet-summary-line-chart-container').empty();
  //         $('.lineChartSpinner').hide();
  //         $('#meet-summary-line-chart-container').
  //         append(
  //             '<div align="center" class="no-record"><b>No Records Found.</b></div>')
  //         return true
  //     }else {
  //         $('#meet-summary-line-chart-container').html('');
  //         $('#meet-summary-line-chart-container').
  //         append('<canvas id="meet-line-chart-report"></canvas>');
  //         $('.lineChartSpinner').hide();
  //     }
  //
  //
  //     //get the line chart canvas
  //     var ctx = document.getElementById('meet-line-chart-report').getContext('2d');
  //
  //     let lineChartData = {
  //         labels: data.dates,
  //         datasets: [
  //             {
  //                 label: "Total Athletes",
  //                 backgroundColor: 'rgb(255, 99, 132)',
  //                 borderColor: 'rgb(255, 99, 132)',
  //                 data: data.athletes,
  //                 fill: false,
  //             },
  //             {
  //                 label: "Total Coaches",
  //                 backgroundColor: 'rgb(54,162,235)',
  //                 borderColor: 'rgb(54, 162, 235)',
  //                 data: data.coaches,
  //                 fill: false,
  //             },
  //             {
  //                 label: "Total Gyms",
  //                 backgroundColor: 'rgb(98,233,40)',
  //                 borderColor: 'rgb(98,233,40)',
  //                 data: data.gyms,
  //                 fill: false,
  //             },
  //         ],
  //     };
  //
  //     ctx.canvas.style.height = '450px'
  //     ctx.canvas.style.width = '100%'
  //     let meetLineChart = new Chart(ctx, {
  //         type: 'line',
  //         data: lineChartData,
  //         options: {
  //             responsive: true,
  //             scales: {
  //                 xAxes: [{
  //                     gridLines: {
  //                         color: "rgba(0, 0, 0, 0)",
  //                     },
  //                     ticks: {
  //                         display: false //this will remove only the label
  //                     }
  //                 }],
  //                 yAxes: [{
  //                     gridLines: {
  //                         color: "rgba(0, 0, 0, 0)",
  //                     },
  //                     ticks: {
  //                         min: 0,
  //                         stepSize: 5
  //                     },
  //                     display: true,
  //                     scaleLabel: {
  //                         display: true,
  //                         labelString: 'Number of Athletes, Coaches and Gyms'
  //                     }
  //                 }]
  //             }
  //         },
  //     })
  // }
  //
  // //Meet Bar chart
  // function loadMeetBarChart() {
  //     $.ajax({
  //         type: 'GET',
  //         url: meetChartURL + meet_id + '/bar-chart-meet',
  //         dataType: 'json',
  //         cache: false,
  //     }).done(prepareMeetBarChart)
  // }
  //
  // function prepareMeetBarChart (result) {
  //     $('#meet-summary-bar-chart-container').html('')
  //     let data = result.data
  //
  //     let isNoRecord = false;
  //     $.each(data.earnedAmount, function( index, value ) {
  //         if(value > 0){
  //             isNoRecord = true;
  //             return false;
  //         }
  //     });
  //
  //     if (!isNoRecord) {
  //         $('#meet-summary-bar-chart-container').empty()
  //         $('.barChartSpinner').hide();
  //         $('#meet-summary-bar-chart-container').
  //         append(
  //             '<div align="center" class="no-record"><b>No Records Found.</b></div>')
  //         return true
  //     } else {
  //         $('#meet-summary-bar-chart-container').html('')
  //         $('#meet-summary-bar-chart-container').
  //         append('<canvas id="meet-bar-chart-report"></canvas>')
  //         $('.barChartSpinner').hide();
  //     }
  //
  //     //get the line chart canvas
  //     var ctx = document.getElementById('meet-bar-chart-report').getContext('2d');
  //
  //     let lineChartData = {
  //         labels: data.dates,
  //         datasets: [
  //             {
  //                 label: "Total Earning",
  //                 backgroundColor: 'rgb(99,138,255)',
  //                 borderColor: 'rgb(99,138,255)',
  //                 data: data.earnedAmount,
  //                 fill: false,
  //             },
  //         ],
  //     };
  //
  //     ctx.canvas.style.height = '450px'
  //     ctx.canvas.style.width = '100%'
  //     let meetLineChart = new Chart(ctx, {
  //         type: 'bar',
  //         data: lineChartData,
  //         options: {
  //             responsive: true,
  //             legend: false,
  //             tooltips: {
  //                 enabled: true,
  //                 mode: 'single',
  //                 callbacks: {
  //                     label: function (tooltipItem, data) {
  //                         var label = data.datasets[tooltipItem.datasetIndex].label;
  //                         var datasetLabel = data.datasets[tooltipItem.datasetIndex].data[tooltipItem.index];
  //                         return label + ': ' + datasetLabel;
  //                     },
  //                 },
  //             },
  //             scales: {
  //                 xAxes: [{
  //                     gridLines: {
  //                         color: "rgba(0, 0, 0, 0)",
  //                     },
  //                     ticks: {
  //                         display: false //this will remove only the label
  //                     }
  //                 }],
  //                 yAxes: [{
  //                     gridLines: {
  //                         color: "rgba(0, 0, 0, 0)",
  //                     },
  //                     ticks: {
  //                         min: 0,
  //                         stepSize: 100
  //                     },
  //                     display: true,
  //                     scaleLabel: {
  //                         display: true,
  //                         labelString: 'Total Earning in USD($)'
  //                     }
  //                 }]
  //             }
  //         },
  //     })
  // }
  //
  // function addCommas (nStr) {
  //     nStr += '';
  //     let x = nStr.split('.');
  //     let x1 = x[0];
  //     let x2 = x.length > 1 ? '.' + x[1] : '';
  //     var rgx = /(\d+)(\d{3})/;
  //     while (rgx.test(x1)) {
  //         x1 = x1.replace(rgx, '$1' + ',' + '$2');
  //     }
  //     return x1 + x2;
  // };
  //
  // function loadMeetPieChart () {
  //     $.ajax({
  //         type: 'GET',
  //         url: meetChartURL + meet_id + '/pie-chart-meet',
  //         cache: false,
  //     }).done(prepareMeetPieChart);
  // };
  //
  // function prepareMeetPieChart (result) {
  //     $('#meet-summary-pie-chart-container').html('');
  //     let data = result.data;
  //
  //     let isNoRecord = false;
  //     $.each(data.dataPoints, function( index, value ) {
  //         if(value > 0){
  //             isNoRecord = true;
  //             return false;
  //         }
  //     });
  //
  //     if (!isNoRecord) {
  //         $('#meet-summary-pie-chart-container').empty();
  //         $('#meet-summary-pie-chart-container').append(
  //             '<div align="center" class="no-record"><b>No Records Found.</b></div>');
  //         return true;
  //     } else {
  //         $('#meet-summary-pie-chart-container').html('');
  //         $('#meet-summary-pie-chart-container').
  //         append('<canvas id="meet-pie-chart-report"></canvas>');
  //     }
  //     let ctx = document.getElementById('meet-pie-chart-report').getContext('2d');
  //     ctx.canvas.style.height = '350px';
  //     ctx.canvas.style.width = '100%';
  //     let pieChartData = {
  //         labels: data.labels,
  //         datasets: [
  //             {
  //                 data: data.dataPoints,
  //                 backgroundColor: ['#47c363', '#fc984b','#3abaf4','#d23af4'],
  //             }],
  //     };
  //
  //     window.myBar = new Chart(ctx, {
  //         type: 'pie',
  //         data: pieChartData,
  //         options: {
  //             responsive: true,
  //         },
  //     });
  // };
});

/***/ }),

/***/ 46:
/*!******************************************************!*\
  !*** multi ./resources/js/host/meet/meet_summary.js ***!
  \******************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

module.exports = __webpack_require__(/*! E:\xampp_7.2\htdocs\AllGym\new\portal-v2.0\resources\js\host\meet\meet_summary.js */"./resources/js/host/meet/meet_summary.js");


/***/ })

/******/ });