@extends('admin.layouts.app')

@section('page_css')
    <link href="{{ asset('assets/admin/css/jquery.dataTables.min.css') }}" rel="stylesheet" type="text/css"/>
@endsection

@section('content')
    <div class="content-header">
        <div class="container-fluid">
            <div class="col-12 d-flex header__criteria">
                <h1 class="m-0 w-100">Pending Withdrawal Balance Report</h1>
                <div class="filter-container w-100 row justify-content-md-end header__breadcrumb">
                    <div class="mr-2">
                        <a class="btn btn-primary" href="{{route('print.pending.withdrawal.balance')}}" target="_blank">Export PDF</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <section class="content">
        <div class="container-fluid">
            <div class="row">
                @include('admin.reports.pending_withdrawal_balance.table')
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
        $('#pendingWithdrawalBalanceTbl').dataTable({
            "order": [[3, "asc"]],
        });
    </script>

@endsection
