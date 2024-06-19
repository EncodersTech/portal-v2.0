'use strict';
require('../main');

$(document).on('click','.add-conversation-btn', function () {
    $.ajax({
        url: '/gyms/'+ gymId +'/add-meet-register-user',
        type: 'GET',
        success: function (result) {
            if (result.success) {
                $('#addConversionUserModal .modal-search-box').val('');
                $('#addConversionUserModal').find('.selected-user-no-user').addClass('d-none');
                $('.add-conversation-gym').html('');
                if (result.data.length > 0) {
                    $.each(result.data, function (i, v) {
                        let data = [
                            {
                                'image': v.image,
                                'name': v.name,
                                'email': v.email,
                                'id': v.id
                            }];
                        $('.add-conversation-gym').append(prepareTemplateRender('#displayGymTemplate', data));
                    });
                } else {
                    $('#addConversionUserModal').find('.selected-user-no-user').addClass('d-none');
                }
                $('#addConversionUserModal').modal('show');
            }
        },
        error: function (result) {
            console.log(result.responseJSON.message);
            displayErrorMessage(result.responseJSON.message);
        },
    });
});

$(document).on('click', '.setting-icon', function () {
    if ($('.chat-profile').hasClass('chat-profile--active')) {
        $('.chat-profile').removeClass('chat-profile--active');
    } else {
        $('.chat-profile').addClass('chat-profile--active');
    }
});

$(document).on('click', '.chat-profile__close-btn', function () {
    if ($('.chat-profile').hasClass('chat-profile--active')) {
        $('.chat-profile').removeClass('chat-profile--active');
    }
});

$(document).on('click', '.selected-user-conversion', function (e) {
    let userId = $(e.currentTarget).data('user');
    $('#addConversionUserModal').modal('hide');
    $('.modal-backdrop').remove();
    displayConversion(userId, true);
});

$(document).on('click', '.send-message', function () {
    sendMessage();
});

$(document).on('keyup', '.conversation__footer', function (e) {
    let code = e.keyCode || e.which;
    if(code == 13) { //Enter keycode
        sendMessage();
    }

    let message = $(this).find('.conversation-input-box').val();
    if (isEmpty(message)){
        $('.footer-send').removeClass('footer-send-active');
    } else {
        $('.footer-send').addClass('footer-send-active');
    }
});

$(document).on('click', '.conversation-user', function (e) {
    let userId = $(e.currentTarget).data('user');
    displayConversion(userId);
});

$(document).on('keyup', '.search-box', function (e) {
    let value = $(this).val().toLowerCase();
    $(".user-conversation .user-card").filter(function() {
        $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
        checkIfEmpty()
    });
});

$(document).on('keyup', '#searchMember', function (e) {
    let value = $(this).val().toLowerCase();
    $(".add-conversation-gym .selected-user-conversion").filter(function() {
        $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
        if ($(".add-conversation-gym").find(".selected-user-conversion:visible").length === 0){
            $(".selected-user-no-user").removeClass('d-none');
        } else {
            $(".selected-user-no-user").addClass('d-none');
        }
    });
});

const checkIfEmpty = () => {
    if ($(".user-conversation").find(".user-card:visible").length === 0){
        $('.sidebar-no-conversation').removeClass('d-none');
    } else {
        $('.sidebar-no-conversation').addClass('d-none');
    }
};

function sendMessage() {
    let toId = $('.chat-conversation-menu').find('.conversation__header').attr('data-userid');
    let message = $('.conversation__footer').find('.conversation-input-box').val();
    if (isEmpty($.trim(message))) {
        displayErrorMessage('Please enter message.');
        return false;
    }
    $.ajax({
        url: '/gyms/'+ gymId + '/send-message',
        type: 'POST',
        data: {
            'toId': toId,
            'message': message,
        },
        success: function (result) {
            if (result.success) {
                if ($.isEmptyObject(result.data)) {
                    return false;
                }
                displayConversion(result.data, false);
            }
        },
        error: function (result) {
            displayErrorMessage(result.responseJSON.message);
        },
    });
}

function displayConversion(userId, selectedUser = false) {
    $.ajax({
        url: '/gyms/' + gymId + '/add-conversion/'+ userId +'/user',
        type: 'GET',
        success: function (result) {
            if (result.success) {
                if ($.isEmptyObject(result.data)) {
                    return false;
                }
                let meetUser = result.data.users;
                let gym = result.data.gym;
                let conversation = result.data.conversation;
                let data = [
                    {
                        'image': gym.profile_picture,
                        'name': gym.name,
                        'email': gym.user.email,
                        'phone' : gym.user.office_phone,
                        'users' : meetUser,
                        'conversation': conversation,
                        'id': gym.id
                    }];
                // left sidebar user conversation
                if (selectedUser) {
                    $('.user-conversation').prepend(prepareTemplateRender('#addConversationUserTemplate', data));
                    $('.sidebar-no-conversation').addClass('d-none');
                }
                $('.no-selected').addClass('d-none');
                $(`.user-${gym.id} .user-detail .user-name`).find('.sidebar-conversation-count').addClass('d-none');
                $(`.user-${gym.id} .user-detail .user-name`).find('.sidebar-conversation-count').text('');
                $('.user-conversation').find(`.user-card`).removeClass('selected-user-card-active');
                if ($(`.chat-conversation-menu .chat-menu-${gym.id}`)) {
                    $('.user-conversation').find(`.user-${gym.id}`).addClass('selected-user-card-active');
                }

                $('.chat-conversation-menu').removeClass('d-none');
                $('.chat-conversation-menu').html('');
                $('.chat-conversation-menu').append(prepareTemplateRender('#userConversationTemplate', data));
                $.each(conversation, function (i, v) {
                    let  timeDateClass = i.split(' ').join('_').replace(',', '');
                    $('.chat-conversation-menu').find('.conversation__body').append(
                        '<div class="conversation-date '+ timeDateClass +'">\n' +
                        '<span>'+ i +'</span>\n' +
                        '</div>'
                    );
                    $.each(v, function(m, n) {
                        $('.chat-conversation-menu').find('.conversation__body').append(prepareTemplateRender('#userConversationBodyMessageTemplate', n));
                    });
                });
                $('.chat-profile__column-title').find('.float-right').text($('.chat__person-box').length);
                $('.conversation__footer').find('.conversation-input-box').focus();
                scrollToTheBottomComments();
                mainSidebarConversationCount();
            }
        },
        error: function (result) {
            console.log(result.responseJSON.message);
            displayErrorMessage(result.responseJSON.message);
        },
    });
}

