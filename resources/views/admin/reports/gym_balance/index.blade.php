@extends('admin.layouts.app')

@section('page_css')
    <link href="{{ asset('assets/admin/css/jquery.dataTables.min.css') }}" rel="stylesheet" type="text/css"/>
@endsection

@section('content')
    <div class="content-header">
        <div class="container-fluid">
            <div class="col-12 d-flex header__criteria">
                    <h1 class="m-0">Gym Balance Report</h1>
            </div>
        </div>
        <div class="container-fluid alert-info mt-2" style="padding:5px;">
            <div class="col-12">
                <i class="fa fa-info-circle"></i> If "Cleared Balance" is negative, it means overdraft and balance adjustment is done. <br>
                <i class="fa fa-info-circle"></i> If "Balance Transaction" is negative, it means overdraft or there are pending transactions to be cleared. <br>
                <i class="fa fa-info-circle"></i> If "Cleared Balance" and "Lifetime Balance" are not equal, it means there are pending transactions. <br>
                <i class="fa fa-info-circle"></i> If "Cleared Balance" and "Balance Transaction" are not equal, it means there are pending transactions. <br>
                
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
