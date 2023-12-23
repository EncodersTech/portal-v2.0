<div class="row">
    <div class="col">
        <form method="POST" ref="competitionSettingsForm"
            action="{{ route('gyms.meets.update.3', ['gym' => $gym, 'meet' => $meet]) }}">
            @csrf
            @method('PATCH')

            <div class="row">
                <div class="col">
                    <h5 class="border-bottom">
                        <span class="fas fa-fw fa-clipboard-check"></span>
                        Categories and Sanctions
                        <span class="text-danger">*</span>
                    </h5>
                </div>
            </div>

            <div class="row">
                <div class="col">
                    <div class="alert alert-info mb-1">
                        <span class="fas fa-info-circle"></span> You cannot add levels to categories that require a
                        sanction.
                        Levels in said categories can be added once we receive a sanction from the associated
                        sanctioning body.
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col">
                    <div class="alert alert-warning">
                        <span class="fas fa-exclamation-triangle"></span> Levels added to categories that already have
                        registrations
                        cannot be changed once saved. Please make sure that the changes you're making are final.
                    </div>
                </div>
            </div>

            <div class="small" :class="{ 'd-none': !isLoading }">
                <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true">
                </span> Loading, please wait ...
            </div>

            <div class="row" v-if="!isLoading">
                <div class="col">
                    <ag-meet-categories :available_bodies="bodyCategories" :loading="isLoading" field="categories"
                        :error="isError" :errormessage="errorMessage" @meet-categories-changed="onMeetCategoriesChanged"
                        :restricted="{{ $restricted_edit ? 'true' : 'false' }}" :initial="{{ $meet->categories }}"
                        :requires_sanction="{{ $required_sanctions }}" :restricted_bodies="{{ $restricted_bodies }}">
                    </ag-meet-categories>
                    <div class="text-info font-weight-bold mb-3">
                        <span class="fas fa-info-circle"></span> Please select your categories
                        above first before moving to choose levels. When you uncheck a category, levels from
                        that category will be removed from the level list.
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col">
                    <label for="meet_competition_format_id">
                        <span class="fas fa-fw fa-align-center"></span>
                        Competition Format
                        <span class="text-danger">*</span>
                    </label>
                </div>
            </div>

            <div class="row">
                <div class="col-lg mb-3">
                    <select id="meet_competition_format_id" name="meet_competition_format_id"
                        class="form-control form-control-sm @error('meet_competition_format_id') is-invalid @enderror"
                        data-other="{{ \App\Models\MeetCompetitionFormat::OTHER }}" required>
                        @foreach ($competition_formats as $format)
                        <option value="{{ $format->id }}" {{
                                    $meet->oldOrValue('meet_competition_format_id') == $format->id ?
                                    'selected' :
                                    ''
                                }}>
                            {{ $format->name }}
                        </option>
                        @endforeach
                    </select>
                    @error('meet_competition_format_id')
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                    @enderror
                </div>
                <div class="col-lg mb-3">
                    <input id="meet_competition_format_other" name="meet_competition_format_other"
                        autocomplete="meet_competition_format_other" placeholder="(Enter your competition format ...)"
                        class="form-control form-control-sm @error('meet_competition_format_other') is-invalid @enderror"
                        value="{{ $meet->oldOrValue('meet_competition_format_other') }}" type="text" required>
                    @error('meet_competition_format_other')
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                    @enderror
                </div>
            </div>

            <div class="row">
                <div class="col-lg mb-3">
                    <label for="team_format" class="mb-1">
                        <span class="fas fa-fw fa-info-circle"></span>
                        Team Format
                    </label>
                    <textarea id="team_format" name="team_format" autocomplete="team_format"
                        placeholder="Team format ..."
                        class="form-control form-control-sm @error('team_format') is-invalid @enderror">{{ $meet->oldOrValue('team_format') }}</textarea>
                    @error('team_format')
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                    @enderror
                </div>
            </div>

            <div class="row mt-3">
                <div class="col">
                    <h5 class="border-bottom">
                        <span class="fas fa-fw fa-layer-group"></span> Levels
                    </h5>
                </div>
            </div>

            <div class="small" :class="{ 'd-none': !isLoading }">
                <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true">
                </span> Loading, please wait ...
            </div>

            <div class="row" v-if="!isLoading">
                <div class="col">
                    <ag-meet-levels :available_bodies="bodies" :category_filters="selected_categories"
                        :loading="isLoading" field="levels" :error="isError" :errormessage="errorMessage"
                        singular="level" plural="levels" :late="{{ $meet->allow_late_registration ? 'true' : 'false' }}"
                        :meet="{{ $meet }}"
                        :restricted="{{ $restricted_edit ? 'true' : 'false' }}" :initial="{{ $meet->jsonLevels }}"
                        :requires_sanction="{{ $required_sanctions }}" :restricted_bodies="{{ $restricted_bodies }}">
                    </ag-meet-levels>
                </div>
            </div>

            <div class="d-flex flex-row flex-nowrap mt-3">
                <div class="flex-grow-1">
                    <a href="{{ route('gyms.meets.index', ['gym' => $gym]) }}" class="btn btn-primary">
                        <span class="fas fa-long-arrow-alt-left"></span> Back
                    </a>
                </div>

                <div class="ml-3">
                    <button class="btn btn-success" type="submit">
                        <span class="fas fa-save"></span> Save
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>