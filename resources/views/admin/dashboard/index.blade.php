@extends('admin.layouts.app')

@section('page_css')
    <link href="{{mix('assets/admin/style/dashboard.css')}}" rel="stylesheet" type="text/css"/>
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
            @include('admin.dashboard.chart.admin_card_fee_chart')
        </div>
    </section>
@endsection

@section('page_js')
@endsection

@section('scripts')
@endsection
