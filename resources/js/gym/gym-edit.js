require('../main');

$(document).ready(e => {
    let _busy = false;
    
    require('./include/gym-memberhsip-checkboxes');

    setupGymPictureHandler();

    function setupGymPictureHandler() {
        let profilePictureInput = $('#profile-picture');

        $('#profile-picture-change').click(e => {
            changeProfilePicture();
        });

        $('#profile-picture-display').click(e => {
            changeProfilePicture();
        });
    
        profilePictureInput.change(e => {
            if (profilePictureInput.val())
                $('#profile-picture-change-form').submit();
        });

        function changeProfilePicture() {
            if (_busy)
                return;
            profilePictureInput.click();
        }
    }
});