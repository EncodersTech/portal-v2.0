setupGymForm();

function setupGymForm() {
    let checkboxes = $('.gym-membership-checkbox');

    checkboxes.each((e, v) => {
        let checkbox = $(v);
        let input = $('input[name="' + checkbox.data('body') + '_membership"]');

        if (input.val()) {
            checkbox.prop('checked', true);
            input.prop('disabled', false);
        }
    });

    checkboxes.click(e => {
        let checkbox = $(e.currentTarget);
        let input = $('input[name="' + checkbox.data('body') + '_membership"]');
        
        input.prop('disabled', !checkbox.prop('checked'));
    });
}