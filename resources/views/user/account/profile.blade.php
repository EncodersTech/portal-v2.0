<div class="row">
    <div class="col">
        <h5 class="border-bottom"><span class="fas fa-user-edit"></span> Edit Profile</h5>
    </div>
</div>
<div class="row mb-3">    
    <div class="col-sm-2">
        <img id="profile-picture-display" src="{{ Auth::user()->profile_picture }}"
            class="rounded profile-picture-256" alt="Profile Picture">
        
        <div class="pt-1">
            <form class="d-inline-block mr-1" action="{{ route('account.picture.reset') }}"
                    method="POST">
                @csrf
                <button type="submit" class="btn btn-sm btn-link p-0">
                    <span class="fas fa-fw fa-ban"></span> Clear
                </button>
            </form>

            <form id="profile-picture-change-form"
                    class="d-inline-block" action="{{ route('account.picture.change') }}"
                    method="POST" enctype="multipart/form-data">
                @csrf
                <input type="file" tabindex="-1" name="profile_picture" id="profile-picture"
                        class="invisible-file-input" accept="image/jpeg,image/png">
            </form>
            <button type="button" class="btn btn-sm btn-link p-0" id="profile-picture-change">
                <span class="fas fa-fw fa-sync-alt"></span> Change
            </button>
        </div>

        <div class="small text-dark">
            {{ $profile_picture_max_size }} max.
        </div>
    </div>
    <div class="col">
        <form method="POST" action="{{ route('account.profile.update') }}">
            @csrf
            @method('PATCH')

            <div class="row">
                <div class="col-lg mb-3">
                    <label for="first_name" class="mb-1">
                        <span class="fas fa-fw fa-user-circle"></span> {{ __('messages.first_name') }} <span class="text-danger">*</span>
                    </label>
                    <div class="input-group input-group-sm">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><span class="fas fa-fw fa-user"></span></span>
                        </div>
                        <input id="first_name" type="text" class="form-control @error('first_name') is-invalid @enderror" 
                                name="first_name" value="{{ Auth::user()->first_name }}" placeholder="{{ __('messages.first_name')}}" 
                                required autocomplete="first_name" autofocus>
                    </div>
                    @error('first_name')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>
    
                <div class="col-lg mb-3">
                    <label for="last_name" class="mb-1">
                        <span class="fas fa-fw fa-user-circle"></span> {{ __('messages.last_name') }} <span class="text-danger">*</span>
                    </label>
                    <div class="input-group input-group-sm">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><span class="far fa-fw fa-user"></span></span>
                        </div>
                        <input id="last_name" type="text" class="form-control @error('last_name') is-invalid @enderror" 
                                name="last_name" value="{{ Auth::user()->last_name }}" placeholder="{{ __('messages.last_name')}}" 
                                required autocomplete="last_name">
                    </div>
                    @error('last_name')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>
            </div>

            <div class="row">
                <div class="col-lg mb-3">
                    <label for="email" class="mb-1">
                        <span class="fas fa-fw fa-envelope"></span> {{ __('messages.email') }}
                    </label>
                    <div class="input-group input-group-sm">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><span class="fas fa-fw fa-envelope"></span></span>
                        </div>
                        <input id="email" type="email" class="form-control" 
                                name="email" value="{{ Auth::user()->email }}" placeholder="{{ __('messages.email') }}" 
                                autocomplete="none" disabled autofocus>
                    </div>
                </div>
            
                <div class="col-lg mb-3">
                    <label for="office_phone" class="mb-1">
                        <span class="fas fa-fw fa-phone"></span> {{ __('messages.office_phone') }} <span class="text-danger">*</span>
                    </label>
                    <div class="input-group input-group-sm">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><span class="fas fa-fw fa-phone"></span></span>
                        </div>
                        <input id="office_phone" type="text" class="form-control @error('office_phone') is-invalid @enderror" 
                                name="office_phone" value="{{ Auth::user()->office_phone }}" placeholder="{{ __('messages.office_phone')}}" 
                                required autocomplete="office_phone">
                    </div>
                    @error('office_phone')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>
            </div>

            <div class="row">
                <div class="col mb-3">
                    <label for="job_title" class="mb-0">
                        <span class="fas fa-fw fa-briefcase"></span> {{ __('messages.job_title') }} <span class="text-danger">*</span>
                    </label>
                    <div class="input-group input-group-sm">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><span class="fas fa-fw fa-briefcase"></span></span>
                        </div>
                        <input id="job_title" type="text" class="form-control @error('job_title') is-invalid @enderror" 
                                name="job_title" value="{{ Auth::user()->job_title }}" placeholder="{{ __('messages.job_title')}}" 
                                required autocomplete="job_title">
                    </div>
                    @error('job_title')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>
            </div>

            <div class="row">
                <div class="col mb-3 text-right">
                    <button type="submit" class="btn btn-sm btn-success">
                        <span class="fas fa-save"></span> Save
                    </button>
                </div>
            </div>
        </form>
    </div>
</div> 