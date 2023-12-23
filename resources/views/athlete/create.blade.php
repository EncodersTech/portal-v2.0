@extends('layouts.main')

@section('content-header')
    <span class="fas fa-fw fa-running"></span>
    Create an Athlete
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
                <form method="POST" action="{{ route('gyms.athletes.store', ['gym' => $gym]) }}">
                    @csrf

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
                                        name="first_name" value="{{ old('first_name') }}" placeholder="First Name"
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
                                        name="last_name" value="{{ old('last_name') }}" placeholder="Last Name"
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
                                    <option value="female" {{ old('gender') == 'female' ? 'selected' : '' }}>
                                        Female
                                    </option>
                                    <option value="male" {{ old('gender') == 'male' ? 'selected' : '' }}>
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
                                    :value="{{ old('dob') ? 'new Date(\'' . old('dob') . '\')' : 'state.date' }}"
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
                                            {{ $size->id == old('tshirt_size_id') ? 'selected' : '' }}>
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
                                            {{ $size->id == old('leo_size_id') ? 'selected' : '' }}>
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
                                    id="is_us_citizen" {{ old('is_us_citizen') != null ? 'checked' : ''}}>
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
                                    {{ old('usag_checkbox') != null ? 'checked' : ''}}>
                                <label class="form-check-label" for="usag_checkbox">
                                    USAG Membership
                                </label>
                            </div>

                            <div id="usag-membership-fields">
                                <div class="mb-3">
                                    <input id="usag_no" type="text" placeholder="Enter your USAG membership No."
                                            class="form-control form-control-sm @error('usag_no') is-invalid @enderror"
                                            name="usag_no" data-body="usag" value="{{ old('usag_no') }}"
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
                                                    {{ $level->id == old('usag_level_id') ? 'selected' : '' }}
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
                                        {{ old('usag_active') != null ? 'checked' : ''}}>
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
                                    {{ old('usaigc_checkbox') != null ? 'checked' : ''}}>
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
                                                name="usaigc_no" data-body="usaigc" value="{{ old('usaigc_no') }}"
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
                                                    {{ $level->id == old('usaigc_level_id') ? 'selected' : '' }}
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
                                        {{ old('usaigc_active') != null ? 'checked' : ''}}>
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
                                    {{ old('aau_checkbox') != null ? 'checked' : ''}}>
                                <label class="form-check-label" for="aau_checkbox">
                                    AAU Membership
                                </label>
                            </div>

                            <div id="aau-membership-fields">
                                <div class="mb-3">
                                    <input id="aau_no" type="text" placeholder="Enter your AAU membership No."
                                            class="form-control form-control-sm @error('aau_no') is-invalid @enderror"
                                            name="aau_no" data-body="aau" value="{{ old('aau_no') }}"
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
                                                    {{ $level->id == old('aau_level_id') ? 'selected' : '' }}
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
                                        {{ old('aau_active') != null ? 'checked' : ''}}>
                                    <label for="aau_active">
                                        This membership is active
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg mb-1 pt-1 column-split">
                            <div class="form-check mb-2">
                                <input id="nga_checkbox" class="form-check-input athlete-membership-checkbox"
                                       name="nga_checkbox" type="checkbox" data-body="nga"
                                    {{ old('nga_checkbox') != null ? 'checked' : ''}}>
                                <label class="form-check-label" for="nga_checkbox">
                                    NGA Membership
                                </label>
                            </div>

                            <div id="nga-membership-fields">
                                <div class="mb-3">
                                    <input id="nga_no" type="text" placeholder="Enter your NGA membership No."
                                           class="form-control form-control-sm @error('nga_no') is-invalid @enderror"
                                           name="nga_no" data-body="nga" value="{{ old('nga_no') }}"
                                           required autocomplete="nga_no" disabled>
                                    @error('nga_no')
                                    <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="nga_level_id">
                                        <span class="fas fa-fw fa-layer-group"></span> Level
                                    </label>
                                    <select id="nga_level_id" class="form-control form-control-sm @error('nga_level_id') is-invalid @enderror"
                                            name="nga_level_id" required disabled>
                                        <option value="">(Choose below ...)</option>
                                        @foreach ($bodies['NGA']['categories'] as $categoryName => $category)
                                            <optgroup class="bg-secondary" label="{{ $categoryName }}"
                                                      data-male="{{ $category['male'] ? 1 : 0 }}"
                                                      data-female="{{ $category['female'] ? 1 : 0 }}">
                                            </optgroup>
                                            @foreach ($category['levels'] as $level)
                                                <option class="ml-3" value="{{ $level->id }}"
                                                        {{ $level->id == old('nga_level_id') ? 'selected' : '' }}
                                                        data-male="{{ $category['male'] ? 1 : 0 }}"
                                                        data-female="{{ $category['female'] ? 1 : 0 }}">
                                                    {{ $level->name }}
                                                </option>
                                            @endforeach
                                        @endforeach
                                    </select>
                                    @error('nga_level_id')
                                    <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>

                                <div>
                                    <input type="checkbox" id="nga_active" name="nga_active"
                                        {{ old('nga_active') != null ? 'checked' : ''}}>
                                    <label for="nga_active">
                                        This membership is active
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col text-right">
                            <button type="submit" class="btn btn-success">
                                <span class="fas fa-plus"></span> Create
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

    </div>

@endsection

@section('scripts-main')
    <script src="{{ mix('js/athlete/athlete-create.js') }}"></script>
@endsection
