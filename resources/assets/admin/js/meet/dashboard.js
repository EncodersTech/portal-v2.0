'use strict';

$(document).ready(function () {
    let tbl = $('#meetGymsTbl').DataTable({
        processing: true,
        serverSide: true,
        "ajax": {
            "url": meetGymUrl,
            data: function (data) {
                data.meet_id = meetId;
            },
        },
        "order": [[0, "asc" ]],
        "columnDefs": [
            {
                "targets": [4, 5],
                'class': 'text-center',
                'width': '5%',
            },
            {
                "targets": [7],
                'class': 'text-center',
                'width': '10%',
            },
            {
                "targets": [2],
                'class': 'text-center',
                'width': '13%',
            },
            {
                "targets": [1],
                'width': '15%',
            },
            {
                "targets": [3],
                "searchable": false,
                'class': 'text-center',
                'width': '10%',
            },
            {
                "targets": [6, 8],
                'class': 'text-center',
                'width': '3%',
            },
        ],
        columns: [
            {
                data: 'gym.name',
                name: 'gym.name',
            },
            {
                data: function data(row) {
                    return moment(row.created_at, 'YYYY-MM-DD').format('Do MMM, YYYY');
                },
                name: 'gym.name',
            },
            {
                data: function data(row) {
                    return '<span class="text-danger">WIP</span>';
                },
                name: 'ach_fee_override',
            },
            {
                data: function data(row) {
                    return '<span>&#36;</span> '+ addCommas(parseFloat(row.total_fee).toFixed(2));
                },
                name: 'total_fee',
            },
            {
                data: 'athletes_count',
                name: 'athletes_count',
            },
            {
                data: 'coaches_count',
                name: 'coaches_count',
            },
            {
                data: 'teams_count',
                name: 'teams_count',
            },
            {
                data: function data(row) {
                    let paymentStatus = (!row.payment_status) ? 'Pending Deposit' : 'available';
                    if(row.payment_status){
                        if(row.gym.user.cleared_balance == 0){
                            return '--';
                        }
                        return '<span class="badge badge-success">'+paymentStatus+'</span>';
                    }else {
                        return '<span class="badge badge-danger">'+paymentStatus+'</span>';
                    }
                },
                name: 'paypal_fee_override',
            },
            {
                data: function data(row) {
                    return '<span>&#36;</span> '+ addCommas(parseFloat(row.gym.user.cleared_balance).toFixed(2));
                },
                name: 'gym.user.cleared_balance',
            },
        ],
    });

    $(document).on('click', '.copy-meet-url', function (e){
        e.preventDefault();
        let copyText = $(this).attr('data-url');
        copyTextValue(copyText);
    })

    $(document).on('click', '#meet-public-url-copy', function(event){
        event.preventDefault();
        let copyText = $('#meet-public-url').val();
       copyTextValue(copyText);
    });

    function copyTextValue (copyText) {
        let textarea = document.createElement("textarea");
        textarea.textContent = copyText;
        textarea.style.position = "fixed"; // Prevent scrolling to bottom of page in MS Edge.
        document.body.appendChild(textarea);
        textarea.select();
        document.execCommand("copy");

        document.body.removeChild(textarea);
    }
});

$(document).on('keyup keydown keypress', '#customHandlingFee', function () {
    let regex = /^\d{0,5}(\.\d{0,2})?$/i;
    if (!regex.test($(this).val())) {
        $(this).val('');
        displayErrorMessage('Please enter a correct fee, format 0.00.');
    }
});

$(document).on('submit','#updateHandlingFee',function (e){
    $('#saveBtn').prop('disabled',true);
   $.ajax({
       url:updateHandlingFeeRoute,
       cache:false,
       method:'post',
       data: $(this).serialize(),
       success: function (result) {
           if (result.success) {
                displaySuccessMessage(result.message);
               $('#customHandlingFee').blur();
               $('#saveBtn').prop('disabled',false);
               location.reload();
           }
       },
       error: function (result) {
           displayErrorMessage(result.responseJSON.message);
           $('#saveBtn').prop('disabled',false);
       }
   });
   return false;
});

