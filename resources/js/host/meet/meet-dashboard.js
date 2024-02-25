'use strict';
$(document).ready(function () {

    setTimeout(function () {
        //message body summernote
        $('#messageBody').summernote({
            placeholder: 'Write message here...',
            minHeight: 200,
            toolbar: [
                ['style', ['bold', 'italic', 'underline', 'clear']],
                ['font', ['strikethrough', 'superscript', 'subscript']],
                ['fontsize', ['fontsize']],
                ['insert', ['link']],
                ['color', ['color']],
                ['para', ['paragraph']],
                ['height', ['height']]],
        });

        checkBoxSelect();

        //select all checkbox
        function checkBoxSelect () {
            $('#ckbCheckAll').click(function () {
                $('.gymCheck').prop('checked', $(this).prop('checked'));
            });

            $('.gymCheck').on('click', function () {
                if ($('.gymCheck:checked').length == $('.gymCheck').length) {
                    $('#ckbCheckAll').prop('checked', true);
                } else {
                    $('#ckbCheckAll').prop('checked', false);
                }
            });
        }
    }, 3200)

    //submit massmailer form
    $(document).on('submit', '#submitMassNotification', function () {
        let $description = $('<div />').html($('#messageBody').summernote('code'));
        let empty = $description.text().trim().replace(/ \r\n\t/g, '') === '';

        if ($('.gymCheck:checked').length === 0) {
            showError('Please select at least one gym.');
            return false;
        }

        if ($('#messageBody').summernote('isEmpty') || empty) {
            showError('Please write your message.');
            return false;
        }
        $('#sedMailNotification').prop('disabled', true);
        return true;
    });


    //if add attachment then show attachment.
    $(document).on('change', '#documentImage', function () {
        let extension = isValidDocument($(this), '#validationErrorsBox');
        if (!isEmpty(extension) && extension != false) {
            $('#validationErrorsBox').html('').hide();
            displayDocument(this, '#previewImage', extension);
        }
    });

    window.isValidDocument = function (
        inputSelector, validationMessageSelector) {
        let ext = $(inputSelector).val().split('.').pop().toLowerCase();
        //console.log('ext',ext);
        if(isEmpty(ext)){
            $('#previewImage').attr('src', defaultImage);
            return false;
        }
        if ($.inArray(ext, ['png', 'jpg', 'jpeg', 'pdf', 'doc', 'docx', 'xlsx']) == -1) {
            $(inputSelector).val('');
            showError('The document must be a file of type: jpeg, jpg, png, pdf, doc, docx.');
            return false;
        }
        return ext;
    };

    function displayDocument(input, selector, extension) {
        let displayPreview = true;
        if (input.files && input.files[0]) {
            let reader = new FileReader();
            reader.onload = function (e) {
                let image = new Image();
                if ($.inArray(extension, ['pdf', 'doc', 'docx', 'xlsx']) == -1) {
                    image.src = e.target.result;
                } else {
                    if (extension == 'pdf') {
                        image.src = pdfDocumentImageUrl;
                    } else if (extension == 'xlsx') {
                        image.src = excelDocumentImageUrl;
                    } else {
                        image.src = docxDocumentImageUrl;
                    }
                }
                image.onload = function () {
                    $(selector).attr('src', image.src);
                    displayPreview = true;
                };
            };
            if (displayPreview) {
                reader.readAsDataURL(input.files[0]);
                $(selector).show();
            }
        }
    };

    function isEmpty(value) {
        return value === undefined || value === null || value === '';
    };

    function showError(msg) {
        $.alert({
            title: 'Whoops',
            content: msg,
            icon: 'fas fa-exclamation-triangle',
            type: 'red',
            typeAnimated: true
        });
    }
});
