<div class="row">
    <div class="col">
        <form method="POST" ref="competitionSettingsForm"
            action="{{ route('gyms.meets.store.3', ['gym' => $gym, 'temporary' => $tm]) }}">

            @csrf

            <div class="row">
                <div class="col">
                    <div class="alert alert-info">
                        <span class="fas fa-info-circle"></span> If your meet has USA Gymnastics levels,
                        you will not be able to add USAG levels right now. Levels will be sent from USAG
                        once your sanction is completed. Please note, you still must select the appropriate category
                        (i.e. Women's Artistic Gymnastics).<br />
                        You can still add levels from other sanctioning bodies by checking the appropriate organization
                        names below.
                    </div>
                </div>
            </div>

            <div :class="{'d-none': (step != 1)}">
                <div class="row">
                    <div class="col">
                        <h5 class="border-bottom">
                            <span class="fas fa-fw fa-clipboard-check"></span>
                            Categories and Sanctions
                            <span class="text-danger">*</span>
                        </h5>
                    </div>
                </div>

                <div class="small" :class="{ 'd-none': !isLoading }">
                    <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true">
                    </span> Loading, please wait ...
                </div>

                <div class="row" v-if="!isLoading">
                    <div class="col">
                        <ag-meet-categories :available_bodies="bodyCategories" :loading="isLoading" field="categories"
                            :error="isError" :errormessage="errorMessage"
                            @meet-categories-changed="onMeetCategoriesChanged" :initial="{{
                                $tm->oldOrValue('categories') != null ?
                                $tm->oldOrValue('categories') :
                                '[]'
                            }}" :requires_sanction="{{ $required_sanctions }}">
                        </ag-meet-categories>
                    </div>
                </div>
                <input type="hidden" name="sanction_body_no" id="sanction_body_no" />

                <div class="text-info font-weight-bold mb-3">
                    <span class="fas fa-info-circle"></span> Please select your categories
                    above first before moving to choose levels. When you uncheck a category, levels from
                    that category will be removed from the level list.
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
                            ref='meetCompetitionFormatId'
                            class="form-control form-control-sm @error('meet_competition_format_id') is-invalid @enderror"
                            data-other="{{ \App\Models\MeetCompetitionFormat::OTHER }}" required>
                            @foreach ($competition_formats as $format)
                            <option value="{{ $format->id }}" {{
                                        $tm->oldOrValue('meet_competition_format_id') == $format->id ?
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
                            ref='meetCompetitionFormatOther' autocomplete="meet_competition_format_other"
                            placeholder="(Enter your competition format ...)"
                            class="form-control form-control-sm @error('meet_competition_format_other') is-invalid @enderror"
                            value="{{ $tm->oldOrValue('meet_competition_format_other') }}" type="text" required>
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
                        <textarea id="team_format" name="team_format" autocomplete="team_format" ref='teamFormat'
                            placeholder="Team format ..."
                            class="form-control form-control-sm @error('team_format') is-invalid @enderror">{{ $tm->oldOrValue('team_format') }}</textarea>
                        @error('team_format')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                        @enderror
                    </div>
                </div>
            </div>


            <div :class="{'d-none': (step != 2)}">
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
                            singular="level" plural="levels"
                            :meet="{{ $tm }}"
                            :late="{{ $tm->allow_late_registration ? 'true' : 'false' }}" :initial="{{
                                $tm->oldOrValue('levels') != null ?
                                $tm->oldOrValue('levels') :
                                '[]'
                            }}" :requires_sanction="{{ $required_sanctions }}">
                        </ag-meet-levels>
                    </div>
                </div>
            </div>

            <div class="d-flex flex-row flex-nowrap mt-3">
                <div class="flex-grow-1">
                    <a ref="previousStepLink" class="d-none"
                        href="{{ route('gyms.meets.create.step.view', ['gym' => $gym, 'step' => (($step == 3 ? 7:$step )- 1), 'temporary' => $tm]) }}">
                    </a>

                    <button class="btn btn-primary" type="button" @click="previousStep">
                        <span class="fas fa-long-arrow-alt-left"></span> Back
                    </button>
                </div>

                <div class="ml-3">
                    <button class="btn btn-success" type="button" @click="nextStep()">
                        Next <span class="fas fa-long-arrow-alt-right"></span>
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>