@extends('admin.layouts.app')

@section('page_css')
    <style>
        .settings label{
            font-size: 14px !important;
        }
    </style>
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
            {{ Form::open(['route' => ['admin.settings.update'], 'method' => 'post']) }}
                @include('admin.settings.fields')
            {{ Form::close() }}
        </div>
    </section>
@endsection

@section('page_js')
    <script src="{{ mix('assets/admin/js/setting/setting.js') }}"></script>
@endsection

