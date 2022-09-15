@extends('admin.layouts.app')
@section('page_css')
    <link href="{{ asset('assets/admin/css/jquery.dataTables.min.css') }}" rel="stylesheet" type="text/css"/>
@endsection
@section('content')
    <div class="content-header">
        <div class="container-fluid">
            <div class="col-12 d-flex header__criteria justify-content-between">
                <h1 class="m-0">Transfer Report</h1>
                <a class="btn btn-primary text-white" href="{{ route('create.transfer') }}">Create Transfer</a>
            </div>
        </div>
    </div>
    <section class="content">
        <div class="container-fluid">
            <div class="row">
                @include('admin.transfer.table')
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
        let transferUrl = "{{route('admin.transfer')}}";
        let transferReason = @json(\App\Models\UserBalanceTransaction::TRANSFER_REASON);
    </script>
    <script src="{{ mix('assets/admin/js/transfer/transfer.js') }}"></script>
@endsection
