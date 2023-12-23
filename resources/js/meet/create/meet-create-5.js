require('../../main');

$(document).ready(e => {
    setupPrimaryInfoBehavior();
    setupSecondaryInfoBehavior();

    function setupPrimaryInfoBehavior() {
        let primaryButton = $('#primary_use_own_info');
        let primaryInputs = $('input[data-primary-info], select[data-primary-info]');
        
        primaryButton.click(e => {
            primaryInputs.each((i, item) => {
                item.value = item.dataset.primaryInfo;
            });
        });
    }

    function setupSecondaryInfoBehavior() {
        let checkbox = $('#secondary_contact');
        let secondaryFields = $('.secondary-info-fields input, .secondary-info-fields select');
        let secondarySelect = $('#secondary_use_own_info');

        checkbox.change(e => {
            secondaryFields.prop('disabled', !checkbox.prop('checked'));
        });
        checkbox.change();

        secondarySelect.change(e => {
            if (secondarySelect.val()) {
                let option = secondarySelect.find(':selected').first()[0];
                if (option) {
                    $('#secondary_contact_first_name').val(option.dataset.secondaryFirst);
                    $('#secondary_contact_last_name').val(option.dataset.secondaryLast);
                    $('#secondary_contact_email').val(option.dataset.secondaryEmail);
                    $('#secondary_contact_job_title').val(option.dataset.secondaryJob);
                    $('#secondary_contact_phone').val(option.dataset.secondaryPhone);
                }
            }
        });        
    }
});