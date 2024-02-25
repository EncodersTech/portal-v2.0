@extends('layouts.main')

@section('content-header')
    <span class="fas fa-fw fa-running"></span>
    Edit a Coach
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
    <div class="modal fade" id="comparison-coach-modal" tabindex="-1" role="dialog" baria-labelledby="comparison-coach-modal" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title text-danger">
                        <span class="fas fa-times-circle"></span> Coach Save Failed
                    </h5>
                    <button type="button" class="close comparison-coach-modal-close" aria-label="Close">
                        <span class="fas fa-times" aria-hidden="true"></span>
                    </button>
                </div>

                @if(Session::has('ngaCoachData'))
                <div class="modal-body">
                    @php
                    $allgym_coach = Session::get('ngaCoachData')['allgym'];
                    $nga_coach = Session::get('ngaCoachData')['nga'];
                    $myDateTime = DateTime::createFromFormat('Y-m-d  h:i:s', $allgym_coach['dob']);
                    $newDateString = $myDateTime->format('m/d/Y');
                    
                    $myDateTimeNGA = DateTime::createFromFormat('m/d/Y',  $nga_coach['DOB']);
                    $newDateStringNGA = $myDateTimeNGA->format('m/d/Y');
                    @endphp
                    <div class="row">
                        <div class="col-md-6">
                            <b>Allgymnastics</b><br>    
                            <form method="POST" action="{{ route('gyms.coaches.update', ['gym' => $gym, 'coach' => $allgym_coach]) }} " >
                                @csrf
                                @method('PATCH')
                                <label for="a_first_name">First Name</label>
                                <input required type="text" onchange="changedData('a_first_name')" name="first_name" id="a_first_name" value="{{ $allgym_coach['first_name'] }}" class="form-control {{ $allgym_coach['first_name'] != $nga_coach['FirstName'] ? 'alert-danger' : '' }}">
                                <label for="a_last_name">Last Name</label>
                                <input required type="text" onchange="changedData('a_last_name')" name="last_name" id="a_last_name" value="{{ $allgym_coach['last_name'] }}" class="form-control {{ $allgym_coach['last_name'] != $nga_coach['LastName'] ? 'alert-danger' : '' }}">
                                <label for="a_gender">Gender</label>
                                <select id="a_gender" onchange="changedData('a_gender')" class="form-control {{ strtoupper($allgym_coach['gender'][0]) != $nga_coach['Gender'] ? 'alert-danger' : '' }}"
                                        name="gender" required > 
                                    <option value="">(Choose below ...)</option>
                                    <option value="female" {{ $allgym_coach['gender'] == 'female' ? 'selected' : '' }}>
                                        Female
                                    </option>
                                    <option value="male" {{ $allgym_coach['gender'] == 'male' ? 'selected' : '' }}>
                                        Male
                                    </option>
                                </select>
                                <label for="a_date">DOB</label>
                                <div class="input-group input-group-sm">
                                    <datepicker :input-class="'form-control {{ $newDateString != $newDateStringNGA ? 'alert-danger' : '' }}'"
                                    :value="{{ $allgym_coach['dob'] ? 'new Date(\'' . $allgym_coach['dob'] . '\')' : '' }}"
                                        :wrapper-class="'flex-grow-1'" name="dob" :format="'MM/dd/yyyy'" placeholder="mm/dd/yyyy"
                                        :bootstrap-styling="true" :typeable="true" :required="true" id="a_date" onchange="changedData('a_date')">
                                    </datepicker>
                                </div>
                            </select>
                                <input type="hidden" name="isUpdate" value="1">
                                <input type="hidden" name="nga_no" value="{{ $allgym_coach['nga_no'] }}"><br>
                                <input type="submit" name="" id="" value="Force Save AllGym Data" class="btn btn-primary">
                            </form>
                        </div>
                        <div class="col-md-6">
                            <b>NGA Data</b><br>
                            <div> 
                                <label for="n_first_name">First Name</label>
                                <input type="text" id="n_first_name"  value="{{ $nga_coach['FirstName'] }}" class="form-control" readonly>
                                <label for="n_last_name">Last Name</label>
                                <input type="text" id="n_last_name" value="{{ $nga_coach['LastName'] }}" class="form-control" readonly>
                                <label for="n_gender">Gender</label>
                                <input type="text" id="n_gender" value="{{ $nga_coach['Gender'] == 'F' ? 'Female' : 'Male' }}" readonly class="form-control">
                                <label for="">DOB</label>
                                <input type="text" id="n_dob" value="{{ $newDateStringNGA }}" readonly class="form-control">
                            </div>
                        </div>
                    </div>
                    
                    <div class="modal-footer pb-0 pr-0 pl-0 -1 d-block">
                        <div class="text-info small mb-3">
                            <span class="fas fa-info-circle mt-2 mb-2"></span> 
                            There is currently data that does not match NGA website. If you Save AllGymnastics data, the coach will not be validated with NGA.
                        </div>
                    </div>
                </div>
            @endif
            </div>
        </div>
    </div>

    <div class="content-main p-3">
        @if ($coach->usaigc_no != null)
        <div class="alert alert-primary"><strong class="d-block mb-2"><span class="fas fa-exclamation-circle"></span>
        Adding a new Meet
        </strong>
    </div>
        @endif
        <div class="row">
            <div class="col">
                <form method="POST" action="{{ route('gyms.coaches.update', ['gym' => $gym, 'coache' => $coach]) }}">
                    @csrf
                    @method('PATCH')

                    <div class="row">
                        <div class="col">
                            <h5 class="border-bottom"><span class="fas fa-fw fa-user-plus"></span> Coach Info</h5>
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
                                        name="first_name" value="{{ $coach->first_name }}" placeholder="First Name"
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
                                        name="last_name" value="{{ $coach->last_name }}" placeholder="Last Name"
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
                                    <option value="female" {{ $coach->gender == 'female' ? 'selected' : '' }}>
                                        Female
                                    </option>
                                    <option value="male" {{ $coach->gender == 'male' ? 'selected' : '' }}>
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
                                    :value="{{ $coach->dob ? 'new Date(\'' . $coach->dob . '\')' : 'date' }}"
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
                                            {{ $size->id == $coach->tshirt_size_id ? 'selected' : '' }}>
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
                    </div>

                    <div class="row">
                        <div class="col">
                            <h5 class="border-bottom"><span class="fas fa-fw fa-receipt"></span> Memberships</h5>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-lg mb-1 pt-1">
                            <div class="form-check mb-2">
                                <input id="usag_checkbox" class="form-check-input coach-membership-checkbox"
                                    name="usag_checkbox" type="checkbox" data-body="usag"
                                    {{ $coach->usag_no != null ? 'checked' : ''}}>
                                <label class="form-check-label" for="usag_checkbox">
                                    USAG Membership
                                </label>
                            </div>

                            <div id="usag-membership-fields">
                                <div class="mb-3">
                                    <input id="usag_no" type="text" placeholder="Enter your USAG membership No."
                                            class="form-control form-control-sm @error('usag_no') is-invalid @enderror"
                                            name="usag_no" data-body="usag" value="{{ $coach->usag_no }}"
                                            required autocomplete="usag_no" disabled>
                                    @error('usag_no')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="usag_expiry">
                                        <span class="fas fa-fw fa-calendar-check"></span>
                                        Professional No. Expiry Date
                                    </label>
                                    <datepicker input-class="form-control form-control-sm vue-date-picker-fixer"
                                        :value="{{ $coach->usag_expiry != null ? 'new Date(\'' . $coach->usag_expiry . '\')' : 'null' }}"
                                        :wrapper-class="'flex-grow-1'" name="usag_expiry" :format="'MM/dd/yyyy'" placeholder="mm/dd/yyyy"
                                        :bootstrap-styling="true" :typeable="true" :disabled="usagDatesDisabled"
                                        placeholder="Professional No. Expiry Date">
                                    </datepicker>
                                    @error('usag_expiry')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="usag_safety_expiry">
                                        <span class="fas fa-fw fa-calendar-check"></span>
                                        Safety Certification Expiry Date
                                    </label>
                                    <datepicker input-class="form-control form-control-sm vue-date-picker-fixer"
                                        :value="{{ $coach->usag_safety_expiry != null ? 'new Date(\'' . $coach->usag_safety_expiry . '\')' : 'null' }}"
                                        :wrapper-class="'flex-grow-1'" name="usag_safety_expiry" :format="'MM/dd/yyyy'" placeholder="mm/dd/yyyy"
                                        :bootstrap-styling="true" :typeable="true" :disabled="usagDatesDisabled"
                                        placeholder="Safety Certification Expiry Date">
                                    </datepicker>
                                    @error('usag_safety_expiry')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="usag_safesport_expiry">
                                        <span class="fas fa-fw fa-calendar-check"></span>
                                        SafeSport Expiry Date
                                    </label>
                                    <datepicker input-class="form-control form-control-sm vue-date-picker-fixer"
                                        :value="{{ $coach->usag_safesport_expiry != null ? 'new Date(\'' . $coach->usag_safesport_expiry . '\')' : 'null' }}"
                                        :wrapper-class="'flex-grow-1'" name="usag_safesport_expiry" :format="'MM/dd/yyyy'" placeholder="mm/dd/yyyy"
                                        :bootstrap-styling="true" :typeable="true" :disabled="usagDatesDisabled"
                                        placeholder="SafeSport Expiry Date">
                                    </datepicker>
                                    @error('usag_safesport_expiry')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="usag_background_expiry">
                                        <span class="fas fa-fw fa-calendar-check"></span>
                                        Background Expiry Date
                                    </label>
                                    <datepicker input-class="form-control form-control-sm vue-date-picker-fixer"
                                        :value="{{ $coach->usag_background_expiry != null ? 'new Date(\'' . $coach->usag_background_expiry . '\')' : 'null' }}"
                                        :wrapper-class="'flex-grow-1'" name="usag_background_expiry" :format="'MM/dd/yyyy'" placeholder="mm/dd/yyyy"
                                        :bootstrap-styling="true" :typeable="true" :disabled="usagDatesDisabled"
                                        placeholder="Background Expiry Date">
                                    </datepicker>
                                    @error('usag_background_expiry')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>

                                <div>
                                    <input type="checkbox" id="usag_u100_certification" name="usag_u100_certification"
                                        {{ $coach->usag_u100_certification ? 'checked' : ''}}>
                                    <label for="usag_u100_certification">
                                            U100 Certification
                                    </label>
                                </div>

                                <div>
                                    <input type="checkbox" id="usag_active" name="usag_active"
                                        {{ $coach->usag_active ? 'checked' : ''}}>
                                    <label for="usag_active">
                                            This membership is active
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg mb-1 pt-1 column-split">
                            <div class="form-check mb-2">
                            <input id="usaigc_checkbox" class="form-check-input coach-membership-checkbox"
                                    name="usaigc_checkbox" type="checkbox" data-body="usaigc"
                                    {{ $coach->usaigc_no != null ? 'checked' : ''}}>
                                <label class="form-check-label" for="usaigc_checkbox">
                                    USAIGC Membership
                                </label>
                            </div>

                            <div id="usaigc-membership-fields">
                                <div class="mb-3">
                                    <input id="usaigc_no" type="text" placeholder="Enter your USAIGC membership No."
                                            class="form-control form-control-sm @error('usaigc_no') is-invalid @enderror"
                                            name="" data-body="usaigc" value="{{ $coach->usaigc_no }}"
                                            required autocomplete="usaigc_no" disabled readonly>
                                    @error('usaigc_no')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                                <div>
                                    <input type="checkbox" id="usaigc_background_check" name="usaigc_background_check"
                                        {{ $coach->usaigc_background_check ? 'checked' : ''}}>
                                    <label for="usaigc_background_check">
                                        USAIGC Background Check
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg mb-1 pt-1 column-split">
                            <div class="form-check mb-2">
                                <input id="aau_checkbox" class="form-check-input coach-membership-checkbox"
                                    name="aau_checkbox" type="checkbox" data-body="aau"
                                    {{ $coach->aau_no != null ? 'checked' : ''}}>
                                <label class="form-check-label" for="aau_checkbox">
                                    AAU Membership
                                </label>
                            </div>

                            <div id="aau-membership-fields">
                                <div class="mb-3">
                                    <input id="aau_no" type="text" placeholder="Enter your AAU membership No."
                                            class="form-control form-control-sm @error('aau_no') is-invalid @enderror"
                                            name="aau_no" data-body="aau" value="{{ $coach->aau_no }}"
                                            required autocomplete="aau_no" disabled>
                                    @error('aau_no')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="col-lg mb-1 pt-1 column-split">
                            <div class="form-check mb-2">
                                <input id="nga_checkbox" class="form-check-input coach-membership-checkbox"
                                       name="nga_checkbox" type="checkbox" data-body="nga"
                                    {{ $coach->nga_no != null ? 'checked' : ''}}>
                                <label class="form-check-label" for="nga_checkbox">
                                    NGA Membership
                                </label>
                            </div>

                            <div id="nga-membership-fields">
                                <div class="mb-3">
                                    <input id="nga_no" type="text" placeholder="Enter your NGA membership No."
                                           class="form-control form-control-sm @error('nga_no') is-invalid @enderror"
                                           name="nga_no" data-body="nga" value="{{ $coach->nga_no }}"
                                           required autocomplete="nga_no" disabled>
                                    @error('nga_no')
                                    <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col text-right">
                            <div class="mb-2 mr-2 d-inline-block">
                                <a href="{{ route('gyms.coaches.index', ['gym' => $gym]) }}"
                                    class="btn btn-primary">
                                    <span class="fas fa-long-arrow-alt-left"></span> Back
                                </a>
                            </div>

                            <div class="mb-2 mr-2 d-inline-block">
                                <button type="submit" class="btn btn-success">
                                    <span class="fas fa-save"></span> Save
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

    </div>

@endsection

@section('scripts-main')
    <script src="{{ mix('js/coach/coach-edit.js') }}"></script>
    <script>
        $(document).ready(function(){
            var isError = '<?php echo json_encode(Session::has('ngaCoachData')); ?>';
            //console.log(isError);
            if(isError == 'true')
            {
                $("#comparison-coach-modal").modal('show');
            }
            $('.comparison-coach-modal-close').click(e => {
                $('#comparison-coach-modal').modal('hide');
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
    </script>
@endsection
