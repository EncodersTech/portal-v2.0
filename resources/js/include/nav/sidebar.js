
$(document).ready(() => {

    rememberSidebarState($('.sidebar'));

    $('.sidebar-collapse-button').click(e => {
        toggleSidebar($(e.currentTarget).parents(".sidebar").first());
    });

    $('.sidebar-logout-button').click(e => {
        e.preventDefault();
        $('#sidebar-logout-form').first().submit();
    });

    $('.sidebar-dropdown-toggle').click(e => {
        let sidebar = $(e.currentTarget).parents(".sidebar").first();

        if (sidebar.hasClass('sidebar-collapsed') && isNotMorphedToMobile(sidebar)) {
            toggleSidebar(sidebar);
            e.stopPropagation();
        }
    });

    if ($('#sidebar-gym-list-accordion').data('expand-default')) {
        let first = $('#sidebar-gym-list-accordion>.sidebar-item>.collapse').first();
        if (first.length > 0)
            first.collapse('show');
    }

    function toggleSidebar(sidebar, toggle) {
        sidebar.toggleClass('sidebar-collapsed', toggle);
        Cookies.set('sidebar-collapsed', sidebar.hasClass('sidebar-collapsed'));
    }

    function rememberSidebarState(sidebar) {
        let collapsed = (Cookies.get('sidebar-collapsed') == 'true');
        if (collapsed && isNotMorphedToMobile(sidebar))
            toggleSidebar(sidebar, true);
    }

    function isNotMorphedToMobile(sidebar) {
        return (sidebar.width() <= 248);
    }

    $('.sidebar-gym-menu').click(function () {
        getUnreadCount($(this).data('gym'));
    });

    if (!$('.sidebar-gym-menu a').hasClass('collapsed')) {
        if (!isEmpty($('.sidebar-gym-menu').data('gym'))) {
            getUnreadCount($('.sidebar-gym-menu').data('gym'));
        }
    }

    function getUnreadCount(gymId)
    {
        $.ajax({
            url: '/gyms/'+ gymId +'/get-unread-count',
            type: 'GET',
            success: function (result) {
                if (result.success) {
                    $('.unread-count').removeClass('d-none');
                    $('.unread-count').text('');
                    if (result.data > 0) {
                        $('.unread-count').text(result.data);
                    } else {
                        if (!$('.sidebar-gym-menu a').hasClass('collapsed')) {
                            $('.unread-count').addClass('d-none');
                        }
                    }
                }
            },
            error: function (result) {
                displayErrorMessage(result.responseJSON.message);
            },
        });
    }
    function getUnreadUsag(gymId)
    {
        // $.ajax({
        //     url: '/gyms/'+ gymId +'/get-unread-count',
        //     type: 'GET',
        //     success: function (result) {
        //         if (result.success) {
        //             $('.unread-count').removeClass('d-none');
        //             $('.unread-count').text('');
        //             if (result.data > 0) {
        //                 $('.unread-count').text(result.data);
        //             } else {
        //                 if (!$('.sidebar-gym-menu a').hasClass('collapsed')) {
        //                     $('.unread-count').addClass('d-none');
        //                 }
        //             }
        //         }
        //     },
        //     error: function (result) {
        //         displayErrorMessage(result.responseJSON.message);
        //     },
        // });
    }
});
