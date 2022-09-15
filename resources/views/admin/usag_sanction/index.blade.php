@extends('admin.layouts.app')

@section('page_css')
    <link href="{{mix('assets/admin/style/usag.css')}}" rel="stylesheet" type="text/css"/>
@endsection

@section('content')
    <div class="content-header">
        <div class="container-fluid">
            <div class="col-12 d-flex header__criteria">
                <h1 class="m-0 w-100">USAG Sanctions</h1>
                <div class="filter-container w-100 row justify-content-md-end header__breadcrumb">
                    <div class="mr-2">
                        {{ Form::text('from_date', null, ['class' => 'form-control', 'autocomplete' => 'off','id'=>'searchData','placeholder'=>'Search']) }}
                    </div>
                </div>
            </div>
        </div>
    </div>
    <section class="content">
        <div class="container-fluid">
            <div class="row usagSanctionDiv">
                @include('admin.usag_sanction.usag_sanction')
                <div class="searchUsag text-center d-none" style="width:800px; margin:0 auto;">
                    <p style="font-size: 20px"><i class="fas fa-spinner fa-spin"></i>&nbsp;&nbsp; Loading...</p>
                </div>
                <div class="no-record-found d-none" style="width:200px; margin:0 auto;"></div>
                <input type="hidden" name="hiddenPage" id="hiddenPage" value="1"/>
            </div>
        </div>
        @include('admin.usag_sanction.templates.templates')
    </section>
@endsection

@section('page_js')
@endsection

@section('scripts')
    <script>
        {{--let searchUsagSanction = "{{ route('search.usag.sanction') }}";--}}
        let usagSanctions = "{{ route('admin.usag.sanctions') }}";
    </script>
    <script src="{{ mix('assets/admin/js/usag_sanction/usag_sanction.js') }}"></script>
@endsection

