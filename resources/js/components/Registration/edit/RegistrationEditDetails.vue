<template>
    <div>
        <div class="modal fade" id="modal-registration-add-coach" tabindex="-1" role="dialog" aria-labelledby="modal-registration-add-coach" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">

                    <div class="modal-header">
                        <h5 class="modal-title text-primary">
                            <span class="fas fa-user-plus"></span> Add coach
                        </h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span class="fas fa-times" aria-hidden="true"></span>
                        </button>
                    </div>

                    <div class="modal-body">
                        <p>Please select which coach to add</p>
                        <div>
                            <select class="form-control form-control-sm" v-model="add_coach_coach">
                                <option value="">(Choose ...)</option>
                                <option v-for="coach in gym.coaches" :key="'modal-coach-' + coach.id"
                                    :value="coach">
                                    {{ coach.first_name }} {{ coach.last_name }}
                                </option>
                            </select>
                        </div>

                        <div class="text-right mt-3">
                            <button v-if="this.add_coach_coach"
                                class="btn btn-sm btn-success" @click="addModalCoach()">
                                <span class="fas fa-user-plus"></span> Add
                            </button>
                            <button v-else class="btn btn-sm btn-secondary" data-dismiss="modal">
                                <span class="far fa-fw fa-times-circle"></span> Close
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="modal-registration-add-athlete" tabindex="-1" role="dialog" aria-labelledby="modal-registration-add-athlete" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">

                    <div class="modal-header">
                        <h5 class="modal-title text-primary">
                            <span class="fas fa-user-plus"></span> Add Athlete
                        </h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span class="fas fa-times" aria-hidden="true"></span>
                        </button>
                    </div>

                    <div class="modal-body">
                        <div v-if="add_athlete_level">
                            <p>Please select which athlete to add</p>
                            <div>
                                <select class="form-control form-control-sm" v-model="add_athlete_athlete" multiple size='10'>
                                    <option v-for="athlete in filtreredAthletesForAddModal" :key="'modal-' + athlete.id"
                                        :value="athlete">
                                        {{ athlete.first_name }} {{ athlete.last_name }}
                                    </option>
                                </select>
                            </div>

                            <div v-if="add_athlete_level.allow_specialist && add_athlete_level.has_specialist && add_athlete_athlete.length > 0" class="mt-1">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="modal-add-athlete-specialist"
                                        v-model="add_athlete_specialist">
                                    <label class="form-check-label" for="modal-add-athlete-specialist">
                                        Add in specialist events
                                    </label>
                                </div>

                                <div v-if="add_athlete_specialist" class="ml-3">
                                    <b v-if="getFilteredSpecialistEvents(0).length > 0">Women's Event</b>
                                    <div v-for="evt in getFilteredSpecialistEvents(0)" :key="evt.id" class="form-check">
                                        <span v-if="evt.female">
                                            <input class="form-check-input" type="checkbox"
                                                :id="'modal-add-athlete-specialist-event' + evt.id"
                                                v-model="add_athlete_events[evt.id].checked">
                                            <label class="form-check-label" :for="'modal-add-athlete-specialist-event' + evt.id">
                                                {{ evt.name }}
                                            </label>
                                        </span>
                                    </div>
                                    <b v-if="getFilteredSpecialistEvents(1).length > 0">Men's Event</b>
                                    <div v-for="evt in getFilteredSpecialistEvents(1)" :key="evt.id" class="form-check">
                                        <span v-if="evt.male">
                                            <input class="form-check-input" type="checkbox"
                                                :id="'modal-add-athlete-specialist-event' + evt.id"
                                                v-model="add_athlete_events[evt.id].checked">
                                            <label class="form-check-label" :for="'modal-add-athlete-specialist-event' + evt.id">
                                                {{ evt.name }}
                                            </label>
                                        </span>
                                    </div>
                                    <b v-if="getFilteredSpecialistEvents(2).length > 0">Common Event</b>
                                    <div v-for="evt in getFilteredSpecialistEvents(2)" :key="evt.id" class="form-check">
                                        <span v-if="evt.female">
                                            <input class="form-check-input" type="checkbox"
                                                :id="'modal-add-athlete-specialist-event' + evt.id"
                                                v-model="add_athlete_events[evt.id].checked">
                                            <label class="form-check-label" :for="'modal-add-athlete-specialist-event' + evt.id">
                                                {{ evt.name }}
                                            </label>
                                        </span>
                                    </div>
                                    <!-- <div v-if="add_athlete_athlete.gender == 'female'">
                                    </div>
                                    <div v-else>
                                    </div> -->
                                </div>
                            </div>

                            <div class="text-right mt-3">
                                <button v-if="this.add_athlete_athlete.length > 0"
                                    class="btn btn-sm btn-success" @click="addModalAthlete()">
                                    <span class="fas fa-user-plus"></span> Add
                                </button>
                                <button v-else class="btn btn-sm btn-secondary" data-dismiss="modal">
                                    <span class="far fa-fw fa-times-circle"></span> Close
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="modal-registration-move-athlete" tabindex="-1" role="dialog" aria-labelledby="modal-registration-move-athlete" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">

                    <div class="modal-header">
                        <h5 class="modal-title text-primary">
                            <span class="fas fa-exchange-alt"></span> Move Athlete
                        </h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span class="fas fa-times" aria-hidden="true"></span>
                        </button>
                    </div>

                    <div class="modal-body">
                        <p>Please choose where to move this athlete :</p>
                        <div class="form-group">
                            <select class="form-control form-control-sm" v-model="move_athlete_body"
                                @change="move_athlete_category = ''; move_athlete_level = ''">
                                <option value="">(Choose ...)</option>
                                <option v-for="b in filtreredBodiesForMoveModal" :key="'modal-' + b.path"
                                    :value="b">
                                    {{ b.name }}
                                </option>
                            </select>
                        </div>

                        <div class="form-group">
                            <select class="form-control form-control-sm" v-model="move_athlete_category"
                                :disabled="!move_athlete_body" @change="move_athlete_level = ''">
                                <option value="">(Choose ...)</option>
                                <option v-for="c in move_athlete_body.categories" :key="'modal-' + c.path"
                                    :value="c">
                                    {{ c.name }}
                                </option>
                            </select>
                        </div>

                        <div class="form-group">
                            <select class="form-control form-control-sm" v-model="move_athlete_level"
                                :disabled="!move_athlete_category">
                                <option value="">(Choose ...)</option>
                                <option v-for="l in move_athlete_category.levels" :key="'modal-' + l.uid"
                                    :value="l">
                                    {{ l.name }} |
                                    <span v-if="l.male && l.female">(Both)</span>
                                    <span v-else-if="l.female">Female</span>
                                    <span v-else>Male</span>
                                </option>
                            </select>
                        </div>

                        <div class="text-right mt-3">
                            <button v-if="this.move_athlete_level"
                                class="btn btn-sm btn-success" @click="moveAthlete()">
                                <span class="fas fa-exchange-alt"></span> Move
                            </button>
                            <button v-else class="btn btn-sm btn-secondary" data-dismiss="modal">
                                <span class="far fa-fw fa-times-circle"></span> Close
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="modal-registration-add-events" tabindex="-1" role="dialog" aria-labelledby="modal-registration-add-events" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">

                    <div class="modal-header">
                        <h5 class="modal-title text-primary">
                            <span class="fas fa-user-plus"></span> Add Events
                        </h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span class="fas fa-times" aria-hidden="true"></span>
                        </button>
                    </div>

                    <div class="modal-body">
                        <p>Please select which events to add</p>

                        <div v-for="evt in add_athlete_events" :key="evt.id" class="form-check">
                            <input class="form-check-input" type="checkbox"
                                :id="'modal-add-events-event-' + evt.id"
                                v-model="add_athlete_events[evt.id].checked">
                            <label class="form-check-label" :for="'modal-add-events-event-' + evt.id">
                                {{ evt.name}}
                            </label>
                        </div>

                        <div class="text-right mt-3">
                            <button v-if="this.add_athlete_events"
                                class="btn btn-sm btn-success" @click="addModalEvents()">
                                <span class="fas fa-user-plus"></span> Add
                            </button>
                            <button v-else class="btn btn-sm btn-secondary" data-dismiss="modal">
                                <span class="far fa-fw fa-times-circle"></span> Close
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="small" v-if="isLoading">
            <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true">
            </span> Loading {{ plural }}, please wait ...
        </div>

        <div class="alert alert-danger" v-if="isError">
            <span class="fas fa-fw fa-times-circle"></span> Whoops !<br/>
            <div class="mt-1" v-html="errorMessage"></div>
        </div>

        <div class="alert alert-warning" v-if="warnMessage != null">
            <span class="fas fa-fw fa-exclamation-triangle"></span>
            <span v-html="warnMessage"></span>
        </div>

        <div v-if="!(isLoading || isError)">
            <div v-if="!gymId">
                <span class="fas fa-exclamation-triangle"></span> Please choose a gym to register with.
            </div>
            <div v-else>
                <!-- ################################ ATHLETES ################################ -->
                <div class="row clickable" @click="showAthletes = !showAthletes">
                    <div class="col">
                        <h5 class="border-bottom">
                            <span class="fas fa-fw fa-running"></span> Athletes
                            <span :class="'fas fa-fw fa-caret-' + (showAthletes ? 'down' : 'right')"></span>
                        </h5>
                    </div>
                </div>
                <div v-if="showAthletes">
                    <div class="d-flex flex-row flew-nowrap mb-2">
                        <div class="flex-grow-1">
                            <strong>Global limit :</strong>
                            <span v-if="meet.athlete_limit !== null">
                                {{ meet.freeSlots(true) }} free slots.
                            </span>
                            <span v-else>
                                No
                            </span>
                        </div>
                        <div>
                            <div class="d-inline-block mr-2 ml-2">
                                <button class="btn btn-sm btn-primary" title="Collapse All"
                                    @click="toggleItems(false)" type="button">
                                    <span class="fas fa-fw fa-compress"></span>
                                </button>
                                <button class="btn btn-sm btn-primary" title="Expand All"
                                    @click="toggleItems(true)" type="button">
                                    <span class="fas fa-fw fa-expand"></span>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="alert alert-info">
                        <span class="fas fa-fw fa-info-circle"></span> To register or modify your registration for USAG you must first use USAG Meet Reservations at usagym.org.
                    </div>

                    <div v-for="body in bodies" :key="body.path" class="mb-2">
                        <div class="mb-1">
                            <button class="btn btn-sm btn-dark btn-block text-left left-btn"
                                type="button" @click="toggleElement(body)">
                                <span class="fas fa-fw fa-receipt"></span>
                                {{ body.name }}
                                <span :class="'fas fa-fw fa-caret-' + (body.expanded ? 'down' : 'right')"></span>
                            </button>
                        </div>

                        <div v-if="body.expanded" class="ml-3">
                            <div v-if="!gymBodyFilter[body.id]" class="alert alert-danger small mb-0">
                                <span class="fas fa-fw fa-exclamation-circle"></span>
                                Your gym does not have a valid {{ constants.bodies[body.id] }} membership number on file.
                                Please update you gym details before proceeding.
                            </div>

                            <div v-else v-for="category in body.categories" :key="category.path" class="mb-1">
                                <div class="mb-1">
                                    <button class="btn btn-sm btn-info btn-block text-left left-btn"
                                        type="button" @click="toggleElement(category)">
                                        <span class="fas fa-fw fa-cubes"></span>
                                        {{ category.name }}
                                        <span :class="'fas fa-fw fa-caret-' + (category.expanded ? 'down' : 'right')"></span>
                                    </button>
                                </div>

                                <div v-if="category.expanded" class="ml-3 mb-2">
                                    <div v-for="level in category.levels" :key="level.uid" class="mb-1">
                                        <div class="mb-2">
                                            <div class="btn btn-sm btn-secondary btn-block text-left left-btn"
                                                    @click="toggleElement(level)">
                                                <div class="d-flex flex-no-wrap flex-row">
                                                    <div class="flex-grow-1">
                                                        <span>
                                                            <span v-if="(level.addedAthletes().length > 0) && (level.freeSlots() < 0)"
                                                                class="text-danger">
                                                                <span class="fas fa-fw fa-exclamation-triangle"></span>
                                                            </span>
                                                            <span v-else>
                                                                <span class="fas fa-fw fa-layer-group"></span>
                                                            </span>
                                                            {{ level.name }}
                                                        </span> |
                                                        <span class="ml-2 mr-2">
                                                            <span v-if="level.male && level.female">Both</span>
                                                            <span v-else-if="level.male">Male</span>
                                                            <span v-else-if="level.female">Female</span>
                                                        </span> |
                                                        <span class="ml-2 mr-2">
                                                            {{ level.athletes.length }} Athletes
                                                        </span> |
                                                        <span class="ml-2 mr-2">
                                                            <span v-if="level.enable_athlete_limit">
                                                                {{ level.freeSlots(true) }} free slots
                                                            </span>
                                                            <span v-else>
                                                                No limit
                                                            </span>
                                                        </span>
                                                        <span :class="'fas fa-fw fa-caret-' + (level.expanded ? 'down' : 'right')"></span>
                                                    </div>
                                                    <div v-if="level.disabled" class="text-danger font-weight-bold" title="This level was disabled by the meet host">
                                                        <span class="fas fa-fw fa-exclamation-triangle"></span> Disabled
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div v-if="level.expanded" class="ml-3 mb-1">
                                            <div v-if="level.disabled" class="class alert alert-danger small">
                                                <span class="fas fa-fw fa-exclamation-triangle"></span>
                                                This level was disabled by the meet host. You cannot add or move existing athletes to this level.
                                                You can still scratch or move athletes registered in this level to other levels.
                                                <strong>If you're not sure how to proceed, please reach out to the meet host.</strong>
                                            </div>

                                            <div v-if="level.allow_team" class="d-flex flex-row flex-no-wrap mb-1">
                                                <div>
                                                    <div v-if="level.team" class="text-info">
                                                        <span class="fas fa-users"></span>
                                                        Athletes in this level are registered as a team.
                                                    </div>
                                                    <div v-else class="">
                                                        <span class="far fa-user"></span>
                                                        Athletes in this level are NOT registered as a team.
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="small mb-1">
                                                <div v-if="permissions.scratch" class="btn-group">
                                                    <button v-if="!level.changes.team && level.team"
                                                        class="dropdown-item text-danger" style="border: 1px solid;" type="button"
                                                        @click="toggleTeam(level, false)">
                                                        <span class="fas fa-fw fa-eraser"></span> Scratch Team
                                                    </button>
                                                </div>
                                                <div class="btn-group" v-if="level.allow_team">
                                                    <button v-if="!level.changes.team && !level.team"
                                                        class="dropdown-item text-success" style="border: 1px solid;" type="button"
                                                        @click="toggleTeam(level, true)">
                                                        <span class="fas fa-fw fa-users"></span> Register as Team
                                                    </button>

                                                    <div v-else>
                                                        <button class="dropdown-item" type="button" style="border: 1px solid;"
                                                            @click="revertChanges(level, 'level')">
                                                            <span class="fa fa-fw fa-undo-alt"></span> Revert Changes
                                                        </button>
                                                    </div>
                                                </div>
                                                
                                            </div>

                                            <div class="small mb-1">
                                                <div>
                                                    <div class="d-inline-block mr-1" v-if="level.discount_fee">
                                                        <strong>Discount fee:</strong>
                                                        ${{ numberFormat(level.registration_fee)}}
                                                    </div>
                                                    <div class="d-inline-block mr-1" v-else>
                                                        <strong>Regular fee:</strong>
                                                        ${{ numberFormat(level.registration_fee)}}
                                                    </div>
                                                    <div class="d-inline-block" v-if="late">
                                                        | <strong>Late:</strong>
                                                        ${{ numberFormat(level.late_registration_fee)}}
                                                    </div>
                                                </div>
                                                <div v-if="level.has_specialist">
                                                    <div class="d-inline-block mr-1">
                                                        <strong>Specialist:</strong>
                                                        {{ level.allow_specialist ? 'Allowed' : 'Not Allowed'}}
                                                    </div>
                                                    <div class="d-inline-block" v-if="level.allow_specialist">
                                                        |
                                                        <div class="d-inline-block mr-1">
                                                            <strong>Specialist regular fee:</strong>
                                                            ${{ numberFormat(level.specialist_registration_fee)}}
                                                        </div>
                                                        <div class="d-inline-block mr-1" v-if="late">
                                                            | <strong>Specialist late fee:</strong>
                                                            ${{ numberFormat(level.specialist_late_registration_fee)}}
                                                        </div>
                                                    </div>
                                                </div>
                                                <div>
                                                    <div class="d-inline-block mr-1">
                                                        <strong>Team:</strong>
                                                        {{ level.allow_team ? 'Allowed' : 'Not Allowed'}}
                                                    </div>
                                                    <div class="d-inline-block" v-if="level.allow_team">
                                                        |
                                                        <div class="d-inline-block">
                                                            <strong>Team regular fee:</strong>
                                                            ${{ numberFormat(level.team_registration_fee)}}
                                                        </div>
                                                        <div class="d-inline-block" v-if="late">
                                                            | <strong>Team late fee:</strong>
                                                            ${{ numberFormat(level.team_late_registration_fee)}}
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="table-responsive-lg">
                                                <table class="table table-sm table-hover">
                                                    <thead class="bg-primary text-light">
                                                        <tr>
                                                            <th scope="col" class="align-middle"
                                                                v-if="level.has_specialist && level.allow_specialist">
                                                                Event
                                                            </th>
                                                            <th scope="col" class="align-middle">
                                                                First Name
                                                            </th>
                                                            <th scope="col" class="align-middle">
                                                                Last Name
                                                            </th>
                                                            <th scope="col" class="align-middle">
                                                                Date Of Birth
                                                            </th>
                                                            <th scope="col" class="align-middle">
                                                                {{ body.name }} No.
                                                            </th>
                                                            <th v-if="meet.tshirt_chart != null" scope="col" class="align-middle">
                                                                T-shirt
                                                            </th>
                                                            <th v-if="meet.leo_chart != null" scope="col" class="align-middle">
                                                                Leo
                                                            </th>
                                                            <th scope="col" class="align-middle">
                                                                Fee
                                                            </th>
                                                            <th scope="col" class="align-middle">
                                                                Status
                                                            </th>
                                                            <th scope="col" class="align-middle text-right">
                                                            </th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <tr v-if="level.athletes.length < 1">
                                                            <td colspan="9">
                                                                No athletes.
                                                            </td>
                                                        </tr>

                                                        <tr v-for="athlete in level.athletes" :key="level.uid + '-' + athlete.id">
                                                            <td v-if="level.has_specialist && level.allow_specialist" class="align-middle">
                                                                <div v-if="athlete.is_specialist" class="small">
                                                                    <div v-for="event in athlete.events" :key="event.id"
                                                                        class="d-flex flex-no-wrap flex-row align-items-top my-1">
                                                                        <div>
                                                                            <span v-if="event.is_new">
                                                                                <span class="fas fa-fw fa-user-plus text-info" title="New">
                                                                                </span>
                                                                            </span>
                                                                            <span v-else>
                                                                                <span v-if="event.status == constants.specialists.statuses.Registered"
                                                                                    class="fas fa-fw fa-check text-success" title="Registered">
                                                                                </span>

                                                                                <span v-else-if="event.status == constants.specialists.statuses.Pending"
                                                                                    class="fas fa-fw fa-clock text-warning" title="Pending">
                                                                                    <span v-if="event.in_waitlist"
                                                                                        class="fas fa-exclamation-triangle text-warning" title="Waitlist">
                                                                                    </span>
                                                                                </span>

                                                                                <span v-else class="fas fa-fw fa-times text-danger" title="Scratched"></span>
                                                                            </span>

                                                                            {{ specialist_events[event.event_id].name }}
                                                                            <span v-if="event.was_late" class="text-danger">(Late)</span>
                                                                        </div>
                                                                        <div class="btn-group dropdown">
                                                                            <button type="button" class="btn btm-sm btn-link pt-0"
                                                                                data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                                                <span class="fas fa-fw fa-ellipsis-v"></span>
                                                                            </button>
                                                                            <div class="dropdown-menu dropdown-menu-right">
                                                                                <div v-if="athlete.is_new || event.is_new">
                                                                                    <button class="dropdown-item text-danger"
                                                                                        type="button" @click="scratchObject(event, 'event', athlete, level)">
                                                                                        <span class="fas fa-fw fa-user-minus"></span> Remove
                                                                                    </button>
                                                                                </div>
                                                                                <div v-else>
                                                                                    <button v-if="event.permissions && event.permissions.scratch()" class="dropdown-item text-danger"
                                                                                        type="button" @click="scratchObject(event, 'event', athlete, level)">
                                                                                        <span class="fas fa-fw fa-user-slash"></span> Scratch
                                                                                    </button>

                                                                                    <div v-if="event.permissions && event.has_changes()">
                                                                                        <button class="dropdown-item" type="button"
                                                                                            @click="revertChanges(event, 'event', athlete, level)">
                                                                                            <span class="fa fa-fw fa-undo-alt"></span> Revert Changes
                                                                                        </button>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div v-else>
                                                                    All Around
                                                                </div>
                                                            </td>
                                                            <td class="align-middle">
                                                                <div v-if="athlete.editing.first_name">
                                                                    <input type="text" class="form-control form-control-sm"
                                                                        v-model="athlete.first_name" :disabled="!athlete.editing.first_name"
                                                                        :ref="'athlete-' + athlete.id + '-first_name'"
                                                                        @blur="stopEditingField(athlete, 'first_name')">
                                                                </div>
                                                                <div v-else @dblclick="startEditingField(athlete, 'first_name', 'athlete-' + athlete.id, 'change_details')">
                                                                    {{ athlete.first_name }}
                                                                </div>
                                                            </td>
                                                            <td class="align-middle">
                                                                <div v-if="athlete.editing.last_name">
                                                                    <input type="text" class="form-control form-control-sm"
                                                                        v-model="athlete.last_name" :disabled="!athlete.editing.last_name"
                                                                        :ref="'athlete-' + athlete.id + '-last_name'"
                                                                        @blur="stopEditingField(athlete, 'last_name')">
                                                                </div>
