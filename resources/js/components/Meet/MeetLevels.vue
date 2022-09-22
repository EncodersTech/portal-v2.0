<template>
    <div>
        <div class="modal fade" id="modal-add-level" tabindex="-1" role="dialog"
            aria-labelledby="modal-add-level" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-xl" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title text-info">
                            <span class="fas fa-plus"></span> Add Levels
                        </h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span class="fas fa-times" aria-hidden="true"></span>
                        </button>
                    </div>

                    <div class="modal-body">
                        <div class="row">
                            <div class="col-lg-4 mb-2 separator">
                                <div v-for="body in filtered_bodies" :key="body.path">
                                    <div v-if="body.show_in_add_modal">
                                        <button class="btn btn-sm btn-block mb-2 text-left"
                                            :class="[body.expanded ? 'btn-primary' : 'btn-secondary']"
                                            type="button" @click="toggleBody(body)">
                                            <span class="fas fa-fw fa-receipt"></span>
                                            {{ body.name }}
                                            <span :class="'fas fa-fw fa-caret-' + (body.expanded ? 'down' : 'right')"></span>
                                        </button>

                                        <div class="ml-3" v-if="body.expanded">
                                            <div v-for="category in body.categories" :key="category.path">
                                                <div v-if="category.show_in_add_modal">
                                                    <button class="btn btn-sm btn-block mb-2 text-left"
                                                        :class="[category.expanded ? 'btn-info' : 'btn-secondary']"
                                                        type="button" @click="toggleCategory(body, category)">
                                                        <span class="fas fa-fw fa-ellipsis-v"></span>
                                                        {{ category.name }}
                                                        <span :class="'fas fa-fw fa-caret-' + (category.expanded ? 'down' : 'right')"></span>
                                                    </button>

                                                    <div class="ml-3 mb-3" v-if="category.expanded">
                                                        <button class="btn btn-link btn-sm btn-block text-left"
                                                            type="button"
                                                            @click="toggleCheckAll(body, category)">
                                                            <span class="fas fa-fw fa-check-square"></span> Select all
                                                        </button>
                                                        <div v-for="level in category.levels" :key="level.id">
                                                            <div class="form-check pb-1">
                                                                <input class="form-check-input" :id="'filter-level-' + level.id"
                                                                    type="checkbox" v-model="level.checked"
                                                                    @change="toggleCheck(body, category, level)">
                                                                <label class="form-check-label" :for="'filter-level-' + level.id">
                                                                    {{ level.name }}
                                                                </label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md mb-2">
                                <div class="alert alert-danger"
                                    v-if="newLevel && (newLevel.error != null)">
                                    <span class="fas fa-times-circle"></span>
                                    <span v-html="newLevel.error"></span>
                                </div>

                                <div class="alert alert-info font-weight-bold"
                                    v-if="(selected == null) || (selected.levels == null) || (selected.levels.length < 1)">
                                    <span class="fas fa-info-circle"></span> Start by clicking on Sanctioning Body to the left<br/>
                                    You can only select and add levels from one category at a time.

                                    If Late Registration was enabled (in step 2-Registration &amp; Payment) you will be able to add fees
                                    while adding levels.
                                </div>
                                <div v-else>
                                    <div v-if="selected.body != null" class="mb-2">
                                        <ol class="breadcrumb font-weight-bold">
                                            <li class="breadcrumb-item text-primary">
                                                {{ selected.body.name }}
                                            </li>
                                            <li v-if="selected.category != null"
                                                class="breadcrumb-item text-info">
                                                {{ selected.category.name }}
                                            </li>
                                            <li class="breadcrumb-item">
                                                {{
                                                    selected.levels.length > 1 ?
                                                    '(' + selected.levels.length + ' Levels)' :
                                                    selected.levels[0].name
                                                }}
                                            </li>
                                        </ol>
                                    </div>

                                    <div class="mb-2">
                                        <div class="row"
                                            v-if="selected.category.male && selected.category.female">
                                            <div class="col-lg">
                                                <div class="form-check pb-1">
                                                    <input class="form-check-input" id="add-level-male"
                                                        type="checkbox" v-model="newLevel.male">
                                                    <label class="form-check-label" for="add-level-male">
                                                        Allow male athletes
                                                    </label>
                                                </div>
                                            </div>
                                            <div class="col-lg">
                                                <div class="form-check pb-1">
                                                    <input class="form-check-input" id="add-level-female"
                                                        type="checkbox" v-model="newLevel.female">
                                                    <label class="form-check-label" for="add-level-female">
                                                        Allow female athletes
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                        <div v-else class="text-info">
                                            <span class="fas fa-info-circle"></span> This category allows
                                            {{ selected.category.male ? 'male' : 'female' }} athletes only.
                                        </div>
                                    </div>

                                    <div class="row mb-2">
                                        <div class="col-lg mb-2">
                                            <label for="add-level-registration-fee">
                                                <span class="fas fa-fw fa-dollar-sign"></span>
                                                Registration Fee
                                            </label>
                                            <div class="input-group input-group-sm">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text">
                                                        <span class="fas fa-fw fa-dollar-sign"></span>
                                                    </span>
                                                </div>
                                                <input id="add-level-registration-fee" class="form-control"
                                                    placeholder="0.00" autocomplete="off" type="text"
                                                    v-model="newLevel.registration_fee">
                                            </div>
                                        </div>
                                        
                                        <div v-if="late" class="col-lg mb-2">
                                            <label for="add-level-late-registration-fee">
                                                <span class="fas fa-fw fa-dollar-sign"></span>
                                                Late Registration Fee
                                            </label>
                                            <div class="input-group input-group-sm">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text">
                                                        <span class="fas fa-fw fa-dollar-sign"></span>
                                                    </span>
                                                </div>
                                                <input id="add-level-late-registration-fee" class="form-control"
                                                    placeholder="0.00" autocomplete="off" type="text"
                                                    v-model="newLevel.late_registration_fee">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row mb-2">
                                        <div v-if="first_discount_enable" class="col-lg mb-2">
                                            <label for="add-level-specialist-registration-fee">
                                                <span class="fas fa-fw fa-dollar-sign"></span>
                                                First Registration Fee
                                            </label>
                                            <div class="input-group input-group-sm">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text">
                                                        <span class="fas fa-fw fa-dollar-sign"></span>
                                                    </span>
                                                </div>
                                                <input id="add-level-specialist-registration-fee" class="form-control"
                                                    placeholder="0.00" autocomplete="off" type="text"
                                                    v-model="newLevel.registration_fee_first">
                                            </div>
                                        </div>
                                        <div v-if="second_discount_enable" class="col-lg mb-2">
                                            <label for="add-level-specialist-registration-fee">
                                                <span class="fas fa-fw fa-dollar-sign"></span>
                                                Second Registration Fee
                                            </label>
                                            <div class="input-group input-group-sm">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text">
                                                        <span class="fas fa-fw fa-dollar-sign"></span>
                                                    </span>
                                                </div>
                                                <input id="add-level-specialist-registration-fee" class="form-control"
                                                    placeholder="0.00" autocomplete="off" type="text"
                                                    v-model="newLevel.registration_fee_second">
                                            </div>
                                        </div>
                                        <div v-if="third_discount_enable" class="col-lg mb-2">
                                            <label for="add-level-specialist-registration-fee">
                                                <span class="fas fa-fw fa-dollar-sign"></span>
                                                Third Registration Fee
                                            </label>
                                            <div class="input-group input-group-sm">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text">
                                                        <span class="fas fa-fw fa-dollar-sign"></span>
                                                    </span>
                                                </div>
                                                <input id="add-level-specialist-registration-fee" class="form-control"
                                                    placeholder="0.00" autocomplete="off" type="text"
                                                    v-model="newLevel.registration_fee_third">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="mb-2" v-if="hasSpecialist(selected.body, selected.category)">
                                        <div class="form-check pb-1">
                                            <input class="form-check-input" id="add-level-allow-specialists"
                                                type="checkbox" v-model="newLevel.allow_specialist">
                                            <label class="form-check-label" for="add-level-allow-specialists">
                                                Allow specialist events
                                            </label>
                                        </div>
                                        <div class="row">
                                            <div class="col-lg mb-2">
                                                <label for="add-level-specialist-registration-fee">
                                                    <span class="fas fa-fw fa-dollar-sign"></span>
                                                    Registration Fee
                                                </label>
                                                <div class="input-group input-group-sm">
                                                    <div class="input-group-prepend">
                                                        <span class="input-group-text">
                                                            <span class="fas fa-fw fa-dollar-sign"></span>
                                                        </span>
                                                    </div>
                                                    <input id="add-level-specialist-registration-fee" class="form-control"
                                                        placeholder="0.00" autocomplete="off" type="text"
                                                        :disabled="!newLevel.allow_specialist"
                                                        v-model="newLevel.specialist_registration_fee">
                                                </div>
                                            </div>
                                            

                                            <div v-if="late" class="col-lg mb-2">
                                                <label for="add-level-specialist-late-registration-fee">
                                                    <span class="fas fa-fw fa-dollar-sign"></span>
                                                    Late Registration Fee
                                                </label>
                                                <div class="input-group input-group-sm">
                                                    <div class="input-group-prepend">
                                                        <span class="input-group-text">
                                                            <span class="fas fa-fw fa-dollar-sign"></span>
                                                        </span>
                                                    </div>
                                                    <input id="add-level-specialist-late-registration-fee" class="form-control"
                                                        placeholder="0.00" autocomplete="off" type="text"
                                                        :disabled="!newLevel.allow_specialist"
                                                        v-model="newLevel.specialist_late_registration_fee">
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="mb-2">
                                        <div class="form-check pb-1">
                                            <input class="form-check-input" id="add-level-allow-team"
                                                type="checkbox" v-model="newLevel.allow_team">
                                            <label class="form-check-label" for="add-level-allow-team">
                                                Allow teams
                                            </label>
                                        </div>
                                        <div class="row">
                                            <div class="col-lg mb-2">
                                                <label for="add-level-team-registration-fee">
                                                    <span class="fas fa-fw fa-dollar-sign"></span>
                                                    Registration Fee
                                                </label>
                                                <div class="input-group input-group-sm">
                                                    <div class="input-group-prepend">
                                                        <span class="input-group-text">
                                                            <span class="fas fa-fw fa-dollar-sign"></span>
                                                        </span>
                                                    </div>
                                                    <input id="add-level-team-registration-fee" class="form-control"
                                                        placeholder="0.00" autocomplete="off" type="text"
                                                        :disabled="!newLevel.allow_team"
                                                        v-model="newLevel.team_registration_fee">
                                                </div>
                                            </div>

                                            <div v-if="late" class="col-lg mb-2">
                                                <label for="add-level-team-late-registration-fee">
                                                    <span class="fas fa-fw fa-dollar-sign"></span>
                                                    Late Registration Fee
                                                </label>
                                                <div class="input-group input-group-sm">
                                                    <div class="input-group-prepend">
                                                        <span class="input-group-text">
                                                            <span class="fas fa-fw fa-dollar-sign"></span>
                                                        </span>
                                                    </div>
                                                    <input id="add-level-team-late-registration-fee" class="form-control"
                                                        placeholder="0.00" autocomplete="off" type="text"
                                                        :disabled="!newLevel.allow_team"
                                                        v-model="newLevel.team_late_registration_fee">
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                   <!-- <div class="mb-2">
                                        <div class="form-check pb-1">
                                            <input class="form-check-input" id="add-level-enable-athlete-limit"
                                                type="checkbox" v-model="newLevel.enable_athlete_limit">
                                            <label class="form-check-label" for="add-level-enable-athlete-limit">
                                                Enable Per Level Athlete Limit
                                            </label>
                                        </div>
                                        <div>
                                            <input class="form-control form-control-sm" type="number"
                                                placeholder="0.00" autocomplete="off"
                                                :disabled="!newLevel.enable_athlete_limit"
                                                v-model="newLevel.athlete_limit">
                                        </div>
                                    </div> -->

                                    <div class="mt-2 text-right">
                                        <button type="button" class="btn btn-success"
                                        @click="addItems">
                                            <span class="fas fa-plus"></span> Add
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="modal-edit-level" tabindex="-1" role="dialog"
            aria-labelledby="modal-edit-level" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-xl" role="document">
                <div v-if="editedLevel == null" class="modal-content">
                    <div class="alert alert-danger small">
                        Something went wrong.
                    </div>
                </div>
                <div v-else class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title text-info">
                            <span class="fas fa-edit"></span> Edit {{ editedLevel.name }}
                        </h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span class="fas fa-times" aria-hidden="true"></span>
                        </button>
                    </div>

                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md mb-2">
                                <div class="alert alert-danger"
                                    v-if="(editedLevel.error != null)">
                                    <span class="fas fa-times-circle"></span>
                                    <span v-html="editedLevel.error"></span>
                                </div>
                                <div>
                                    <div class="row mb-2">
                                        <div class="col-lg mb-2">
                                            <label for="add-level-registration-fee">
                                                <span class="fas fa-fw fa-dollar-sign"></span>
                                                Registration Fee
                                            </label>
                                            <div class="input-group input-group-sm">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text">
                                                        <span class="fas fa-fw fa-dollar-sign"></span>
                                                    </span>
                                                </div>
                                                <input id="add-level-registration-fee" class="form-control"
                                                    placeholder="0.00" autocomplete="off" type="text"
                                                    v-model="editedLevel.registration_fee">
                                            </div>
                                        </div>

                                        <div v-if="late" class="col-lg mb-2">
                                            <label for="add-level-late-registration-fee">
                                                <span class="fas fa-fw fa-dollar-sign"></span>
                                                Late Registration Fee
                                            </label>
                                            <div class="input-group input-group-sm">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text">
                                                        <span class="fas fa-fw fa-dollar-sign"></span>
                                                    </span>
                                                </div>
                                                <input id="add-level-late-registration-fee" class="form-control"
                                                    placeholder="0.00" autocomplete="off" type="text"
                                                    v-model="editedLevel.late_registration_fee">
                                            </div>
                                        </div>
                                    </div>
                                    <div v-if="first_discount_enable" class="row mb-2">
                                        <div class="col-lg mb-2">
                                            <label for="add-level-registration-fee">
                                                <span class="fas fa-fw fa-dollar-sign"></span>
                                                First Registration Fee
                                            </label>
                                            <div class="input-group input-group-sm">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text">
                                                        <span class="fas fa-fw fa-dollar-sign"></span>
                                                    </span>
                                                </div>
                                                <input id="add-level-registration-fee" class="form-control"
                                                    placeholder="0.00" autocomplete="off" type="text"
                                                    v-model="editedLevel.registration_fee_first">
                                            </div>
                                        </div>
                                    </div>
                                    <div v-if="second_discount_enable" class="row mb-2">
                                        <div class="col-lg mb-2">
                                            <label for="add-level-registration-fee">
                                                <span class="fas fa-fw fa-dollar-sign"></span>
                                                Second Registration Fee
                                            </label>
                                            <div class="input-group input-group-sm">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text">
                                                        <span class="fas fa-fw fa-dollar-sign"></span>
                                                    </span>
                                                </div>
                                                <input id="add-level-registration-fee" class="form-control"
                                                    placeholder="0.00" autocomplete="off" type="text"
                                                    v-model="editedLevel.registration_fee_second">
                                            </div>
                                        </div>
                                    </div>
                                    <div v-if="third_discount_enable" class="row mb-2">
                                        <div class="col-lg mb-2">
                                            <label for="add-level-registration-fee">
                                                <span class="fas fa-fw fa-dollar-sign"></span>
                                                Third Registration Fee
                                            </label>
                                            <div class="input-group input-group-sm">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text">
                                                        <span class="fas fa-fw fa-dollar-sign"></span>
                                                    </span>
                                                </div>
                                                <input id="add-level-registration-fee" class="form-control"
                                                    placeholder="0.00" autocomplete="off" type="text"
                                                    v-model="editedLevel.registration_fee_third">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="mb-2" v-if="hasSpecialist(editedLevel.body, editedLevel.category)">
                                        <div class="form-check pb-1">
                                            <input class="form-check-input" id="add-level-allow-specialists"
                                                type="checkbox" v-model="editedLevel.allow_specialist">
                                            <label class="form-check-label" for="add-level-allow-specialists">
                                                Allow specialist events
                                            </label>
                                        </div>
                                        <div class="row">
                                            <div class="col-lg mb-2">
                                                <label for="add-level-specialist-registration-fee">
                                                    <span class="fas fa-fw fa-dollar-sign"></span>
                                                    Registration Fee
                                                </label>
                                                <div class="input-group input-group-sm">
                                                    <div class="input-group-prepend">
                                                        <span class="input-group-text">
                                                            <span class="fas fa-fw fa-dollar-sign"></span>
                                                        </span>
                                                    </div>
                                                    <input id="add-level-specialist-registration-fee" class="form-control"
                                                        placeholder="0.00" autocomplete="off" type="text"
                                                        :disabled="!editedLevel.allow_specialist"
                                                        v-model="editedLevel.specialist_registration_fee">
                                                </div>
                                            </div>

                                            <div v-if="late" class="col-lg mb-2">
                                                <label for="add-level-specialist-late-registration-fee">
                                                    <span class="fas fa-fw fa-dollar-sign"></span>
                                                    Late Registration Fee
                                                </label>
                                                <div class="input-group input-group-sm">
                                                    <div class="input-group-prepend">
                                                        <span class="input-group-text">
                                                            <span class="fas fa-fw fa-dollar-sign"></span>
                                                        </span>
                                                    </div>
                                                    <input id="add-level-specialist-late-registration-fee" class="form-control"
                                                        placeholder="0.00" autocomplete="off" type="text"
                                                        :disabled="!editedLevel.allow_specialist"
                                                        v-model="editedLevel.specialist_late_registration_fee">
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="mb-2">
                                        <div class="form-check pb-1">
                                            <input class="form-check-input" id="add-level-allow-team"
                                                type="checkbox" v-model="editedLevel.allow_team">
                                            <label class="form-check-label" for="add-level-allow-team">
                                                Allow teams
                                            </label>
                                        </div>
                                        <div class="row">
                                            <div class="col-lg mb-2">
                                                <label for="add-level-team-registration-fee">
                                                    <span class="fas fa-fw fa-dollar-sign"></span>
                                                    Registration Fee
                                                </label>
                                                <div class="input-group input-group-sm">
                                                    <div class="input-group-prepend">
                                                        <span class="input-group-text">
                                                            <span class="fas fa-fw fa-dollar-sign"></span>
                                                        </span>
                                                    </div>
                                                    <input id="add-level-team-registration-fee" class="form-control"
                                                        placeholder="0.00" autocomplete="off" type="text"
                                                        :disabled="!editedLevel.allow_team"
                                                        v-model="editedLevel.team_registration_fee">
                                                </div>
                                            </div>

                                            <div v-if="late" class="col-lg mb-2">
                                                <label for="add-level-team-late-registration-fee">
                                                    <span class="fas fa-fw fa-dollar-sign"></span>
                                                    Late Registration Fee
                                                </label>
                                                <div class="input-group input-group-sm">
                                                    <div class="input-group-prepend">
                                                        <span class="input-group-text">
                                                            <span class="fas fa-fw fa-dollar-sign"></span>
                                                        </span>
                                                    </div>
                                                    <input id="add-level-team-late-registration-fee" class="form-control"
                                                        placeholder="0.00" autocomplete="off" type="text"
                                                        :disabled="!editedLevel.allow_team"
                                                        v-model="editedLevel.team_late_registration_fee">
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- <div class="mb-2">
                                        <div class="form-check pb-1">
                                            <input class="form-check-input" id="add-level-enable-athlete-limit"
                                                type="checkbox" v-model="editedLevel.enable_athlete_limit">
                                            <label class="form-check-label" for="add-level-enable-athlete-limit">
                                                Enable Per Level Athlete Limit
                                            </label>
                                        </div>
                                        <div>
                                            <input class="form-control form-control-sm" type="number"
                                                placeholder="0.00" autocomplete="off"
                                                :disabled="!editedLevel.enable_athlete_limit"
                                                v-model="editedLevel.athlete_limit">
                                        </div>
                                    </div> -->

                                    <div class="mt-2 text-right">
                                        <button type="button" class="btn btn-success"
                                        @click="saveEditedLevel">
                                            <span class="fas fa-save"></span> Save
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <input type="hidden" :name="field" :value="output" :disabled="isError">
        <div class="small" :class="{ 'd-none': !isLoading }">
            <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true">
            </span> Loading {{ plural }}, please wait ...
        </div>

        <div class="alert alert-danger" :class="{ 'd-none': !isError }">
            <span class="fas fa-fw fa-times-circle"></span> Whoops !<br/>
            <div class="mt-1" v-html="errorMessage"></div>
        </div>

        <div class="alert alert-warning" v-if="warnMessage != null">
            <span class="fas fa-fw fa-exclamation-triangle"></span>
            <span v-html="warnMessage"></span>
        </div>

        <div :class="{'d-none': isLoading || isError }">
            <div class="d-flex flex-row flew-nowrap mb-2">
                <div class="flex-grow-1">
                    <button class="btn btn-sm btn-danger" title="Remove All"
                        v-if="!hasNoItems" @click="clearItems" type="button">
                        <span class="fas fa-fw fa-trash"></span> Remove All
                    </button>
                </div>
                <div>
                    <div class="d-inline-block mr-2 ml-2">
                        <button class="btn btn-sm btn-primary" title="Collapse All"
                            v-if="!hasNoItems" @click="toggleItems(false)" type="button">
                            <span class="fas fa-fw fa-compress"></span>
                        </button>
                        <button class="btn btn-sm btn-primary" title="Expand All"
                            v-if="!hasNoItems" @click="toggleItems(true)" type="button">
                            <span class="fas fa-fw fa-expand"></span>
                        </button>
                    </div>

                    <button v-if="can_add_levels" class="btn btn-sm btn-success" type="button" @click="showAddModal">
                        <span class="fas fa-fw fa-plus"></span> Add Levels
                    </button>
                </div>
            </div>

            <div class="alert alert-info" :class="{ 'd-none': !hasNoItems }">
                <span class="fas fa-info-circle"></span> No {{ plural }} added.
            </div>

            <div v-if="!hasNoItems">
                <div v-for="body in items" :key="body.path" class="mb-2">
                    <div class="d-flex flex-row flex-nowrap mb-1">
                        <div class="flex-grow-1">
                            <button class="btn btn-sm btn-dark btn-block text-left left-btn"
                                type="button" @click="body.expanded = !body.expanded">
                                <span class="fas fa-fw fa-receipt"></span>
                                {{ body.name }}
                                <span :class="'fas fa-fw fa-caret-' + (body.expanded ? 'down' : 'right')"></span>
                            </button>
                        </div>
                        <div v-if="!body.locked" class="">
                            <button class="btn btn-sm btn-danger right-btn" type="button" :disabled="body.locked"
                                title="Remove Body" @click="removeBody(body)">
                                <span class="fas fa-fw fa-trash"></span>
                            </button>
                        </div>
                    </div>

                    <div v-if="body.expanded" class="ml-3">
                        <div v-for="category in body.categories" :key="category.path" class="mb-1">
                            <div class="d-flex flex-row flex-nowrap mb-1">
                                <div class="flex-grow-1">
                                    <button class="btn btn-sm btn-info btn-block text-left left-btn"
                                        type="button" @click="category.expanded = !category.expanded">
                                        <span class="fas fa-fw fa-receipt"></span>
                                        {{ category.name }}
                                        <span :class="'fas fa-fw fa-caret-' + (category.expanded ? 'down' : 'right')"></span>
                                    </button>
                                </div>
                                <div v-if="!category.locked" class="">
                                    <button class="btn btn-sm btn-danger right-btn" type="button" :disabled="category.locked"
                                        title="Remove Category" @click="removeCategory(body, category)">
                                        <span class="fas fa-fw fa-trash"></span>
                                    </button>
                                </div>
                            </div>

                            <div v-if="category.expanded" class="ml-3 mb-1">
                                <div class="table-responsive-lg small">
                                    <table class="table table-sm table-hover">
                                        <thead class="thead-light">
                                            <tr>
                                                <th scope="col" class="align-middle">
                                                    Level
                                                </th>
                                                <th scope="col" class="align-middle">
                                                    Gender
                                                </th>
                                                <th scope="col" class="align-middle">
                                                    Registration
                                                </th>
                                                <th v-if="hasSpecialist(body, category)" scope="col"
                                                    class="align-middle">
                                                    Specialist
                                                </th>
                                                <th scope="col" class="align-middle">
                                                    Teams
                                                </th>
                                                <th scope="col" class="text-right align-middle"></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr v-for="level in category.levels" :key="levelUniqueId(level)">
                                                <td class="align-middle">
                                                    {{ level.name }}
                                                </td>
                                                <td class="align-middle">
                                                    <span v-if="level.male && level.female">Both</span>
                                                    <span v-else-if="level.male">Male</span>
                                                    <span v-else-if="level.female">Female</span>
                                                </td>
                                                <td class="align-middle">
                                                    <div v-if="level.enable_athlete_limit">
                                                        <strong>Limit:</strong> {{ level.athlete_limit }}
                                                    </div>
                                                    <div>
                                                        <strong>Regular:</strong>
                                                        ${{ level.registration_fee}}
                                                    </div>
                                                    <div v-if="late">
                                                        <strong>Late:</strong>
                                                        ${{ level.late_registration_fee}}
                                                    </div>
                                                    <div v-if="first_discount_enable">
                                                        <strong>First Reg.:</strong>
                                                        ${{ level.registration_fee_first}}
                                                    </div>
                                                    <div v-if="second_discount_enable">
                                                        <strong>Second Reg.:</strong>
                                                        ${{ level.registration_fee_second}}
                                                    </div>
                                                    <div v-if="third_discount_enable">
                                                        <strong>Third Reg.:</strong>
                                                        ${{ level.registration_fee_third}}
                                                    </div>
                                                </td>

                                                <td v-if="hasSpecialist(body, category)" class="align-middle">
                                                    <div v-if="level.allow_specialist">
                                                        <div>
                                                            <strong>Regular:</strong>
                                                            ${{ level.specialist_registration_fee}}
                                                        </div>
                                                        <div v-if="late">
                                                            <strong>Late:</strong>
                                                            ${{ level.specialist_late_registration_fee}}
                                                        </div>
                                                    </div>
                                                    <div v-else>No</div>
                                                </td>

                                                <td class="align-middle">
                                                    <div v-if="level.allow_team">
                                                        <div>
                                                            <strong>Regular:</strong>
                                                            ${{ level.team_registration_fee}}
                                                        </div>
                                                        <div v-if="late">
                                                            <strong>Late:</strong>
                                                            ${{ level.team_late_registration_fee}}
                                                        </div>
                                                    </div>
                                                    <div v-else>No</div>
                                                </td>

                                                <td class="text-right align-middle">
                                                    <div v-if="level.can_edit" class="mb-1 mr-1 d-inline-block">
                                                        <button class="btn btn-sm btn-success" type="button" :disabled="!level.can_edit"
                                                            title="Edit" @click="editLevel(body, category, level)">
                                                            <span class="fas fa-fw fa-edit"></span>
                                                        </button>
                                                    </div>

                                                    <div v-if="!level.locked" class="mb-1 mr-1 d-inline-block">
                                                        <button class="btn btn-sm btn-danger" type="button" :disabled="level.locked"
                                                            title="Remove" @click="removeLevel(body, category, level)">
                                                            <span class="fas fa-fw fa-trash"></span>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style lang="css" scoped>
    .membership {
        margin-bottom: 0.25rem;
        border-bottom: 1px solid #CED4DA

    }

    .membership:last-child {
        margin-bottom: 0;
        border-bottom: none;
    }

    .clickable {
        cursor: pointer;
    }

    .left-btn {
        border-top-right-radius: 0;
        border-bottom-right-radius: 0;
        box-shadow: none;
    }

    .right-btn {
        border-top-left-radius: 0;
        border-bottom-left-radius: 0;
        box-shadow: none;
    }

    @media (max-width: 991px) {
        .separator {
            border-bottom: 1px solid #CED4DA;
        }
    }
