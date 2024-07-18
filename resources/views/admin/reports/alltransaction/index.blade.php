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
            @include('include.errors')
            @if( Session::has('success'))
                <div class="alert alert-success">
                    <ul class="mb-0">
                        <li>{{ Session::get('success') }}</li>
                    </ul>
                </div>
            @endif

            @include('admin.reports.alltransaction.alltransaction')

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


    });

</script>

@endsection