<!--                                                                <div v-else @dblclick="startEditingField(athlete, 'last_name', 'athlete-' + athlete.id, 'change_details')">-->
                                                                    {{ athlete.last_name}}
<!--                                                                </div>-->
                                                            </td>

                                                            <td class="align-middle">
                                                                <div v-if="athlete.editing.dob">
                                                                    <datepicker :input-class="'form-control form-control-sm vue-date-picker-fixer'"
                                                                        :format="'MM/dd/yyyy'" :value="athlete.dob.toDate()" :disabled="!athlete.editing.dob"
                                                                        @input="updateDate($event, athlete, 'dob')"
                                                                        :wrapper-class="'flex-grow-1'" :bootstrap-styling="true" :typeable="true"
                                                                        :ref="'athlete-' + athlete.id + '-dob'"
                                                                        @closed="stopEditingDate(athlete, 'dob', 'athlete-' + athlete.id)">
                                                                    </datepicker>
                                                                </div>
<!--                                                                <div v-else @dblclick="startEditingDate(athlete, 'dob', 'athlete-' + athlete.id, 'change_details')">-->
                                                                    {{ athlete.dob_display }}
<!--                                                                </div>-->
                                                            </td>

                                                            <td class="align-middle">
                                                                <div v-if="athlete.editing.sanction_no">
                                                                    <input type="text" class="form-control form-control-sm"
                                                                        v-model="athlete[athlete.sanction_no]" :disabled="!athlete.editing.sanction_no"
                                                                        :ref="'athlete-' + athlete.id + '-' + athlete.sanction_no"
                                                                        @input="updateSanctionField(athlete, 'sanction_no')"
                                                                        @blur="stopEditingField(athlete, 'sanction_no', true)">
                                                                </div>
<!--                                                                <div v-else @dblclick="startEditingSanction(athlete, 'sanction_no', 'athlete-' + athlete.id, 'change_number')">-->
                                                                    <span v-if="body.id == constants.bodies.USAG">
                                                                        {{ athlete.usag_no }}
                                                                    </span>
                                                                    <span v-if="body.id == constants.bodies.USAIGC">
                                                                        {{ athlete.usaigc_no }}
                                                                    </span>
                                                                    <span v-if="body.id == constants.bodies.AAU">
                                                                        {{ athlete.aau_no }}
                                                                    </span>
                                                                    <span v-else>
                                                                        {{ athlete.nga_no }}
                                                                    </span>
