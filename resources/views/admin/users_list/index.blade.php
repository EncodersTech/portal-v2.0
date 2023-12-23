@extends('admin.layouts.app')

@section('page_css')
    <link href="{{ asset('assets/admin/css/jquery.dataTables.min.css') }}" rel="stylesheet" type="text/css"/>
@endsection

@section('content')
    <div class="content-header">
        <div class="container-fluid">
            <div class="col-12 d-flex header__criteria">
                    <h1 class="m-0">Users</h1>
                    <div class="filter-container w-100 row justify-content-md-end header__breadcrumb">
                        <div class="btn-group userAction" role="group">
                            <div class="btn-group">
                                <button id="userAction" type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Actions
                                </button>
                                <div class="dropdown-menu us_ac_dropdown" role="menu" style="">
                                    <a class="dropdown-item" href="{{route('users.export.excel')}}">Export Excel</a>
                                    <a class="dropdown-item" target="_blank" href="{{route('users.export.pdf')}}">Export PDF</a>
                                </div>
                            </div>
                        </div>
                </div>
            </div>
        </div>
    </div>
    <section class="content">
        <div class="container-fluid">
            <div class="row">
                @include('admin.users_list.table')
            </div>
            @include('admin.users_list.templates.templates')
            @include('admin.users_list.withdrawal-money-modal')
        </div>
    </section>
@endsection

@section('page_js')
    <script src="{{ asset('assets/admin/js/jquery.dataTables.min.js') }}"></script>
    <script src="{{ mix('assets/admin/js/custom-datatable.js') }}"></script>
@endsection

@section('scripts')
    <script>
        let userUrl = "{{route('admin.users')}}";
        let impersonateUrl = "{{url('impersonate')}}";
        let isImpersonate = "{{ (session('impersonated_by')) ? false : true }}";
    </script>
    <script src="{{ mix('assets/admin/js/user/user.js') }}"></script>
@endsection

