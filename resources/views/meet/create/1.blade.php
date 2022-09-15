<div class="row">
    <div class="col">
        <form method="POST" action="{{ route('gyms.meets.store.1', ['gym' => $gym, 'temporary' => $tm]) }}">
            @csrf

            <div class="row">
                <div class="col">
                    <h5 class="border-bottom"><span class="fas fa-fw fa-align-justify"></span> General Info</h5>
                </div>
            </div>

            <div class="row">
                <div class="col-lg mb-3">
                    <label for="name" class="mb-1">
                        <span class="fas fa-fw fa-calendar-week"></span>
                        Meet Name <span class="text-danger">*</span>
                    </label>
                    <input id="name" name="name" autocomplete="name" placeholder="Meet name"
                           class="form-control form-control-sm @error('name') is-invalid @enderror"
                           value="{{ $tm->oldOrValue('name') }}"
                           type="text" required autofocus>
                    @error('name')
                    <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>

                <div class="col-lg mb-3">
                    <label for="website" class="mb-1">
                        <span class="fas fa-fw fa-link"></span>
                        Meet Website <span class="text-danger">*</span>
                    </label>
                    <input id="website" name="website" autocomplete="website" placeholder="https://example.com"
                           class="form-control form-control-sm @error('website') is-invalid @enderror"
                           value="{{ $tm->oldOrValue('website') }}"
                           required>
                    @error('website')
                    <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>
            </div>

            <div class="row">
                <div class="col-lg mb-3">
                    <label for="description" class="mb-1">
                        <span class="fas fa-fw fa-info-circle"></span>
                        Description <span class="text-danger">*</span>
                    </label>
                    <textarea id="description" name="description" autocomplete="description" placeholder="Description ..."
                              class="form-control form-control-sm @error('description') is-invalid @enderror"
                              required>{{ $tm->oldOrValue('description') }}</textarea>
                    @error('description')
                    <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>
            </div>

            <div class="row">
                <div class="col-lg mb-3">
                    <label for="start_date" class="mb-1"
                           ref="oldStartDate" data-value="{{ $tm->oldOrValue('start_date') }}">
                        <span class="fas fa-fw fa-calendar-alt"></span>
                        Meet Start Date <span class="text-danger">*</span>
                    </label>
                    <datepicker :input-class="'form-control form-control-sm bg-white'" :value="startDate"
                                @selected="startDateChanged" @input="startDateChanged"
                                :wrapper-class="'flex-grow-1'" name="start_date" :format="'MM/dd/yyyy'" placeholder="mm/dd/yyyy"
                                :bootstrap-styling="true" :typeable="true" :required="true">
                    </datepicker>
                    @error('start_date')
                    <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>

                <div class="col-lg mb-3">
                    <label for="end_date" class="mb-1"
                           ref="oldEndDate" data-value="{{ $tm->oldOrValue('end_date') }}">
                        <span class="fas fa-fw fa-calendar-alt"></span>
                        Meet End Date <span class="text-danger">*</span>
                    </label>
                    <datepicker :input-class="'form-control form-control-sm bg-white'" :value="endDate"
                                :wrapper-class="'flex-grow-1'" name="end_date" :format="'MM/dd/yyyy'" placeholder="mm/dd/yyyy"
                                :bootstrap-styling="true" :typeable="true" :required="true">
                    </datepicker>
                    @error('end_date')
                    <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>
            </div>

            <div class="row">
                <div class="col-lg mb-3">
                    <label for="equipement" class="mb-1">
                        <span class="fas fa-fw fa-dumbbell"></span>
                        Equipment <span class="text-danger">*</span>
                    </label>
                    <textarea id="equipement" name="equipement" autocomplete="equipement" placeholder="Equipment ..."
                              class="form-control form-control-sm @error('equipement') is-invalid @enderror"
                              required>{{ $tm->oldOrValue('equipement') }}</textarea>
                    @error('equipement')
                    <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>
            </div>

            <div class="row">
                <div class="col-lg mb-3">
                    <label for="notes" class="mb-1">
                        <span class="fas fa-fw fa-info-circle"></span> Notes
                    </label>
                    <textarea id="notes" name="notes" autocomplete="notes" placeholder="Notes ..."
                              class="form-control form-control-sm @error('notes') is-invalid @enderror"
                    >{{ $tm->oldOrValue('notes') }}</textarea>
                    @error('notes')
                    <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>

                <div class="col-lg mb-3">
                    <label for="special_annoucements" class="mb-1">
                        <span class="fas fa-fw fa-info-circle"></span> Special Annoucements
                    </label>
                    <textarea id="special_annoucements" name="special_annoucements" autocomplete="special_annoucements" placeholder="Special Annoucements ..."
                              class="form-control form-control-sm @error('special_annoucements') is-invalid @enderror"
                    >{{ $tm->oldOrValue('special_annoucements') }}</textarea>
                    @error('special_annoucements')
                    <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>
            </div>

            <div class="row">
                <div class="col-lg mb-3">
                    <div class="form-check mb-1">
                        <input class="form-check-input" type="checkbox"
                               name="tshirt_size_chart_checkbox" id="tshirt_size_chart_checkbox"
                                {{
                                    (old('tshirt_size_chart_checkbox') != null) ||
                                    ($tm->tshirt_size_chart_id != null) ?
                                    'checked' : ''
                                }}>
                        <label class="form-check-label" for="tshirt_size_chart_checkbox">
                            <span class="fas fa-fw fa-tshirt"></span> Require T-shirt sizes at registration
                        </label>
                    </div>
                    <select id="tshirt_size_chart_id" name="tshirt_size_chart_id" required
                            class="form-control form-control-sm @error('tshirt_size_chart_id') is-invalid @enderror">

                        @foreach ($tshirt_charts as $chart)
                            <option value="{{ $chart->id }}"
                                    {{
                                        $chart->id == $tm->oldOrValue('tshirt_size_chart_id') ?
                                        'selected' :
                                        ($chart->is_default ? 'selected' : '')
                                    }}>
                                {{ $chart->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('tshirt_size_chart_id')
                    <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>

                <div class="col-lg mb-3">
                    <div class="form-check mb-1">
                        <input class="form-check-input" type="checkbox"
                               name="leo_size_chart_checkbox" id="leo_size_chart_checkbox"
                                {{
                                    (old('leo_size_chart_checkbox') != null) ||
                                    ($tm->leo_size_chart_id != null) ?
                                    'checked' : ''
                                }}>
                        <label class="form-check-label" for="leo_size_chart_checkbox">
                            <span class="fas fa-fw fa-female"></span> Require Leotard sizes at registration
                        </label>
                    </div>

                    <select id="leo_size_chart_id" name="leo_size_chart_id" required
                            class="form-control form-control-sm @error('leo_size_chart_id') is-invalid @enderror">
                        @foreach ($leo_charts as $chart)
                            <option value="{{ $chart->id }}"
                                    {{
                                        $chart->id == $tm->oldOrValue('leo_size_chart_id') ?
                                        'selected' :
                                        ($chart->is_default ? 'selected' : '')
                                    }}>
                                {{ $chart->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('leo_size_chart_id')
                    <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>
            </div>
            <div class="row">
                <div class="col">
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox"
                               name="show_participate" id="show_participate_clubs" {{
                                (old('show_participate') != false) ||
                                ($tm->show_participate_clubs != false) ?
                                'checked' : ''
                            }}>
                        <label class="form-check-label" for="show_participate_clubs">
                            Show Participating Clubs on Browse Meets Page
                        </label>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col">
                    <div class="form-check mb-3">
                        <input class="form-check-input" @if(!\App\Models\Setting::getSetting(\App\Models\Setting::ENABLED_FEATURED_MEET_FEE)->value) disabled @endif type="checkbox"
                               name="is_featured" id="isFeaturedMeet" {{
                                (old('is_featured') != false) ||
                                ($tm->is_featured != false) ?
                                'checked' : ''
                            }}>
                        <label class="form-check-label" for="isFeaturedMeet">
                            Featured Meet
                        </label><br>
                        <small style="margin-left: -20px"><i class="fas fa-info-circle"></i> By enabling featured meets you agree to our Featured Meets <a href="{{ App\Models\Setting::getSetting(App\Models\Setting::TERMS_SERVICE_LINK)->value }}">terms of service</a>.</small>
                    </div>
                </div>
            </div>
            @if (false)
                @include('meet.create.mso')
            @endif

            <div class="row">
                <div class="col">
                    <h5 class="border-bottom"><span class="fas fa-fw fa-money-bill-alt"></span>
                        Admission <span class="text-danger">*</span>
                    </h5>
                </div>
            </div>

            <div class="row">
                <div class="col">
                    <admissions singular="admission" plural="admissions" field="admissions"
                                :values="{{
                        $tm->oldOrValue('admissions') != null ?
                        $tm->oldOrValue('admissions') :
                        '[]'
                    }}">
                    </admissions>
                    @error('admissions')
                    <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>
            </div>

            <div class="row">
                <div class="col">
                    <h5 class="border-bottom">
                        <div class="d-flex flex-row flex-nowrap">
                            <div class="flex-grow-1">
                                <span class="fas fa-fw fa-building"></span> Venue
                            </div>
                            <div class="ml-2">
                                <button type="button" class="btn btn-sm btn-link" id="venue_use_gym_address">
                                    <span class="fas fa-copy"></span> Fill with this gym's address
                                </button>
                            </div>
                        </div>
                    </h5>
                </div>
            </div>

            <div class="row">
                <div class="col-lg mb-3">
                    <label for="venue_name" class="mb-1">
                        <span class="fas fa-fw fa-building"></span>
                        Venue Name <span class="text-danger">*</span>
                    </label>
                    <input id="venue_name" name="venue_name" autocomplete="venue_name" placeholder="Venue name"
                           class="form-control form-control-sm @error('venue_name') is-invalid @enderror"
                           value="{{ $tm->oldOrValue('venue_name') }}"
                           type="text" data-venue-gym="{{ $gym->name }}" required>
                    @error('venue_name')
                    <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>

                <div class="col-lg mb-3">
                    <label for="venue_website" class="mb-1">
                        <span class="fas fa-fw fa-link"></span>
                        Venue Website <span class="text-danger">*</span>
                    </label>
                    <input id="venue_website" name="venue_website" autocomplete="venue_website" placeholder="https://example.com"
                           class="form-control form-control-sm @error('venue_website') is-invalid @enderror"
                           value="{{ $tm->oldOrValue('venue_website') }}"
                           type="text" data-venue-gym="{{ $gym->website }}" required>
                    @error('venue_website')
                    <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>
            </div>

            <div class="row">
                <div class="col-lg mb-3">
                    <label for="venue_addr_1" class="mb-1">
                        <span class="fas fa-fw fa-map-marker-alt"></span>
                        Address Line 1 <span class="text-danger">*</span>
                    </label>
                    <input id="venue_addr_1" name="venue_addr_1" autocomplete="venue_addr_1" placeholder="Venue Address Line 1"
                           class="form-control form-control-sm @error('venue_addr_1') is-invalid @enderror"
                           value="{{ $tm->oldOrValue('venue_addr_1') }}"
                           data-venue-gym="{{ $gym->addr_1 }}"
                           type="text">
                    @error('venue_addr_1')
                    <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>

                <div class="col-lg mb-3">
                    <label for="venue_addr_2" class="mb-1">
                        <span class="fas fa-fw fa-map-marker"></span>
                        Address Line 2
                    </label>
                    <input id="venue_addr_2" name="venue_addr_2" autocomplete="venue_addr_2" placeholder="Venue Address Line 1"
                           class="form-control form-control-sm @error('venue_addr_2') is-invalid @enderror"
                           value="{{ $tm->oldOrValue('venue_addr_2') }}"
                           data-venue-gym="{{ $gym->addr_2 }}"
                           type="text">
                    @error('venue_addr_2')
                    <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>
            </div>

            <div class="row">
                <div class="col-lg mb-3">
                    <label for="venue_city" class="mb-1">
                        <span class="fas fa-fw fa-map-marked-alt"></span>
                        City <span class="text-danger">*</span>
                    </label>
                    <input id="venue_city" name="venue_city" autocomplete="venue_city" placeholder="Venue city"
                           class="form-control form-control-sm @error('venue_city') is-invalid @enderror"
                           value="{{ $tm->oldOrValue('venue_city') }}"
                           data-venue-gym="{{ $gym->city }}" type="text" required>
                    @error('venue_city')
                    <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>

                <div class="col-lg mb-3">
                    <label for="venue_state_id" class="mb-1">
                        <span class="fas fa-fw fa-map-marked"></span>
                        State <span class="text-danger">*</span>
                    </label>
                    <select id="venue_state_id" name="venue_state_id" required
                            data-venue-gym="{{ $gym->state->code != 'WW' ? $gym->state->code : '' }}"
                            class="form-control form-control-sm @error('venue_state_id') is-invalid @enderror">
                        <option value="">(Choose below ...)</option>
                        @foreach ($states as $state)
                            <option value="{{ $state->code }}"
                                    {{
                                        ($tm->oldOrValue('venue_state_id') == $state->code) ||
                                        ($tm->oldOrValue('venue_state_id') == $state->id) ?
                                        'selected' : ''
                                    }}>
                                {{ $state->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('venue_state_id')
                    <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>

                <div class="col-lg mb-3">
                    <label for="venue_zipcode" class="mb-1">
                        <span class="fas fa-fw fa-map-marked-alt"></span>
                        Zip code <span class="text-danger">*</span>
                    </label>
                    <input id="venue_zipcode" name="venue_zipcode" autocomplete="venue_zipcode" placeholder="Venue zipcode"
                           class="form-control form-control-sm @error('venue_zipcode') is-invalid @enderror"
                           value="{{ $tm->oldOrValue('venue_zipcode') }}"
                           data-venue-gym="{{ $gym->zipcode }}" type="text" required>
                    @error('venue_zipcode')
                    <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>
            </div>

            <div class="row">
                <div class="col">
                    <div class="text-right">
                        <button class="btn btn-success" type="submit">
                            Next <span class="fas fa-long-arrow-alt-right"></span>
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
