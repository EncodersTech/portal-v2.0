@extends('layouts.main')

@section('content-header')
    <span class="fas fa-fw fa-running"></span>
    Review Failed Import Entry
    <span class="small text-dark">
        {{ $_managed->isNotCurrentUser() ? '(on behalf of ' . $_managed->first_name .')' : ''}}
    </span>
@endsection

@section('content-main')
    <h5 class="secondary-title font-weight-bold ml-3">
        <span class="fas fa-fw fa-dumbbell"></span>
        {{ $gym->name }}
    </h5>

    @include('include.errors')

    <div class="content-main p-3">
        <div class="row">
                <div class="col">
                    <div class="alert alert-info">
                        <span class="fas fa-info-circle"></span>
                        @switch($athlete->error_code)
                            @case(\App\Models\FailedAthleteImport::ERROR_CODE_DUPLICATE)
                                This import failed because at the time of import it was a duplicate.<br>
                                @if ($duplicate != null)
                                    You can 
                                    <a href="{{ route('gyms.athletes.edit', ['gym' => $gym, 'athlete' => $duplicate]) }}"
                                        class="alert-link" target="_blank">
                                        <span class="fas fa-external-link-alt"></span> view the matched athlete
                                    </a> (opens in a new window).<br/>
                                    You can also review the information below and overwrite the matched
                                    athlete's info.
                                @else
                                    However, we can't find the matched athlete right now, because either
                                    their athlete number was changed, or they were removed from your
                                    roster.<br/>
                                    You can review the information below and add this athlete instead.
                                @endif  
                                
                                @if ($athlete->error_message != null)
                                    <br/><br/>Additionally, the following fields were either missing or
                                    had invalid values :<br>
                                    <div class="small ml-1 pl-2 mt-1 border-left border-light preserve-new-lines"
                                        >{{ $athlete->error_message }}
                                    </div>                                
                                @endif
                                @break
                            @case(\App\Models\FailedAthleteImport::ERROR_CODE_VALIDATION)
                                @if (\App\Models\Gym::IMPORT_METHOD_API)
                                    API Import :
                                @else
                                    File Import :
                                @endif
                                This import failed because the following fields were either missing or
                                had invalid values :<br>
                                <div class="small ml-1 pl-2 mt-1 border-left border-light preserve-new-lines"
                                    >{{ $athlete->error_message }}
                                </div>
                                @break

                            @default
                                This import failed because a server error occured.
                        @endswitch
                    </div>
                </div>
        </div>

        <div class="row">
            <div class="col">
                <form method="POST" 
                    action="{{
                        $duplicate != null ?
                        route('gyms.athletes.import.failed.update', ['gym' => $gym, 'failed' => $athlete, 'duplicate' => $duplicate]) :
                        route('gyms.athletes.import.failed.create', ['gym' => $gym, 'failed' => $athlete])
                    }}">
                    @csrf

                    @if ($duplicate != null)
                        @method('PATCH')    
                    @endif                    

                    <div class="row">
                        <div class="col">
                            <h5 class="border-bottom"><span class="fas fa-fw fa-user-plus"></span> Athlete Info</h5>
                        </div>
                    </div>
        
                    <div class="row mt-2">
                        <div class="col-lg mb-3">
                            <label for="first_name">
                                <span class="fas fa-fw fa-user"></span> First Name <span class="text-danger">*</span>
                            </label>
                            <div class="input-group input-group-sm">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><span class="fas fa-fw fa-user"></span></span>
                                </div>
                                <input id="first_name" type="text" class="form-control @error('first_name') is-invalid @enderror" 
                                        name="first_name" value="{{ $athlete->first_name }}" placeholder="First Name" 
                                        required autocomplete="first_name" autofocus>
                            </div>
                            @error('first_name')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
            
                        <div class="col-lg mb-3">
                            <label for="last_name">
                                <span class="far fa-fw fa-user"></span> Last Name <span class="text-danger">*</span>
                            </label>
                            <div class="input-group input-group-sm">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><span class="far fa-fw fa-user"></span></span>
                                </div>
                                <input id="last_name" type="text" class="form-control @error('last_name') is-invalid @enderror" 
                                        name="last_name" value="{{ $athlete->last_name }}" placeholder="Last Name" 
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
                            <label for="gender">
                                <span class="fas fa-fw fa-venus-mars"></span> Gender <span class="text-danger">*</span>
                            </label>
                            <div class="input-group input-group-sm">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><span class="fas fa-fw fa-venus-mars"></span></span>
                                </div>
                                <select id="gender" class="form-control @error('gender') is-invalid @enderror" 
                                        name="gender" required>
                                    <option value="">(Choose below ...)</option>
                                    <option value="female" {{ $athlete->gender == 'female' ? 'selected' : '' }}>
                                        Female
                                    </option>
                                    <option value="male" {{ $athlete->gender == 'male' ? 'selected' : '' }}>
                                        Male
                                    </option>
                                </select>
                            </div>
                            @error('gender')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
            
                        <div class="col-lg mb-3">
                            <label for="dob">
                                <span class="fas fa-fw fa-birthday-cake"></span> Date Of Birth <span class="text-danger">*</span>
                            </label>
                            <div class="input-group input-group-sm">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><span class="fas fa-fw fa-birthday-cake"></span></span>
                                </div>
                                <datepicker :input-class="'form-control form-control-sm bg-white'"
                                    :value="{{ $athlete->dob ? 'new Date(\'' . $athlete->dob . '\')' : 'state.date' }}"
                                    :wrapper-class="'flex-grow-1'" name="dob" :format="'MM/dd/yyyy'" placeholder="mm/dd/yyyy"
                                    :bootstrap-styling="true" :typeable="true" :required="true">
                                </datepicker>
                            </div>
                            @error('dob')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>                        
                    </div>

                    <div class="row">
                        <div class="col-lg mb-3">
                            <label for="tshirt_size_id">
                                <span class="fas fa-fw fa-tshirt"></span> T-Shirt Size
                            </label>
                            <div class="input-group input-group-sm">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><span class="fas fa-fw fa-tshirt"></span></span>
                                </div>
                                <select id="tshirt_size_id" class="form-control @error('tshirt_size_id') is-invalid @enderror" 
                                        name="tshirt_size_id">
                                    <option value="">(Choose below ...)</option>
                                    @foreach ($tshirt_chart->sizes as $size)
                                        <option value="{{ $size->id }}" 
                                            {{ $size->id == $athlete->tshirt_size_id ? 'selected' : '' }}>
                                            {{ $size->size }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            @error('tshirt_size_id')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="col-lg mb-3">
                            <label for="leo_size_id">
                                <span class="fas fa-fw fa-female"></span> Leotard Size
                            </label>
                            <div class="input-group input-group-sm">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><span class="fas fa-fw fa-female"></span></span>
                                </div>
                                <select id="leo_size_id" class="form-control @error('leo_size_id') is-invalid @enderror" 
                                        name="leo_size_id">
                                    <option value="">(Choose below ...)</option>
                                    @foreach ($leo_chart->sizes as $size)
                                        <option value="{{ $size->id }}" 
                                            {{ $size->id == $athlete->leo_size_id ? 'selected' : '' }}>
                                            {{ $size->size }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            @error('leo_size_id')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="is_us_citizen"
                                    id="is_us_citizen" {{ $athlete->is_us_citizen != null ? 'checked' : ''}}>
                                <label class="form-check-label" for="is_us_citizen">
                                    US Citizen
                                </label>
                            </div>

                            @error('is_us_citizen')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                    </div>

                    <div class="row">
                        <div class="col">
                            <h5 class="border-bottom"><span class="fas fa-fw fa-receipt"></span> Memberships</h5>
                        </div>
                    </div>
        
                    <div class="row">
                        <div class="col-lg mb-1 pt-1">
                            <div class="form-check mb-2">
                                <input id="usag_checkbox" class="form-check-input athlete-membership-checkbox"
                                    name="usag_checkbox" type="checkbox" data-body="usag"
                                    {{ $athlete->usag_checkbox != null ? 'checked' : ''}}>
                                <label class="form-check-label" for="usag_checkbox">
                                    USAG Membership
                                </label>
                            </div>
                            
                            <div id="usag-membership-fields">
                                <div class="mb-3">
                                    <input id="usag_no" type="text" placeholder="Enter your USAG membership No." 
                                            class="form-control form-control-sm @error('usag_no') is-invalid @enderror" 
                                            name="usag_no" data-body="usag" value="{{ $athlete->usag_no }}"
                                            required autocomplete="usag_no" disabled>
                                    @error('usag_no')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="usag_level_id">
                                        <span class="fas fa-fw fa-layer-group"></span> Level
                                    </label>
                                    <select id="usag_level_id" class="form-control form-control-sm @error('usag_level_id') is-invalid @enderror" 
                                            name="usag_level_id" required disabled>
                                        <option value="">(Choose below ...)</option>
                                        @foreach ($bodies['USAG']['categories'] as $categoryName => $category)
                                            <optgroup class="bg-secondary" label="{{ $categoryName }}"
                                                data-male="{{ $category['male'] ? 1 : 0 }}"
                                                data-female="{{ $category['female'] ? 1 : 0 }}">
                                            </optgroup>
                                            @foreach ($category['levels'] as $level)
                                                <option class="ml-3" value="{{ $level->id }}" 
                                                    {{ $level->id == $athlete->usag_level_id ? 'selected' : '' }}   
                                                    data-male="{{ $category['male'] ? 1 : 0 }}"
                                                    data-female="{{ $category['female'] ? 1 : 0 }}">
                                                    {{ $level->name }}
                                                </option>
                                            @endforeach
                                        @endforeach
                                    </select>
                                    @error('usag_level_id')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>

                                <div>
                                    <input type="checkbox" id="usag_active" name="usag_active"
                                        {{ $athlete->usag_active != null ? 'checked' : ''}}>
                                    <label for="usag_active">
                                            This membership is active
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg mb-1 pt-1 column-split">
                            <div class="form-check mb-2">
                                <input id="usaigc_checkbox" class="form-check-input athlete-membership-checkbox"
                                    name="usaigc_checkbox" type="checkbox" data-body="usaigc"
                                    {{ $athlete->usaigc_checkbox != null ? 'checked' : ''}}>
                                <label class="form-check-label" for="usaigc_checkbox">
                                    USAIGC Membership
                                </label>
                            </div>
                            
                            <div id="usaigc-membership-fields">
                                <div class="mb-3">
                                    <div class="input-group input-group-sm">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">IGC</span>
                                        </div>
                                        <input id="usaigc_no" type="text" placeholder="Enter your USAIGC membership No." 
                                                class="form-control form-control-sm @error('usaigc_no') is-invalid @enderror" 
                                                name="usaigc_no" data-body="usaigc" value="{{ $athlete->usaigc_no }}"
                                                required autocomplete="usaigc_no" disabled>
                                    </div>
                                    @error('usaigc_no')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="usaigc_level_id">
                                        <span class="fas fa-fw fa-layer-group"></span> Level
                                    </label>
                                    <select id="usaigc_level_id" class="form-control form-control-sm @error('usaigc_level_id') is-invalid @enderror" 
                                            name="usaigc_level_id" required disabled>
                                        <option value="">(Choose below ...)</option>
                                        @foreach ($bodies['USAIGC']['categories'] as $categoryName => $category)
                                            <optgroup class="bg-secondary" label="{{ $categoryName }}"
                                                data-male="{{ $category['male'] ? 1 : 0 }}"
                                                data-female="{{ $category['female'] ? 1 : 0 }}">
                                            </optgroup>
                                            @foreach ($category['levels'] as $level)
                                                <option class="ml-3" value="{{ $level->id }}" 
                                                    {{ $level->id == $athlete->usaigc_level_id ? 'selected' : '' }}
                                                    data-male="{{ $category['male'] ? 1 : 0 }}"
                                                    data-female="{{ $category['female'] ? 1 : 0 }}">
                                                    {{ $level->name }}
                                                </option>
                                            @endforeach
                                        @endforeach
                                    </select>
                                    @error('usaigc_level_id')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>

                                <div>
                                    <input type="checkbox" id="usaigc_active" name="usaigc_active"
                                        {{ $athlete->usaigc_active != null ? 'checked' : ''}}>
                                    <label for="usaigc_active">
                                        This membership is active
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg mb-1 pt-1 column-split">
                            <div class="form-check mb-2">
                                <input id="aau_checkbox" class="form-check-input athlete-membership-checkbox"
                                    name="aau_checkbox" type="checkbox" data-body="aau"
                                    {{ $athlete->aau_checkbox != null ? 'checked' : ''}}>
                                <label class="form-check-label" for="aau_checkbox">
                                    AAU Membership
                                </label>
                            </div>
                            
                            <div id="aau-membership-fields">
                                <div class="mb-3">
                                    <input id="aau_no" type="text" placeholder="Enter your AAU membership No." 
                                            class="form-control form-control-sm @error('aau_no') is-invalid @enderror" 
                                            name="aau_no" data-body="aau" value="{{ $athlete->aau_no }}"
                                            required autocomplete="aau_no" disabled>
                                    @error('aau_no')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            
                                <div class="mb-3">
                                    <label for="aau_level_id">
                                        <span class="fas fa-fw fa-layer-group"></span> Level
                                    </label>
                                    <select id="aau_level_id" class="form-control form-control-sm @error('aau_level_id') is-invalid @enderror" 
                                            name="aau_level_id" required disabled>
                                        <option value="">(Choose below ...)</option>
                                        @foreach ($bodies['AAU']['categories'] as $categoryName => $category)
                                            <optgroup class="bg-secondary" label="{{ $categoryName }}"
                                                data-male="{{ $category['male'] ? 1 : 0 }}"
                                                data-female="{{ $category['female'] ? 1 : 0 }}">
                                            </optgroup>
                                            @foreach ($category['levels'] as $level)
                                                <option class="ml-3" value="{{ $level->id }}" 
                                                    {{ $level->id == $athlete->aau_level_id ? 'selected' : '' }}
                                                    data-male="{{ $category['male'] ? 1 : 0 }}"
                                                    data-female="{{ $category['female'] ? 1 : 0 }}">
                                                    {{ $level->name }}
                                                </option>
                                            @endforeach
                                        @endforeach
                                    </select>
                                    @error('aau_level_id')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>

                                <div>
                                    <input type="checkbox" id="aau_active" name="aau_active"
                                        {{ $athlete->aau_active != null ? 'checked' : ''}}>
                                    <label for="aau_active">
                                        This membership is active
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col text-right">
                            <button type="submit" class="btn btn-success">
                                @if ($duplicate != null)
                                    <span class="fas fa-highlighter"></span> Overwrite
                                @else 
                                    <span class="fas fa-user-plus"></span> Add
                                @endif
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div> 
        
    </div>

@endsection

@section('scripts-main')
    <script src="{{ mix('js/athlete/athlete-edit.js') }}"></script>
@endsection