window.scrollToTheBottomComments = function () {
    let height = $('.conversation__body').outerHeight();
    $('.conversation__body').scrollTop(height * height);
};

$(document).ready(function () {
    window.Echo.private(`conversation.${gymId}`).
        listen('.ConversationSent', (e) => {
            if (gymId == e.receiverGymId && e.type == 1) {
                let data = [{
                    'date': e.date,
                    'receiveMessage': e.receiveMessage,
                    'senderMessage': e.senderMessage,
                    'message': e.message,
                    'receiverImage': e.receiverImage,
                    'senderImage': e.senderImage,
                    'time': e.time,
                    'senderGymId': e.senderGymId,
                    'image': e.gym.profile_picture,
                    'name': e.gym.name,
                    'email': e.gym.user.email,
                    'id': e.gym.id
                }];
                let  timeDateClass = e.date.split(' ').join('_').replace(',', '');
                if (!$(`.user-chat-container .user-conversation .user-${e.senderGymId}`).hasClass(`user-${e.senderGymId}`)) {
                    $('.sidebar-no-conversation').addClass('d-none');
                    $('.user-chat-container .user-conversation').prepend(prepareTemplateRender('#addConversationUserTemplate', data));
                }
                $('.chat-conversation-menu .conversation__body .no-conversation').addClass('d-none');
                if ($('.conversation__body').parents(`.chat-menu-${gymId}`).hasClass(`chat-menu-${gymId}`)) {
                    if (!$('.conversation__body').find('.conversation-date').hasClass(`${timeDateClass}`)) {
                        $('.chat-conversation-menu').find('.conversation__body').append(
                            '<div class="conversation-date '+ timeDateClass +'">\n' +
                            '<span>'+ e.date +'</span>\n' +
                            '</div>'
                        );
                    }
                    $('.conversation__body').append(prepareTemplateRender('#displayMessageTemplate', data));
                    scrollToTheBottomComments();
                }
                if (!$(`.chat-menu-${gymId}`).hasClass('d-none')) {
                    if ($('.conversation__header').data('userid') == e.senderGymId) {
                        $.ajax({
                            url: '/gyms/'+ gymId +'/read-at/'+ e.senderGymId,
                            type: 'post',
                            success: function (result) {
                                if (result.success) {

                                }
                            },
                            error: function (result) {
                                displayErrorMessage(result.responseJSON.message);
                            },
                        });
                    } else {
                        conversationSidebarCount(e.senderGymId);
                    }
                } else {
                    conversationSidebarCount(e.senderGymId);
                }
                mainSidebarConversationCount();
            }
        });
});

function conversationSidebarCount(senderGymId) {
    let unreadCount = $(`.user-${senderGymId} .user-detail .user-name`).find('.sidebar-conversation-count').text();
    if (unreadCount == '') {
        unreadCount = 0;
    }
    unreadCount++;
    if ($(`.user-${senderGymId} .user-detail .user-name`).find('.sidebar-conversation-count').text() > 0) {
        $(`.user-${senderGymId} .user-detail .user-name`).find('.sidebar-conversation-count').text(unreadCount);
        $(`.user-${senderGymId} .user-detail .user-name`).find('.sidebar-conversation-count').removeClass('d-none');
    } else {
        $(`.user-${senderGymId} .user-detail .user-name`).append('<span class="bg-success sidebar-conversation-count small text-center">'+ unreadCount +'</span>');
    }
}

function mainSidebarConversationCount() {
    if (!$('.sidebar-gym-menu a').hasClass('collapsed')) {
        $.ajax({
            url: '/gyms/' + gymId + '/get-unread-count',
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
}

function continuousCheckMessage(){
    var childDivs = $( ".user-conversation" ).children("div"); //[1].getAttribute('data-user')
    console.log(childDivs);
    var k = childDivs.length;
    var i, id;
    for(i=1;i<k;i++)
    {
        id = parseInt(childDivs[i].getAttribute('data-user'));
        displayConversion(id);
    }
}
window.onload = function() {
    //setInterval(continuousCheckMessage, 2000);
}
