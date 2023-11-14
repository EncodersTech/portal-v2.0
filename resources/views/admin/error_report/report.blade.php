<div class="card">
    <div class="card-body">
        <div class="" id="message_level" style="display:none;">
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

<div>
</div>