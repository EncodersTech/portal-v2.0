@extends('layouts.main')
@section('page_css')
    <link rel="stylesheet" href="{{ asset('assets/admin/css/select2.min.css') }}"  type="text/css"/>
    <link rel="stylesheet" href="{{ asset('assets/admin/css/summernote.min.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/selectize.js/0.12.6/css/selectize.bootstrap3.min.css" integrity="sha256-ze/OEYGcFbPRmvCnrSeKbRTtjG4vGLHXgOqsyLFTRjg=" crossorigin="anonymous" />
        
    <style>
        .tableFixHead thead th { position: sticky; top: -1px; background:#eee;}
        .image__file-upload{
            padding: 10px;
            background: #0a5b7a;
            display: table;
            color: #fff;
            border-radius: .25rem;
            border-color: #0a5b7a;
            cursor: pointer;
        }
        .thumbnail-preview {
            max-width: 100px !important;
            height: 70px !important;
       }

        .selectGymDiv{
            height: 350px;
            overflow: auto;
        }
        .info-box {
            display: block;
            min-height: 90px;
            background: #fff;
            width: 100%;
            box-shadow: 0 1px 1px rgba(0, 0, 0, 0.1);
            border-radius: 2px;
            margin-bottom: 15px;
        }
        .info-box-icon {
            border-top-left-radius: 2px;
            border-top-right-radius: 0;
            border-bottom-right-radius: 0;
            border-bottom-left-radius: 2px;
            display: block;
            float: left;
            height: 90px;
            width: 90px;
            text-align: center;
            font-size: 45px;
            line-height: 90px;
            background: rgba(0,0,0,0.2);
        }
        .info-box-content {
            padding: 5px 10px;
            margin-left: 90px;
        }
        .fa-fa-setting{
            color: white;
            margin-top: 20px;
        }
        .info-box-text {
            text-transform: uppercase;
        }
        .progress-description, .info-box-text {
            display: block;
            font-size: 14px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .info-box-number {
            display: block;
            font-weight: bold;
            font-size: 16px;
        }
    </style>
@endsection
@section('content-header')
<form action="{{ route('account.host.dashboard') }}" method="post">
    @csrf
    <div class="row">
        <div class="col-2"><span class="fas fa-fw fa-calendar-check"></span>Active Gyms: </div>
        <div class="col-6">
            <select name="gym_id" id="" class="form-control">
                @foreach($active_gyms as $gym)
                    <option value="{{ $gym->id }}" @if($gym->id == $current_gym->id) selected @endif>{{ $gym->name }}</option>
                    <!-- <option value="{{ $gym->id }}" >{{ $gym->name }}</option> -->
                @endforeach
            </select>
        </div>
        <div class="col-2">
            <button class="btn btn-primary" type="submit">Select</button>
        </div>
    </div>
</form>
@endsection

@section('content-main')
    @include('include.errors')

    <div class="content-main p-3 mt-5">
        <div class="row">
            <div class="col-lg-3 mb-3">
                <ul class="nav flex-column nav-pills" role="tablist" id="view-meet-list-tabs">
                    <li class="nav-item">
                        <a class="nav-link active" data-toggle="tab" href="#general-tab" role="tab">
                            <span class="fas fa-fw fa-align-justify"></span> Summary
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link" data-toggle="tab" href="#registration-tab" role="tab">
                            <span class="fas fa-fw fa-money-check"></span> Registered Gyms Information
                        </a>
                    </li>
                    <!-- <li class="nav-item">
                        <a class="nav-link" data-toggle="tab" href="#export-tab" role="tab">
                            <span class="fas fa-fw fa-cogs"></span> Export
                        </a>
                    </li> -->
                </ul>
            </div>

            <div class="col">
                <div class="tab-content">
                    <div class="tab-pane fade show active" id="general-tab" role="tabpanel">
                        <div class="float-right">
                            <b>Export: </b>
                            <a href="{{route('account.host.dashboard.export.csv', $current_gym->id)}}" class="btn btn-primary">CSV</a>
                            <a href="{{route('account.host.dashboard.export.xlsx', $current_gym->id)}}" class="btn btn-primary">Xlsx</a>
                            <a href="{{route('account.host.dashboard.export.pdf', $current_gym->id)}}" class="btn btn-primary">PDF</a>
                        </div>
                        <h2 class="mb-4"><b>Lifetime Totals</b></h2>
                        @include('host_dashboard.summary')
                        <h2 class="mb-4"><b>Current (<?= date('Y') ?>) Year Totals</b></h2>
                        @include('host_dashboard.summary_current_year')
                        
                        @include('host_dashboard.meet_summary')
                    </div>
                    <div class="tab-pane fade" id="registration-tab" role="tabpanel">
                        @include('host_dashboard.gym_summary')
                    </div>
                    
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts-main')
    
@endsection


