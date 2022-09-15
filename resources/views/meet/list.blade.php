@extends('layouts.main')

@section('content-header')
    <span class="fas fa-fw fa-calendar-check"></span>
    {{ $_managed->isNotCurrentUser() ? $_managed->first_name . '\'s' : 'My'}} Meets
@endsection

@section('content-main')
    @include('include.errors')

    <h5 class="secondary-title font-weight-bold ml-3">
        <span class="fas fa-fw fa-dumbbell"></span>
        {{ $gym->name }}
    </h5>

    <div class="content-main">
        <div>
            <ul class="nav nav-tabs" role="tablist" id="meet-list-tabs">
                <li class="nav-item">
                    <a class="nav-link" id="meet-active-tab" data-toggle="tab" href="#tab-active-meets" role="tab">
                        Active
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="meet-archived-tabs" data-toggle="tab" href="#tab-archived-gyms" role="tab">
                        Archived
                    </a>
                </li>
            </ul>
        </div>
        <div class="tab-content p-3">
            <div class="tab-pane fade" id="tab-active-meets" role="tabpanel">               
                @include('meet.list.active')
            </div>

            <div class="tab-pane fade" id="tab-archived-gyms" role="tabpanel">
                @include('meet.list.archived')
            </div>
            
        </div>
    </div>

@endsection

@section('scripts-main')
    <script src="{{ mix('js/meet/meets.js') }}"></script>
@endsection