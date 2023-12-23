require('../main')

$(document).ready(e => {

    let select = $('#continue-select');
    let button = $('#continue-button');

    select.change(e => {
        button.attr('href', select.val());
    })
});