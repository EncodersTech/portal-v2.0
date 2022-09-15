import '../main.js';
import Axios from 'axios';
import Cookies from 'js-cookie';
import 'jquery-confirm';

$(document).ready(() => {

    let _busy = false;
    
    setupProfilePictureHandler();
    
    function setupProfilePictureHandler() {
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