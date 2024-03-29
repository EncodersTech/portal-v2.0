@extends('admin.layouts.app')

@section('page_css')
    <link href="{{ asset('assets/admin/css/jquery.dataTables.min.css') }}" rel="stylesheet" type="text/css"/>
    <style>
        .red{
            color: red;
            background-color: #f2dede;
        }
        .green{
            color: green;
            background-color: #dff0d8;
        }
        .warning{
            color: #8a6d3b;
            background-color: #fcf8e3;
        }
    </style>
@endsection

@section('content')
    <div class="content-header">
        <div class="container-fluid">
            <div class="col-12 d-flex header__criteria">
                    <h1 class="m-0">Gym Balance Report</h1>
            </div>
        </div>
    </div>
    <section class="content">
        <div class="container-fluid">
            <div class="row">
                @include('admin.reports.gym_balance.table')
            </div>
        </div>
    </section>
@endsection

@section('page_js')
    <script src="{{ asset('assets/admin/js/jquery.dataTables.min.js') }}"></script>
    <script src="{{ mix('assets/admin/js/custom-datatable.js') }}"></script>
@endsection

@section('scripts')
    <script>
        let gymBalanceUrl = "{{route('admin.gym.balance.reports')}}";
    </script>
    <script src="{{ mix('assets/admin/js/reports/reports.js') }}"></script>
@endsection
