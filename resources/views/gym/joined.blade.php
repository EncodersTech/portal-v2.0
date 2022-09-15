@extends('layouts.main')

@section('content-header')
    <span class="fas fa-fw fa-calendar-check"></span>
    {{ $_managed->isNotCurrentUser() ? $_managed->first_name . '\'s' : 'My'}} Joined Meets
@endsection

@section('content-main')
    @include('include.errors')

    <h5 class="secondary-title font-weight-bold ml-3">
        <span class="fas fa-fw fa-dumbbell"></span>
        {{ $gym->name }}
    </h5>

    <div class="content-main p-3">        
        <ag-joined-meet-list singular="meet" plural="meets" prefix="joined-meet-list-{{ $gym->id }}"
            :gym-id="{{ $gym->id }}" :managed="{{ $_managed->id }}">
        </ag-joined-meet-list>
    </div>

@endsection

@section('scripts-main')
    <script src="{{ mix('js/gym/joined.js') }}"></script>
@endsection