<!--                                                                </div>-->
                                                            </td>

                                                            <td v-if="meet.tshirt_chart != null" scope="col" class="align-middle">
                                                                <div v-if="athlete.editing.tshirt">
                                                                    <select v-model="athlete.tshirt_size_id" class="form-control form-control-sm"
                                                                        :ref="'athlete-' + athlete.id + '-tshirt'" :disabled="!athlete.editing.tshirt"
                                                                        @blur="stopEditingField(athlete, 'tshirt', true)" @change="updateClothingSize(athlete, 'tshirt')">
                                                                        <option value="-1">
                                                                            (Choose ...)
                                                                        </option>
                                                                        <option v-for="size in meet.tshirt_chart.sizes"
                                                                            :key="size.id" :value="size.id">
                                                                            {{ size.size }}
                                                                        </option>
                                                                    </select>
                                                                </div>
                                                                <div v-else @dblclick="startEditingField(athlete, 'tshirt', 'athlete-' + athlete.id, 'change_details')">
                                                                    <div v-if="athlete.tshirt != null">
                                                                        {{ athlete.tshirt.size }}
                                                                    </div>
                                                                    <div v-else>—</div>
                                                                </div>
                                                            </td>
                                                            <td v-if="meet.leo_chart != null" scope="col" class="align-middle">
                                                                <div v-if="athlete.gender == 'male'">—</div>
                                                                <div v-else-if="athlete.editing.leo">
                                                                    <select v-model="athlete.leo_size_id" class="form-control form-control-sm"
                                                                        :ref="'athlete-' + athlete.id + '-leo'" :disabled="!athlete.editing.leo"
                                                                        @blur="stopEditingField(athlete, 'leo', true)" @change="updateClothingSize(athlete, 'leo')">
                                                                        <option value="-1">
                                                                            (Choose ...)
                                                                        </option>
                                                                        <option v-for="size in meet.leo_chart.sizes"
                                                                            :key="size.id" :value="size.id">
                                                                            {{ size.size }}
                                                                        </option>
                                                                    </select>
                                                                </div>
                                                                <div v-else @dblclick="startEditingField(athlete, 'leo', 'athlete-' + athlete.id, 'change_details')">
                                                                    <div v-if="athlete.leo != null">
                                                                        {{ athlete.leo.size }}
                                                                    </div>
                                                                    <div v-else>—</div>
                                                                </div>
                                                            </td>

                                                            <td class="align-middle">
                                                                ${{ numberFormat(athlete.total) }}
                                                            </td>

                                                            <td class="align-middle">
                                                                <div v-if="level.disabled" class="d-inline-block text-danger mr-1" title="This level was disabled by the meet host">
                                                                    <span class="fas fa-fw fa-exclamation-triangle"></span>
                                                                </div>
                                                                <div class="d-inline-block">
                                                                    <div v-if="athlete.is_new">
                                                                        <span class="badge badge-info">New</span>
                                                                    </div>
                                                                    <div v-else-if="athlete.is_specialist">
                                                                        <div v-if="athlete.status == constants.specialists.statuses.Registered">
                                                                            <span class="badge badge-success">Registered</span>
                                                                        </div>

                                                                        <div v-else-if="athlete.status == constants.specialists.statuses.Pending">
                                                                            <span class="badge badge-warning">Pending</span>
                                                                        </div>

                                                                        <div v-else-if="athlete.status == constants.specialists.statuses.Scratched">
                                                                            <span class="badge badge-danger">Scratched</span>
                                                                        </div>

                                                                        <div v-else>
                                                                            <span class="badge badge-secondary">Mixed</span>
                                                                        </div>
                                                                    </div>
                                                                    <div v-else>
                                                                        <div v-if="athlete.status == constants.athletes.statuses.Registered">
                                                                            <span class="badge badge-success">Registered</span>
                                                                        </div>

                                                                        <div v-else-if="athlete.status == constants.athletes.statuses.NonReserved">
                                                                            <span class="badge badge-warning">
                                                                                <span v-if="athlete.in_waitlist">Waitlist</span>
                                                                                <span v-else>Pending</span>
                                                                                (Non-Reserved)
                                                                            </span>
                                                                        </div>

                                                                        <div v-else-if="athlete.status == constants.athletes.statuses.Reserved">
                                                                            <span class="badge badge-secondary">Pending<br/>(Reserved)</span>
                                                                        </div>

                                                                        <div v-else>
                                                                            <span class="badge badge-danger">Scratched</span>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </td>

                                                            <td class="align-middle text-right">
                                                                <span v-if="athlete.has_changes()" title="Changed. Click to revert changes." class="fas fa-fw fa-edit text-danger clickable" @click="revertChanges(athlete, 'athlete', level)">
                                                                </span>
                                                                <span v-if="athlete.pin_out_of_waitlist" title="Pinned Out Of Waitlist"
                                                                    class="fas fa-fw fa-thumbtack text-success">
                                                                </span>
                                                                <span v-if="athlete.to_waitlist" title="Waitlist"
                                                                    class="fas fa-fw fa-exclamation-triangle text-warning">
                                                                </span>
                                                                <div class="btn-group dropdown" v-if="!category.locked">
                                                                    <button type="button" class="btn btm-sm btn-link"
                                                                        data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                                        <span class="fas fa-fw fa-ellipsis-v"></span>
                                                                    </button>
                                                                    <div class="dropdown-menu dropdown-menu-right">
                                                                        <!-- <button v-if="(registration.transactions[0].status == 6) && athlete.in_waitlist"
                                                                            class="dropdown-item text-primary" type="button"
                                                                            @click="level.pinUnpin(athlete)">
                                                                            <span class="fas fa-fw fa-thumbtack"></span>
                                                                            <span v-if="!athlete.pin_out_of_waitlist">Pin Out Of Waitlist</span>
                                                                            <span v-else>Unpin</span>
                                                                        </button> -->
                                                                        <button v-if="!meet.is_waitlist && athlete.is_new &&
                                                                                ((level.waitlist_slots > 0) || (meet.waitlist_slots > 0))"
                                                                                class="dropdown-item text-primary" type="button"
                                                                                @click="level.pinUnpin(athlete)">
                                                                                <span class="fas fa-fw fa-thumbtack"></span>
                                                                                <span v-if="!athlete.pin_out_of_waitlist">Pin Out Of Waitlist</span>
                                                                                <span v-else>Unpin</span>
                                                                            </button>
                                                                        <div v-if="athlete.is_new">
                                                                            <button class="dropdown-item text-danger"
                                                                                type="button" @click="scratchObject(athlete, 'athlete', level)">
                                                                                <span class="fas fa-fw fa-user-minus"></span> Remove
                                                                            </button>
                                                                        </div>
                                                                        <div v-else>
                                                                            <button v-if="!athlete.is_scratched() && athlete.permissions.scratch()" class="dropdown-item text-danger"
                                                                                type="button" @click="scratchObject(athlete, 'athlete', level)">
                                                                                <span class="fas fa-fw fa-user-slash"></span> Scratch
                                                                            </button>
                                                                            <button v-if="!athlete.is_scratched() && !athlete.permissions.scratch() && (athlete.permissions.hasOwnProperty('scratch_without_refund') && athlete.permissions.scratch_without_refund())" class="dropdown-item text-danger"
                                                                                type="button" @click="scratchObject(athlete, 'athlete', level)">
                                                                                <span class="fas fa-fw fa-user-slash"></span> Scratch Without Refund
                                                                            </button>
                                                                            <button v-if="athlete.permissions.change_level()"
                                                                                class="dropdown-item text-info" type="button"
                                                                                    @click="showMoveModal(athlete, level.uid)">
                                                                                <span class="fas fa-fw fa-exchange-alt"></span> Move To ...
                                                                            </button>
                                                                            <button v-if="athlete.is_specialist && athlete.permissions.add_specialist_events()"
                                                                                class="dropdown-item" type="button" @click="showAddEventsModal(level, athlete)">
                                                                                <span class="fa fa-fw fa-user-tag"></span> Add Event
                                                                            </button>

                                                                            <div v-if="athlete.has_changes()">
                                                                                <button class="dropdown-item" type="button"
                                                                                    @click="revertChanges(athlete, 'athlete', level)">
                                                                                    <span class="fa fa-fw fa-undo-alt"></span> Revert Changes
                                                                                </button>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>

                                            <div class="d-flex flex-row flew-nowrap mt-1 mb-3 p-1 border-top">
                                                <div class="flex-grow-1">
                                                    <button v-if="!category.locked && (!level.disabled)" class="btn btn-sm btn-success"
                                                        @click="showAddModal(level)">
                                                        <span class="fas fa-user-plus"></span> Add Athlete 
                                                    </button>
                                                </div>
                                                <div>
                                                    <span class="text-grat-500 mr-1">
                                                        <span class="fas fa-coins"></span> Level Subtotal :
                                                    </span>
                                                    <span class="text-dark font-weight-bold">
                                                        ${{ numberFormat(level.subtotal) }}
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ################################ COACHES ################################ -->
                <div class="row clickable mt-3" @click="showCoaches = !showCoaches">
                    <div class="col">
                        <h5 class="border-bottom">
                            <span class="fas fa-fw fa-chalkboard-teacher"></span> Coaches
                            <span :class="'fas fa-fw fa-caret-' + (showCoaches ? 'down' : 'right')"></span>
                        </h5>
                    </div>
                </div>
                <div v-if="showCoaches">
                    <div class="table-responsive-lg ml-3">
                        <table class="table table-sm table-hover">
                            <thead class="bg-success text-light">
                                <tr>
                                    <th scope="col" class="align-middle">
                                    </th>

                                    <th scope="col" class="align-middle">
                                        First Name
                                    </th>
                                    <th scope="col" class="align-middle">
                                        Last Name
                                    </th>
                                    <th scope="col" class="align-middle">
                                        Date Of Birth
                                    </th>
                                    <th v-if="meet.tshirt_chart != null" scope="col" class="align-middle">
                                        T-shirt
                                    </th>
                                    <th scope="col" class="align-middle">
                                        USAG
                                    </th>
                                    <th scope="col" class="align-middle">
                                        USAIGC
                                    </th>
                                    <th scope="col" class="align-middle">
                                        AAU
                                    </th>
                                    <th scope="col" class="align-middle">
                                        NGA
                                    </th>
                                    <th scope="col" class="align-middle">
                                        Status
                                    </th>
                                    <th scope="col" class="align-middle text-right">
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-if="coaches.length < 1">
                                    <td colspan="6">
                                        No coaches.
                                    </td>
                                </tr>

                                <tr v-for="coach in coaches" :key="coach.id">
                                    <td class="align-middle">
                                        <span class="fas fa-check-circle text-success" title="Received in a USAG reservation"></span>
                                    </td>

                                    <td class="align-middle">
                                        <div v-if="coach.editing.first_name">
                                            <input type="text" class="form-control form-control-sm"
                                                v-model="coach.first_name" :disabled="!coach.editing.first_name"
                                                :ref="'coach-' + coach.id + '-first_name'"
                                                @blur="stopEditingField(coach, 'first_name')">
                                        </div>
                                        <div v-else @dblclick="startEditingField(coach, 'first_name', 'coach-' + coach.id, 'change_details')">
                                            {{ coach.first_name }}
                                        </div>
                                    </td>
                                    <td class="align-middle">
                                        <div v-if="coach.editing.last_name">
                                            <input type="text" class="form-control form-control-sm"
                                                v-model="coach.last_name" :disabled="!coach.editing.last_name"
                                                :ref="'coach-' + coach.id + '-last_name'"
                                                @blur="stopEditingField(coach, 'last_name')">
                                        </div>
                                        <div v-else @dblclick="startEditingField(coach, 'last_name', 'coach-' + coach.id, 'change_details')">
                                            {{ coach.last_name}}
                                        </div>
                                    </td>

                                    <td class="align-middle">
                                        <div v-if="coach.editing.dob">
                                            <datepicker :input-class="'form-control form-control-sm vue-date-picker-fixer'"
                                                :format="'MM/dd/yyyy'" :value="coach.dob.toDate()" :disabled="!coach.editing.dob"
                                                @input="updateDate($event, coach, 'dob')"
                                                :wrapper-class="'flex-grow-1'" :bootstrap-styling="true" :typeable="true"
                                                :ref="'coach-' + coach.id + '-dob'"
                                                @closed="stopEditingDate(coach, 'dob', 'coach-' + coach.id)">
                                            </datepicker>
                                        </div>
                                        <div v-else @dblclick="startEditingDate(coach, 'dob', 'coach-' + coach.id, 'change_details', 'change_details')">
                                            {{ coach.dob_display }}
                                        </div>
                                    </td>

                                    <td v-if="meet.tshirt_chart != null" scope="col" class="align-middle">
                                        <div v-if="coach.editing.tshirt">
                                            <select v-model="coach.tshirt_size_id" class="form-control form-control-sm"
                                                :ref="'coach-' + coach.id + '-tshirt'" :disabled="!coach.editing.tshirt"
                                                @blur="stopEditingField(coach, 'tshirt', true)" @change="updateClothingSize(coach, 'tshirt')">
                                                <option value="-1">
                                                    (Choose ...)
                                                </option>
                                                <option v-for="size in meet.tshirt_chart.sizes"
                                                    :key="size.id" :value="size.id">
                                                    {{ size.size }}
                                                </option>
                                            </select>
                                        </div>
                                        <div v-else @dblclick="startEditingField(coach, 'tshirt', 'coach-' + coach.id, 'change_details')">
                                            <div v-if="coach.tshirt != null">
                                                {{ coach.tshirt.size }}
                                            </div>
                                            <div v-else>—</div>
                                        </div>
                                    </td>

                                    <td class="align-middle">
                                        <div v-if="coach.editing.usag_no">
                                            <input type="text" class="form-control form-control-sm"
                                                v-model="coach.usag_no" :disabled="!coach.editing.usag_no"
                                                :ref="'coach-' + coach.id + '-usag_no'"
                                                @blur="stopEditingField(coach, 'usag_no')">
                                        </div>
                                        <div v-else @dblclick="startEditingField(coach, 'usag_no', 'coach-' + coach.id, 'change_number')">
                                            <div v-if="coach.usag_no != null">
                                                {{ coach.usag_no }}
                                            </div>
                                            <div v-else>—</div>
                                        </div>
                                    </td>

                                    <td class="align-middle">
                                        <div v-if="coach.editing.usaigc_no">
                                            <div class="input-group input-group-sm">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text">IGC</span>
                                                </div>
                                                <input type="text" class="form-control form-control-sm"
                                                    v-model="coach.usaigc_no" :disabled="!coach.editing.usaigc_no"
                                                    :ref="'coach-' + coach.id + '-usaigc_no'"
                                                    @blur="stopEditingField(coach, 'usaigc_no')">
                                            </div>
                                        </div>
                                        <div v-else @dblclick="startEditingField(coach, 'usaigc_no', 'coach-' + coach.id, 'change_number')">
                                            <div v-if="coach.usaigc_no != null">
                                                {{ coach.usaigc_no }}
                                            </div>
                                            <div v-else>—</div>
                                        </div>
                                    </td>

                                    <td class="align-middle">
                                        <div v-if="coach.editing.aau_no">
                                            <input type="text" class="form-control form-control-sm"
                                                v-model="coach.aau_no" :disabled="!coach.editing.aau_no"
                                                :ref="'coach-' + coach.id + '-aau_no'"
                                                @blur="stopEditingField(coach, 'aau_no')">
                                        </div>
                                        <div v-else @dblclick="startEditingField(coach, 'aau_no', 'coach-' + coach.id, 'change_number')">
                                            <div v-if="coach.aau_no != null">
                                                {{ coach.aau_no }}
                                            </div>
                                            <div v-else>—</div>
                                        </div>
                                    </td>

                                    <td class="align-middle">
                                        <div v-if="coach.editing.nga_no">
                                            <input type="text" class="form-control form-control-sm"
                                                   v-model="coach.nga_no" :disabled="!coach.editing.nga_no"
                                                   :ref="'coach-' + coach.id + '-nga_no'"
                                                   @blur="stopEditingField(coach, 'nga_no')">
                                        </div>
                                        <div v-else @dblclick="startEditingField(coach, 'nga_no', 'coach-' + coach.id, 'change_number')">
                                            <div v-if="coach.nga_no != null">
                                                {{ coach.nga_no }}
                                            </div>
                                            <div v-else>—</div>
                                        </div>
                                    </td>

                                    <td class="align-middle">
                                        <div v-if="coach.is_new">
                                            <span class="badge badge-info">New</span>
                                        </div>
                                        <div v-else>
                                            <div v-if="coach.status == constants.coaches.statuses.Registered">
                                                <span class="badge badge-success">Registered</span>
                                            </div>

                                            <div v-else-if="coach.status == constants.coaches.statuses.NonReserved">
                                                <span class="badge badge-warning">
                                                    <span v-if="coach.in_waitlist">Waitlist</span>
                                                    <span v-else>Pending</span>
                                                    <br/>(Non-Reserved)
                                                </span>
                                            </div>

                                            <div v-else-if="coach.status == constants.coaches.statuses.Reserved">
                                                <span class="badge badge-secondary">Pending<br/>(Reserved)</span>
                                            </div>

                                            <div v-else>
                                                <span class="badge badge-danger">Scratched</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="align-middle text-right">
                                        <span v-if="coach.has_changes()" title="Changed. Click to revert changes"
                                            class="fas fa-fw fa-edit text-danger clickable" @click="revertChanges(coach, 'coach')">
                                        </span>
                                        <span v-if="coach.to_waitlist" title="Waitlist"
                                            class="fas fa-fw fa-exclamation-triangle text-warning">
                                        </span>
                                        <div v-if="!coach.locked" class="btn-group dropdown">
                                            <button type="button" class="btn btm-sm btn-link"
                                                data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                <span class="fas fa-fw fa-ellipsis-v"></span>
                                            </button>
                                            <div class="dropdown-menu dropdown-menu-right">
                                                <div v-if="coach.is_new">
                                                    <button class="dropdown-item text-danger"
                                                        type="button" @click="scratchObject(coach, 'coach')">
                                                        <span class="fas fa-fw fa-user-minus"></span> Remove
                                                    </button>
                                                </div>
                                                <div v-else>
                                                    <button v-if="coach.permissions.scratch()" class="dropdown-item text-danger"
                                                        type="button" @click="scratchObject(coach, 'coach')">
                                                        <span class="fas fa-fw fa-user-slash"></span> Scratch
                                                    </button>

                                                    <div v-if="coach.has_changes()">
                                                        <button class="dropdown-item" type="button"
                                                            @click="revertChanges(coach, 'coach')">
                                                            <span class="fa fa-fw fa-undo-alt"></span> Revert Changes
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div v-if="permissions.scratch" class="mt-1">
                        <button class="btn btn-sm btn-success"
                            @click="showAddCoach()">
                            <span class="fas fa-user-plus"></span> Add Coach
                        </button>
                    </div>
                </div>

                <div class="d-flex flex-row flew-nowrap mt-3 mb-2 p-3 rounded bg-primary">
                    <div class="flex-grow-1 text-uppercase">
                        <span class="text-secondary mr-1">
                            <span class="fas fa-coins"></span> Total :
                        </span>
                        <span class="text-white font-weight-bold">${{ numberFormat(total) }}</span>
                    </div>
                    <div v-if="previous_remaining != 0" class="flex-grow-1 text-uppercase">
                        <span class="text-secondary mr-1">
                            <span class="fas fa-coins"></span> Previous Deposit Remaining :
                        </span>
                        <span class="text-white font-weight-bold">${{ numberFormat(previous_remaining) }}</span>
                    </div>
                    <div>
                        <button class="btn btn-sm btn-success" @click="validateRegistration">
                            Next <span class="fas fa-long-arrow-alt-right"></span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
    .clickable {
        cursor: pointer;
    }
</style>

