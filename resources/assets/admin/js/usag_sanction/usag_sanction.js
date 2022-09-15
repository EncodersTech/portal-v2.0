'use strict';

$(document).ready(function () {
    var page = $('#hiddenPage').val();

    $(document).on('click', '.pagination li > a', function (event) {
        event.preventDefault();
        var page = $(this).attr('href').split('page=')[1];
        var query = $('#searchData').val();
        $('li').removeClass('active');
        $(this).parent().addClass('active');

        fetchData(page, query);
    });

    function fetchData (page,searchData){
        $.ajax({
            url: 'search-usag-sanctions?page=' + page + '&searchData=' + searchData,
            type: 'GET',
            cache: false,
            success: function (result) {
                if (result.success) {
                    $('.searchUsag').hide();
                    $('.usagSanctionDiv').html('').html(result.data);
                }
            },
            error: function (result) {
                displayErrorMessage(result.responseJSON.message);
            },
        });
    }

    $(document).on('keyup', '#searchData', function () {
        $('.searchUsag').removeClass('d-none').show();
        $('.no-record-found').addClass('d-none').hide();
        let searchData = $(this).val();
        fetchData(page,searchData);
    });

    $(document).on('click', '#usagSanctionHide', function (event) {
        let sanctionId = $(event.currentTarget).attr('data-id');
        $(this).html('<i class="fas fa-sync font-size-12px fa-spin"></i>');
        $.ajax({
            url: usagSanctions + '/' + sanctionId + '/usag-sanctions-hide',
            method: 'post',
            cache: false,
            success: function (result) {
                if (result.success) {
                    var query = $('#searchData').val();
                    fetchData(page,query);
                }
            },
            error: function (result) {
                UnprocessableInputError(result);
                $(this).html('<i class="fas fa-eye-slash"></i>');
            },
        })
    });

    $(document).on('click', '#usagSanctionDelete', function (event) {
        let sanctionId = $(event.currentTarget).attr('data-id');
        swal({
                title: 'Delete !',
                text: 'Are you sure want to delete this USAG Sanctions ?',
                type: 'warning',
                showCancelButton: true,
                closeOnConfirm: false,
                showLoaderOnConfirm: true,
                confirmButtonColor: '#007bff',
                cancelButtonColor: '#d33',
                cancelButtonText: 'No',
                confirmButtonText: 'Yes',
            }, function () {
                $.ajax({
                    url: usagSanctions + '/' + sanctionId,
                    type: 'DELETE',
                    dataType: 'json',
                    success: function (obj) {
                        if (obj.success) {
                            var query = $('#searchData').val();
                            fetchData(page, query);
                        }
                        swal({
                            title: 'Deleted!',
                            text: 'USAG Sanctions has been deleted.',
                            type: 'success',
                            confirmButtonColor: '#007bff',
                            timer: 2000,
                        });
                    },
                    error: function (data) {
                        swal({
                            title: '',
                            text: data.responseJSON.message,
                            type: 'error',
                            confirmButtonColor: '#007bff',
                            timer: 5000,
                        });
                    },
                });
            }
        );
    });
});
