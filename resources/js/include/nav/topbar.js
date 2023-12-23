
$(document).ready(() => {
    let _busy = false;
    let selector = $('#managed-account-selector');
    let form = $('#managed-account-selector-form');
    let previousValue = selector.val();

    selector.change(e => {
        confirmAction(
            'Do you really want to switch accounts ?<br/>You will be redirected to the dashboard.',
            'blue',
            'fas fa-question-circle',
            () => {
                form.submit();
            },
            () => {
                selector.val(previousValue);
            }
        );
    });

    function confirmAction(msg, color, icon, callbackYes, callbackNo) {
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

                    if (callbackNo)
                        callbackNo();

                    this.close();
                },
                confirm:  {
                    text: 'Yes',
                    btnClass: 'btn-' + color,
                    action: function () {
                        _busy = false;
                        callbackYes();
                    }
                }
            }
        });
    }
});