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
                    <h1 class="m-0">Balance Adjustment</h1>
                    <div class="filter-container w-100 row justify-content-md-end header__breadcrumb">

                    </div>
                </div>
            </div>
        </div>
    </div>
    <section class="content">
        <div class="container-fluid">
            <form id="balance_adjustment_form">
                @csrf
                <div class="row">
                    <div class="col-6">
                        <label for="">User Email</label>
                        <input type="email" name="email" class="form-control" id="email">
                    </div>
                    <div class="col-6" id="nextcolumn">

                    </div>
                </div>
                
                <label for="">Amount</label>
                <input type="text" name="amount" class="form-control">

                <button type="submit" class="btn btn-primary">Adjust</button>
            </form>
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
        // onkeyout event for email input
        $('#email').on('focusout', function(){
            var email = $(this).val();
            if(email.length > 0)
            {
                $.ajax({
                    url: "{{ route('admin.gym.balance.get_user') }}",
                    type: "GET",
                    data: {email: email},
                    success: function(response){
                        if(response.status == 'success')
                        {
                            var user = response.user;
                            var html = '<label for="">User Name</label>';
                            html += '<input type="text" name="name" class="form-control" value="'+user.full_name+'" readonly>';
                            html += '<label for="">Current Balance</label>';
                            html += '<input type="text" name="balance" class="form-control" value="'+user.cleared_balance+'" readonly>';
                            html += '<label for="">Lifetime Balance</label>';
                            html += '<input type="text" name="balance" class="form-control" value="'+user.pending_balance+'" readonly>';
                            $('#nextcolumn').html(html);
                        }
                    }
                });
                
            }
        });
        $('#balance_adjustment_form').on('submit', function(e){
            e.preventDefault();
            var form = $(this);
            var url = form.attr('action');
            var data = form.serialize();
            $.ajax({
                url: "{{ route('admin.gym.balance.adjust') }}",
                type: "POST",
                data: data,
                success: function(response){
                    if(response.status == 'success')
                    {
                        alert('Balance adjusted successfully');
                    }
                }
            });
        });
    });

</script>

@endsection

