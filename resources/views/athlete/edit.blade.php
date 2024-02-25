@extends('layouts.main')

@section('content-header')
    <span class="fas fa-fw fa-running"></span>
    Edit an Athlete
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

    <div class="modal fade" id="comparison-athlete-modal" tabindex="-1" role="dialog" baria-labelledby="comparison-athlete-modal" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title text-danger">
                        <span class="fas fa-times-circle"></span> Athlete Save Failed
                    </h5>
                    <button type="button" class="close comparison-athlete-modal-close" aria-label="Close">
                        <span class="fas fa-times" aria-hidden="true"></span>
                    </button>
                </div>

                @if(Session::has('ngaData'))
                <div class="modal-body">
                    @php
                    $allgym_athlete = Session::get('ngaData')['allgym'];
                    $nga_athlete = Session::get('ngaData')['nga'];
                    
                    $myDateTime = DateTime::createFromFormat('Y-m-d  h:i:s', $allgym_athlete['dob']);
                    $newDateString = $myDateTime->format('m/d/Y');

                    $myDateTimeNGA = DateTime::createFromFormat('m/d/Y',  $nga_athlete['DOB']);
                    $newDateStringNGA = $myDateTimeNGA->format('m/d/Y');

                    @endphp
                    <div class="row">
                        <div class="col-md-6">
                            <b>Allgymnastics</b><br>        
                            <form method="POST" action="{{ route('gyms.athletes.update', ['gym' => $gym, 'athlete' => $allgym_athlete]) }} " >
                                @csrf
                                @method('PATCH')
                                <label for="a_first_name">First Name</label>
                                <input required type="text" onchange="changedData('a_first_name')" name="first_name" id="a_first_name" value="{{ $allgym_athlete['first_name'] }}" class="form-control {{ $allgym_athlete['first_name'] != $nga_athlete['FirstName'] ? 'alert-danger' : '' }}">
                                <label for="a_last_name">Last Name</label>
                                <input required type="text" onchange="changedData('a_last_name')" name="last_name" id="a_last_name" value="{{ $allgym_athlete['last_name'] }}" class="form-control {{ $allgym_athlete['last_name'] != $nga_athlete['LastName'] ? 'alert-danger' : '' }}">
                                <label for="a_gender">Gender</label>
                                <select id="a_gender" onchange="changedData('a_gender')" class="form-control {{ strtoupper($allgym_athlete['gender'][0]) != $nga_athlete['Gender'] ? 'alert-danger' : '' }}"
                                        name="gender" required > 
                                    <option value="">(Choose below ...)</option>
                                    <option value="female" {{ $allgym_athlete['gender'] == 'female' ? 'selected' : '' }}>
                                        Female
                                    </option>
                                    <option value="male" {{ $allgym_athlete['gender'] == 'male' ? 'selected' : '' }}>
                                        Male
                                    </option>
                                </select>
                                <label for="a_date">DOB</label>
                                <div class="input-group input-group-sm">
                                    <datepicker :input-class="'form-control {{ $newDateString != $newDateStringNGA ? 'alert-danger' : '' }}'"
                                    :value="{{ $allgym_athlete['dob'] ? 'new Date(\'' . $allgym_athlete['dob'] . '\')' : '' }}"
                                        :wrapper-class="'flex-grow-1'" name="dob" :format="'MM/dd/yyyy'" placeholder="mm/dd/yyyy"
                                        :bootstrap-styling="true" :typeable="true" :required="true" id="a_date" onchange="changedData('a_date')">
                                    </datepicker>
                                </div>
                                <label for="a_nga_level_id">NGA Level</label>
                                <select id="a_nga_level_id" class="form-control" name="nga_level_id" required>
                                    <option value="">(Choose below ...)</option>
                                    @foreach ($bodies['NGA']['categories'] as $categoryName => $category)
                                    <optgroup class="bg-secondary" label="{{ $categoryName }}"
                                                    data-male="{{ $category['male'] ? 1 : 0 }}"
                                                    data-female="{{ $category['female'] ? 1 : 0 }}">
                                                </optgroup>
                                                @foreach ($category['levels'] as $level)
                                                <option class="ml-3" value="{{ $level->id }}"
                                                {{ $level->id == $allgym_athlete['nga_level_id'] ? 'selected' : '' }}
                                                data-male="{{ $category['male'] ? 1 : 0 }}"
                                                data-female="{{ $category['female'] ? 1 : 0 }}">
                                                {{ $level->name }}
                                            </option>
                                        @endforeach
                                    @endforeach
                                </select>
                                <label for="a_tshirt_size_id">T-Shirt Size</label>
                                <select id="a_tshirt_size_id" class="form-control" name="tshirt_size_id">
                                    <option value="">(Choose below ...)</option>
                                    @foreach ($tshirt_chart->sizes as $size)
                                    <option value="{{ $size->id }}"
                                    {{ $size->id == $allgym_athlete['tshirt_size_id'] ? 'selected' : '' }}>
                                    {{ $size->size }}
                                </option>
                                @endforeach
                            </select>
                                <input type="hidden" name="isUpdate" value="1">
                                <input type="hidden" name="nga_no" value="{{ $allgym_athlete['nga_no'] }}"><br>
                                <input type="submit" name="" id="" value="Force Save AllGym Data" class="btn btn-primary">
                            </form>
                        </div>
                        <div class="col-md-6">
                            <b>NGA Data</b><br>
                            <div> 
                                <label for="n_first_name">First Name</label>
                                <input type="text" id="n_first_name"  value="{{ $nga_athlete['FirstName'] }}" class="form-control" readonly>
                                <label for="n_last_name">Last Name</label>
                                <input type="text" id="n_last_name" value="{{ $nga_athlete['LastName'] }}" class="form-control" readonly>
                                <label for="n_gender">Gender</label>
                                <input type="text" id="n_gender" value="{{ $nga_athlete['Gender'] == 'F' ? 'Female' : 'Male' }}" readonly class="form-control">
                                <label for="">DOB</label>
                                <input type="text" id="n_dob" value="{{ $newDateStringNGA }}" readonly class="form-control">
                                <label for="">NGA Level</label>
                                <input type="text" class="form-control" id="n_nga_level" value="{{ $nga_athlete['Level'] }}" readonly>
                                <label for="">T-Shirt Size</label>
                                <input type="text" class="form-control" value="{{ $nga_athlete['TShirt'] }}" readonly>
                            </div>
                        </div>
                    </div>
                    
                    <div class="modal-footer pb-0 pr-0 pl-0 -1 d-block">
                        <div class="text-info small mb-3">
                            <span class="fas fa-info-circle mt-2 mb-2"></span> 
                            There is currently data that does not match the NGA website. If you save AllGym data
                            it will not be reflected on the NGA website. We suggest logging into your NGA account
                            and editing your data so it matches.     
                        </div>
                    </div>
                </div>
            @endif
            </div>
        </div>
    </div>

    <div class="content-main p-3">
        <div class="row">
            <div class="col">
                <form method="POST" action="{{ route('gyms.athletes.update', ['gym' => $gym, 'athlete' => $athlete]) }}">
                    @csrf
                    @method('PATCH')

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
                                    id="is_us_citizen" {{ $athlete->is_us_citizen ? 'checked' : ''}}>
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
                                    {{ $athlete->usag_no != null ? 'checked' : ''}}>
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
                                        {{ $athlete->usag_active ? 'checked' : ''}}>
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
                                    {{ $athlete->usaigc_no != null ? 'checked' : ''}}>
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
                                        {{ $athlete->usaigc_active ? 'checked' : ''}}>
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
                                    {{ $athlete->aau_no != null ? 'checked' : ''}}>
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
                                        {{ $athlete->aau_active ? 'checked' : ''}}>
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
                                    {{ $athlete->nga_no != null ? 'checked' : ''}}>
                                <label class="form-check-label" for="nga_checkbox">
                                    NGA Membership
                                </label>
                            </div>

                            <div id="nga-membership-fields">
                                <div class="mb-3">
                                    <input id="nga_no" type="text" placeholder="Enter your NGA membership No."
                                           class="form-control form-control-sm @error('nga_no') is-invalid @enderror"
                                           name="nga_no" data-body="nga" value="{{ $athlete->nga_no }}"
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
                                                        {{ $level->id == $athlete->nga_level_id ? 'selected' : '' }}
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
                                        {{ $athlete->nga_active ? 'checked' : ''}}>
                                    <label for="nga_active">
                                        This membership is active
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col text-right">
                            <div class="mb-2 mr-2 d-inline-block">
                                <a href="{{ route('gyms.athletes.index', ['gym' => $gym]) }}"
                                    class="btn btn-primary">
                                    <span class="fas fa-long-arrow-alt-left"></span> Back
                                </a>
                            </div>

                            <div class="mb-2 mr-2 d-inline-block">
                                <button type="submit" class="btn btn-success" id="btnSave">
                                    <span class="fas fa-save"></span> Save
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

    </div>

    <!-- echo '<script>$("#comparison-athlete-modal").modal(\'show\')</script>'; -->
