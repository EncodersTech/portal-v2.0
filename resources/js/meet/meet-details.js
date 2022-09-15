require('../main');

window.Vue = require('vue');

Vue.component('ag-meet-levels-view', require('../components/Meet/MeetLevelsView.vue').default);

$(document).ready(e => {

    const app = new Vue({
        el: '#app',
    });

    if ($('#meet-public-url-copy').length > 0) {
        var meetUrl = new ClipboardJS('#meet-public-url-copy');

        meetUrl.on('success', function(e) {
            switchCopySuccessMessage(true, 'Copied !');
            e.clearSelection();
            _.debounce(switchCopySuccessMessage, 1500)(false, '');
        });

        meetUrl.on('error', function(e) {
            switchCopySuccessMessage(true, 'Ctrl+C to copy !');
            _.debounce(switchCopySuccessMessage, 2500)(false, '');
        });

        function switchCopySuccessMessage(shown, msg) {
            let text = $('#meet-public-url-copy-success')
            let elem = $('#meet-public-url-copy-success-message');

            text.html(msg);
            elem.css('visibility', shown ? 'visible' : 'hidden');
        }
    }
});