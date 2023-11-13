<div class="card">
    @include('admin.error_report.modal.edit_usag_level')
    @include('admin.error_report.modal.details_error')
    <div class="card-body">
        <div class="" id="message_level" style="display:none;">
        </div>
        <div class="row mb-2">
            <div class="col-6" onclick="changeCaret('usag_level','usag_level_div');">
                <h4>USAG Levels
                    <i class="fas fa-caret-down" id="usag_level"></i>
                </h4>
            </div>
            <div class="col-6">
                <button onclick="insert_usag_level(this)" class="btn btn-success float-right" data-toggle="modal" data-target="#modal-usag-level-update">Add New Level</button>
            </div>
        </div>
        <div id="usag_level_div">
            <table class="table mt-2" id="usag_level_table">
                <thead>
                    <tr>
                        <th>id</th>
                        <th>level category</th>
                        <th>code</th>
                        <th>name</th>
                        <th>abbrebiation</th>
                        <th>status</th>
                        <th>action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($usag_levels as $level)
                        <tr>
                            <td>{{ $level->id }}</td>
                            <td>{{ $level->level_category->name }}</td>
                            <td>{{ $level->code }}</td>
                            <td>{{ $level->name }}</td>
                            <td>{{ $level->abbreviation }}</td>
                            <td>{{ $level->is_disabled ? "Disabled" : "Active" }}</td>
                            <td>
                                <button onclick="update_usag_level(this)" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#modal-usag-level-update" data-id="{{ $level->id }}">Update</button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="row mb-2">
            <div class="col-6" onclick="changeCaret('logged_error','logged_div');">
                <h4>Logged Errors
                    <i class="fas fa-caret-down" id="logged_error"></i>
                </h4>
            </div>
        </div>
        <div id="logged_div">
            @foreach($log_errors as $log_error)
                <div class="row">
                    <div class="col-12">
                        <h5 onclick="changeCaret('logged_error_index_{{$log_error['id']}}','logged_error_div_{{$log_error['id']}}');"> {{ $log_error['date'] }} :: {{ $log_error['name'] }}
                            <i class="fas fa-caret-down" id="logged_error_index_{{$log_error['id']}}"></i>
                        </h5>
                    </div>
                </div>
                <div class="row" id="logged_error_div_{{$log_error['id']}}">
                    <table class="table mt-2" id="error_log_{{$log_error['id']}}">
                        <thead>
                            <tr>
                                <th>id</th>
                                <th>error heading</th>
                                <th>action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($log_error['errors'] as $key=>$error)
                                <tr>
                                    <td>{{ $key + 1 }}</td>
                                    <td>{{ $error['heading'] }}</td>
                                    <td>
                                        <div style="display:none;" id="data">{{$error['details']}}</div>
                                        <button onclick="showError(this)" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#modal-error-tracing">Show</button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endforeach
        </div>
    </div>
</div>

<!-- create a modal for usag data update -->
<div>

</div>
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
        $('#usag_level_table').DataTable();

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