<script>
import DatePicker from 'vuejs-datepicker';
import { v4 as uuidv4 } from 'uuid';

export default {
    name: 'RegistrationEditDetails',
    components: {
        'datepicker': DatePicker
    },
    props: {
        managed: {
            default: null,
            type: Number
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
        gymId: {
            type: Number,
            default: null
        },
        meetId: {
            type: Number,
            default: null
        },
        registrationId: {
            type: Number,
            default: null
        },
        requires_sanction: {
            type: Object,
            default: () => ({
                1: true,    // USAG
                2: false,   // USAIGC
                3: false,   // AAU
                4: false,   // NGA
            })
        },
        previous_remaining: {
            type: Number,
            default: null
        }
    },
    computed: {
        constants() {
            return {
                bodies: {
                    1: 'USAG',
                    2: 'USAIGC',
                    3: 'AAU',
                    4: 'NGA',
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
                transactions: {
                    methods: {
                        _array: [1, 2, 3, 4, 5],
                        1: 'Credit Card',
                        2: 'PayPal',
                        3: 'ACH',
                        4: 'Mailed Check',
                        5: 'Allgymnastics.com Balance',
                        Card: 1,
                        Paypal: 2,
                        Ach: 3,
                        Check: 4,
                        Balance: 5,
                    },
                    statuses: {
                        _array: [1, 2, 3, 4],
                        1: 'Pending',
                        2: 'Completed',
                        3: 'Canceled / Rejected',
                        4: 'Failed',
                        Pending: 1,
                        Completed: 2,
                        Canceled: 3,
                        Failed: 4,
                    },
                },
                registrations: {
                    statuses: {
                        1: 'Registered',
                        2: 'Pending Waitlist',
                        3: 'Confirmed Waitlist',
                        4: 'Canceled / Rejected',
                        Registered: 1,
                        Waitlist: 2,
                        ConfirmedWaitlist: 3,
                        Canceled: 4,
                    }
                },
                athletes: {
                    statuses: {
                        1: 'Registered',
                        2: 'Pending (Non-Reserved)',
                        3: 'Pending (Reserved)',
                        4: 'Scratched',
                        Mixed: -1,
                        Registered: 1,
                        NonReserved: 2,
                        Reserved: 3,
                        Scratched: 4,
                    }
                },
                specialists: {
                    statuses: {
                        '-1': 'Mixed',
                        1: 'Registered',
                        2: 'Pending',
                        4: 'Scratched',
                        Mixed: -1,
                        Registered: 1,
                        Pending: 2,
                        Scratched: 4,
                    }
                },
                coaches: {
                    statuses: {
                        1: 'Registered',
                        2: 'Pending (Non-Reserved)',
                        3: 'Pending (Reserved)',
                        4: 'Scratched',
                        Registered: 1,
                        NonReserved: 2,
                        Reserved: 3,
                        Scratched: 4,
                    }
                },
            };
        },

        filtreredBodiesForMoveModal() {
            let  bodies = [];

            if (this.move_athlete_athlete !== null) {
                for (let i in this.bodies) {
                    let b = _.cloneDeep(this.bodies[i]);
                    if (!this.gymBodyFilter[b.id])
                        continue;

                    let bodyName = this.constants.bodies[b.id].toLowerCase();
                    let hasSanction = this.move_athlete_athlete[bodyName + '_active'] &&
                        (this.move_athlete_athlete[bodyName + '_no'] !== null);

                    if (!hasSanction)
                        continue;

                    let categories = [];
                    for (let j in b.categories) {
                        let c = _.cloneDeep(b.categories[j]);
                        let levels = c.levels.filter(l => {
                            if (l.disabled)
                                return false;

                            if (l.uid == this.move_athlete_current_level)
                                return false;
                            let male = this.move_athlete_athlete.gender == 'male';
                            let female = this.move_athlete_athlete.gender == 'female';
                            let flag = true;

                            if (male)
                                flag = flag && l.male;

                            if (female)
                                flag = flag && l.female;

                            if (this.move_athlete_athlete.is_specialist)
                                flag = flag && l.has_specialist;

                            return flag;
                        });

                        if (levels.length > 0) {
                            c.levels = levels;
                            categories.push(c);
                        }
                    }
                    if (categories.length > 0) {
                        b.categories = categories;
                        bodies.push(b);
                    }
                }
            }
            return bodies;
        },

        filtreredAthletesForAddModal() {
            let  result = [];

            if (this.add_athlete_level != null) {
                let body;
                switch(this.add_athlete_level.sanctioning_body_id) {
                    case this.constants.bodies.USAG:
                        body = 'usag';
                        break;

                    case this.constants.bodies.USAIGC:
                        body = 'usaigc';
                        break;

                    case this.constants.bodies.AAU:
                        body = 'aau';
                        break;

                    case this.constants.bodies.NGA:
                        body = 'nga';
                        break;
                }
                result = this.gym.athletes.filter(a => {
                    let sanction = a[body + '_active'] && (a[body + '_no'] !== null);
                    let gender = (this.add_athlete_level.male && (a.gender == 'male')) ||
                            (this.add_athlete_level.female && (a.gender == 'female'));

                    return sanction && gender;
                });
            }

            return result;
        }
    },
    data() {
        return {
            isInitialized: false,
            isLoading: false,
            isError: false,
            errorMessage: '',
            warnMessage: null,

            meet: null,
            gym: null,
            gymBodyFilter: {},
            registration: null,

            bodies: {},
            coaches: [],
            specialist_events: {},

            total: 0.00,
            add_coach_coach: null,

            add_athlete_level: null,
            add_athlete_athlete: [],
            add_athlete_specialist: false,
            add_athlete_events: {},

            move_athlete_body: '',
            move_athlete_category: '',
            move_athlete_level: '',
            move_athlete_current_level: '',
            move_athlete_athlete: '',

            showAthletes: true,
            showCoaches: false,

            genderAwareMeetLevelMatrix: {},
            registrationLevelToMeetLevelMatrix: {},

            permissions: {
                change_details: false,
                change_level:false,
                change_number:false,
                change_specialist_events:false,
                scratch:false,
                scratch_without_refund: false
            },

            editing: {
                object: null,
                field: null
            },

            level_as_a_team: false,
        }
    },
    watch: {
    },
    methods: {
        getFilteredSpecialistEvents(gender){
            let maleEvents = [];
            let femaleEvents = [];
            let common = [];
            if(this.add_athlete_events != null)
            {
                for (let event in this.add_athlete_events)
                {
                    let evt = this.add_athlete_events[event];
                    if(evt.male && !evt.female)
                        maleEvents.push(evt);
                    if(evt.female && !evt.male)
                        femaleEvents.push(evt);
                    if(evt.male && evt.female)
                        common.push(evt);
                }
            }
            if(gender == 1)
                return maleEvents;
            else if(gender == 0)
                return femaleEvents;
            else if(gender == 2)
                return common;
        },
        hasSpecialist(body, category) {
            return (((body.id == this.constants.bodies.USAIGC) && (category.id == this.constants.categories.GYMNASTICS_WOMEN))
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

        toggleElement(el) {
            el.expanded = !el.expanded;
        },

        toggleItems(toggle) {
            for (let i in this.bodies) {
                let b = this.bodies[i];
                b.expanded = toggle;
                for (let j in b.categories) {
                    let c = b.categories[j];
                    c.expanded = toggle;
                    for (let k in c.levels) {
                        let l = c.levels[k];
                        l.expanded = toggle
                    }
                }
            }
        },

        calculateAthleteFee(level, athlete, stop) {
            let total = 0;

            if (!athlete.to_waitlist) {
                 if (athlete.is_specialist) {
                    athlete.events.forEach(event => {
                        total += Utils.toFloat(event.new_fee) + Utils.toFloat(event.new_late_fee)
                            - Utils.toFloat(event.new_refund) - Utils.toFloat(event.new_late_refund);
                    });
                } else {
                    total = Utils.toFloat(athlete.new_fee) + Utils.toFloat(athlete.new_late_fee)
                            - Utils.toFloat(athlete.new_refund) - Utils.toFloat(athlete.new_late_refund);
                }
            }

            athlete.total = total;
            if (stop !== true)
                this.calculateLevelSubtotal(level);
        },

        calculateLevelSubtotal(level, stop) {
            let total = 0;

            let levelHasSpecialistOnly = true;

            level.athletes.filter(a => !a.to_waitlist)
                            .forEach(a => {
                                total += a.total;

                                if (!a.is_specialist)
                                    levelHasSpecialistOnly = false;
                            });

            if (level.allow_team) {
                if (level.team) {
                    let diff = (level.team_fee + level.team_late_fee - level.team_refund - level.team_late_refund);
                    if (diff == 0) {
                        total += level.team_registration_fee;
                        if (this.late)
                            total += level.team_late_registration_fee;
                    }
                }
            }

            level.subtotal = total;
            if (stop !== true)
                this.calculateMeetTotal();
        },

        calculateMeetTotal() {
            let total = 0;

            for (let i in this.bodies) {
                let b = this.bodies[i];
                for (let j in b.categories) {
                    let c = b.categories[j];
                    for (let k in c.levels) {
                        let l = c.levels[k];
                        total += l.subtotal;
                    }
                }
            }

            if ((total > 0) && (this.late)) {
                let diff = (this.registration.late_fee - this.registration.late_refund);

                if (diff == 0)
                    total += this.meet.late_registration_fee;
            }

            this.total = total;

            this.$emit('total-changed', total);
        },

        calculateMeetFees() {
            for (let i in this.bodies) {
                let b = this.bodies[i];
                for (let j in b.categories) {
                    let c = b.categories[j];
                    for (let k in c.levels) {
                        let l = c.levels[k];
                        l.athletes.forEach(a => {
                            this.calculateAthleteFee(l, a, true);
                        });
                        this.calculateLevelSubtotal(l, true);
                    }
                }
            }
            this.calculateMeetTotal();
        },

        startEditingField(obj, field, refPrefix, permissionFn, skipCompare) {
            if (permissionFn) {
                let permission = obj.permissions[permissionFn]();
                if (!permission)
                    return;
            }

            if (this.editing.object !== null) {
                this.stopEditingField(this.editing.object, this.editing.field, skipCompare)
            }

            if (obj.locked && !['first_name', 'tshirt', 'leo'].includes(field))
                return;

            this.editing.object = obj;
            this.editing.field = field;
            obj.editing[field] = true;

            if  (refPrefix) {
                this.$nextTick(() => { this.$refs[refPrefix + '-' + field][0].focus()});
            }
        },

        stopEditingField(obj, field, skipCompare, skipConvert) {
            if (!skipCompare) {
                if (!skipConvert)
                    obj[field] = (obj[field] == '' ? null : obj[field]);

                obj.changes[field] = (obj[field] != obj.original_data[field]);
            }
            this.editing.object = null;
            this.editing.field = null;
            obj.editing[field] = false;
        },

        startEditingDate(obj, field, refPrefix, permissionFn) {
            let permission = obj.permissions[permissionFn]();
            if (!permission)
                return;

            if (obj.locked)
                return;

            this.startEditingField(obj, field, null);

            if  (refPrefix) {
                this.$nextTick(() => {
                    let picker = this.$refs[refPrefix + '-' + field][0];
                    picker.$el.querySelector("input").focus();
                    picker.showCalendar();
                });
            }
        },

        stopEditingDate(obj, field) {
            this.stopEditingField(obj, field);
        },

        startEditingSanction(obj, field, refPrefix, permissionFn) {
            let permission = obj.permissions[permissionFn]();
            if (!permission)
                return;

            this.startEditingField(obj, field, null);

            if  (refPrefix) {
                this.$nextTick(() => {
                    this.$refs[refPrefix + '-' + obj[field]][0].focus();
                });
            }
        },

        updateDate(val, obj, field, allowNull) {
            if (val !== null) {
                obj[field] = Moment(val);
                obj[field + '_display'] = obj[field].format('MM/DD/YYYY');
            }
            //this.stopEditingDate(obj, field);
        },

        updateClothingSize(obj, type) {
            let newSize = this.meet[type + '_chart'].sizes
                            .filter(s => s.id == obj[type + '_size_id']);

            if (newSize.length != 1) {
                obj[type + '_size_id'] = -1;
                obj[type] = null;
            } else {
                newSize = newSize[0];
                obj[type] = _.cloneDeep(newSize);
            }
            obj.changes[type] = (obj[type + '_size_id'] != obj.original_data[type + '_size_id']);

            this.stopEditingField(obj, type, true);
        },

        updateSanctionField(obj, field) {
            obj.changes[field] = (obj[obj[field]] != obj.original_data[obj[field]]);
        },

        scratchObject(obj, type, parent, grandpa) {
            switch (type) {
                case 'athlete':
                    if (obj.is_new) {
                        Utils.remove(parent.athletes, obj);
                    } else if (obj.is_specialist) {
                        obj.events.forEach(evt => {
                            if(evt.status != this.constants.athletes.statuses.Scratched)
                                this.scratchObject(evt, 'event', obj, parent);
                        });
                        obj.status = this.constants.athletes.statuses.Scratched;
                        obj.changes.scratch = true;
                    } else {
                        obj.status = this.constants.athletes.statuses.Scratched;
                        obj.changes.scratch = true;
                        parent.freed_slots++;
                    }
                    break;

                case 'event':
                    let prevStatus = parent.status;
                    if (obj.is_new) {
                        Utils.remove(parent.events, obj);

                        if (parent.events.filter(e => e.is_new).length < 1)
                            parent.changes.events = false;

                        if (parent.events.length < 1)
                            Utils.remove(grandpa.athletes, parent);
                    } else {
                        obj.status = this.constants.specialists.statuses.Scratched;
                        obj.changes.scratch = true;
                    }
                    // parent.deduceStatus();
                    // if ((prevStatus != parent.status) && (parent.status == this.constants.specialists.statuses.Scratched)) {
                    //     parent.changes.scratch = true;
                    // }
                    break;

                case 'coach':
                    if (obj.is_new) {
                        Utils.remove(this.coaches, obj);
                    } else {
                        obj.status = this.constants.coaches.statuses.Scratched;
                        obj.changes.scratch = true;
                    }
                    break;
            }
            this.calculateWaitlistStatuses();
        },

        revertChanges(obj, type, parent, grandpa) {
            switch (type) {
                case 'athlete':
                    if (obj.is_new) {
                        //console.log('new ?');
                    } else {
                        if (obj.is_specialist) {
                            let i = obj.events.length;
                            while (i--) {
                                let evt = obj.events[i];
                                this.revertChanges(evt, 'event', obj, parent);
                            };
                            obj.changes.events = false;
                        } else {
                            if (obj.changes.scratch) {

                                if (
                                    parent.athletes
                                            .filter(a => {
                                                return (a[a.sanction_no] == obj[obj.sanction_no]) && !a.is_scratched();
                                            }).length > 0
                                ) {
                                    this.showAlert(
                                        'Cannot unscratch athlete. An athlete with the same number already exists in the level.',
                                        'Whoops !',
                                        'red',
                                        'fas fa-times-circle'
                                    );
                                } else {
                                    obj.status = obj.original_data.status;
                                    obj.changes.scratch = false;
                                    parent.freed_slots--;
                                }
                            }
                        }

                        if (obj.changes.moved_to) {
                            let curLevel = parent;
                            let origLevel = this.genderAwareMeetLevelMatrix[
                                this.registrationLevelToMeetLevelMatrix[
                                    obj.original_data.registration_level.id
                                ]
                            ];

                            let newSanction = this.constants.bodies[origLevel.sanctioning_body_id
                                                            ].toLowerCase() + '_no';

                            if (
                                origLevel.athletes
                                        .filter(a => {
                                            return (a[a.sanction_no] == obj[newSanction]) && !a.is_scratched();
                                        }).length > 0
                            ) {
                                this.showAlert(
                                    'Cannot move athlete back to original level. An athlete with the same number already exists in the original level.',
                                    'Whoops !',
                                    'red',
                                    'fas fa-times-circle'
                                );
                            } else {
                                obj.changes.moved_to = false;
                                Utils.remove(curLevel.athletes, obj);
                                origLevel.athletes.push(obj);

                                obj.sanction_no = newSanction;
                                obj.changes.sanction_no = false;
                            }
                        }

                        if (obj.changes.first_name) {
                            obj.first_name = obj.original_data.first_name;
                            obj.changes.first_name = false;
                        }

                        if (obj.changes.last_name) {
                            obj.last_name = obj.original_data.last_name;
                            obj.changes.last_name = false;
                        }

                        if (obj.changes.dob) {
                            obj.dob = Moment(obj.original_data.dob.toDate());
                            obj.dob_display = obj.dob.format('MM/DD/YYYY');
                            obj.changes.dob = false;
                        }

                        if (obj.changes.tshirt) {
                            obj.tshirt = _.cloneDeep(obj.original_data.tshirt);
                            obj.tshirt_size_id = obj.original_data.tshirt_size_id;
                            obj.changes.tshirt = false;
                        }

                        if (obj.changes.leo) {
                            obj.leo = _.cloneDeep(obj.original_data.leo);
                            obj.leo_size_id = obj.original_data.leo_size_id;
                            obj.changes.leo = false;
                        }

                        if (obj.changes.sanction_no) {
                            obj[obj.sanction_no] = obj.original_data[obj.sanction_no];
                            obj.changes.sanction_no = false;
                        }

                        if (!obj.is_new) {
                            obj.fee = 0;
                            obj.late_fee = 0;
                            obj.refund = 0;
                            obj.late_refund = 0;

                            obj.new_fee = 0;
                            obj.new_late_fee = 0;
                            obj.new_refund = 0;
                            obj.new_late_refund = 0;
                        }
                    }
                    break;

                case 'event':
                    if (obj.is_new) {
                        Utils.remove(parent.events, obj);
                    } else {
                        let prevStatus = parent.status;

                        if (obj.changes.scratch) {
                            obj.status = obj.original_data.status;
                            obj.changes.scratch = false;
                        }

                        if (!obj.is_new) {
                            obj.fee = 0;
                            obj.late_fee = 0;
                            obj.refund = 0;
                            obj.late_refund = 0;

                            obj.new_fee = 0;
                            obj.new_late_fee = 0;
                            obj.new_refund = 0;
                            obj.new_late_refund = 0;
                        }

                        parent.deduceStatus();
                        if ((prevStatus == this.constants.specialists.statuses.Scratched)
                            && (parent.status != this.constants.specialists.statuses.Scratched)) {
                            parent.changes.scratch = false;
                        }
                    }
                    break;

                case 'coach':
                    if (obj.is_new) {
                        //console.log('new ?');
                    } else {
                        if (obj.changes.scratch) {
                            obj.status = obj.original_data.status;
                            obj.changes.scratch = false;
                        }

                        if (obj.changes.first_name) {
                            obj.first_name = obj.original_data.first_name;
                            obj.changes.first_name = false;
                        }

                        if (obj.changes.last_name) {
                            obj.last_name = obj.original_data.last_name;
                            obj.changes.last_name = false;
                        }

                        if (obj.changes.dob) {
                            obj.dob = Moment(obj.original_data.dob.toDate());
                            obj.dob_display = obj.dob.format('MM/DD/YYYY');
                            obj.changes.dob = false;
                        }

                        if (obj.changes.usag_no) {
                            obj.usag_no = obj.original_data.usag_no;
                            obj.changes.usag_no = false;
                        }

                        if (obj.changes.usaigc_no) {
                            obj.usaigc_no = obj.original_data.usaigc_no;
                            obj.changes.usaigc_no = false;
                        }

                        if (obj.changes.aau_no) {
                            obj.aau_no = obj.original_data.aau_no;
                            obj.changes.aau_no = false;
                        }

                        if (obj.changes.nga_no) {
                            obj.nga_no = obj.original_data.nga_no;
                            obj.changes.nga_no = false;
                        }

                        if (obj.changes.tshirt) {
                            obj.tshirt = _.cloneDeep(obj.original_data.tshirt);
                            obj.tshirt_size_id = obj.original_data.tshirt_size_id;
                            obj.changes.tshirt = false;
                        }
                    }
                    break;

                case 'level':
                    if (obj.changes.team) {
                        this.level_as_a_team = obj.original_data.team;
                        obj.team = obj.original_data.team;
                        obj.changes.team = false;
                    }
                    break;
            }
            this.calculateWaitlistStatuses();
        },

        showMoveModal(athlete, current) {
            if (athlete.locked)
                return;

            this.move_athlete_body = '';
            this.move_athlete_category = '';
            this.move_athlete_level = '';
            this.move_athlete_current_level = current;
            this.move_athlete_athlete = athlete;
            $('#modal-registration-move-athlete').modal('show');
        },

        moveAthlete() {
            $('#modal-registration-move-athlete').modal('hide');

            if (this.move_athlete_level) {
                let athlete = this.move_athlete_athlete;
                let newLevel = this.genderAwareMeetLevelMatrix[this.move_athlete_level.uid];
                let oldLevel = this.genderAwareMeetLevelMatrix[this.move_athlete_current_level];
                let origLevel = this.genderAwareMeetLevelMatrix[
                    this.registrationLevelToMeetLevelMatrix[
                        athlete.original_data.registration_level.id
                    ]
                ];

                if (!athlete.is_specialist && newLevel.enable_athlete_limit && (newLevel.freeSlots() < 1)) {
                    this.showAlert(
                        'This level is at capacity. To move an athlete into this level,' +
                        ' please scrath them from the old level and add them into the new level',
                        'Whoops !',
                        'red',
                        'fas fa-times-circle'
                    );
                    return;
                }


                let newSanction = this.constants.bodies[newLevel.sanctioning_body_id
                                            ].toLowerCase() + '_no';

                let exists = newLevel.athletes.filter(a => {
                                            return (a[a.sanction_no] == athlete[newSanction]) && !a.is_scratched();
                                        }).length > 0;
                if (exists) {
                    this.showAlert(
                        'An athlete with the same number already exists in this level',
                        'Whoops !',
                        'red',
                        'fas fa-times-circle'
                    );
                    return;
                }

                athlete.changes.moved_to = newLevel.uid;
                Utils.remove(oldLevel.athletes, athlete);
                newLevel.athletes.push(athlete);

                let newFee;
                let newLateFee;
                let moveAthleteNewFee = 0;

                if (athlete.is_specialist) {
                    let newFee = 0;
                    let newLateFee = 0;
                    let moveSpecialistNewFee = 0;

                    athlete.events.forEach(evt => {
                        if (!evt.is_scratched()) {
                            moveSpecialistNewFee = newLevel.specialist_registration_fee - oldLevel.specialist_registration_fee;
                            if(oldLevel.specialist_registration_fee > newLevel.specialist_registration_fee){
                                moveSpecialistNewFee = 0;
                            }
                            evt.new_fee = moveSpecialistNewFee;
                            evt.new_late_fee = 0; //already registered - do not charge late fee ::: this.late ? newLevel.specialist_late_registration_fee : 0;
                        }
                    });
                } else {
                    moveAthleteNewFee = newLevel.registration_fee - oldLevel.registration_fee;
                    if(oldLevel.registration_fee > newLevel.registration_fee){
                        moveAthleteNewFee = 0;
                    }
                    athlete.new_fee = (moveAthleteNewFee < 0) ? -(moveAthleteNewFee) : moveAthleteNewFee;
                    // athlete.new_late_fee = this.late ? newLevel.late_registration_fee : 0;
                    athlete.new_late_fee = 0;
                }

                athlete.sanction_no = newSanction;
                athlete.changes.sanction_no = (athlete[athlete.sanction_no] != athlete.original_data[athlete.sanction_no]);

                this.calculateWaitlistStatuses();
            }
        },

        toggleTeam(level, toggle) {
            try {
                if (level.athletes.length < 2)
                    throw 'You need to select at least two athletes for register as team.';

                this.level_as_a_team = true;

                if (!this.permissions.scratch && toggle == false)
                    return;

                level.team = toggle;
                level.changes.team = (level.team != level.original_data.team);
                this.calculateWaitlistStatuses();
            } catch (error) {
                this.showAlert(
                    error,
                    'Whoops !',
                    'red',
                    'fas fa-times-circle'
                );
            }
        },

        showAddModal(level) {
            if (level.disabled || level.locked)
                return;

            this.add_athlete_level = level;
            this.add_athlete_athlete = [];
            this.add_athlete_specialist = false;
            this.add_athlete_events = {};
            for (let i in this.specialist_events) {
                let evt = this.specialist_events[i];
                if (!this.add_athlete_events[evt.id]) {
                    // If the entry with evt.id doesn't exist, add it
                    Vue.set(this.add_athlete_events, evt.id, {
                        ..._.cloneDeep(evt),
                        checked: false,
                    });
                }

                // if(level.sanctioning_body.id == evt.sanctioning_body.id)
                // {
                //     Vue.set(this.add_athlete_events, evt.id, {
                //         ... _.cloneDeep(evt),
                //         checked: false,
                //     });
                // }
                
            }
            $('#modal-registration-add-athlete').modal('show');
        },

        addModalAthlete() {
            $('#modal-registration-add-athlete').modal('hide');

            if (this.add_athlete_athlete.length > 0) {
                let vm = this;
                let events = [];
                if (this.add_athlete_specialist) {
                    for (let x in this.add_athlete_events) {
                        let addedEvent = this.add_athlete_events[x];
                        if (addedEvent.checked) {
                            let evt = {
                                changes: {
                                    scratch: false,
                                },
                                event_id: addedEvent.id,
                                fee: 0,
                                refund: 0,
                                late_fee: 0,
                                late_refund: 0,
                                new_fee: this.add_athlete_level.specialist_registration_fee,
                                new_refund: 0,
                                new_late_fee: this.late ? this.add_athlete_level.specialist_late_registration_fee : 0,
                                new_late_refund: 0,
                                has_changes: function() {
                                    for (let i in this.changes) {
                                        let c = this.changes[i];
                                        if ((c !== null) && (c !== false))
                                            return true;
                                    }
                                    return false;
                                },
                                id: 'evt-' + _.uniqueId(),
                                in_waitlist: false,
                                is_scratched: function() {
                                    return this.status == vm.constants.specialists.statuses.Scratched
                                },
                                is_new: true,
                                permissions: {
                                    scratch: function() {
                                        return evt.is_new || (
                                            vm.permissions.scratch &&
                                            (evt.status == vm.constants.specialists.statuses.Registered)
                                        );
                                    },
                                    scratch_without_refund: function(){
                                        return evt.is_new || (
                                            !vm.permissions.scratch &&
                                            !evt.has_pending_events()
                                        );
                                    }
                                },
                                status: this.constants.specialists.statuses.Pending,
                                was_late: this.late,
                            };
                            evt.original_data = _.cloneDeep(evt);
                            events.push(evt);
                        }
                    }

                    if (events.length < 1) {
                        this.showAlert(
                            'Please choose at least one event to register an athlete in specialist events',
                            'Whoops !',
                            'red',
                            'fas fa-times-circle'
                        );
                        return;
                    }
                }
                for(let atl in this.add_athlete_athlete)
                {
                    let athlete = _.cloneDeep(this.add_athlete_athlete[atl]);
                    this.status == vm.constants.athletes.statuses.NonReserved;

                    athlete.is_scratched = function() {
                        return this.status == vm.constants.athletes.statuses.Scratched
                    };

                    athlete.original_id = athlete.id;
                    athlete.id = uuidv4();
                    athlete.is_new = true;
                    athlete.to_waitlist = false;
                    athlete.is_specialist = this.add_athlete_specialist;
                    athlete.pin_out_of_waitlist = false;
                    athlete.events = (athlete.is_specialist ? events : null);
                    athlete.was_late = this.late;
                    athlete.fee = 0;
                    athlete.refund = 0;
                    athlete.late_fee = 0;
                    athlete.late_refund = 0;
                    athlete.gender_display = athlete.gender.charAt(0).toUpperCase() + athlete.gender.slice(1)
                    athlete.dob = Moment(athlete.dob);
                    athlete.dob_display = athlete.dob.format('MM/DD/YYYY');
                    athlete.sanction_no = this.constants.bodies[
                                                this.add_athlete_level.sanctioning_body_id
                                            ].toLowerCase() + '_no';

                    let exists = this.add_athlete_level.athletes.filter(a => {
                                        return (a[a.sanction_no] == athlete[athlete.sanction_no]) && !a.is_scratched();
                                    }).length > 0;
                    if (exists) {
                        this.showAlert(
                            'An athlete with the same number already exists in this level',
                            'Whoops !',
                            'red',
                            'fas fa-times-circle'
                        );
                        return;
                    }

                    if (this.meet.tshirt_chart != null) {
                        if ((athlete.tshirt == null) ||
                            (this.meet.tshirt_chart.id != athlete.tshirt.clothing_size_chart_id)) {
                            athlete.tshirt_size_id = -1;
                            athlete.tshirt = null;
                        }
                    }

                    if (this.meet.leo_chart != null) {
                        if ((athlete.leo == null) ||
                            (this.meet.leo_chart.id != athlete.leo.clothing_size_chart_id)) {
                            athlete.leo_size_id = -1;
                            athlete.leo = null;
                        }
                    }

                    if (athlete.is_specialist) {
                        athlete.editing = {
                            first_name: false,
                            last_name: false,
                            dob: false,
                            sanction_no: false,
                            tshirt: false,
                            leo: false,
                        };

                        athlete.changes = {
                            events: false,
                            scratch: false,
                            moved_to: false,
                            first_name: false,
                            last_name: false,
                            dob: false,
                            sanction_no: false,
                            tshirt: false,
                            leo: false,
                        };

                        athlete.has_changes = function() {
                            for (let i in this.changes) {
                                let c = this.changes[i];
                                if ((c !== null) && (c !== false))
                                    return true;
                            }
                            let flag = false;
                            this.events.some(evt => {
                                if (evt.hasOwnProperty('has_changes') && evt.has_changes())
                                    flag = true;
                                return flag;
                            });
                            return flag;
                        };

                        athlete.deduceStatus = function() {
                            let statuses = [];
                            this.events.forEach(e => {
                                if (!statuses.includes(e.status))
                                    statuses.push(e.status);
                            });
                            if (statuses.length > 1)
                                this.status = vm.constants.specialists.statuses.Mixed;
                            else
                                this.status = statuses[0];
                        }

                        athlete.has_pending_events = function() {
                            return this.events
                                        .filter(e => e.status == vm.constants.specialists.statuses.Pending)
                                        .length > 0;
                        }

                        athlete.permissions = {
                            change_details: function() {
                                return athlete.is_new || (
                                    vm.permissions.change_details &&
                                    (athlete.status != vm.constants.specialists.statuses.Scratched)
                                );
                            },
                            change_level: function() {
                                return athlete.is_new || (
                                    vm.permissions.change_level &&
                                    (athlete.status == vm.constants.specialists.statuses.Registered)
                                );
                            },
                            change_number: function() {
                                return athlete.is_new || vm.permissions.change_number;
                            },
                            add_specialist_events: function() {
                                return athlete.is_new || (
                                    vm.permissions.add_specialist_events &&
                                    (athlete.status != vm.constants.specialists.statuses.Scratched)
                                );
                            },
                            scratch: function() {
                                return athlete.is_new || (
                                    vm.permissions.scratch &&
                                    !athlete.has_pending_events()
                                );
                            },
                            scratch_without_refund: function(){
                                return athlete.is_new || (
                                    !vm.permissions.scratch &&
                                    !athlete.has_pending_events()
                                );
                            }
                        };

                        athlete.original_data = _.cloneDeep(athlete);
                        athlete.deduceStatus();

                        athlete.total = 0;
                        athlete.events.forEach(evt => {
                            athlete.total += Utils.toFloat(evt.new_fee) + Utils.toFloat(evt.new_late_fee)
                                                - Utils.toFloat(evt.new_refund) - Utils.toFloat(evt.new_late_refund);
                        });

                    } else {
                        athlete.editing = {
                            first_name: false,
                            last_name: false,
                            dob: false,
                            sanction_no: false,
                            tshirt: false,
                            leo: false,
                        };

                        athlete.changes = {
                            scratch: false,
                            moved_to: false,
                            first_name: false,
                            last_name: false,
                            dob: false,
                            sanction_no: false,
                            tshirt: false,
                            leo: false,
                        };

                        athlete.has_changes = function() {
                            for (let i in this.changes) {
                                let c = this.changes[i];
                                if ((c !== null) && (c !== false))
                                    return true;
                            }
                            return false;
                        };

                        athlete.permissions = {
                            change_details: function() {
                                return athlete.is_new || (
                                    vm.permissions.change_details &&
                                    (athlete.status != vm.constants.athletes.statuses.Scratched)
                                );
                            },
                            change_level: function() {
                                return athlete.is_new || (
                                    vm.permissions.change_level &&
                                    (athlete.status == vm.constants.athletes.statuses.Registered)
                                );
                            },
                            change_number: function() {
                                return athlete.is_new || vm.permissions.change_number;
                            },
                            add_specialist_events: function() {
                                return false;
                            },
                            scratch: function() {
                                return athlete.is_new || (
                                    vm.permissions.scratch &&
                                    (athlete.status == vm.constants.athletes.statuses.Registered)
                                );
                            },
                            scratch_without_refund: function(){
                                return athlete.is_new || (
                                    !vm.permissions.scratch &&
                                    (athlete.status == vm.constants.athletes.statuses.Registered)
                                );
                            }
                        };

                        athlete.new_fee = this.add_athlete_level.registration_fee;
                        athlete.new_refund = 0;
                        athlete.new_late_fee = this.late ? this.add_athlete_level.late_registration_fee : 0;
                        athlete.new_late_refund = 0;

                        athlete.original_data = _.cloneDeep(athlete);
                        athlete.total = Utils.toFloat(athlete.new_fee) + Utils.toFloat(athlete.new_late_fee)
                                - Utils.toFloat(athlete.new_refund) - Utils.toFloat(athlete.new_late_refund);
                    }

                    this.add_athlete_level.athletes.push(athlete);
                    this.calculateWaitlistStatuses();
                }
                
            }
        },

        showAddCoach() {
            if (!this.permissions.scratch)
                return;
            this.add_coach_coach = '';
            $('#modal-registration-add-coach').modal('show');
        },

        addModalCoach() {
            $('#modal-registration-add-coach').modal('hide');

            if (this.add_coach_coach) {
                let vm = this;
                let coach = _.cloneDeep(this.add_coach_coach);

                coach.status = this.constants.coaches.statuses.NonReserved;

                coach.is_scratched = function() {
                    return this.status == vm.constants.coaches.statuses.Scratched
                };

                coach.original_id = coach.id;
                coach.id = uuidv4();
                coach.is_new = true;
                coach.gender_display = coach.gender.charAt(0).toUpperCase() + coach.gender.slice(1)
                coach.dob = Moment(coach.dob);
                coach.dob_display = coach.dob.format('MM/DD/YYYY');

                coach.to_waitlist = this.meet.is_waitlist;

                coach.editing = {
                    first_name: false,
                    last_name: false,
                    dob: false,
                    usag_no: false,
                    usaigc_no: false,
                    aau_no: false,
                    nga_no: false,
                    tshirt: false,
                };

                if (this.meet.tshirt_chart != null) {
                    coach.editing.tshirt = false;
                    if (
                        (coach.tshirt == null) ||
                        (this.meet.tshirt_chart.id != coach.tshirt.clothing_size_chart_id)
                    ) {
                        coach.tshirt_size_id = -1;
                        coach.tshirt = null;
                    }
                }

                coach.permissions = {
                    change_details: function() {
                        return coach.is_new || (
                            vm.permissions.change_details &&
                            (coach.status != vm.constants.athletes.statuses.Scratched)
                        );
                    },
                    change_number: function() {
                        return coach.is_new || vm.permissions.change_number;
                    },
                    scratch: function() {
                        return coach.is_new || (
                            vm.permissions.scratch &&
                            (coach.status == vm.constants.athletes.statuses.Registered)
                        );
                    },
                };

                coach.changes = {
                    scratch: false,
                    first_name: false,
                    last_name: false,
                    dob: false,
                    usag_no: false,
                    usaigc_no: false,
                    aau_no: false,
                    nga_no: false,
                    tshirt: false,
                };

                coach.has_changes = function() {
                    for (let i in this.changes) {
                        let c = this.changes[i];
                        if ((c !== null) && (c !== false))
                            return true;
                    }
                    return false;
                };

                if (coach.usag_expiry != null) {
                    coach.usag_expiry = Moment(coach.usag_expiry);
                    coach.usag_expiry_display = coach.usag_expiry.format('MM/DD/YYYY');
                }

                if (coach.usag_safety_expiry != null) {
                    coach.usag_safety_expiry = Moment(coach.usag_safety_expiry);
                    coach.usag_safety_expiry_display = coach.usag_safety_expiry.format('MM/DD/YYYY');
                }

                if (coach.usag_safesport_expiry != null) {
                    coach.usag_safesport_expiry = Moment(coach.usag_safesport_expiry);
                    coach.usag_safesport_expiry_display = coach.usag_safesport_expiry.format('MM/DD/YYYY');
                }

                if (coach.usag_background_expiry != null) {
                    coach.usag_background_expiry = Moment(coach.usag_background_expiry);
                    coach.usag_background_expiry_display = coach.usag_background_expiry.format('MM/DD/YYYY');
                }

                coach.original_data = _.cloneDeep(coach);

                this.coaches.push(coach);
            }
        },

        showAddEventsModal(level, athlete) {
            if (level.locked)
                return;

            if (!athlete.is_specialist)
                return;

            if (!athlete.permissions.add_specialist_events())
                return;

            this.add_athlete_level = level;
            this.add_athlete_athlete = athlete;
            this.add_athlete_events = {};
            for (let i in this.specialist_events) {
                let evt = this.specialist_events[i];
                let existing = athlete.events.filter(e => {
                    return (e.event_id == evt.id) && !e.is_scratched();
                });
                if (existing.length)
                    continue;
                Vue.set(this.add_athlete_events, evt.id, {
                    ... _.cloneDeep(evt),
                    checked: false,
                });
            }

            $('#modal-registration-add-events').modal('show');
        },

        addModalEvents() {
            $('#modal-registration-add-events').modal('hide');

            if (this.add_athlete_athlete) {
                let vm = this;

                for (let x in this.add_athlete_events) {
                    let addedEvent = this.add_athlete_events[x];
                    if (addedEvent.checked) {

                        let existing = this.add_athlete_athlete.events.filter(e => {
                            return (e.event_id == addedEvent.id) && !e.is_scratched();
                        });
                        if (existing.length) {
                            this.showAlert(
                                'This athlete is already competing in ' + addedEvent.name,
                                'Whoops !',
                                'red',
                                'fas fa-times-circle'
                            );
                            return;
                        }

                        let evt = {
                            changes: {
                                scratch: false,
                            },
                            event_id: addedEvent.id,
                            fee: 0,
                            refund: 0,
                            late_fee: 0,
                            late_refund: 0,
                            new_fee: this.add_athlete_level.specialist_registration_fee,
                            new_refund: 0,
                            new_late_fee: this.late ? this.add_athlete_level.specialist_late_registration_fee : 0,
                            new_late_refund: 0,
                            has_changes: function() {
                                for (let i in this.changes) {
                                    let c = this.changes[i];
                                    if ((c !== null) && (c !== false))
                                        return true;
                                }
                                return false;
                            },
                            id: 'evt-' + _.uniqueId(),
                            in_waitlist: false,
                            is_scratched: function() {
                                return this.status == vm.constants.specialists.statuses.Scratched
                            },
                            is_new: true,
                            permissions: {
                                scratch: function() {
                                    return evt.is_new || (
                                        vm.permissions.scratch &&
                                        (evt.status == vm.constants.specialists.statuses.Registered)
                                    );
                                },
                                scratch_without_refund: function(){
                                    return evt.is_new || (
                                        !vm.permissions.scratch &&
                                        !evt.has_pending_events()
                                    );
                                }
                            },
                            status: this.constants.specialists.statuses.Pending,
                            was_late: this.late,
                        };
                        evt.original_data = _.cloneDeep(evt);
                        this.add_athlete_athlete.events.push(evt);
                        this.add_athlete_athlete.changes.events = true;
                    }
                }

                this.calculateWaitlistStatuses();
            }
        },

        calculateWaitlistStatuses() {
            for (let i in this.bodies) {
                let b = this.bodies[i];
                for (let j in b.categories) {
                    let c = b.categories[j];
                    for (let k in c.levels) {
                        let l = c.levels[k];
                        l.athletes.forEach(a => a.to_waitlist = false);
                    }
                }
            }

            let w = this.meet.is_waitlist;
            let ra = [];
            for (let i in this.bodies) {
                let b = this.bodies[i];
                for (let j in b.categories) {
                    let c = b.categories[j];
                    for (let k in c.levels) {
                        let l = c.levels[k];

                        if (!l.enable_athlete_limit)
                            continue;

                        let fa = [...l.athletes.filter(a => a.is_new && !a.is_specialist)];
                        let lws = fa.length;
                        lws -= (l.slots + l.freed_slots - l.movedInAthletes());

                        l.waitlist_slots = lws;

                        if (w) {
                            fa.forEach(a => a.to_waitlist = true);
                        } else if (lws > 0) {
                            fa.sort((x, y) => {
                                if (x.pin_out_of_waitlist == y.pin_out_of_waitlist)
                                    return 0;

                                if (x.pin_out_of_waitlist)
                                    return -1;

                                return 1;
                            }).reverse().forEach(a => {
                                if (lws > 0) {
                                    a.to_waitlist = true;
                                    lws--;
                                } else {
                                    a.to_waitlist = false;
                                }
                            });
                        }
                    }
                }
            }

            if (this.meet.athlete_limit !== null) {
                for (let i in this.bodies) {
                    let b = this.bodies[i];
                    for (let j in b.categories) {
                        let c = b.categories[j];
                        for (let k in c.levels) {
                            let l = c.levels[k];
                            ra = ra.concat(l.athletes.filter(a => a.is_new && !a.is_specialist && !a.to_waitlist));
                        }
                    }
                }
                let mws = ra.length - this.meet.slots;
                this.meet.waitlist_slots = mws;
                if (mws > 0) {
                    ra.sort((x, y) => {
                        if (x.pin_out_of_waitlist == y.pin_out_of_waitlist)
                            return 0;

                        if (x.pin_out_of_waitlist)
                            return -1;

                        return 1;
                    }).reverse().forEach(a => {
                        if (mws > 0) {
                            a.to_waitlist = true;
                            mws--;
                        }
                    });
                }
            }

            this.calculateMeetFees();
        },

        validateRegistration() {
            try {
                let athleteCount = 0;
                let competingAthletes = 0;
                let competingCoaches = 0;
                let coachCount = 0;

                let result = {
                    total: this.total,
                    meet: this.meet,
                    gym: this.gymId,
                    bodies: [],
                    coaches: [],
                };

                for (let i in this.bodies) {
                    let cs = this.bodies[i].categories;
                    let b = {
                        ..._.cloneDeep(this.bodies[i]),
                        categories: [],
                    };
                    for (let j in cs) {
                        let ls = cs[j].levels;
                        let c = {
                            ..._.cloneDeep(cs[j]),
                            levels: [],
                        };
                        for (let k in ls) {
                            let as = ls[k].athletes;
                            let l = {
                                ..._.cloneDeep(ls[k]),
                                athletes: []
                            };

                            if(l.has_changes()){
                                this.level_as_a_team = true
                            }

                            if((as.length < 1) && (l.has_changes()))
                                throw 'You need to select at least one athlete per each submitted level.';

                            delete l.pivot;
                            delete l.sanctioning_body;
                            delete l.level_category;

                            as.forEach(a => {
                                if (!a.is_scratched())
                                    competingAthletes++;

                                if (a.is_new || a.has_changes()) {
                                    let athlete = {
                                        ..._.cloneDeep(a),
                                        id: a.is_new ? a.original_id : a.id,
                                        dob: a.dob.format('MM/DD/YYYY'),
                                        original_data: {
                                            id: a.original_data.id,
                                            level_registration_id: a.original_data.level_registration_id,
                                        }
                                    };

                                    delete athlete.registration_level;
                                    delete athlete.editing;
                                    delete athlete.usag_level;
                                    delete athlete.usaigc_level;
                                    delete athlete.aau_level;
                                    delete athlete.nga_level;

                                    if (this.meet.tshirt_chart != null) {
                                        if (a.tshirt_size_id == -1) {
                                            throw 'T-shirts sizes are required for this meet.' +
                                            '\nPlease make sure you have provided a valid t-shirt size for all selected athletes.';
                                        }
                                        athlete.tshirt = a.tshirt_size_id;
                                    }

                                    if ((this.meet.leo_chart != null) && (a.gender == "female")) {
                                        if (a.leo_size_id == -1) {
                                            throw 'Leo sizes are required for this meet.' +
                                            '\nPlease make sure you have provided a valid Leo size for all selected athletes.';
                                        }
                                        athlete.leo = a.leo_size_id;
                                    }

                                    if (a.is_specialist) {
                                        athlete.events = [];
                                        a.events.forEach(evt => {
                                            if (evt.is_new || (evt.hasOwnProperty('has_changes') && evt.has_changes())) {
                                                athlete.events.push({
                                                    ..._.cloneDeep(evt),
                                                    to_waitlist: a.to_waitlist,
                                                    original_data: {
                                                        id: a.original_data.id,
                                                    }
                                                });
                                            }
                                        });
                                    }

                                    l.athletes.push(athlete);
                                    athleteCount++;
                                }
                            });
                            if (l.has_changes() || (l.athletes.length > 0))
                                c.levels.push(l);
                        }
                        if (c.levels.length > 0)
                            b.categories.push(c);
                    }
                    if (b.categories.length > 0)
                        result.bodies.push(b);
                }

                // if (competingAthletes < 1)
                //         throw 'You need to select at least one athlete to compete.';

                let neededSanctions = [];
                /*
                for (let body in this.bodies) {
                    if (
                        (body == this.constants.bodies.USAIGC) ||
                        (body == this.constants.bodies.USAG)
                    )
                        continue;

                    neededSanctions.push(this.constants.bodies[body].toLowerCase());
                }
                */

                competingCoaches = this.coaches.filter(c => !c.is_scratched());
                if (neededSanctions.length > 0) {
                    competingCoaches.forEach(c => {
                        let flag = false;
                        neededSanctions.forEach(body => {
                            flag = flag || (c[body + '_no'] !== null);
                        });

                        if (!flag)
                            throw 'No sanction number was provided for coach ' + c.first_name + ' ' + c.last_name + '. Please update your coach details in your roster.';
                    });
                }

                // if (competingCoaches.length < 1)
                //     throw 'Please select at least one coach to attend competition.';

                result.coaches = _.cloneDeep(this.coaches.filter(c => c.is_new || c.has_changes()));

                coachCount = result.coaches.length;
                if ((athleteCount < 1) && (coachCount < 1) && (this.level_as_a_team === false) && this.previous_remaining == 0)
                    throw 'No changes were made.';

                result.coaches.map(c => {
                    c.id = c.is_new ? c.original_id : c.id;
                    c.dob = c.dob.format('MM/DD/YYYY');
                    c.tshirt = c.tshirt_size_id;
                });

                this.$emit('process-data', result);
            } catch (error) {
                this.showAlert(
                    error,
                    'Whoops !',
                    'red',
                    'fas fa-times-circle'
                );
            };
        },

        levelUniqueId(level) {
            return level.id + (level.male ? '-m' : '') + (level.female ? '-f' : '');
        },

        loadGymDetails(result) {
            if (!result) {
                return axios.get('/api/gyms/' + this.gymId, {
                    'params': {
                        '__managed': this.managed
                    }
                });
            }

            this.gym = {
                ...result.data.gym,
                athletes: [],
                coaches: []
            };

            this.gymBodyFilter = {
                [this.constants.bodies.USAG]: (this.gym.usag_membership !== null),
                [this.constants.bodies.USAIGC]: (this.gym.usaigc_membership !== null),
                [this.constants.bodies.AAU]: (this.gym.aau_membership !== null),
                [this.constants.bodies.NGA]: (this.gym.nga_membership !== null),
            };
        },

        loadGymAthletes(result) {
            if (!result) {
                return axios.get('/api/gyms/' + this.gymId + '/athletes', {
                    'params': {
                        '__managed': this.managed
                    }
                });
            }

            for (let i in result.data.athletes) {
                let athlete = result.data.athletes[i];

                athlete.gender_display = athlete.gender.charAt(0).toUpperCase() + athlete.gender.slice(1)
                athlete.dob = Moment(athlete.dob);
                athlete.dob_display = athlete.dob.format('MM/DD/YYYY');

                this.gym.athletes.push(athlete);
            }
        },

        loadGymCoaches(result) {
            if (!result) {
                return axios.get('/api/gyms/' + this.gymId + '/coaches', {
                    'params': {
                        '__managed': this.managed
                    }
                });
            }

            for (let i in result.data.coaches) {
                let coach = result.data.coaches[i];

                coach.gender_display = coach.gender.charAt(0).toUpperCase() + coach.gender.slice(1)
                coach.dob = Moment(coach.dob);
                coach.dob_display = coach.dob.format('MM/DD/YYYY');

                if (coach.usag_expiry != null) {
                    coach.usag_expiry = Moment(coach.usag_expiry);
                    coach.usag_expiry_display = coach.usag_expiry.format('MM/DD/YYYY');
                }

                if (coach.usag_safety_expiry != null) {
                    coach.usag_safety_expiry = Moment(coach.usag_safety_expiry);
                    coach.usag_safety_expiry_display = coach.usag_safety_expiry.format('MM/DD/YYYY');
                }

                if (coach.usag_safesport_expiry != null) {
                    coach.usag_safesport_expiry = Moment(coach.usag_safesport_expiry);
                    coach.usag_safesport_expiry_display = coach.usag_safesport_expiry.format('MM/DD/YYYY');
                }

                if (coach.usag_background_expiry != null) {
                    coach.usag_background_expiry = Moment(coach.usag_background_expiry);
                    coach.usag_background_expiry_display = coach.usag_background_expiry.format('MM/DD/YYYY');
                }

                this.gym.coaches.push(coach);
            }
        },

        loadSpecialistEvents(result) {
            if (!result)
                return axios.get('/api/app/specialist');

            for (let i in result.data.events) {
                let event = result.data.events[i];
                this.specialist_events[event.id] = event;
            }
        },

        loadMeetDetails(result) {
            if (!result)
                return axios.get('/api/app/meet/' + this.meetId);

            if (result.data.meets.length != 1)
                throw 'Something went wrong while loading this meet\'s details.';

            let vm = this;
            let meet = result.data.meets[0];
            meet.late_registration_fee = Utils.toFloat(meet.late_registration_fee);
            meet.freed_slots = 0;
            meet.waitlist_slots = 0;

            let bodies = {};
            for (let i in meet.levels) {
                let level = meet.levels[i];

                let body = {
                    ... _.cloneDeep(level.sanctioning_body),
                    name: level.sanctioning_body.initialism,
                    categories: {},
                    expanded: true,
                    path: 'b' + level.sanctioning_body.id
                };
                if (bodies.hasOwnProperty(body.id))
                    body = bodies[body.id];

                body.locked = this.requires_sanction[level.sanctioning_body.id];

                let category = {
                    ... _.cloneDeep(level.level_category),
                    levels: [],
                    expanded: false,
                    path: 'b' + body.id + '-c' + level.level_category.id
                };
                if (body.categories.hasOwnProperty(category.id))
                    category = body.categories[category.id];

                let meetCategory = meet.categories.find(c => {
                                        return (c.pivot.sanctioning_body_id == body.id) &&
                                                (c.id == category.id);
                                    });
                if (meetCategory === undefined)
                    throw 'Something went wrong (can\'t find category)';

                category.locked = body.locked ||
                                meetCategory.pivot.officially_sanctioned ||
                                meetCategory.pivot.frozen;
             
                // category.locked = meetCategory.pivot.frozen;
                let f_date =  Moment(meet.registration_first_discount_end_date, 'YYYY-MM-DD');
                let s_date =  Moment(meet.registration_second_discount_end_date, 'YYYY-MM-DD');
                let t_date =  Moment(meet.registration_third_discount_end_date, 'YYYY-MM-DD');
                let c_date = Moment(new Date(),"YYYY-MM-DD");

                let f_d = Moment(f_date.format("YYYY-MM-DD")).format('x');
                let s_d = Moment(s_date.format("YYYY-MM-DD")).format('x');
                let t_d = Moment(t_date.format("YYYY-MM-DD")).format('x');
                let c_d = Moment(c_date.format("YYYY-MM-DD")).format('x');

                let registration_updated_fee = null;
                if(meet.registration_third_discount_is_enable)
                {
                    if(t_d - c_d >= 0)
                        registration_updated_fee = level.pivot.registration_fee_third;
                }
                if(meet.registration_second_discount_is_enable)
                {
                    if(s_d - c_d >= 0)
                        registration_updated_fee =  level.pivot.registration_fee_second;
                }
                if(meet.registration_first_discount_is_enable)
                {
                    if(f_d - c_d >= 0)
                        registration_updated_fee =  level.pivot.registration_fee_first;
                }
                level.pivot.registration_fee = registration_updated_fee == null ? level.pivot.registration_fee : registration_updated_fee;
                

                level = {
                    ... _.cloneDeep(level),
                    disabled: level.pivot.disabled,
                    male: level.pivot.allow_men,
                    female: level.pivot.allow_women,
                    registration_fee: Utils.toFloat(level.pivot.registration_fee),
                    late_registration_fee: Utils.toFloat(level.pivot.late_registration_fee),
                    allow_specialist: level.pivot.allow_specialist,
                    specialist_registration_fee: Utils.toFloat(level.pivot.specialist_registration_fee),
                    specialist_late_registration_fee: Utils.toFloat(level.pivot.specialist_late_registration_fee),
                    allow_team: level.pivot.allow_teams,
                    team_registration_fee: Utils.toFloat(level.pivot.team_registration_fee),
                    team_late_registration_fee: Utils.toFloat(level.pivot.team_late_registration_fee),
                    enable_athlete_limit: level.pivot.enable_athlete_limit,
                    athlete_limit: level.pivot.athlete_limit,
                    athletes: [],
                    freed_slots: 0,
                    team_fee: 0,
                    team_late_fee: 0,
                    team_refund: 0,
                    team_late_refund: 0,
                    subtotal: 0,
                    expanded: false,
                    team: false,
                    waitlist_slots: 0,
                    locked: category.locked,
                };

                level.discount_fee = registration_updated_fee != null ? true:false;

                if (level.enable_athlete_limit) {
                    let gender = ((level.male && level.female) ? 'both' : (level.male ? 'male' : 'female'));
                    level.slots = level.athlete_limit - meet.used_slots[level.id][gender]['count'];
                } else {
                    level.slots = null;
                }

                level.addedAthletes = function() {
                    return this.athletes.filter(a => {
                        let flag = (a.changes.moved_to !== false) || a.is_new;
                        flag = flag && !a.is_specialist && !a.is_scratched() && !a.to_waitlist;
                        return flag;
                    });
                }

                level.freeSlots = function(readable) {
                    if (!this.enable_athlete_limit)
                        return;

                    let r = this.slots - this.addedAthletes().length + this.freed_slots;
                    return (readable && (r < 1) ? 'No' : r)
                }

                level.movedInAthletes = function() {
                    return this.athletes.filter(a => {
                        let flag = (a.changes.moved_to !== false);
                        flag = flag && !a.is_specialist && !a.is_scratched() && !a.to_waitlist;
                        return flag;
                    });
                }

                level.newAthletes = function() {
                    return this.athletes.filter(a => {
                        return a.is_new && !a.is_specialist && !a.is_scratched() && !a.to_waitlist;
                    });
                };

                level.changes = {
                    team: false,
                };

                level.has_changes = function() {
                    for (let i in this.changes) {
                        let c = this.changes[i];
                        if ((c !== null) && (c !== false))
                            return true;
                    }
                    this.athletes.forEach(a => {
                        if (a.has_changes())
                            return true;
                    });
                    return false;
                };

                level.pinUnpin = function(athlete) {
                    // if (vm.registration.transactions[0].status != 6)
                    //     return;
                    if (vm.meet.is_waitlist)
                        return;

                    athlete.pin_out_of_waitlist = !athlete.pin_out_of_waitlist;
                    vm.calculateWaitlistStatuses();
                };

                level.uid = this.levelUniqueId(level);
                level.has_specialist = this.hasSpecialist(body, category);

                level.original_data = _.cloneDeep(level);

                this.genderAwareMeetLevelMatrix[level.uid] = level;

                category.levels.push(level);
                body.categories[category.id] = category;
                bodies[body.id] = body;
            }
            for (let i in bodies)
                Vue.set(this.bodies, bodies[i].id, bodies[i]);

            if (meet.athlete_limit !== null)
                meet.slots = meet.athlete_limit - meet.used_slots.total;

            meet.freeSlots = function(readable) {
                if (this.athlete_limit === null)
                    return;

                let count = 0;
                for (let i in vm.bodies) {
                    let b = vm.bodies[i];
                    for (let j in b.categories) {
                        let c = b.categories[j];
                        for (let k in c.levels) {
                            let l = c.levels[k];
                            count += l.newAthletes().length - l.freed_slots;
                        }
                    }
                }
                let r = this.slots - count;
                return (readable && (r < 1) ? 'No' : r);
            };

            meet.newAthletes = function() {
                let result = [];
                for (let i in vm.bodies) {
                    let b = vm.bodies[i];
                    for (let j in b.categories) {
                        let c = b.categories[j];
                        for (let k in c.levels) {
                            let l = c.levels[k];
                            result = result.concat(l.newAthletes());
                        }
                    }
                }
                return result;
            }

            this.meet = meet;
        },

        loadRegistrationDetails(result) {
            if (!result) {
                return axios.get('/api/registration/' + this.registrationId, {
                    'params': {
                        '__managed': this.managed
                    }
                })
            }

            let vm = this;
            let registration = result.data;
            Vue.set(this, 'permissions', registration.editing_abilities);

            registration.late_fee = Utils.toFloat(registration.late_fee);
            registration.late_refund = Utils.toFloat(registration.late_refund);

            for (let i in registration.levels) {
                let level = registration.levels[i];

                this.registrationLevelToMeetLevelMatrix[level.pivot.id] = this.levelUniqueId({
                    id: level.id,
                    male: level.pivot.allow_men,
                    female: level.pivot.allow_women,
                });
            }

            for (let i in registration.athletes) {
                let athlete = _.cloneDeep(registration.athletes[i]);

                athlete.is_scratched = function() {
                    return this.status == vm.constants.athletes.statuses.Scratched
                };
                if (athlete.is_scratched())
                    continue;

                athlete.is_new = false;
                athlete.is_specialist = false;
                athlete.to_waitlist = false;
                athlete.pin_out_of_waitlist = false;
                athlete.gender_display = athlete.gender.charAt(0).toUpperCase() + athlete.gender.slice(1)
                athlete.dob = Moment(athlete.dob);
                athlete.dob_display = athlete.dob.format('MM/DD/YYYY');
                athlete.sanction_no = this.constants.bodies[
                                            athlete.registration_level.level.sanctioning_body_id
                                        ].toLowerCase() + '_no';

                athlete.editing = {
                    first_name: false,
                    last_name: false,
                    dob: false,
                    sanction_no: false,
                    tshirt: false,
                    leo: false,
                };

                if (this.meet.tshirt_chart != null) {
                    if ((athlete.tshirt == null) ||
                        (this.meet.tshirt_chart.id != athlete.tshirt.clothing_size_chart_id)) {
                        athlete.tshirt_size_id = -1;
                    }
                }

                if (this.meet.leo_chart != null) {
                    if ((athlete.leo == null) ||
                        (this.meet.leo_chart.id != athlete.leo.clothing_size_chart_id)) {
                        athlete.leo_size_id = -1;
                    }
                }

                athlete.changes = {
                    scratch: false,
                    moved_to: false,
                    first_name: false,
                    last_name: false,
                    dob: false,
                    sanction_no: false,
                    tshirt: false,
                    leo: false,
                };

                athlete.has_changes = function() {
                    for (let i in this.changes) {
                        let c = this.changes[i];
                        if ((c !== null) && (c !== false))
                            return true;
                    }
                    return false;
                };

                athlete.permissions = {
                    change_details: function() {
                        return athlete.is_new || (
                            vm.permissions.change_details &&
                            (athlete.status != vm.constants.athletes.statuses.Scratched)
                        );
                    },
                    change_level: function() {
                        return athlete.is_new || (
                            vm.permissions.change_level &&
                            (athlete.status == vm.constants.athletes.statuses.Registered)
                        );
                    },
                    change_number: function() {
                        return athlete.is_new || vm.permissions.change_number;
                    },
                    add_specialist_events: function() {
                        return false;
                    },
                    scratch: function() {
                        return athlete.is_new || (
                            vm.permissions.scratch &&
                            (athlete.status == vm.constants.athletes.statuses.Registered)
                        );
                    },
                    scratch_without_refund: function(){
                            return athlete.is_new || (
                                !vm.permissions.scratch &&
                                (athlete.status == vm.constants.athletes.statuses.Registered)
                            );
                        }
                };

                athlete.original_data = _.cloneDeep(athlete);

                let athleteLevel = this.genderAwareMeetLevelMatrix[
                    this.registrationLevelToMeetLevelMatrix[athlete.registration_level.id]
                ];
                athleteLevel.team_fee = Utils.toFloat(athlete.registration_level.team_fee);
                athleteLevel.team_late_fee = Utils.toFloat(athlete.registration_level.team_late_fee);
                athleteLevel.team_refund = Utils.toFloat(athlete.registration_level.team_refund);
                athleteLevel.team_late_refund = Utils.toFloat(athlete.registration_level.team_late_refund);
                athleteLevel.team = athlete.registration_level.has_team/*(Utils.toFloat(athleteLevel.team_fee) + Utils.toFloat(athleteLevel.team_late_fee)
                                    - Utils.toFloat(athleteLevel.team_refund) - Utils.toFloat(athleteLevel.team_late_refund)) > 0*/;
                athleteLevel.original_data.team = athleteLevel.team;

                athlete.locked = athleteLevel.locked;

                athlete.new_fee = 0;
                athlete.new_refund = 0;
                athlete.new_late_fee = 0;
                athlete.new_late_refund = 0;

                athlete.original_level = athleteLevel.uid;
                athlete.total = Utils.toFloat(athlete.new_fee) + Utils.toFloat(athlete.new_late_fee)
                            - Utils.toFloat(athlete.new_refund) - Utils.toFloat(athlete.new_late_refund);
                athleteLevel.athletes.push(athlete);
                athleteLevel.athletes.sort((a, b) => {
                    if (a.last_name.toLowerCase() < b.last_name.toLowerCase())
                        return -1;

                    if (a.last_name.toLowerCase() > b.last_name.toLowerCase())
                        return 1;

                    return 0;
                });
            }

            for (let i in registration.specialists) {
                let specialist = _.cloneDeep(registration.specialists[i]);

                specialist.is_scratched = function() {
                    return this.status == vm.constants.specialists.statuses.Scratched
                };

                specialist.is_new = false;
                specialist.is_specialist = true;
                specialist.to_waitlist = false;
                specialist.pin_out_of_waitlist = false;
                specialist.gender_display = specialist.gender.charAt(0).toUpperCase() + specialist.gender.slice(1)
                specialist.dob = Moment(specialist.dob);
                specialist.dob_display = specialist.dob.format('MM/DD/YYYY');
                specialist.sanction_no = this.constants.bodies[
                                            specialist.registration_level.level.sanctioning_body_id
                                        ].toLowerCase() + '_no';

                specialist.editing = {
                    first_name: false,
                    last_name: false,
                    dob: false,
                    sanction_no: false,
                    tshirt: false,
                    leo: false,
                };

                if (this.meet.tshirt_chart != null) {
                    if (
                        (specialist.tshirt == null) ||
                        (this.meet.tshirt_chart.id != specialist.tshirt.clothing_size_chart_id)
                    ) {
                        specialist.tshirt_size_id = -1;
                    }
                }

                if (this.meet.leo_chart != null) {
                    if (
                        (specialist.leo == null) ||
                        (this.meet.leo_chart.id != specialist.leo.clothing_size_chart_id)
                    ) {
                        specialist.leo_size_id = -1;
                    }
                }

                specialist.changes = {
                    events: false,
                    scratch: false,
                    moved_to: false,
                    first_name: false,
                    last_name: false,
                    dob: false,
                    sanction_no: false,
                    tshirt: false,
                    leo: false,
                };

                specialist.has_changes = function() {
                    for (let i in this.changes) {
                        let c = this.changes[i];
                        if ((c !== null) && (c !== false))
                            return true;
                    }
                    let flag = false;
                    this.events.some(evt => {
                        if (evt.hasOwnProperty('has_changes') && evt.has_changes())
                            flag = true;
                        return flag;
                    });
                    return flag;
                };

                specialist.deduceStatus = function() {
                    let statuses = [];
                    this.events.forEach(e => {
                        if (!statuses.includes(e.status))
                            statuses.push(e.status);
                    });
                    if (statuses.length > 1)
                        this.status = vm.constants.specialists.statuses.Mixed;
                    else
                        this.status = statuses[0];
                }

                specialist.has_pending_events = function() {
                    return this.events
                                .filter(e => e.status == vm.constants.specialists.statuses.Pending)
                                .length > 0;
                }

                specialist.permissions = {
                    change_details: function() {
                        return specialist.is_new || (
                            vm.permissions.change_details &&
                            (specialist.status != vm.constants.specialists.statuses.Scratched)
                        );
                    },
                    change_level: function() {
                        return specialist.is_new || (
                            vm.permissions.change_level &&
                            (specialist.status == vm.constants.specialists.statuses.Registered)
                        );
                    },
                    change_number: function() {
                        return specialist.is_new || vm.permissions.change_number;
                    },
                    add_specialist_events: function() {
                        return specialist.is_new || (
                            vm.permissions.add_specialist_events &&
                            (specialist.status != vm.constants.specialists.statuses.Scratched)
                        );
                    },
                    scratch: function() {
                        return specialist.is_new || (
                            vm.permissions.scratch &&
                            !specialist.has_pending_events()
                        );
                    },
                };

                for (let j in specialist.events) {
                    let evt = specialist.events[j];

                    evt.new_fee = 0;
                    evt.new_refund = 0;
                    evt.new_late_fee = 0;
                    evt.new_late_refund = 0;

                    evt.is_scratched = function() {
                        return this.status == vm.constants.specialists.statuses.Scratched
                    };
                    if (evt.is_scratched())
                        continue;

                    evt.is_new = false;

                    evt.changes = {
                        scratch: false,
                    };

                    evt.has_changes = function() {
                        for (let i in this.changes) {
                            let c = this.changes[i];
                            if ((c !== null) && (c !== false))
                                return true;
                        }
                        return false;
                    };

                    evt.permissions = {
                        scratch: function() {
                            return evt.is_new || (
                                vm.permissions.scratch &&
                                (evt.status == vm.constants.specialists.statuses.Registered)
                            );
                        },
                    };

                    evt.original_data = _.cloneDeep(evt);
                }

                specialist.original_data = _.cloneDeep(specialist);

                specialist.deduceStatus();

                if (specialist.is_scratched())
                    continue;

                let specialistLevel = this.genderAwareMeetLevelMatrix[
                    this.registrationLevelToMeetLevelMatrix[specialist.registration_level.id]
                ];

                specialistLevel.team_fee = Utils.toFloat(specialist.registration_level.team_fee);
                specialistLevel.team_late_fee = Utils.toFloat(specialist.registration_level.team_late_fee);
                specialistLevel.team_refund = Utils.toFloat(specialist.registration_level.team_refund);
                specialistLevel.team_late_refund = Utils.toFloat(specialist.registration_level.team_late_refund);
                specialistLevel.team = specialist.registration_level.has_team/*(Utils.toFloat(specialistLevel.team_fee) + Utils.toFloat(specialistLevel.team_late_fee)
                                    - Utils.toFloat(specialistLevel.team_refund) - Utils.toFloat(specialistLevel.team_late_refund)) > 0*/;

                specialist.locked = specialistLevel.locked;
                specialist.original_level = specialistLevel.uid;
                specialist.total = 0;
                specialist.events.forEach(evt => {
                    specialist.total += Utils.toFloat(evt.new_fee) + Utils.toFloat(evt.new_late_fee)
                                        - Utils.toFloat(evt.new_refund) - Utils.toFloat(evt.new_late_refund);
                });
                specialistLevel.athletes.push(specialist);
                specialistLevel.athletes.sort((a, b) => {
                    if (a.last_name.toLowerCase() < b.last_name.toLowerCase())
                        return -1;

                    if (a.last_name.toLowerCase() > b.last_name.toLowerCase())
                        return 1;

                    return 0;
                });
            }

            for (let i in registration.coaches) {
                let coach = _.cloneDeep(registration.coaches[i]);

                coach.is_scratched = function() {
                    return this.status == vm.constants.coaches.statuses.Scratched
                };
                if (coach.is_scratched())
                    continue;

                coach.locked = coach.from_usag;
                coach.is_new = false;
                coach.to_waitlist = false;
                coach.gender_display = coach.gender.charAt(0).toUpperCase() + coach.gender.slice(1)
                coach.dob = Moment(coach.dob);
                coach.dob_display = coach.dob.format('MM/DD/YYYY');

                coach.editing = {
                    first_name: false,
                    last_name: false,
                    dob: false,
                    usag_no: false,
                    usaigc_no: false,
                    aau_no: false,
                    nga_no: false,
                    tshirt: false,
                };

                if (this.meet.tshirt_chart != null) {
                    coach.editing.tshirt = false;
                    if (
                        (coach.tshirt == null) ||
                        (this.meet.tshirt_chart.id != coach.tshirt.clothing_size_chart_id)
                    ) {
                        coach.tshirt_size_id = -1;
                    }
                }

                coach.permissions = {
                    change_details: function() {
                        return coach.is_new || (
                            vm.permissions.change_details &&
                            (coach.status != vm.constants.athletes.statuses.Scratched)
                        );
                    },
                    change_number: function() {
                        return coach.is_new || vm.permissions.change_number;
                    },
                    scratch: function() {
                        return coach.is_new || (
                            vm.permissions.scratch &&
                            (coach.status == vm.constants.athletes.statuses.Registered)
                        );
                    },
                };

                coach.changes = {
                    scratch: false,
                    first_name: false,
                    last_name: false,
                    dob: false,
                    usag_no: false,
                    usaigc_no: false,
                    aau_no: false,
                    nga_no: false,
                    tshirt: false,
                };

                coach.has_changes = function() {
                    for (let i in this.changes) {
                        let c = this.changes[i];
                        if ((c !== null) && (c !== false))
                            return true;
                    }
                    return false;
                };

                if (coach.usag_expiry != null) {
                    coach.usag_expiry = Moment(coach.usag_expiry);
                    coach.usag_expiry_display = coach.usag_expiry.format('MM/DD/YYYY');
                }

                if (coach.usag_safety_expiry != null) {
                    coach.usag_safety_expiry = Moment(coach.usag_safety_expiry);
                    coach.usag_safety_expiry_display = coach.usag_safety_expiry.format('MM/DD/YYYY');
                }

                if (coach.usag_safesport_expiry != null) {
                    coach.usag_safesport_expiry = Moment(coach.usag_safesport_expiry);
                    coach.usag_safesport_expiry_display = coach.usag_safesport_expiry.format('MM/DD/YYYY');
                }

                if (coach.usag_background_expiry != null) {
                    coach.usag_background_expiry = Moment(coach.usag_background_expiry);
                    coach.usag_background_expiry_display = coach.usag_background_expiry.format('MM/DD/YYYY');
                }

                coach.original_data = _.cloneDeep(coach);

                this.coaches.push(coach);
                this.coaches.sort((a, b) => {
                    if (a.last_name.toLowerCase() < b.last_name.toLowerCase())
                        return -1;

                    if (a.last_name.toLowerCase() > b.last_name.toLowerCase())
                        return 1;

                    return 0;
                });
            }

            this.registration  = registration;
            this.calculateWaitlistStatuses();
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

        showAlert(msg, title, color, icon) {
            $.alert({
                title: title,
                content: msg,
                icon: icon,
                type: color,
                typeAnimated: true
            });
        },
    },
    beforeMount() {
        try {
            this.isLoading = true;
            this.gym = {
                athletes: [],
                coaches: []
            };
            Promise.all([
                this.loadGymDetails(),
                this.loadMeetDetails(),
                this.loadSpecialistEvents(),
                this.loadGymAthletes(),
                this.loadGymCoaches(),
                this.loadRegistrationDetails(),
            ]).then(results => {
                let i = 0;
                this.loadGymDetails(results[i++]),
                this.loadMeetDetails(results[i++]);
                this.loadSpecialistEvents(results[i++]),
                this.loadGymAthletes(results[i++]);
                this.loadGymCoaches(results[i++]);
                this.loadRegistrationDetails(results[i++]);
            }).catch(error => {
                let msg = '';
                if (error.response) {
                    msg = error.response.data.message;
                } else if (error.request) {
                    msg = 'No server response.';
                } else if (error.message) {
                    msg = error.message;
                } else {
                    msg = error;
                }
                this.errorMessage = msg + '<br/>Please reload this page.';
                this.isError = true;
            }).finally(() => {
                this.isLoading = false;
            });
        } catch (error) {
            this.errorMessage = error + '<br/>Please reload this page.';
            this.isError = true;
        }
    }
}
</script>
