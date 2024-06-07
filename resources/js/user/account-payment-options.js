import '../main.js';
import Axios from 'axios';
import Cookies from 'js-cookie';
import 'jquery-confirm';

$(document).ready(() => {

    let _busy = false;

    $('#cardnumber').on('input', function() {
        var cardnumber = $(this).val();
        cardnumber = cardnumber.replace(/[^0-9\s]/g, '');
        var length = cardnumber.length;
        if (length == 4 || length == 9 || length == 14)
            $(this).val(cardnumber + ' ');
        else
          $(this).val(cardnumber);
    });

    $("#cardexpirydate").on('input', function() {
        var cardexpirydate = $(this).val();
        cardexpirydate = cardexpirydate.replace(/[^0-9/]/g, '');

        var length = cardexpirydate.length;
        if (length == 2 && cardexpirydate.indexOf('/') == -1)
            $(this).val(cardexpirydate + '/');
        else
            $(this).val(cardexpirydate);
    
    });
    $('#cardcvv').on('input', function () {
        var cardnumber = $(this).val();
        cardnumber = cardnumber.replace(/[^0-9]/g, '');
        $(this).val(cardnumber);
      });

    $('.modal-why-is-my-account-unverified-close').click(e => {
        $('#modal-why-is-my-account-unverified').modal('hide');
    });

    
    var stripe_settings = '<?= json_encode($cc_gateway); ?>';
    if(stripe_settings == 0)
    {
        setupStripeACHLinkModal();
        setupStripeCardLinkModal();
    }
    else
    {
        setupIntellipayCardLinkModal();
    }
    setupDwollaBankAccountLinkModal();
    setupBankAccountVerifyModal();

    function setupStripeACHLinkModal() {
        let stripe = Stripe($('#stripe-publishable-key').val());

        let displayError = $('#stripe-bank-link-bank-errors');
        let spinner = $('#modal-linked-bank-spinner');

        $('.modal-linked-bank-close').click(e => {
            if (_busy)
                return;
            $('#modal-linked-bank').modal('hide');
        });

        $('#stripe-bank-link-form').click(e => {
            // e.preventDefault();
            if (_busy)
                return;

            _busy = true;
            spinner.show();
            var accName = $('#account_name').val();
			var accType = $('#account_type').val();
			var accNumber = $('#account_no').val();
			var accRoutingNumber = $('#routing_no').val();
            stripe.createToken('bank_account', {
				country: 'US',
				currency: 'usd',
				routing_number: accRoutingNumber,
				account_number: accNumber,
				account_holder_name: accName,
				account_holder_type: accType,
			}).then(function(result) {

                if (result.error) {
                    _busy = false;
                    spinner.hide();
                    displayError.html(result.error.message);
                } else {
                    $('#stripe-bank-link-token').val(result.token.id);
                    $('#stripe-bank-account-name').val(accName);
                    $('#stripe-bank-link-add-form').submit();
                }
            });
        });

        $('#verify-stripe-ach').on('click', e => {
            let card_id = $(e.currentTarget).data('card');
            $('#stripe-bank-verify-token').val(card_id);
            // $('#modal-verify-bank').show();
            $('#modal-verify-bank').modal('show');
            // let form = $('form[data-bank=' + card_id + ']');
            // confirmAction(
            //     'This verification process will initiate two micro deposit amount of $0.32 & $0.45. Do you really want to verify this bank account ?',
            //     'red', 'far far-exclamation-triangle',
            //     () => {
            //         form.submit();
            //     }
            // );
        });
        $('#bank-remove-btn').click(e => {
            let card_id = $(event.currentTarget).data('card');
            let form = $('form[data-card=' + card_id + ']');

            confirmAction(
                'Do you really want to unlink this bank ?',
                'red', 'far far-exclamation-triangle',
                () => {
                    form.submit();
                }
            );
        });
    };
    function setupIntellipayCardLinkModal() {

        $('.modal-linked-credit-card-close').click(e => {
            if (_busy)
                return;
            $('#modal-linked-credit-card').modal('hide');
        });
        $('.modal-verify-bank-close').click(e => {
            if (_busy)
                return;
            $('#modal-verify-bank').modal('hide');
        });

        $('#intellipay-card-link-form').submit(e => {
            //e.preventDefault();
            let spinner = $('#modal-linked-credit-card-spinner');
            if (_busy)
                return;
            _busy = true;
            spinner.show();
            $("#intellipay_submitbtn").attr("disabled", true);
        });
    
    }
    function setupStripeCardLinkModal() {
        let stripe = Stripe($('#stripe-publishable-key').val());
        let elements = stripe.elements();
        let card = elements.create('card');
        let displayError = $('#stripe-card-link-card-errors');
        let spinner = $('#modal-linked-credit-card-spinner');

        card.mount('#stripe-card-link-card-element');

        $(card).change(e => {
            if (e.error) {
                displayError.html('event.error.message');
            } else {
                displayError.html('event.error.message');
            }
        });

        $('.modal-linked-credit-card-close').click(e => {
            if (_busy)
                return;
            $('#modal-linked-credit-card').modal('hide');
        });
        $('.modal-verify-bank-close').click(e => {
            if (_busy)
                return;
            $('#modal-verify-bank').modal('hide');
        });

        
        $('#stripe-card-link-form').submit(e => {
            e.preventDefault();
            if (_busy)
                return;

            _busy = true;
            spinner.show();

            stripe.createToken(card).then(function(result) {

                if (result.error) {
                    _busy = false;
                    spinner.hide();
                    displayError.html(result.error.message);
                } else {
                    $('#stripe-card-link-token').val(result.token.id);
                    $('#stripe-card-link-add-form').submit();
                }
            });
        });

        $('.credit-card-remove>button').click(e => {
            let card_id = $(event.currentTarget).data('card');
            let form = $('form[data-card=' + card_id + ']');

            confirmAction(
                'Do you really want to unlink this card ?',
                'red', 'far far-exclamation-triangle',
                () => {
                    form.submit();
                }
            );
        });
    };

    function setupDwollaBankAccountLinkModal() {

        let iavToken;
        let iavContainer = $('#dwolla-bank-link-form-iav-container');
        let iavError = $('#dwolla-bank-link-form-iav-error');
        let spinner = $('#modal-linked-bank-account-spinner');
        let tryAgain = $('#modal-linked-bank-account-try-again');

        $('.modal-linked-bank-account-close').click(e => {
            if (_busy)
                return;
            $('#modal-linked-bank-account').modal('hide');
        });

        // $('#modal-linked-bank-account').on('show.bs.modal', () => {
        //     initIAVFlow();
        // });

        // tryAgain.click(e => {
        //     initIAVFlow();
        // });

        $('.bank-account-remove>button').click(e => {
            let bank_id = $(event.currentTarget).data('bank');
            let form = $('form[data-bank=' + bank_id + ']');
            confirmAction(
                'Do you really want to unlink this account ?',
                'red', 'far far-exclamation-triangle',
                () => {
                    form.submit();
                }
            );
        });

        // function initIAVFlow() {
        //     _busy = true;
        //     iavError.hide();
        //     tryAgain.hide();
        //     spinner.show();

        //     iavContainer.html('');
        //     Axios.get('/api/user/bank/iav').then(result => {
        //         iavToken = result.data.token;

        //         dwolla.configure($('#dwolla-env').val());
        //         dwolla.iav.start(
        //             iavToken, {
        //                 container: 'dwolla-bank-link-form-iav-container',
        //                 stylesheets: [
        //                     'https://fonts.googleapis.com/css?family=Lato&subset=latin,latin-ext',
        //                 ],
        //                 microDeposits: true,
        //                 fallbackToMicroDeposits: true,
        //                 backButton: true,
        //                 subscriber: ({ currentPage, error }) => {
        //                     _busy = false;

        //                     if (currentPage == 'BankSearch')
        //                         spinner.hide();
        //                     else if (currentPage == 'SuccessIAV' || currentPage == 'SuccessMicroDeposits')
        //                         _busy = true;
        //                 }
        //             }, function(err, res) {
        //                 if (err) {
        //                     let msg = err.message;
        //                     switch (err.code) {
        //                         case 'InvalidIavToken':
        //                         case 'UnexpectedPage':
        //                             msg = 'Something went wrong with our payment processor.<br/>' +
        //                                     'Please try again later (code: ' + err.code + ').';
        //                             break;

        //                         // might need to return instead for µ-Deposits to take over
        //                         case 'RateLimitReached':
        //                             msg = 'Sorry, we’re having trouble logging into your account.<br/>' +
        //                                     'Please wait 30 minutes before trying again, or try a different account';
        //                             break;

        //                         default:
        //                     }
        //                     showIAVError(msg);
        //                     return;
        //                 }

        //                 spinner.show();
        //                 setTimeout(() => {
        //                     Utils.refresh();
        //                 }, 7500);
        //             }
        //         );
        //     }).catch(error => {
        //         let msg = '';
        //         if (error.response) {
        //             msg = error.response.data.message;
        //         } else if (error.request) {
        //             msg = 'No server response.';
        //         } else {
        //             msg = error.message;
        //         }
        //         showIAVError(msg);
        //     }).finally(() => {
        //     });

        //     function showIAVError(msg) {
        //         _busy = false;
        //         spinner.hide();
        //         iavError.html(
        //             '<span class="fas fa-fw fa-times-circle"></span> ' + msg
        //         );
        //         iavError.show();
        //         tryAgain.show();
        //     }
        // };
    }

    function setupBankAccountVerifyModal() {
        let verifySpinner = $('#modal-verify-micro-deposits-spinner');

        $('.modal-verify-micro-deposits-close').click(e => {
            if (_busy)
                return false;

            $('#modal-verify-micro-deposits').modal('hide');
        })

        $('.bank-account-verify').click(e => {
            let bankId = $(e.currentTarget).data('bank');
            $('#modal-verify-micro-deposits-form input[name=bank_account]').val(bankId);
            $('#modal-verify-micro-deposits').modal('show');
        });

        $('#modal-verify-micro-deposits-form').submit(e => {
            if (_busy)
                return false;

            _busy = true;
            verifySpinner.show();
            return true;
        });
    }

    function confirmAction(msg, color, icon, callback) {
        if (_busy)
            return;
        _busy = true;

        $.confirm({
            title: 'Are you sure ?',
            content: msg,
            icon: icon,
            type: color,
            typeAnimated: true,
            buttons: {
                no: function () {
                    _busy = false;
                    this.close();
                },
                confirm:  {
                    text: 'Yes',
                    btnClass: 'btn-' + color,
                    action: function () {
                        _busy = false;
                        callback();
                    }
                }
            }
        });
    };
});
