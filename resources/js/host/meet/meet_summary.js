'use strict';
$(document).ready(function () {

    // setTimeout(function () {
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
