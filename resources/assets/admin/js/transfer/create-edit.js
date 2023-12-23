'use strict';

$(document).ready(function () {
    $('#sourceUser').select2({
        width: '100%'
    });
    $('#destinationUser').select2({
        width: '100%'
    });
});

$(document).on('change', '#sourceUser', function () {
    let userId = $(this).val();
    if (!isEmpty(userId)) {
        displaySourceUserBankAccount(userId);
    }
});

$(document).on('change', '#destinationUser', function () {
    let userId = $(this).val();
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
            'userId': userId,
        },
        success: function (result) {
            if (result.success) {
                let sourceBankAccounts = result.data;
                let data = [{
                        'bankAccounts' : sourceBankAccounts,
                    }];
                $('.source-user-bank').html('');
                $('.source-user-bank').append(prepareTemplateRender('#sourceUserBankTemplate', data));
            }
        },
        error: function (result) {
            displayErrorMessage(result.responseJSON.message);
        },
    });
}

function displayDestinationUserBankAccount(userId) {
    $.ajax({
        url: 'bank-accounts',
        type: 'POST',
        data: {
            'userId': userId,
        },
        success: function (result) {
            if (result.success) {
                let destinationBankAccounts = result.data;
                let data = [{
                    'bankAccounts' : destinationBankAccounts,
                }];
                $('.destination-user-bank').html('');
                $('.destination-user-bank').append(prepareTemplateRender('#destinationUserBankTemplate', data));
            }
        },
        error: function (result) {
            displayErrorMessage(result.responseJSON.message);
        },
    });
}

$('#transferStore').on('submit', function (e) {
    e.preventDefault();
    const loadingButton = jQuery(this).find('#btnTransfer');
    loadingButton.button('loading');

    let formData = new FormData($(this)[0]);

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
    })
});
