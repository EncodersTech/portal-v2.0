@extends('layouts.main')

@section('content-header')
    <span class="fas fa-fw fa-running"></span>
    Manage {{ $_managed->isNotCurrentUser() ? $_managed->first_name . '\'s' : 'My'}} Coaches
@endsection

@section('content-main')
    <h5 class="secondary-title font-weight-bold ml-3">
        <span class="fas fa-fw fa-dumbbell"></span>
        {{ $gym->name }}
    </h5>

    @include('include.errors')

    <div class="content-main">        
        <div>
            <ul class="nav nav-tabs" role="tablist" id="coach-list-tabs">
                <li class="nav-item">
                    <a class="nav-link" id="coach-active-tab" data-toggle="tab" href="#tab-active-coaches" role="tab">
                        Active
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="coach-archived-tabs" data-toggle="tab" href="#tab-faulty-coaches" role="tab">
                        Failed Imports
                    </a>
                </li>
            </ul>
        </div>
        <div class="tab-content p-3">
            <div class="tab-pane fade" id="tab-active-coaches" role="tabpanel">               
                @include('coach.list.active')
            </div>

            <div class="tab-pane fade" id="tab-faulty-coaches" role="tabpanel">
                @include('coach.list.faulty')
            </div>
            
        </div>
    </div>

@endsection

@section('scripts-main')
    <script src="{{ mix('js/coach/coaches.js') }}"></script>
@endsection