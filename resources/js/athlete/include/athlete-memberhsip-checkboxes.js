setupAthleteForm();

function setupAthleteForm() {
    let checkboxes = $('.athlete-membership-checkbox');

    checkboxes.each((e, v) => {
        let checkbox = $(v);
        let input = $('input[name="' + checkbox.data('body') + '_no"]');
        let fieldContainer = $('#' + checkbox.data('body') + '-membership-fields');

        if (input.val()) {
            checkbox.prop('checked', true);
            fieldContainer.find('input, select, checkbox').prop('disabled', false);
        }
    });

    checkboxes.click(e => {
        let checkbox = $(e.currentTarget);
        let fieldContainer = $('#' + checkbox.data('body') + '-membership-fields');

        fieldContainer.find('input, select, checkbox').prop('disabled', !checkbox.prop('checked'));
    });
}