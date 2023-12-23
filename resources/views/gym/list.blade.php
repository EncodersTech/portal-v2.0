@extends('layouts.main')

@section('content-header')
    <span class="fas fa-fw fa-dumbbell"></span>
    {{ $_managed->isNotCurrentUser() ? $_managed->first_name . '\'s' : 'My'}} Gyms
@endsection

@section('content-main')
    @include('include.errors')

    <div class="content-main">
        <div>
            <ul class="nav nav-tabs" role="tablist" id="gym-list-tabs">
                <li class="nav-item">
                    <a class="nav-link" id="gym-active-tab" data-toggle="tab" href="#tab-active-gyms" role="tab">
                        Active
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="gym-archived-tabs" data-toggle="tab" href="#tab-archived-gyms" role="tab">
                        Archived
                    </a>
                </li>
            </ul>
        </div>
        <div class="tab-content p-3">
            <div class="tab-pane fade" id="tab-active-gyms" role="tabpanel">               
                @include('gym.list.active')
            </div>

            <div class="tab-pane fade" id="tab-archived-gyms" role="tabpanel">
                @include('gym.list.archived')
            </div>
            
        </div>
    </div>

@endsection

@section('scripts-main')
    <script src="{{ mix('js/gym/gyms.js') }}"></script>
@endsection