</style>


<script>
    export default {
        name: 'MeetLevelsList',
        props: {
            meet:{
                type: Object
            },
            available_bodies: {
                type: Array,
                default: []
            },
            category_filters: {
                type: Array,
                default: [],
            },
            loading: {
                type: Boolean,
                default: false
            },
            error: {
                type: Boolean,
                default: false
            },
            errormessage: {
                type: String,
                default: ''
            },
            initial: {
                type: Array,
                default: []
            },
            field: {
                type: String,
                default: 'levels'
            },
            singular: {
                type: String,
                default: 'item'
            },
            plural: {
                type: String,
                default: 'items'
            },
            late: {
                type: Boolean,
                default: false
            },
            mode: {
                type: Number,
                default: 1
            },
            restricted: {
                type: Boolean,
                default: false
            },
            restricted_bodies: {
                type: Object,
                default: () => ({
                    1: {},   // USAG
                    2: {},   // USAIGC
                    3: {},   // AAU
                    4: {},   // NGA
                })
            },
            requires_sanction: {
                type: Object,
                default: () => ({
                    1: true,    // USAG
                    2: false,   // USAIGC
                    3: false,   // AAU
                    4: false,   // NGA
                })
            }
        },
        computed: {
            constants() {
                return {
                    bodies: {
                        USAG: 1,
                        USAIGC: 2,
                        AAU: 3,
                        NGA: 4
                    },

                    categories: {
                        GYMNASTICS_WOMEN: 1,
                        GYMNASTICS_MEN: 2,
                        TRAMPOLINE_TUMBLING: 3,
                        RHYTHMIC: 4,
                        ACROBATIC: 5,
                        GYMNASTICS_FOR_ALL: 6,
                        TUMBLING: 7,
                    },

                    modes: {
                        CREATE: 1,
                        EDIT: 2,
                    },

                    settings: {
                        defaultExpand: true
                    },
                };
            },
            newLevelDefaults() {
                return {
                    male: false,
                    female: false,
                    registration_fee: 0.00,
                    late_registration_fee: 0.00,
                    allow_specialist: false,
                    specialist_registration_fee: 0.00,
                    specialist_late_registration_fee: 0.00,
                    allow_team: false,
                    team_registration_fee: 0.00,
                    team_late_registration_fee: 0.00,
                    enable_athlete_limit: false,
                    athlete_limit: 0,
                    error: null,

                    registration_fee_first: 0.00,
                    registration_fee_second: 0.00,
                    registration_fee_third: 0.00,
                };
            },
            output() {
                try {
                    if (this.isError)
                        return '';

                    let result = [];

                    this.items.forEach(b => {
                        b.categories.forEach(c => {
                            c.levels.forEach(l => {
                                result.push({
                                    id: l.id,
                                    male: l.male,
                                    female: l.female,
                                    registration_fee: l.registration_fee,
                                    late_registration_fee: l.late_registration_fee,
                                    allow_specialist: l.allow_specialist,
                                    specialist_registration_fee: l.specialist_registration_fee,
                                    specialist_late_registration_fee: l.specialist_late_registration_fee,
                                    allow_team: l.allow_team,
                                    team_registration_fee: l.team_registration_fee,
                                    team_late_registration_fee: l.team_late_registration_fee,
                                    enable_athlete_limit: l.enable_athlete_limit,
                                    athlete_limit: l.athlete_limit,

                                    registration_fee_first: l.registration_fee_first, 
                                    registration_fee_second: l.registration_fee_second,
                                    registration_fee_third: l.registration_fee_third

                                });
                            });
                        });
                    });

                    return (result.length < 1 ? '[]' : JSON.stringify(result));
                } catch (error) {
                    this.errorMessage = 'Something went wrong while compiling your admissions.';
                    this.isError = true;
                };
            }
        },
        watch: {
            loading() {
                this.isLoading = this.loading;
            },
            error() {
                this.isError = this.error;
            },
            errormessage() {
                this.errorMessage = this.errormessage;
            },
            category_filters() {
                this.filterBodies();

                this.can_add_levels = this.filtered_bodies.filter(b => b.show_in_add_modal).length > 0;
                if (this.initiate) {
                    this.initialize();
                }
            },
            items: {
                immediate: true,
                handler() {
                    this.hasNoItems = (this.items.length < 1);
                }
            }
        },
        data() {
            return {
                isLoading: false,
                isError: false,
                hasNoItems: false,
                errorMessage: '',
                bodies: [],
                filtered_bodies: [],
                items: [],
                selected: null,
                newLevel: this.newLevelDefaults,
                levelCategoryBodyMatrix: {},
                initiate: true,
                warnMessage: null,
                editedLevel: null,
                editedOldLevel: null,
                can_add_levels: false,
                first_discount_enable: this.meet.registration_first_discount_is_enable,
                second_discount_enable: this.meet.registration_second_discount_is_enable,
                third_discount_enable: this.meet.registration_third_discount_is_enable
            }
        },
        methods: {
            hasSpecialist(body, category) {
                return ((body.id == this.constants.bodies.USAIGC)
                    && (category.id == this.constants.categories.GYMNASTICS_WOMEN) 
                    || body.id == this.constants.bodies.NGA);
            },

            numberFormat(n) {
                try {
                    let fee = Utils.toFloat(n);
                    return (fee === null ? n : fee.toFixed(2));
                } catch (e) {
                    return n;
                }
            },

            toggleBody(body) {
                this.filtered_bodies.forEach(item => {
                    item.categories.forEach(category => {
                        category.levels.forEach(level => this.toggleCheck(body, category, level, false));
                        category.expanded = false
                    });
                    this.newLevel = {...this.newLevelDefaults};
                    if (item.path != body.path) {
                        item.expanded = false;
                    }
                });
                body.expanded = !body.expanded;
            },

            toggleCategory(body, category) {
                body.categories.forEach(item => {
                    item.levels.forEach(level => this.toggleCheck(body, category, level, false));
                    if (item.path != category.path)
                        item.expanded = false;

                    this.newLevel = {...this.newLevelDefaults};
                });
                category.expanded = !category.expanded;
            },

            toggleCheck(body, category, level, force) {
                let levels = [];

                if ((this.selected != null) && (this.selected.levels != null))
                    levels = this.selected.levels;

                let itemIndex = levels.indexOf(levels.find(item => item.id == level.id));
                let check = (force !== undefined ? force : level.checked);

                level.checked = check;
                if (check && (itemIndex < 0)) {
                    levels.push({...level});
                } else if (!check && (itemIndex > -1)) {
                    levels.splice(itemIndex, 1);
                }

                this.selected = (levels.length > 0 ? {
                    body: body,
                    category: category,
                    levels: levels
                } : null);
            },

            toggleCheckAll(body, category) {
                let check = true;

                if ((this.selected != null) && (this.selected.levels != null))
                    check = !(category.levels.length == this.selected.levels.length);

                category.levels.forEach(level => this.toggleCheck(body, category, level, check));
            },

            resetAddLevelView() {
                this.selected = null;
                this.newLevel = {...this.newLevelDefaults};
            },

            filterBodies() {
                try {
                    let filters = [];
                    for (const i in this.category_filters) {
                        const item = this.category_filters[i];

                        if (!filters[item.body_id])
                            filters[item.body_id] = [];

                        filters[item.body_id].push({
                            id: item.id,
                            sanction: item.sanction,
                            requires_sanction: item.requires_sanction,
                            officially_sanctioned: item.officially_sanctioned,
                        });
                    }

                    this.filtered_bodies = [];
                    this.levelCategoryBodyMatrix = {};
                    for (const i in filters) {
                        const filter = filters[i];

                        const body = this.bodies.find(body => body.id == i);
                        if (!body)
                            throw "Invalid filter data (body)";

                        let body_show_in_add_modal = false;
                        let categories = [];
                        for (const j in filter) {
                            const filterCategory = filter[j];

                            let category = body.categories.find(category => category.id == filterCategory.id);
                            if (!category)
                                throw "Invalid filter data (category)";

                            category.levels.forEach(l => {
                                this.levelCategoryBodyMatrix[l.id] = {
                                    category: category.id,
                                    body: body.id,
                                }
                            })

                            let category_show_in_add_modal = !category.requires_sanction;

                            if (category_show_in_add_modal)
                                body_show_in_add_modal = true;

                            categories.push({
                                ...category,
                                officially_sanctioned: filterCategory.officially_sanctioned,
                                show_in_add_modal: category_show_in_add_modal,
                            });
                        }

                        this.filtered_bodies.push({
                            ...body,
                            categories: categories,
                            show_in_add_modal: body_show_in_add_modal,
                        });
                    }

                    let itemIndex;
                    let i = this.items.length;
                    while(i--) {
                        const body = this.items[i];

                        let filteredBody = this.filtered_bodies.find(b => b.id == body.id);
                        if (!filteredBody) {
                            itemIndex = this.items.indexOf(body);
                            this.items.splice(itemIndex, 1);
                        } else {
                            let j = body.categories.length;
                            while(j--) {
                                const category = body.categories[j];

                                if (!filteredBody.categories.find(c => c.id == category.id)) {
                                    itemIndex = body.categories.indexOf(category);
                                    body.categories.splice(itemIndex, 1);
                                }
                            }
                            if (body.categories.length < 1) {
                                itemIndex = this.items.indexOf(body);
                                this.items.splice(itemIndex, 1);
                            }
                        }
                    }
                } catch (error) {
                    this.errorMessage = error + '<br/>Please reload this page.';
                    this.isError = true;
                }
            },

            clearItems() {
                this.confirmAction(
                    'Do you really want to remove all items ?',
                    'red',
                    'fas fa-trash',
                    () => {
                        this.items = this.items.filter(b => b.locked);
                        this.items.forEach(b => {
                            b.categories = b.categories.filter(c => c.locked);
                        });
                        this.items.forEach(b => {
                            b.categories.forEach(c => {
                                c.levels = c.levels.filter(l => l.locked);
                            })
                        });
                    },
                    this
                );
            },

            removeBody(body) {
                if (body.locked)
                    return;
                this.confirmAction(
                    'Do you really want to remove all levels from ' + body.name + ' ?',
                    'red',
                    'fas fa-trash',
                    () => {
                        let itemIndex = this.items.indexOf(body);
                        this.items.splice(itemIndex, 1);
                    },
                    this
                );
            },

            removeCategory(body, category) {
                if (category.locked)
                    return;
                this.confirmAction(
                    'Do you really want to remove all levels in ' + category.name + ' ?',
                    'red',
                    'fas fa-trash',
                    () => {
                        let itemIndex = body.categories.indexOf(category);
                        body.categories.splice(itemIndex, 1);

                        if (body.categories.length < 1) {
                            itemIndex = this.items.indexOf(body);
                            this.items.splice(itemIndex, 1);
                        }
                    },
                    this
                );
            },

            removeLevel(body, category, level) {
                if (level.locked)
                    return;

                this.confirmAction(
                    'Do you really want to remove ' + level.name + ' ?',
                    'red',
                    'fas fa-trash',
                    () => {
                        let itemIndex = category.levels.indexOf(level);
                        category.levels.splice(itemIndex, 1);

                        if (category.levels.length < 1) {
                            itemIndex = body.categories.indexOf(category);
                            body.categories.splice(itemIndex, 1);

                            if (body.categories.length < 1) {
                                itemIndex = this.items.indexOf(body);
                                this.items.splice(itemIndex, 1);
                            }
                        }
                    },
                    this
                );
            },

            editLevel(body, category, level) {
                if (!level.can_edit)
                    return;
                this.editedOldLevel = level;
                this.editedLevel = {
                    ...level,
                    body: body,
                    category: category,
                    error: null
                }; // no need to deepclone
                $('#modal-edit-level').modal('show');
            },

            saveEditedLevel() {
                try {
                    if (!this.editedLevel.can_edit) {
                        $('#modal-edit-level').modal('hide');
                        return;
                    }

                    this.editedLevel.error = null;

                    let body = this.editedLevel.body;
                    let category = this.editedLevel.category;
                    let fee = null;

                    fee = Utils.toFloat(this.editedLevel.registration_fee);
                    if ((fee === null) || (fee < 0))
                        throw 'Please enter a valid registration fee';
                    this.editedLevel.registration_fee = fee.toFixed(2);
                    
                    // Temporary Level Table
                    if(this.first_discount_enable)
                    {
                        fee = Utils.toFloat(this.editedLevel.registration_fee_first);
                        if ((fee === null) || (fee < 0))
                            throw 'Please enter a valid first registration fee';
                        this.editedLevel.registration_fee_first = fee.toFixed(2);
                    }
                    if(this.second_discount_enable)
                    {
                        fee = Utils.toFloat(this.editedLevel.registration_fee_second);
                        if ((fee === null) || (fee < 0))
                            throw 'Please enter a valid second registration fee';
                        this.editedLevel.registration_fee_second = fee.toFixed(2);
                    }
                    if(this.third_discount_enable)
                    {
                        fee = Utils.toFloat(this.editedLevel.registration_fee_third);
                        if ((fee === null) || (fee < 0))
                            throw 'Please enter a valid third registration fee';
                        this.editedLevel.registration_fee_third = fee.toFixed(2);
                    }

                    if (this.late) {
                        fee = Utils.toFloat(this.editedLevel.late_registration_fee);
                        if ((fee === null) || (fee < 0))
                            throw 'Please enter a valid late registration fee';
                        this.editedLevel.late_registration_fee = fee.toFixed(2);
                    } else {
                        this.editedLevel.late_registration_fee = 0;
                    }

                    if (this.hasSpecialist(body, category) && this.editedLevel.allow_specialist) {
                        fee = Utils.toFloat(this.editedLevel.specialist_registration_fee);
                        if ((fee === null) || (fee < 0))
                            throw 'Please enter a valid specialist registration fee';
                        this.editedLevel.specialist_registration_fee = fee.toFixed(2);

                        if (this.late) {
                            fee = Utils.toFloat(this.editedLevel.specialist_late_registration_fee);
                            if ((fee === null) || (fee < 0))
                                throw 'Please enter a valid specialist late registration fee';
                            this.editedLevel.specialist_late_registration_fee = fee.toFixed(2);
                        } else {
                            this.editedLevel.specialist_late_registration_fee = 0;
                        }
                    } else {
                        this.editedLevel.allow_specialist = false;
                        this.editedLevel.specialist_registration_fee = 0;
                        this.editedLevel.specialist_late_registration_fee = 0;
                    }


                    if (this.editedLevel.allow_team) {
                        fee = Utils.toFloat(this.editedLevel.team_registration_fee);
                        if ((fee === null) || (fee < 0))
                            throw 'Please enter a valid team registration fee';
                        this.editedLevel.team_registration_fee = fee.toFixed(2);

                        if (this.late) {
                            fee = Utils.toFloat(this.editedLevel.team_late_registration_fee);
                            if ((fee === null) || (fee < 0))
                                throw 'Please enter a valid team late registration fee';
                            this.editedLevel.team_late_registration_fee = fee.toFixed(2);
                        } else {
                            this.editedLevel.team_late_registration_fee = 0;
                        }
                    } else {
                        this.editedLevel.allow_team = false;
                        this.editedLevel.team_registration_fee = 0;
                        this.editedLevel.team_late_registration_fee = 0;
                    }

                    if (this.editedLevel.enable_athlete_limit) {
                        let limit = Utils.toInt(this.editedLevel.athlete_limit);
                        if ((limit === null) || (limit < 1))
                            throw 'Please enter a valid athlete limit';
                        this.editedLevel.athlete_limit = limit;
                    } else {
                        this.editedLevel.athlete_limit = 0;
                    }

                    this.editedOldLevel.registration_fee = this.editedLevel.registration_fee;

                    this.editedOldLevel.registration_fee_first = this.editedLevel.registration_fee_first;
                    this.editedOldLevel.registration_fee_second = this.editedLevel.registration_fee_second;
                    this.editedOldLevel.registration_fee_third = this.editedLevel.registration_fee_third;
                    
                    this.editedOldLevel.late_registration_fee = this.editedLevel.late_registration_fee;

                    this.editedOldLevel.allow_specialist = this.editedLevel.allow_specialist;
                    this.editedOldLevel.specialist_registration_fee = this.editedLevel.specialist_registration_fee;
                    this.editedOldLevel.specialist_late_registration_fee = this.editedLevel.specialist_late_registration_fee;

                    this.editedOldLevel.allow_team = this.editedLevel.allow_team;
                    this.editedOldLevel.team_registration_fee = this.editedLevel.team_registration_fee;
                    this.editedOldLevel.team_late_registration_fee = this.editedLevel.team_late_registration_fee;

                    this.editedOldLevel.enable_athlete_limit = this.editedLevel.enable_athlete_limit
                    this.editedOldLevel.athlete_limit = this.editedLevel.athlete_limit;

                    $('#modal-edit-level').modal('hide');
                } catch (error) {
                    this.editedLevel.error = error;
                }
            },

            showAddModal() {
                this.selected = null;
                if (this.filtered_bodies.length < 1)
                    return;

                this.filtered_bodies.forEach(body => {
                    body.categories.forEach(category => {
                        category.levels.forEach(level => level.checked = false);
                        category.expanded = false
                    });
                    body.expanded = false;
                });

                $('#modal-add-level').modal('show');
            },

            addItems() {
                try {
                    this.newLevel.error = null;

                    let category = this.selected.category;
                    let body = this.selected.body;
                    let levels = this.selected.levels;
                    let fee = null;

                    if (levels.length < 1)
                        throw 'Please select a level first';

                    if (category.male && category.female) {
                        if (!(this.newLevel.male || this.newLevel.female))
                            throw 'Please allow at least one gender to compete.'
                    } else {
                        this.newLevel.male = category.male;
                        this.newLevel.female = category.female;
                    }

                    fee = Utils.toFloat(this.newLevel.registration_fee);
                    if ((fee === null) || (fee < 0))
                        throw 'Please enter a valid registration fee';
                    this.newLevel.registration_fee = fee.toFixed(2);
                    
                    // Temporary Level Table
                    if(this.first_discount_enable)
                    {
                        fee = Utils.toFloat(this.newLevel.registration_fee_first);
                        if ((fee === null) || (fee < 0))
                            throw 'Please enter a valid first registration fee';
                        this.newLevel.registration_fee_first = fee.toFixed(2);
                    }
                    if(this.second_discount_enable)
                    {
                        fee = Utils.toFloat(this.newLevel.registration_fee_second);
                        if ((fee === null) || (fee < 0))
                            throw 'Please enter a valid second registration fee';
                        this.newLevel.registration_fee_second = fee.toFixed(2);
                    }
                    if(this.third_discount_enable)
                    {
                        fee = Utils.toFloat(this.newLevel.registration_fee_third);
                        if ((fee === null) || (fee < 0))
                            throw 'Please enter a valid third registration fee';
                        this.newLevel.registration_fee_third = fee.toFixed(2);
                    }

                    if (this.late) {
                        fee = Utils.toFloat(this.newLevel.late_registration_fee);
                        if ((fee === null) || (fee < 0))
                            throw 'Please enter a valid late registration fee';
                        this.newLevel.late_registration_fee = fee.toFixed(2);
                    } else {
                        this.newLevel.late_registration_fee = 0;
                    }

                    if (this.hasSpecialist(body, category) && this.newLevel.allow_specialist) {
                        fee = Utils.toFloat(this.newLevel.specialist_registration_fee);
                        if ((fee === null) || (fee < 0))
                            throw 'Please enter a valid specialist registration fee';
                        this.newLevel.specialist_registration_fee = fee.toFixed(2);

                        if (this.late) {
                            fee = Utils.toFloat(this.newLevel.specialist_late_registration_fee);
                            if ((fee === null) || (fee < 0))
                                throw 'Please enter a valid specialist late registration fee';
                            this.newLevel.specialist_late_registration_fee = fee.toFixed(2);
                        } else {
                            this.newLevel.specialist_late_registration_fee = 0;
                        }
                    } else {
                        this.newLevel.allow_specialist = false;
                        this.newLevel.specialist_registration_fee = 0;
                        this.newLevel.specialist_late_registration_fee = 0;
                    }

                    if (this.newLevel.allow_team) {
                        fee = Utils.toFloat(this.newLevel.team_registration_fee);
                        if ((fee === null) || (fee < 0))
                            throw 'Please enter a valid team registration fee';
                        this.newLevel.team_registration_fee = fee.toFixed(2);

                        if (this.late) {
                            fee = Utils.toFloat(this.newLevel.team_late_registration_fee);
                            if ((fee === null) || (fee < 0))
                                throw 'Please enter a valid team late registration fee';
                            this.newLevel.team_late_registration_fee = fee.toFixed(2);
                        } else {
                            this.newLevel.team_late_registration_fee = 0;
                        }
                    } else {
                        this.newLevel.allow_team = false;
                        this.newLevel.team_registration_fee = 0;
                        this.newLevel.team_late_registration_fee = 0;
                    }

                    if (this.newLevel.enable_athlete_limit) {
                        let limit = Utils.toInt(this.newLevel.athlete_limit);
                        if ((limit === null) || (limit < 1))
                            throw 'Please enter a valid athlete limit';
                        this.newLevel.athlete_limit = limit;
                    } else {
                        this.newLevel.athlete_limit = 0;
                    }

                    let itemsBody;
                    let itemsCategory;
                    let selectedLevels = [];

                    itemsBody = this.items.find(b => b.id == body.id);
                    if (itemsBody) {
                        itemsCategory = itemsBody.categories.find(c => c.id == category.id);
                        if (itemsCategory) {
                            let existingLevels = [];
                            for (const i in this.selected.levels) {
                                const level = this.selected.levels[i];

                                let itemLevels = itemsCategory.levels.filter(l => l.id == level.id);
                                if (itemLevels.length > 0) {
                                    for (const j in itemLevels) {
                                        const itemLevel = itemLevels[j];
                                        if (
                                            ((this.newLevel.male && itemLevel.male) ||
                                            (this.newLevel.female && itemLevel.female))
                                            && !existingLevels.includes(itemLevel.name)
                                        )
                                            existingLevels.push(itemLevel.name);
                                    }
                                }
                            }

                            if (existingLevels.length > 0)
                                throw 'You already added the following levels with conflicting gender ' +
                                'configurations:<br/><strong>' + existingLevels.join(', ') + '</strong>';
                        }
                    }

                    for (const i in this.selected.levels) {
                        let level = this.selected.levels[i];
                        selectedLevels.push({
                            ...level,
                            ...this.newLevel,
                            locked: false,
                            can_edit: true,
                        });
                    }

                    let newBody = (itemsBody === undefined);
                    let newCategory = (itemsCategory === undefined);

                    if (newBody)
                        itemsBody = {
                            ...body,
                            categories: [],
                            expanded: this.constants.settings.defaultExpand,
                            locked: false,
                        };

                    if (newCategory)
                        itemsCategory = {
                            ...category,
                            levels: [],
                            expanded: this.constants.settings.defaultExpand,
                            locked: false,
                        };

                    if (itemsCategory.requires_sanction && !itemsCategory.officially_sanctioned)
                        return;

                    itemsCategory.levels = itemsCategory.levels.concat(selectedLevels);

                    if (newCategory)
                        itemsBody.categories.push(itemsCategory);

                    if (newBody)
                        this.items.push(itemsBody);

                    $('#modal-add-level').modal('hide');
                } catch (error) {
                    this.newLevel.error = error;
                }
            },

            toggleItems(toggle) {
                this.items.forEach(b => {
                    b.expanded = toggle;
                    b.categories.forEach(c => c.expanded = toggle);
                });
            },

            initialize() {
                this.initiate = false;
                try {
                    let isWarn = false;
                    this.initial.forEach(il => {
                        if (this.levelCategoryBodyMatrix.hasOwnProperty(il.id)) {
                            let map = this.levelCategoryBodyMatrix[il.id];

                            let body = this.filtered_bodies.find(b => b.id == map.body);
                            if (!body) {
                                isWarn = true;
                                return
                            }

                            let category = body.categories.find(c => c.id == map.category);
                            if (!category) {
                                isWarn = true;
                                return
                            }

                            let level = category.levels.find(l => l.id == il.id);
                            if (!level) {
                                isWarn = true;
                                return
                            }

                            /* ------------------------------- */
                            let fee;
                            if (category.male && category.female) {
                                if (!(il.male || il.female)) {
                                    isWarn = true;
                                    return
                                }
                            } else {
                                il.male = category.male;
                                il.female = category.female;
                            }

                            fee = Utils.toFloat(il.registration_fee);
                            if ((fee === null) || (fee < 0)) {
                                isWarn = true;
                                return
                            }
                            il.registration_fee = fee.toFixed(2);

                            if (this.first_discount_enable) {
                                fee = Utils.toFloat(il.registration_fee_first);
                                if ((fee === null) || (fee < 0)) {
                                    isWarn = true;
                                    return
                                }
                                il.registration_fee_first = fee.toFixed(2);
                            } else {
                                il.registration_fee_first = 0;
                            }

                            if (this.second_discount_enable) {
                                fee = Utils.toFloat(il.registration_fee_second);
                                if ((fee === null) || (fee < 0)) {
                                    isWarn = true;
                                    return
                                }
                                il.registration_fee_second = fee.toFixed(2);
                            } else {
                                il.registration_fee_second = 0;
                            }

                            if (this.third_discount_enable) {
                                fee = Utils.toFloat(il.registration_fee_third);
                                if ((fee === null) || (fee < 0)) {
                                    isWarn = true;
                                    return
                                }
                                il.registration_fee_third = fee.toFixed(2);
                            } else {
                                il.registration_fee_third = 0;
                            }



                            if (this.late) {
                                fee = Utils.toFloat(il.late_registration_fee);
                                if ((fee === null) || (fee < 0)) {
                                    isWarn = true;
                                    return
                                }
                                il.late_registration_fee = fee.toFixed(2);
                            } else {
                                il.late_registration_fee = 0;
                            }

                            if (this.hasSpecialist(body, category) && il.allow_specialist) {
                                fee = Utils.toFloat(il.specialist_registration_fee);
                                if ((fee === null) || (fee < 0)) {
                                    isWarn = true;
                                    return
                                }
                                il.specialist_registration_fee = fee.toFixed(2);

                                if (this.late) {
                                    fee = Utils.toFloat(il.specialist_late_registration_fee);
                                    if ((fee === null) || (fee < 0)) {
                                        isWarn = true;
                                        return
                                    }
                                    il.specialist_late_registration_fee = fee.toFixed(2);
                                } else {
                                    il.specialist_late_registration_fee = 0;
                                }
                            } else {
                                il.allow_specialist = false;
                                il.specialist_registration_fee = 0;
                                il.specialist_late_registration_fee = 0;
                            }

                            if (il.allow_team) {
                                fee = Utils.toFloat(il.team_registration_fee);
                                if ((fee === null) || (fee < 0)) {
                                    isWarn = true;
                                    return
                                }
                                il.team_registration_fee = fee.toFixed(2);

                                if (this.late) {
                                    fee = Utils.toFloat(il.team_late_registration_fee);
                                    if ((fee === null) || (fee < 0)) {
                                        isWarn = true;
                                        return
                                    }
                                    il.team_late_registration_fee = fee.toFixed(2);
                                } else {
                                    il.team_late_registration_fee = 0;
                                }
                            } else {
                                il.allow_team = false;
                                il.team_registration_fee = 0;
                                il.team_late_registration_fee = 0;
                            }

                            if (il.enable_athlete_limit) {
                                let limit = Utils.toInt(il.athlete_limit);
                                if ((limit === null) || (limit < 1)) {
                                    isWarn = true;
                                    return
                                }
                                il.athlete_limit = limit;
                            } else {
                                il.athlete_limit = 0;
                            }
                            /* ------------------------------- */

                            let itemsBody;
                            let itemsCategory;

                            itemsBody = this.items.find(b => b.id == body.id);
                            if (itemsBody) {
                                itemsCategory = itemsBody.categories.find(c => c.id == category.id);
                                if (itemsCategory) {
                                    let itemLevels = itemsCategory.levels.filter(l => l.id == level.id);
                                    if (itemLevels.length > 0) {
                                        for (const j in itemLevels) {
                                            const itemLevel = itemLevels[j];
                                            if ((this.newLevel.male && itemLevel.male) ||
                                                (this.newLevel.female && itemLevel.female)) {
                                                isWarn = true;
                                                continue;
                                            }
                                        }
                                    }
                                }
                            }

                            let newBody = (itemsBody === undefined);
                            let newCategory = (itemsCategory === undefined);

                            if (newBody) {
                                itemsBody = {
                                    ...body,
                                    categories: [],
                                    expanded: this.constants.settings.defaultExpand,
                                };
                            }

                            if (newCategory) {
                                itemsCategory = {
                                    ...category,
                                    levels: [],
                                    expanded: this.constants.settings.defaultExpand,
                                };
                            }

                            if (itemsCategory.requires_sanction && !itemsCategory.officially_sanctioned)
                                return;

                            itemsCategory.levels.push({
                                ...il,
                                ...level,
                            });

                            if (newCategory)
                                itemsBody.categories.push(itemsCategory);

                            if (newBody)
                                this.items.push(itemsBody);
                        }
                    });

                    if (isWarn)
                        this.warnMessage = 'Something went wrong while loading your existing levels.<br/>' +
                            'Please re-add them, or reload this page to try again.';
                } catch (error) {
                    this.errorMessage = error;
                    this.isError = true;
                }
            },

            levelUniqueId(level) {
                return level.id + (level.male ? '-m' : '') + (level.female ? '-f' : '');
            },

            confirmAction(msg, color, icon, callback, context) {
                $.confirm({
                    title: 'Are you sure ?',
                    content: msg,
                    icon: icon,
                    type: color,
                    typeAnimated: true,
                    buttons: {
                        no: function () {
                            this.close();
                        },
                        confirm:  {
                            text: 'Yes',
                            btnClass: 'btn-' + color,
                            action: function () {
                                callback();
                            }
                        }
                    }
                });
            },
        },
        mounted() {
            try {
                this.newLevel = {...this.newLevelDefaults};
                // this.first_discount_enable = this.meet.registration_first_discount_is_enable;
                // console.log(this.test_variable.registration_first_discount_is_enable);
                // this.first_discount_enable = this.test_variable.registration_first_discount_is_enable;
                for (const i in this.available_bodies) {
                    let body = this.available_bodies[i];
                    body.expanded = false;
                    body.requires_sanction = this.requires_sanction[body.id];
                    body.restricted = this.restricted_bodies[body.id];
                    body.locked = body.restricted || body.requires_sanction;

                    let categories = [];
                    for (const j in body.categories) {
                        let category = body.categories[j];

                        category.body = body.id;
                        category.expanded = false;
                        category.requires_sanction = body.requires_sanction;
                        category.locked = Boolean(this.restricted_bodies[body.id][category.id]) || category.requires_sanction;
                        category.can_edit = !(Boolean(this.restricted_bodies[body.id][category.id]) /*|| category.requires_sanction*/);//body.restricted;

                        for (const k in category.levels) {
                            let level = category.levels[k];
                            level.category = category.id;
                            level.body = body.id;
                            level.checked = false;
                            level.locked = category.locked;
                            level.can_edit = category.can_edit;
                        }

                        categories.push(category);
                    }
                    body.categories = categories;

                    this.bodies.push(body);
                }
            } catch (error) {
                this.errorMessage = error + '<br/>Please reload this page.';
                this.isError = true;
            }
        }
    }
</script>
