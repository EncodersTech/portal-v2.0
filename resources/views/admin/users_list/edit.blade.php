@extends('admin.layouts.app')

@section('content')
<link href="https://gitcdn.github.io/bootstrap-toggle/2.2.2/css/bootstrap-toggle.min.css" rel="stylesheet">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2 d-flex">
                <div class="col-sm-6 d-flex">
                    <h1 class="m-0">Edit User</h1>
                </div>
                <div class="ml-auto mr-3">
                    <a href="{{route('admin.users')}}" class="btn btn-primary text-white">Back</a>
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
            <div class="row">
                <form method="POST" action="{{ route('users.update', $user->id) }}">
                    @csrf
                    @method('PATCH')
                    @include('admin.users_list.edit-fields')
                </form>
            </div>
        </div>
    </section>
@endsection

@section('page_js')
@endsection

@section('scripts')
<script src="https://gitcdn.github.io/bootstrap-toggle/2.2.2/js/bootstrap-toggle.min.js"></script>
    <script>
    </script>
@endsection

