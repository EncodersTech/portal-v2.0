require('../main');

$(document).ready(e => {

    let _busy = false;

    setupTabRemember();
    
    $('.gym-archive>button').click(e => {
        let gym_id = $(event.currentTarget).data('gym');
        let form = $('.gym-archive>form[data-gym=' + gym_id + ']');
        confirmAction(
            'Do you really want to archive this gym ?',
            'orange', 'far far-exclamation-triangle',
            () => {
                form.submit();
            },
            {_busy: _busy}
        );            
    });

    $('.gym-restore>button').click(e => {
        let gym_id = $(event.currentTarget).data('gym');
        let form = $('.gym-restore>form[data-gym=' + gym_id + ']');
        confirmAction(
            'Do you really want to restore this gym ?',
            'orange', 'far far-exclamation-triangle',
            () => {
                form.submit();
            },
            {_busy: _busy}
        );            
    });

    function setupTabRemember() {
        let savedTab = $('#' + Cookies.get('gym-list-tab'));
        let tabs = $('#gym-list-tabs a[data-toggle="tab"]');

        if (savedTab.length > 0)
            savedTab.tab('show');
        else
            tabs.first().tab('show');

        tabs.on('shown.bs.tab', e => {
            let currentTab = $(e.target).attr('id');
            Cookies.set('gym-list-tab', currentTab);
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