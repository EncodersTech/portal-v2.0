@extends('layouts.main')

@section('content-header')
    <span class="fas fa-fw fa-running"></span>
    Manage {{ $_managed->isNotCurrentUser() ? $_managed->first_name . '\'s' : 'My'}} Athletes
@endsection

@section('content-main')
    <h5 class="secondary-title font-weight-bold ml-3">
        <span class="fas fa-fw fa-dumbbell"></span>
        {{ $gym->name }}
    </h5>
    @include('include.errors')

    <div class="content-main">        
        <div>
            <div class="alert alert-warning"><i class="fas fa-info-circle"></i> Changes made on NGA website may take a day to reflect on Allgymnastics</div>
            <ul class="nav nav-tabs" role="tablist" id="athlete-list-tabs">
                <li class="nav-item">
                    <a class="nav-link" id="athlete-active-tab" data-toggle="tab" href="#tab-active-athletes" role="tab">
                        Active
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="athlete-archived-tabs" data-toggle="tab" href="#tab-faulty-athletes" role="tab">
                        Failed Imports
                    </a>
                </li>
            </ul>
        </div>
        <div class="tab-content p-3">
            <div class="tab-pane fade" id="tab-active-athletes" role="tabpanel">           
                @include('athlete.list.active')
            </div>

            <div class="tab-pane fade" id="tab-faulty-athletes" role="tabpanel">
                @include('athlete.list.faulty')
            </div>
            
        </div>
    </div>

@endsection

@section('scripts-main')
    <script src="{{ mix('js/athlete/athletes.js') }}"></script>
@endsection