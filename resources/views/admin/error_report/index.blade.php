@extends('admin.layouts.app')

@section('page_css')
    <link href="{{ asset('assets/admin/css/jquery.dataTables.min.css') }}" rel="stylesheet" type="text/css"/>
    <link rel="stylesheet" href="{{ asset('assets/admin/css/select2.min.css') }}"  type="text/css"/>
@endsection

@section('content')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-12 d-flex header__criteria">
                    <h1 class="m-0">Settings</h1>
                    <div class="filter-container w-100 row justify-content-md-end header__breadcrumb">

                    </div>
                </div>
            </div>
        </div>
    </div>
    <section class="content">
        <div class="container-fluid">
            @include('admin.error_report.modal.edit_usag_level')
            @include('admin.error_report.modal.details_error')

            @include('include.errors')
            @if( Session::has('success'))
                <div class="alert alert-success">
                    <ul class="mb-0">
                        <li>{{ Session::get('success') }}</li>
                    </ul>
                </div>
            @endif
            @if($page == 'usag_level')
                @include('admin.error_report.usag_level')
            @elseif($page == 'error_notice')
                @include('admin.error_report.report')
            @elseif($page == 'onetimeach_report')
                @include('admin.error_report.achreport')
            @endif
        </div>
    </section>
@endsection

@section('page_js')
    <script src="{{ asset('assets/admin/js/jquery.dataTables.min.js') }}"></script>
    <script src="{{ mix('assets/admin/js/custom-datatable.js') }}"></script>
    <script src="{{ asset('assets/admin/js/select2.min.js') }}"></script>
@endsection

@section('scripts')

<script>
    $(document).ready(function() {
        var global_usag_levels_row = null;

        changeCaret = function(e, target)
        {
            if($('#'+e).hasClass('fa-caret-down'))
            {
                $('#'+target).hide();
                $('#'+e).removeClass('fa-caret-down').addClass('fa-caret-right');
            }
            else
            {
                $('#'+target).show();
                $('#'+e).removeClass('fa-caret-right').addClass('fa-caret-down');
            }
        }
        $('#usag_level_table').DataTable(
            {
                "order": [[ 0, "asc" ]],
                "pageLength": 100,
            }
        );

        $('#modal-usag-level-update-close').click(e => {
            $('#modal-usag-level-update').modal('hide');
        });
        $('#modal-error-tracing-close').click(e => {
            $('#modal-error-tracing').modal('hide');
        });

        showError = function(e)
        {
            let data = $(e).closest('td').find('#data').text();
            // console.log
            $('#error_body').html('<pre>'+data+'</pre>');
        }
        update_usag_level = function(e) {

            // get the closest tr
            global_usag_levels_row = $(e).closest('tr');
            let tr = $(e).closest('tr');
            let id = $(e).data('id');
            let code = tr.find('td').eq(2).text();
            let name = tr.find('td').eq(3).text();
            let abbrebiation = tr.find('td').eq(4).text();
            let status = tr.find('td').eq(5).text();
            let is_disabled = status == 'Disabled' ? 1 : 0;

            $('#modal-usag-level-update').find('#id').val(id);
            $('#modal-usag-level-update').find('#code').val(code);
            $('#modal-usag-level-update').find('#name').val(name);
            $('#modal-usag-level-update').find('#abbrebiation').val(abbrebiation);
            $('#modal-usag-level-update').find('#is_disabled').val(is_disabled);
            $('#modal-usag-level-update').find('#label_category_div').hide();
            $('#btn-usag').text('Update Level');
            
        };
        insert_usag_level = function (e)
        {
            $('#modal-usag-level-update').find('#id').val('');
            $('#modal-usag-level-update').find('#code').val('');
            $('#modal-usag-level-update').find('#name').val('');
            $('#modal-usag-level-update').find('#abbrebiation').val('');
            $('#modal-usag-level-update').find('#is_disabled').val(0);
            $('#modal-usag-level-update').find('#label_category_div').show();
            $('#btn-usag').text('Add New Level');
        }
        $('#usag_level_update_form').submit(e => {
            e.preventDefault();
            let data = $('#usag_level_update_form').serialize();
            let id = $('#modal-usag-level-update').find('#id').val();
            if(id == '')
            {
                $.ajax({
                    url: "{{ route('admin.error_report.insert_usag_level') }}",
                    type: 'POST',
                    data: data,
                    dataType: 'json',
                    success: function(res) {
                        if(res.status == 200) {
                            $('#modal-usag-level-update').modal('hide');
                            $('#message_level').show();
                            $('#message_level').removeClass('alert alert-danger').addClass('alert alert-success').text(res.message);
                            setTimeout(() => {
                                $('#message_level').hide();
                            }, 5000);
                            // how to update that table row with latest data?
                            let tr = `<tr>
                                <td>${res.data.id}</td>
                                <td>${res.data.level_category.name}</td>
                                <td>${res.data.code}</td>
                                <td>${res.data.name}</td>
                                <td>${res.data.abbreviation}</td>
                                <td>${res.data.is_disabled == 0 ? "Active" : "Disabled"}</td>
                                <td>
                                    <button onclick="update_usag_level(this)" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#modal-usag-level-update" data-id="${res.data.id}">Update</button>
                                </td>;
                            </tr>`;
                            $('table tbody').append(tr);
                            
                        }
                    },
                    error: function(err, res, status) {
                        $('#modal-usag-level-update').modal('hide');
                        $('#message_level').show();
                        $('#message_level').removeClass('alert alert-success').addClass('alert alert-danger').text(err.responseJSON.message);
                        setTimeout(() => {
                            $('#message_level').hide();
                        }, 5000);
                    },
                    finally: function() {
                        global_usag_levels_row = null;
                    }
                });
            }
            else
            {
                $.ajax({
                    url: "{{ route('admin.error_report.update_usag_level') }}",
                    type: 'POST',
                    data: data,
                    dataType: 'json',
                    success: function(res) {
                        if(res.status == 200) {
                            $('#modal-usag-level-update').modal('hide');
                            $('#message_level').show();
                            $('#message_level').removeClass('alert alert-danger').addClass('alert alert-success').text(res.message);
                            setTimeout(() => {
                                $('#message_level').hide();
                            }, 5000);
                            // how to update that table row with latest data?
                            global_usag_levels_row.find('td').eq(2).text(res.data.code);
                            global_usag_levels_row.find('td').eq(3).text(res.data.name);
                            global_usag_levels_row.find('td').eq(4).text(res.data.abbreviation);
                            global_usag_levels_row.find('td').eq(5).text(res.data.is_disabled == 0 ? "Active" : "Disabled");
                        }
                    },
                    error: function(err) {
                        $('#modal-usag-level-update').modal('hide');
                        $('#message_level').show();
                        $('#message_level').removeClass('alert alert-success').addClass('alert alert-danger').text(err.responseJSON.message);
                        setTimeout(() => {
                            $('#message_level').hide();
                        }, 5000);
                    },
                    finally: function() {
                        global_usag_levels_row = null;
                    }
                });
            }
            
        });

    });

</script>

@endsection

