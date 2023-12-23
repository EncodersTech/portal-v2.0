'use strict';

$(document).ready(function () {

    $(document).on('keyup keyup keydown keypress', '#dwollaTranCap', function () {
        $(this).val($(this).val().replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1'));
    });

    $(document).on('keyup keyup keydown keypress', '#feeHandling,#feeAch,#feeCheck,#dwollaVeriFee,#feeCc,#feeBalance,#feePaypal,#allTimeWithFee,#featuredMeetFee', function () {
        let regex = /^\d{0,9}(\.\d{0,2})?$/i;
        if (!regex.test($(this).val())) {
            $(this).val('');
            displayErrorMessage('Please enter a correct fee, format 0.00.');
        }
    });
});
