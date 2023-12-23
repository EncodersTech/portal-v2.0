@extends('admin.layouts.app')

@section('page_css')
    <link href="{{ asset('assets/admin/css/jquery.dataTables.min.css') }}" rel="stylesheet" type="text/css"/>
    <style>
        .markFeaturedMeet{
            padding: 7px;
            font-weight: 700;
            text-align: center;
            vertical-align: baseline;
            border-radius: .25rem;
            color: black;
            background-color: #ffc107;
        }
    </style>
@endsection

@section('content')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2 d-flex">
                <div class="col-sm-6 d-flex">
                    <h1 class="m-0">{{$meet->name}}'s Dashboard</h1>

                    @if($meet->is_featured && (now() < $meet->end_date))
                        <div class="markFeaturedMeet ml-3 mb-3">
                            <span class=""><i class="fas fa-star"></i> Featured Meet</span>
                        </div>
                    @endif
                </div>
                <div class="ml-auto mr-3">
                    <a href="{{route('admin.meets')}}" class="btn btn-primary text-white">Back</a>
                </div>
            </div>
        </div>
    </div>
    <section class="content">
        <div class="container-fluid">
            @include('admin.meets.dashboard.card_header')

            <div class="row">
                @include('admin.meets.dashboard.gym_table')
                @include('admin.meets.dashboard.meet_info')
            </div>
        </div>
    </section>
@endsection

@section('page_js')
    <script>
        let meetGymUrl = "{{ route('admin.meet.gyms') }}";
        let meetId = "{{ $meet->id }}";
        let updateHandlingFeeRoute = "{{route('update.handling-fee',$meet->id)}}";
    </script>
    <script src="{{ asset('assets/admin/js/jquery.dataTables.min.js') }}"></script>
    <script src="{{ mix('assets/admin/js/custom-datatable.js') }}"></script>
@endsection

@section('scripts')
    <script src="{{ mix('assets/admin/js/meet/dashboard.js') }}"></script>
@endsection

