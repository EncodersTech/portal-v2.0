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
                    <h1 class="m-0">Meets</h1>
                    <div class="filter-container w-100 row justify-content-md-end header__breadcrumb">
                        <div class="mr-2 meet-menu__from">
                            <label class="lbl-block">{{ __('From') }}</label>
                            {{ Form::text('from_date', null, ['class' => 'form-control', 'autocomplete' => 'off','id'=>'fromDate']) }}
                        </div>
                        <div class="mr-2 meet-menu__to">
                            <label class="lbl-block">{{ __('To') }}</label>
                            {{ Form::text('to_date', null, ['class' => 'form-control', 'autocomplete' => 'off','id'=>'toDate']) }}
                        </div>
                        <div class="mr-2 meet-menu__state">
                            {{ Form::label('state','State') }}<br>
                            {{ Form::select('state',$states, null, ['id'=>'filterState','class'=>'form-control', 'placeholder' => 'All' ]) }}
                        </div>
                        <div class="mr-2 meet-menu__status">
                            {{ Form::label('status','Status') }}<br>
                            {{ Form::select('status',$status, null, ['id'=>'filterStatus','class'=>'form-control', 'placeholder' => 'All' ]) }}
                        </div>
                        <div class="mr-2 meet-menu__sanction">
                            <label class="lbl-block">{{ __('Sanction') }}</label><br>
                            {{ Form::select('sanction_type',$sanction_body, null, ['id'=>'filterSanction','class'=>'form-control','placeholder' => 'All']) }}
                        </div>
                        <div class="mr-2 meetsAction" role="group">
                            <div class="btn-group">
                                <button id="meetsAction" type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Actions
                                </button>
                                <div class="dropdown-menu mee_ac_dropdown" role="menu" style="">
                                    <a class="dropdown-item" href="{{route('meets.export.excel')}}">Export Excel</a>
                                    <a class="dropdown-item" target="_blank" href="{{route('meets.export.pdf')}}">Export PDF</a>
                                </div>
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
                @include('admin.meets.table')
            </div>
        </div>
        @include('admin.meets.templates.templates')
    </section>
@endsection

@section('page_js')
    <script src="{{ asset('assets/admin/js/jquery.dataTables.min.js') }}"></script>
    <script src="{{ mix('assets/admin/js/custom-datatable.js') }}"></script>
    <script src="{{ asset('assets/admin/js/select2.min.js') }}"></script>
@endsection

@section('scripts')
    <script>
        let meetsUrl = "{{route('admin.meets')}}";
    </script>
    <script src="{{ mix('assets/admin/js/meet/meet.js') }}"></script>
@endsection

