@extends('admin.layouts.app')
@section('page_css')
    <link rel="stylesheet" href="{{ asset('assets/admin/css/select2.min.css') }}"  type="text/css"/>
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
                    <h1 class="m-0">Transfer</h1>
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
            {{ Form::open(['route' => ['store.transfer'], 'id' => 'transferStore']) }}
            @include('admin.transfer.fields')
            {{ Form::close() }}
        </div>
        @include('admin.transfer.template.template')
    </section>
@endsection

@section('page_js')
    <script>
        let transferUrl = "{{route('admin.transfer')}}";
    </script>
    <script src="{{ asset('assets/admin/js/select2.min.js') }}"></script>
    <script src="{{ mix('assets/admin/js/transfer/create-edit.js') }}"></script>
@endsection

