
$(document).ready(() => {
    let prefix = 'modal-contact-us';
    let modal = $('#' + prefix);
    let _busy = false;

    modal.on('show.bs.modal', function (e) {
        clearForm();
    });

    el('email').bind('input propertychange', () => {
        el('email').removeClass('is-invalid');
    });

    el('message').bind('input propertychange', () => {
        el('message').removeClass('is-invalid');
        el('message-char-count').html(750 - el('message').val().length);
    });

    el('close').click(() => {
        if (!_busy)
            modal.modal('hide')
    });

    el('submit').click(() => {
        if (_busy)
            return;

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
            })
            .catch(function (error) {
                response = error.response.data;

                try {
                    if ('errors' in response) {
                        if ('email' in response.errors)
                            el('email').addClass('is-invalid');

                        if ('message' in response.errors)
                            el('message').addClass('is-invalid');        
                    }
                } catch (ex) {
                }

                showAlert(response.message, 'error');
            })
            .finally(function () {
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
        return $('#' + prefix + '-' +  el);
    }
});