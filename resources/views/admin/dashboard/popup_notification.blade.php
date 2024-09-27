@extends('admin.layouts.app')

@section('page_css')
    <link href="{{mix('assets/admin/style/dashboard.css')}}" rel="stylesheet" type="text/css"/>
    <link rel="stylesheet" href="{{ asset('assets/admin/css/summernote.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/admin/css/select2.min.css') }}"  type="text/css"/>
    <style>
        h3{
            text-align: center;
        }
    </style>
@endsection

@section('content')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2 d-flex">
                <div class="col-sm-6 d-flex">
                    <h1 class="m-0">Admin Dashboard</h1>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <div class="card">
                <div class="card-body">
                    @if (session('success'))
                        <div class="alert alert-success">
                            {{ session('success') }}
                        </div>
                    @endif
                    @if(session('error'))
                        <div class="alert alert-danger">
                            {{ session('error') }}
                        </div>
                    @endif
                    <form action="{{ route('admin.notification.submit') }}" method="post">
                        @csrf

                        <input type="hidden" name="state" value="<?= $state ?>">
                        <input type="hidden" name="edit_id" value="<?= isset($edit_notification) ? $edit_notification->id : '' ?>">
                        <div>
                            <label for="name" class="mb-1">Validity: <span class="text-danger">*</span></label>
                            <input type="date" name="validity" class="form-control" value="<?= isset($edit_notification) ? $edit_notification->validity : '' ?>">
                        </div>
                        <div>
                            <label for="name" class="mb-1">Users: <span class="text-danger">*</span> <span style="color:green;">(leave blank to select all users by default)</span></label>
                            <select name="user_id[]" id="user_id" class="form-control selectpicker" data-live-search="true" multiple>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}" <?= isset($edit_notification) && in_array($user->id,array_keys(json_decode($edit_notification->selected_users, true))) ? 'selected' : '' ?>>{{ $user->fullName() }} - {{ $user->email }}</option>
                                @endforeach
                            </select>
                            <table class="table">
                                <tr>
                                    <td>
                                        <div class="form-group">
                                            <label for="">Select Meet Hosts</label>
                                            <div class="custom-control custom-switch">
                                                <input type="checkbox" name="cc_gateway" class="custom-control-input" value="1" id="customSwitch3">
                                                <label class="custom-control-label" for="customSwitch3"></label>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="form-group">
                                            <label for="">Select Meet Registrants</label>
                                            <div class="custom-control custom-switch">
                                                <input type="checkbox" name="cc_gateway" class="custom-control-input" value="1" id="customSwitch4" >
                                                <label class="custom-control-label" for="customSwitch4"></label>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            </table>
                            
                        </div>
                        <div>
                            <label for="name" class="mb-1">Notification Title: <span class="text-danger">*</span></label>
                            <input type="text" name="title" id="title" class="form-control" required value="<?= isset($edit_notification) ? $edit_notification->title : '' ?>" >
                        </div>
                        <div class="summernote">
                            <label for="name" class="mb-1">Notification Body: <span class="text-danger">*</span></label>
                            <textarea name="message" id="messageBody" class="form-control"><?= isset($edit_notification) ? $edit_notification->content : '' ?></textarea>
                        </div>
                        <div>
                            <label for="name" class="mb-1">Status: <span class="text-danger">*</span></label>
                            <select name="status" id="status" class="form-control">
                                <option value="1" <?= isset($edit_notification) && $edit_notification->status == 1 ? 'selected' : '' ?>>Active</option>
                                <option value="0" <?= isset($edit_notification) && $edit_notification->status == 0 ? 'selected' : '' ?>>Inactive</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary mt-3" style="width:100%;">Save</button>
                    </form>

                    <div>
                        <hr>
                        <h4 class="mt-5">Notification List</h4>
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Notification Title</th>
                                    <th>Validity</th>
                                    <th>Created At</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($notifications as $notification)
                                    <tr>
                                        <td>{{ $notification->title }}</td>
                                        <td>{{ $notification->validity }}</td>
                                        <td>{{ $notification->created_at }}</td>
                                        <td>
                                            @if($notification->status == 0)
                                                <a href="{{ route('admin.notification.status', $notification->id) }}" class="btn btn-danger">Inactive</a>
                                            @else
                                                <a href="{{ route('admin.notification.status', $notification->id) }}" class="btn btn-success">Active</a>
                                            @endif
                                        </td>
                                        <td>
                                            <a href="{{ route('admin.notification.edit', $notification->id) }}" class="btn btn-info">Edit</a>
                                            <a href="{{ route('admin.notification.delete', $notification->id) }}" class="btn btn-danger">Delete</a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

@section('page_js')
    <script src="{{ asset('assets/admin/js/summernote.min.js') }}"></script>
    <script src="{{ asset('assets/admin/js/select2.min.js') }}"></script>
@endsection

@section('scripts')
<script>
    $(document).ready(function(){
        var meet_host_ids = '<?= json_encode($meet_host_users) ?>';
        var meet_registrant_ids = '<?=json_encode($meet_registrant_users) ?>';
        meet_host_ids = JSON.parse(meet_host_ids);
        meet_registrant_ids = JSON.parse(meet_registrant_ids);

        $('#messageBody').summernote({
            height: 200,
            placeholder: 'Enter your notification body here...',
            tabsize: 2,
            
        });
        $(function() {
            $('.selectpicker').select2();
        });
        $('#customSwitch3').click(function(){
            console.log(meet_host_ids.length);
            // select user_id multi select box and select all meet host users
            if($(this).is(':checked'))
            {
                meet_host_ids.forEach(function(v){
                    console.log(v);
                    $("#user_id option[value='" + v + "']").prop("selected", true);
                    // show
                });
                $('#user_id').select2().trigger('change');
            }else{
                meet_host_ids.forEach(function(v){
                    console.log(v);
                    $("#user_id option[value='" + v + "']").prop("selected", false);
                    // show
                });
                $('#user_id').select2().trigger('change');
            }

        });
        $('#customSwitch4').click(function(){
            console.log(meet_registrant_ids.length);
            // select user_id multi select box and select all meet host users
            if($(this).is(':checked'))
            {
                meet_registrant_ids.forEach(function(v){
                    console.log(v);
                    $("#user_id option[value='" + v + "']").prop("selected", true);
                    // show
                });
                $('#user_id').select2().trigger('change');
            }else{
                meet_registrant_ids.forEach(function(v){
                    console.log(v);
                    $("#user_id option[value='" + v + "']").prop("selected", false);
                    // show
                });
                $('#user_id').select2().trigger('change');
            }

        });
    });
</script>
@endsection