@endsection

@section('scripts-main')
    <script src="{{ mix('js/athlete/athlete-edit.js') }}"></script>
    <script>
        $(document).ready(function(){
            var isError = '<?php echo json_encode(Session::has('ngaData')); ?>';
            //console.log(isError);
            if(isError == 'true')
            {
                $("#comparison-athlete-modal").modal('show');
            }
            $('.comparison-athlete-modal-close').click(e => {
                $('#comparison-athlete-modal').modal('hide');
            });
            changedData = function(id){
                // a_first_name a_last_name a_gender a_date
                // n_first_name n_last_name n_gender n_dob
                var parentC = $('#'+id).val();
                //console.log(parentC);
                if(id == 'a_first_name')
                {
                    if(parentC == $('#n_first_name').val())
                        $('#'+id).removeClass("alert-danger").addClass("alert-success");
                    else
                        $('#'+id).removeClass("alert-success").addClass("alert-danger");

                }else if(id == 'a_last_name'){
                    if(parentC == $('#n_last_name').val())
                        $('#'+id).removeClass("alert-danger").addClass("alert-success");
                    else
                        $('#'+id).removeClass("alert-success").addClass("alert-danger");
                }else if(id == 'a_gender'){
                    if(parentC == $('#n_gender').val().toLowerCase())
                        $('#'+id).removeClass("alert-danger").addClass("alert-success");
                    else
                        $('#'+id).removeClass("alert-success").addClass("alert-danger");
                }else if(id == 'a_date'){
                    if(parentC == $('#n_dob').val())
                        $('#'+id).removeClass("alert-danger").addClass("alert-success");
                    else
                        $('#'+id).removeClass("alert-success").addClass("alert-danger");
                }

                //console.log(id);
            }
            $('#a_gender').change(e => {
                let selected = e.currentTarget.value;
                let enable = (selected == 'female');

                if (selected != '') {
                    let disallowedCategories = $('optgroup[data-' + selected +  '="0"]');
                    let allowedCategories = $('optgroup[data-' + selected +  '="1"]');

                    let disallowedLevels = $('option[data-' + selected +  '="0"]');
                    let allowedLevels = $('option[data-' + selected +  '="1"]');

                    disallowedCategories.hide();
                    allowedCategories.show();

                    disallowedLevels.hide();
                    allowedLevels.show();

                    disallowedLevels.each((e,itm) => {
                        if (itm.selected)
                            itm.parentElement.value = '';
                    });
                }
            });
        });

        $('#btnSave').on('submit click',function (){
            $('#gender').attr('disabled', false);
            $('#tshirt_size_id').attr('disabled', false);
            $('#leo_size_id').attr('disabled', false);
            $('#is_us_citizen').attr('disabled', false);
            $('.edit-dob').attr('disabled', false).css({'background-color': '#e9ecef', 'opacity': 1});
        });
    </script>
@endsection
