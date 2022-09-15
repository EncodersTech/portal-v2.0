require('../app');

$(document).ready(() => {
    $('#password-view-switch').click(e => {
        let type = ($('#password').attr('type') == 'password' ? 'text' : 'password');
        $('#password').attr('type', type);
    });
});
