<template>
    <div>
        <div class="modal fade" id="modal-edit-level" tabindex="-1" role="dialog"
            aria-labelledby="modal-edit-level" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-xl" role="document">
                <div v-if="editedLevel === null" class="modal-content">
                    <div class="alert alert-danger small p-3">
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
                                            <label for="edit-level-registration-fee">
                                                <span class="fas fa-fw fa-dollar-sign"></span>
                                                Registration Fee
                                            </label>
                                            <div class="input-group input-group-sm">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text">
                                                        <span class="fas fa-fw fa-dollar-sign"></span>
                                                    </span>
                                                </div>
                                                <input id="edit-level-registration-fee" class="form-control"
                                                    placeholder="0.00" autocomplete="off" type="text"
                                                    v-model="editedLevel.registration_fee">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row mb-2">
                                        <div class="col-lg mb-2" v-if="selectedMeet.registration_first_discount_is_enable">
                                            <label for="edit-all-levels-team-late-registration-fee">
                                                <span class="fas fa-fw fa-dollar-sign"></span>
                                                Early Registration Fee
                                            </label>
                                            <div class="input-group input-group-sm">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text">
                                                        <span class="fas fa-fw fa-dollar-sign"></span>
                                                    </span>
                                                </div>
                                                <input id="edit-all-levels-team-late-registration-fee" class="form-control"
                                                    placeholder="0.00" autocomplete="off" type="text"
                                                    v-model="editedLevel.registration_fee_first">
                                            </div>
                                        </div>
                                        
                                    </div>
                                    
                                    <div class="row mb-2">
                                        <div v-if="selectedMeet.allow_late_registration" class="col-lg mb-2">
                                            <label for="edit-level-late-registration-fee">
                                                <span class="fas fa-fw fa-dollar-sign"></span>
                                                Late Registration Fee
                                            </label>
                                            <div class="input-group input-group-sm">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text">
                                                        <span class="fas fa-fw fa-dollar-sign"></span>
                                                    </span>
                                                </div>
                                                <input id="edit-level-late-registration-fee" class="form-control"
                                                    placeholder="0.00" autocomplete="off" type="text"
                                                    v-model="editedLevel.late_registration_fee">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="mb-2">
                                        <div class="form-check pb-1">
                                            <input class="form-check-input" id="edit-level-allow-team"
                                                type="checkbox" v-model="editedLevel.allow_teams">
                                            <label class="form-check-label" for="edit-level-allow-team">
                                                Allow teams
                                            </label>
                                        </div>
                                        <div class="row">
                                            <div class="col-lg mb-2">
                                                <label for="edit-level-team-registration-fee">
                                                    <span class="fas fa-fw fa-dollar-sign"></span>
                                                    Registration Fee
                                                </label>
                                                <div class="input-group input-group-sm">
                                                    <div class="input-group-prepend">
                                                        <span class="input-group-text">
                                                            <span class="fas fa-fw fa-dollar-sign"></span>
                                                        </span>
                                                    </div>
                                                    <input id="edit-level-team-registration-fee" class="form-control"
                                                        placeholder="0.00" autocomplete="off" type="text"
                                                        :disabled="!editedLevel.allow_teams"
                                                        v-model="editedLevel.team_registration_fee">
                                                </div>
                                            </div>

                                            <div v-if="selectedMeet.allow_late_registration" class="col-lg mb-2">
                                                <label for="edit-level-team-late-registration-fee">
                                                    <span class="fas fa-fw fa-dollar-sign"></span>
                                                    Late Registration Fee
                                                </label>
                                                <div class="input-group input-group-sm">
                                                    <div class="input-group-prepend">
                                                        <span class="input-group-text">
                                                            <span class="fas fa-fw fa-dollar-sign"></span>
                                                        </span>
                                                    </div>
                                                    <input id="edit-level-team-late-registration-fee" class="form-control"
                                                        placeholder="0.00" autocomplete="off" type="text"
                                                        :disabled="!editedLevel.allow_teams"
                                                        v-model="editedLevel.team_late_registration_fee">
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- <div class="mb-2">
                                        <div class="form-check pb-1">
                                            <input class="form-check-input" id="edit-level-enable-athlete-limit"
                                                type="checkbox" v-model="editedLevel.enable_athlete_limit">
                                            <label class="form-check-label" for="edit-level-enable-athlete-limit">
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

        <div class="modal fade" id="modal-edit-all" tabindex="-1" role="dialog"
            aria-labelledby="modal-edit-all" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-xl" role="document">
                <div v-if="editedLevel === null" class="modal-content">
                    <div class="alert alert-danger small p-3">
                        Something went wrong.
                    </div>
                </div>
                <div v-else class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title text-info">
                            <span class="fas fa-edit"></span> Edit All Levels
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
                                            <label for="edit-all-levels-registration-fee">
                                                <span class="fas fa-fw fa-dollar-sign"></span>
                                                Registration Fee
                                            </label>
                                            <div class="input-group input-group-sm">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text">
                                                        <span class="fas fa-fw fa-dollar-sign"></span>
                                                    </span>
                                                </div>
                                                <input id="edit-all-levels-registration-fee" class="form-control"
                                                    placeholder="0.00" autocomplete="off" type="text"
                                                    v-model="editedLevel.registration_fee">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row mb-2">
                                        <div v-if="selectedMeet.registration_first_discount_is_enable" class="col-lg mb-2">
                                            <label for="add-level-specialist-registration-fee">
                                                <span class="fas fa-fw fa-dollar-sign"></span>
                                                Early Registration Fee
                                            </label>
                                            <div class="input-group input-group-sm">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text">
                                                        <span class="fas fa-fw fa-dollar-sign"></span>
                                                    </span>
                                                </div>
                                                <input id="add-level-specialist-registration-fee" class="form-control"
                                                    placeholder="0.00" autocomplete="off" type="text"
                                                    v-model="editedLevel.registration_fee_first">
                                            </div>
                                        </div>
                                        
                                    </div>
                                    <div class="row mb-2">
                                        <div v-if="selectedMeet.allow_late_registration" class="col-lg mb-2">
                                            <label for="edit-all-levels-late-registration-fee">
                                                <span class="fas fa-fw fa-dollar-sign"></span>
                                                Late Registration Fee
                                            </label>
                                            <div class="input-group input-group-sm">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text">
                                                        <span class="fas fa-fw fa-dollar-sign"></span>
                                                    </span>
                                                </div>
                                                <input id="edit-all-levels-late-registration-fee" class="form-control"
                                                    placeholder="0.00" autocomplete="off" type="text"
                                                    v-model="editedLevel.late_registration_fee">
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="mb-2">
                                        <div class="form-check pb-1">
                                            <input class="form-check-input" id="edit-all-levels-allow-team"
                                                type="checkbox" v-model="editedLevel.allow_teams">
                                            <label class="form-check-label" for="edit-all-levels-allow-team">
                                                Allow teams
                                            </label>
                                        </div>
                                        <div class="row">
                                            <div class="col-lg mb-2">
                                                <label for="edit-all-levels-team-registration-fee">
                                                    <span class="fas fa-fw fa-dollar-sign"></span>
                                                    Registration Fee
                                                </label>
                                                <div class="input-group input-group-sm">
                                                    <div class="input-group-prepend">
                                                        <span class="input-group-text">
                                                            <span class="fas fa-fw fa-dollar-sign"></span>
                                                        </span>
                                                    </div>
                                                    <input id="edit-all-levels-team-registration-fee" class="form-control"
                                                        placeholder="0.00" autocomplete="off" type="text"
                                                        :disabled="!editedLevel.allow_teams"
                                                        v-model="editedLevel.team_registration_fee">
                                                </div>
                                            </div>

                                            <div v-if="selectedMeet.allow_late_registration" class="col-lg mb-2">
                                                <label for="edit-all-levels-team-late-registration-fee">
                                                    <span class="fas fa-fw fa-dollar-sign"></span>
                                                    Late Registration Fee
                                                </label>
                                                <div class="input-group input-group-sm">
                                                    <div class="input-group-prepend">
                                                        <span class="input-group-text">
                                                            <span class="fas fa-fw fa-dollar-sign"></span>
                                                        </span>
                                                    </div>
                                                    <input id="edit-all-levels-team-late-registration-fee" class="form-control"
                                                        placeholder="0.00" autocomplete="off" type="text"
                                                        :disabled="!editedLevel.allow_teams"
                                                        v-model="editedLevel.team_late_registration_fee">
                                                </div>
                                            </div>
                                        </div>
                                        
                                    </div>

                                    <!-- <div class="mb-2">
                                        <div class="form-check pb-1">
                                            <input class="form-check-input" id="edit-all-levels-enable-athlete-limit"
                                                type="checkbox" v-model="editedLevel.enable_athlete_limit">
                                            <label class="form-check-label" for="edit-all-levels-enable-athlete-limit">
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
                                        @click="saveAllLevels">
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

        <div v-if="isLoading" class="text-center p-3">
            <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
            Loading sanction details, please wait ...
        </div>
        <div v-else-if="errorMessage !== null" class="alert alert-danger">
            <strong>
                <span class="fas fa-fw fa-times-circle"></span> Whoops !<br/>
            </strong>
            <div v-html="errorMessage"></div>
        </div>
        <div v-if="state !== null">            
            <div>
                <h5 class="pb-1 border-bottom border-secondary font-weight-bold">
                    <span class="fas fa-fw fa-info-circle"></span>
                    Sanction Info
                </h5>
                <div class="mb-2 ml-3">
                    <div class="">
                        <strong>Gym :</strong> {{ state.gym.name }}
                    </div>
                    <div class="">
                        <strong>Category :</strong> {{ state.category.name }}
                    </div>
                    <div class="">
                        <strong>Assigned To Meet :</strong>
                        <span v-if="!meetSelected">(Not assigned yet)</span>
                        <span v-else>{{ selectedMeet.name }}</span>
                    </div>
                </div>
            </div>

            <div v-if="meetDetailsLink !== null">
                <div class="alert alert-success">
                    Changes were successfully applied.
                    <div class="text-right">
                        <a :href="meetDetailsLink" class="btn btn-small btn-info">
                            <span class="fas fa-eye"></span> View Meet Details
                        </a>
                    </div>
                </div>
            </div>
            <div v-else>
                <div v-if="state.meet === null">
                    <h5 class="pb-1 border-bottom border-secondary font-weight-bold">
                        <span class="fas fa-fw fa-object-group"></span>
                        Assign To Meet
                    </h5>

                    <div class="mb-2 ml-3">
                        <div v-if="state.assignable_meets.length < 1">
                            <div class="alert alert-danger">
                                <strong>
                                    <span class="fas fa-fw fa-exclamation-triangle"></span>
                                    This sanction cannot be assigned to any of your existing meets. Please continue with an option below.<br/>
                                </strong>
                            </div>
                            <div class="row mb-3">
                                <div class="col">
                                    <div class="card-group">
                                        <div class="card">
                                            <div class="card-header bg-primary text-light">
                                                <span class="fas fa-calendar-alt"></span> Assign to an existing meet
                                            </div>
                                            <div class="card-body bg-white pb-0">
                                                If you already created the meet you'd like to assign this sanction to,
                                                please make sure it is published and has {{ state.category.name }} selected in Competitive Settings.
                                            </div>
                                            <div class="card-footer text-right border-top-0 bg-white pt-0">
                                                <a :href="'/gyms/' + state.gym.id + '/meets'"
                                                    class="btn btn-primary" target="_blank">
                                                    <span class="fas fa-external-link-alt"></span>
                                                    View Hosted Meets.
                                                </a>
                                            </div>
                                        </div>

                                        <div class="card">
                                            <div class="card-header bg-dark text-light">
                                                <span class="fas fa-dumbbell"></span> Create a Meet
                                            </div>
                                            <div class="card-body bg-white pb-0">
                                                <p>
                                                    If you have not created the meet you'd like to assign this sanction to,
                                                    please create a meet and then refresh this page.
                                                </p>
                                            </div>
                                            <div class="card-footer text-right border-top-0 bg-white pt-0">
                                                <a :href="'/gyms/' + state.gym.id + '/meets/create'"
                                                    class="btn btn-dark" target="_blank">
                                                    <span class="fas fa-external-link-alt"></span>
                                                    Create
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                                <!-- <ul>
                                    <li>
                                        If you already created the meet you'd like to assign this sanction to,
                                        please make sure it is published and has {{ state.category.name }} selected in Competitive Settings.
                                        <a :href="'/gyms/' + state.gym.id + '/meets'"
                                            class="alert-link" target="_blank">
                                            <span class="fas fa-external-link-alt"></span>
                                            Click here to view your hosted meets.
                                        </a>
                                    </li>
                                    <li>
                                        If you have not created the meet you'd like to assign this sanction to yet, please
                                        <a :href="'/gyms/' + state.gym.id + '/meets/create'"
                                            class="alert-link" target="_blank">
                                            <span class="fas fa-external-link-alt"></span>
                                            create a new meet
                                        </a>
                                        and refresh this page.
                                    </li>
                                </ul> -->

                        </div>
                        <div v-else>
                            <div class="alert alert-warning">
                                <strong>
                                    <span class="fas fa-fw fa-exclamation-triangle"></span>
                                    Assigning this sanction to a meet is permanent !
                                </strong>
                                <p class="m-0">
                                    Once you apply the changes and assign this sanction to a meet,
                                    you will no longer be able to replace it in or remove it from that meet.
                                </p>
                            </div>
                        
                            <div class="form-group">
                                <label for="assign_to_select" class="font-weight-bold">Assign the Sanction to :</label>
                                <select id="assign_to_select" class="form-control form-control-sm"
                                    v-model="selectedMeet">
                                    <option value="">(Choose below ...)</option>
                                    <option v-for="m in state.assignable_meets" :key="m.id"
                                        :value="m">{{ m.name }}
                                    </option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <div>
                    <h5 class="pb-1 border-bottom border-secondary font-weight-bold">
                        <span class="fas fa-fw fa-stream"></span>
                        Sanction Details
                    </h5>

                    <div class="mb-2">
                        <div class="mb-1">
                            <button class="btn btn-sm btn-dark btn-block text-left left-btn"
                                type="button" @click="expanded.initial = !expanded.initial">
                                <span class="fas fa-fw fa-list-ul"></span>
                                Current State
                                <span :class="'fas fa-fw fa-caret-' + (expanded.initial ? 'down' : 'right')"></span>
                            </button>
                        </div>                        

                        <div v-if="expanded.initial" class="ml-3">
                            <div class="mt-1 mb-2">
                                <strong>
                                    {{ state.category.name }}:
                                </strong>
                                <span v-if="state.category.frozen.initial" class="text-danger">
                                    Category is currently frozen. New registrations are not allowed.
                                </span>
                                <span v-else class="text-success">
                                    Category is currently unfrozen. New registrations are allowed.
                                </span>
                            </div>

                            <div v-if="state.initial_count < 1">
                                No levels.
                            </div>
                            <div v-else>
                                <div class="table-responsive-lg">
                                    <table class="table table-sm table-striped">
                                        <thead class="thead-dark">
                                            <tr>
                                                <th scope="col" class="align-middle">
                                                    Name
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr v-for="(level, code) in state.initial" :key="code">                                        
                                                <td class="align-middle">
                                                    {{ level.name }}
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>                  
                            </div>
                        </div>
                    </div>

                    <div class="mb-2">
                        <div class="mb-1">
                            <button class="btn btn-sm btn-dark btn-block text-left left-btn"
                                type="button" @click="expanded.details = !expanded.details">
                                <span class="fas fa-fw fa-tasks"></span>
                                Received Actions Details
                                <span :class="'fas fa-fw fa-caret-' + (expanded.details ? 'down' : 'right')"></span>
                            </button>
                        </div>                        

                        <div v-if="expanded.details" class="ml-3">
                            <div v-if="state.details_count < 1">
                                No actions.
                            </div>
                            <div v-else>
                                <div v-for="(action, actionIndex) in state.details" :key="actionIndex">
                                    <div class="mb-1">
                                        <button :class="'btn btn-sm btn-' + constants.sanctions.colors.bg[action.type] + ' btn-block text-left left-btn'"
                                            type="button" @click="action.expanded = !action.expanded">
                                            <span class="fas fa-fw fa-angle-double-right"></span>                                    
                                            {{ action.readable.timestamp }} |
                                            {{ constants.sanctions.actions[action.type] }}
                                            <span :class="'fas fa-fw fa-caret-' + (action.expanded ? 'down' : 'right')"></span>
                                        </button>
                                    </div>

                                    <div v-if="action.expanded" class="ml-3">
                                        <div v-if="action.freeze !== null">
                                            <div v-if="action.freeze == constants.sanctions.categories.actions.FREEZE" class="text-danger">
                                                This category is now frozen.
                                            </div>
                                            <div v-else class="text-success">
                                                This category is now unfrozen.
                                            </div>
                                        </div>

                                        <ul class="text-danger mb-1">
                                            <li v-for="(issue, issueIndex) in action.issues" :key="actionIndex + '-issue-' + issueIndex">
                                                {{ issue }}
                                            </li>
                                        </ul>

                                        <div v-if="(action.added_count > 0) || (action.removed_count > 0)">
                                            <div class="table-responsive-lg">
                                                <table class="table table-sm table-striped">                                            
                                                    <tbody>
                                                        <tr v-for="(level) in action.removed" :key="actionIndex + '-removed-' + level.id">
                                                            <td :class="'align-middle action-column text-' + constants.sanctions.levels.colors.fg[level.action]">
                                                                <span :class="'fas fa-fw fa-' + constants.sanctions.levels.icons[level.action]">
                                                                </span>
                                                                {{ constants.sanctions.levels.actions[level.action] }}
                                                            </td>
                                                            <td class="align-middle">
                                                                {{ level.name }}
                                                            </td>
                                                        </tr>
                                                        <tr v-for="(level) in action.added" :key="actionIndex + '-added-' + level.id">
                                                            <td :class="'align-middle action-column text-' + constants.sanctions.levels.colors.fg[level.action]">
                                                                <span :class="'fas fa-fw fa-' + constants.sanctions.levels.icons[level.action]">
                                                                </span>
                                                                {{ constants.sanctions.levels.actions[level.action] }}
                                                            </td>
                                                            <td class="align-middle">
                                                                {{ level.name }}
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

                    <div class="mb-2">
                        <div class="mb-1">
                            <button class="btn btn-sm btn-dark btn-block text-left left-btn"
                                type="button" @click="expanded.final = !expanded.final">
                                <span class="fas fa-fw fa-th-list"></span>
                                Final State Preview
                                <span :class="'fas fa-fw fa-caret-' + (expanded.final ? 'down' : 'right')"></span>
                            </button>
                        </div>                        

                        <div v-if="expanded.final" class="ml-3">
                            <div class="mt-1 mb-2">
                                <strong>
                                    {{ state.category.name }}:
                                </strong>
                                <span v-if="state.category.frozen.final" class="text-danger">
                                    Category will be frozen. New registrations will not be allowed.
                                </span>
                                <span v-else class="text-success">
                                    Category will be unfrozen. New registrations will be allowed.
                                </span>
                            </div>

                            <div v-if="meetSelected">
                                <h5 class="border-bottom"><span class="fas fa-fw fa-calendar-check"></span> Meet Details</h5>
                                
                                <div class="alert alert-info mb-1">
                                    <span class="fas fa-fw fa-info-circle"></span> If your local meet data and the your USAG sanction data are different, you choose which data to keep below.
                                </div>

                                <div class="text-right mb-1">
                                    <button class="btn btn-sm btn-danger" @click="toggleAllMeetDataFields(false)">
                                        <span class="fas fa-fw fa-toggle-off"></span> All Local
                                    </button>
                                    <button class="btn btn-sm btn-success" @click="toggleAllMeetDataFields(true)">
                                        <span class="fas fa-fw fa-toggle-on"></span> All USAG
                                    </button>
                                </div>
                                <div class="table-responsive-lg">
                                    <table class="table table-sm table-striped">
                                        <thead class="bg-primary text-light">
                                            <tr>
                                                <th scope="col" class="align-middle">
                                                    Field
                                                </th>
                                                <th scope="col" class="align-middle">
                                                    Local
                                                </th>
                                                <th scope="col" class="align-middle text-center">
                                                    <span class="fas fa-fw fa-toggle-on"></span>
                                                </th>
                                                <th scope="col" class="align-middle">
                                                    USAG
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr v-for="(data, field) in meetDataSwitches" :key="'final-meet-data-' + field">
                                                <td class="'align-middle">
                                                    {{ data.text }}
                                                </td>

                                                <td class="align-middle">
                                                    {{ selectedMeet[data.local_name + (data.has_display ? '_display' : '')] }}
                                                </td>

                                                <td class="align-middle text-center">
                                                    <toggle-button class="mb-0" v-model="data.value" :disabled="state.usag_meet_data[field] === null" :sync="true" :color="toggle_settings.colors" :labels="toggle_settings.labels" :heigth="20" :width="64"/>
                                                </td>

                                                <td class="align-middle">
                                                    <span v-if="state.usag_meet_data[field] !== null">
                                                        {{ state.usag_meet_data[field + (data.has_display ? '_display' : '')] }}
                                                    </span>
                                                    <span v-else>
                                                        —
                                                    </span>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <div>
                                <h5 class="border-bottom"><span class="fas fa-fw fa-layer-group"></span> Levels</h5>
                                <div v-if="state.final_count < 1">
                                    No levels.
                                </div>
                                <div v-else>
                                    <div class="alert alert-info">
                                        <span class="fas fa-fw fa-info-circle"></span> Please add your level entry fees before moving on. (You can add individually or All).
                                    </div>

                                    <div class="text-right mb-3">
                                        <button v-if="meetSelected && hasEditableLevels" class="btn btn-sm btn-success"
                                            type="button" title="Edit" @click="editAllLevels">
                                            <span class="fas fa-fw fa-edit"></span> Edit All                                               
                                        </button>
                                    </div>
                                    <div class="table-responsive-lg">
                                        <table class="table table-sm table-striped">                                
                                            <tbody>
                                                <tr v-for="(level, code) in state.final" :key="code">
                                                    <td :class="'align-middle action-column text-' + constants.sanctions.levels.colors.fg[level.action]">
                                                        <span :class="'fas fa-fw fa-' + constants.sanctions.levels.icons[level.action]">
                                                        </span>
                                                        {{ constants.sanctions.levels.actions[level.action] }}
                                                    </td>

                                                    <td class="align-middle">
                                                        {{ level.name }}
                                                    </td>

                                                    <td class="align-middle small" v-if="meetSelected">
                                                        <div v-if="level.enable_athlete_limit">
                                                            <strong>Limit:</strong> {{ level.athlete_limit }}
                                                        </div>
                                                        <div>
                                                            <strong>Regular:</strong>
                                                            ${{ level.registration_fee}}
                                                        </div>
                                                        <div v-if="selectedMeet.allow_late_registration">
                                                            <strong>Late:</strong>
                                                            ${{ level.late_registration_fee}}
                                                        </div>
                                                        <div v-if="selectedMeet.registration_first_discount_is_enable">
                                                            <strong>Early:</strong>
                                                            ${{ level.registration_fee_first}}
                                                        </div>
                                                        
                                                    </td>

                                                    <td class="align-middle small" v-if="meetSelected">
                                                        <div v-if="level.allow_teams">
                                                            <div>
                                                                <strong>Teams:</strong>
                                                                Yes
                                                            </div>
                                                            <div>
                                                                <strong>Regular:</strong>
                                                                ${{ level.team_registration_fee}}
                                                            </div>
                                                            <div v-if="selectedMeet.allow_late_registration">
                                                                <strong>Late:</strong>
                                                                ${{ level.team_late_registration_fee}}
                                                            </div>
                                                        </div>
                                                        <div v-else>
                                                            <strong>Teams:</strong>
                                                            No
                                                        </div>
                                                    </td>

                                                    <td class="align-middle text-right button-column">
                                                        <button v-if="meetSelected && level.can_edit" class="btn btn-sm btn-success"
                                                            type="button" title="Edit" @click="editLevel(code, level)">
                                                            <span class="fas fa-fw fa-edit"></span>                                                
                                                        </button>
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

                <div class="d-flex flex-row flex-nowrap">
                    <div class="flex-grow-1">
                        <a href="/" class="btn btn-primary">
                            <span class="fas fa-long-arrow-alt-left"></span> Back
                        </a>
                    </div>
                    <div class="">
                        <button class="btn btn-success" :disabled="!canProceed" @click="confirmMerge">
                            Next <span class="fas fa-long-arrow-alt-right"></span>
                        </button>
                    </div>
                </div>
            </div>
        </div>        
    </div>
</template>
    
<style>
    .action-column {
        width: 130px;
    }

    .disabled-column {
        width: 100px;
    }

    .button-column {
        width: 50px;
    }
</style>

<script>
    import { ToggleButton } from 'vue-js-toggle-button';

    export default {
        name: 'SanctionDetails',
        components: {
            ToggleButton
        },
        props: {
            managed: {
                default: null,
                type: Number
            },
            sanction_data: {
                type: Object,
                default: {}
            },

            sanction_id: {
                type: Number,
                default: null
            },
        },
        computed: {
            toggle_settings() {
				return {
					labels: {
						checked: 'USAG', unchecked: 'Local'
					},
					colors: {
						checked: '#38c172',
						unchecked: '#e3342f',
						disabled: '#CCCCCC'
					}
				}
			},

            constants() {
                return {
                    bodies: {
                        1: 'USAG',
                        2: 'USAIGC',
                        3: 'AAU',
                        USAG: 1,
                        USAIGC: 2,
                        AAU: 3
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
                    sanctions: {
                        actions: {
                            ADD: 1,
                            UPDATE: 2,
                            DELETE: 3,
                            CHANGE_VENDOR: 4,
                            1: 'Add',
                            2: 'Update',
                            3: 'Delete',
                            4: 'Change Vendor',
                        },
                        statuses: {
                            PENDING: 1,
                            DISMISSED: 2,
                            MERGED: 3,
                        },
                        categories: {
                            actions: {
                                FREEZE: 1,
                                UNFREEZE: 2,
                                1: 'Freeze',
                                2: 'Unfreeze',
                            },
                        },
                        levels: {
                            actions: {
                                UNCHANGED: 1,
                                ADDED: 2,
                                REMOVED: 3,
                                ENABLED: 4,
                                DISABLED: 5,
                                1: 'Unchanged',
                                2: 'Added',
                                3: 'Removed',
                                4: 'Enabled',
                                5: 'Disabled',
                            },
                            colors: {
                                fg: {
                                    1: 'dark',
                                    2: 'success',
                                    3: 'danger',
                                    4: 'success',
                                    5: 'danger',
                                }
                            },
                            icons: {                                
                                1: 'minus',
                                2: 'plus',
                                3: 'times',
                                4: 'plus',
                                5: 'times',
                            }
                        },
                        colors: {
                            bg: {
                                1: 'success',
                                2: 'warning',
                                3: 'danger',
                                4: 'secondary',
                            }
                        }
                    }
                };
            },

            canProceed () {
                return (this.state.meet !== null) || (this.selectedMeet !== '');
            },

            meetSelected () {
                return (this.selectedMeet !== '')
            },

            hasEditableLevels() {
                for (const i in this.state.final) {
                    if (this.state.final.hasOwnProperty(i)) {
                        const level = this.state.final[i];
                        if (level.can_edit)
                        return true;
                    }
                }
                return false;
            },
        },
        watch: {
            selectedMeet(v) {
                this.meetDataSwitches.registration_end_date.text = 'Registration End Date';
                this.meetDataSwitches.registration_end_date.local_name = 'registration_end_date';

                for (const field in this.meetDataSwitches) {
                    if (this.meetDataSwitches.hasOwnProperty(field)) {
                        const data = this.meetDataSwitches[field];
                        data.value = false;                        
                    }
                }

                if (v !== null) {
                    if (v.allow_late_registration) {
                        this.meetDataSwitches.registration_end_date.text = 'Late Registration End Date';
                        this.meetDataSwitches.registration_end_date.local_name = 'late_registration_end_date';
                    }
                    // if(v.registration_first_discount_is_enable)
                    // {
                    //     this.meetDataSwitches
                    // }
                    // if(v.registration_second_discount_is_enable)
                    // {

                    // }
                    // if(v.registration_third_discount_is_enable)
                    // {

                    // }
                    
                    //         'registration_first_discount_is_enable',
                    //         '',
                    //         ''
                }
            },
        },
        data() {
            return {
                isLoading: false,
                errorMessage: null,
                state: null,
                expanded: {
                    initial: true,
                    details: false,
                    final: true,
                },
                selectedMeet: '',
                editedLevel: null,
                meetDetailsLink: null,
                meetDataSwitches: {
                    start_date: {
                        has_display: true,
                        text: 'Start Date',
                        local_name: 'start_date',
                        value: false,
                    }, 
                    end_date: {
                        has_display: true,
                        text: 'End Date',
                        local_name: 'end_date',
                        value: false,
                    }, 
                    registration_start_date: {
                        has_display: true,
                        text: 'Registration Start Date',
                        local_name: 'registration_start_date',
                        value: false,
                    }, 
                    registration_end_date: {
                        has_display: true,
                        text: 'Registration End Date',
                        local_name: 'registration_end_date',
                        value: false,
                    }, 
                    scratch_date: {
                        has_display: true,
                        text: 'Scratch Date',
                        local_name: 'registration_scratch_end_date',
                        value: false,
                    }, 
                    venue_name: {
                        has_display: false,
                        text: 'Venue Name',
                        local_name: 'venue_name',
                        value: false,
                    }, 
                    venue_addr_1: {
                        has_display: false,
                        text: 'Venue Address 1',
                        local_name: 'venue_addr_1',
                        value: false,
                    }, 
                    venue_addr_2: {
                        has_display: false,
                        text: 'Venue Address 2',
                        local_name: 'venue_addr_2',
                        value: false,
                    }, 
                },
            }
        },
        methods: {
            editLevel(code, level) {
                if (!level.can_edit)
                    return;

                if (!this.meetSelected) {
                    this.showAlert('Please select a meet to assign this sanction to first.', 'Whoops', 'red', 'fas fa-exclamation-triangle');
                    return;
                }

                this.editedLevel = {
                    ...level,
                    code: code,
                    error: null
                }; // no need to deepclone
                $('#modal-edit-level').modal('show');
            },

            editAllLevels() {
                if (!this.meetSelected) {
                    this.showAlert('Please select a meet to assign this sanction to first.', 'Whoops', 'red', 'fas fa-exclamation-triangle');
                    return;
                }

                this.editedLevel = {
                    can_edit: true,
                    registration_fee: 0,
                    registration_fee_first: 0,
                    late_registration_fee: 0,
                    allow_teams: false,
                    team_registration_fee: 0,
                    team_late_registration_fee: 0,
                    enable_athlete_limit: false,
                    athlete_limit: 0,
                    error: null
                };

                $('#modal-edit-all').modal('show');
            },

            saveAllLevels() {
                try {
                    if (!this.editedLevel.can_edit) {
                        $('#modal-edit-all').modal('hide');
                        return;
                    }

                    this.editedLevel.error = null;

                    let fee = null;
                    fee = Utils.toFloat(this.editedLevel.registration_fee);
                    if ((fee === null) || (fee < 0))
                        throw 'Please enter a valid registration fee';
                    this.editedLevel.registration_fee = fee.toFixed(2);

                    if (this.selectedMeet.allow_late_registration) {
                        fee = Utils.toFloat(this.editedLevel.late_registration_fee);
                        if ((fee === null) || (fee < 0))
                            throw 'Please enter a valid late registration fee';
                        this.editedLevel.late_registration_fee = fee.toFixed(2);
                    } else {
                        this.editedLevel.late_registration_fee = 0;
                    }

                    if(this.selectedMeet.registration_first_discount_is_enable)
                    {
                        fee = Utils.toFloat(this.editedLevel.registration_fee_first);
                        if((fee === null) || (fee < 0))
                            throw 'Please enter a valid early registration fee';
                        this.editedLevel.registration_fee_first = fee.toFixed(2);
                    }else{
                        this.editedLevel.registration_fee_first = 0;
                    }
                    

                    if (this.editedLevel.allow_teams) {
                        fee = Utils.toFloat(this.editedLevel.team_registration_fee);
                        if ((fee === null) || (fee < 0))
                            throw 'Please enter a valid team registration fee';
                        this.editedLevel.team_registration_fee = fee.toFixed(2);

                        if (this.selectedMeet.allow_late_registration) {
                            fee = Utils.toFloat(this.editedLevel.team_late_registration_fee);
                            if ((fee === null) || (fee < 0))
                                throw 'Please enter a valid team late registration fee';
                            this.editedLevel.team_late_registration_fee = fee.toFixed(2);
                        } else {
                            this.editedLevel.team_late_registration_fee = 0;
                        }
                    } else {
                        this.editedLevel.allow_teams = false;
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
                    
                    for (const i in this.state.final) {
                        if (this.state.final.hasOwnProperty(i)) {
                            const level = this.state.final[i];

                            if (level.can_edit) {
                                level.registration_fee = this.editedLevel.registration_fee;
                                
                                level.registration_fee_first = this.editedLevel.registration_fee_first;

                                level.late_registration_fee = this.editedLevel.late_registration_fee;

                                level.allow_teams = this.editedLevel.allow_teams;
                                level.team_registration_fee = this.editedLevel.team_registration_fee;
                                level.team_late_registration_fee = this.editedLevel.team_late_registration_fee;

                                level.enable_athlete_limit = this.editedLevel.enable_athlete_limit
                                level.athlete_limit = this.editedLevel.athlete_limit;
                            }                                                       
                        }
                    }

                    $('#modal-edit-all').modal('hide');
                } catch (error) {
                    //console.log(error);
                    this.editedLevel.error = error;
                }
            },

            saveEditedLevel() {
                try {
                    if (!this.editedLevel.can_edit) {
                        $('#modal-edit-level').modal('hide');
                        return;
                    }                        

                    this.editedLevel.error = null;

                    let fee = null;
                    fee = Utils.toFloat(this.editedLevel.registration_fee);
                    if ((fee === null) || (fee < 0))
                        throw 'Please enter a valid registration fee';
                    this.editedLevel.registration_fee = fee.toFixed(2);

                    if (this.selectedMeet.allow_late_registration) {
                        fee = Utils.toFloat(this.editedLevel.late_registration_fee);
                        if ((fee === null) || (fee < 0))
                            throw 'Please enter a valid late registration fee';
                        this.editedLevel.late_registration_fee = fee.toFixed(2);
                    } else {
                        this.editedLevel.late_registration_fee = 0;
                    }

                    if(this.selectedMeet.registration_first_discount_is_enable)
                    {
                        fee = Utils.toFloat(this.editedLevel.registration_fee_first);
                        if((fee === null) || (fee < 0))
                            throw 'Please enter a valid early registration fee';
                        this.editedLevel.registration_fee_first = fee.toFixed(2);
                    }else{
                        this.editedLevel.registration_fee_first = 0;
                    }
                    

                    if (this.editedLevel.allow_teams) {
                        fee = Utils.toFloat(this.editedLevel.team_registration_fee);
                        if ((fee === null) || (fee < 0))
                            throw 'Please enter a valid team registration fee';
                        this.editedLevel.team_registration_fee = fee.toFixed(2);

                        if (this.selectedMeet.allow_late_registration) {
                            fee = Utils.toFloat(this.editedLevel.team_late_registration_fee);
                            if ((fee === null) || (fee < 0))
                                throw 'Please enter a valid team late registration fee';
                            this.editedLevel.team_late_registration_fee = fee.toFixed(2);
                        } else {
                            this.editedLevel.team_late_registration_fee = 0;
                        }
                    } else {
                        this.editedLevel.allow_teams = false;
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

                    this.state.final[this.editedLevel.code].registration_fee = this.editedLevel.registration_fee;
                    
                    this.state.final[this.editedLevel.code].registration_fee_first = this.editedLevel.registration_fee_first;

                    this.state.final[this.editedLevel.code].late_registration_fee = this.editedLevel.late_registration_fee;

                    this.state.final[this.editedLevel.code].allow_teams = this.editedLevel.allow_teams;
                    this.state.final[this.editedLevel.code].team_registration_fee = this.editedLevel.team_registration_fee;
                    this.state.final[this.editedLevel.code].team_late_registration_fee = this.editedLevel.team_late_registration_fee;

                    this.state.final[this.editedLevel.code].enable_athlete_limit = this.editedLevel.enable_athlete_limit
                    this.state.final[this.editedLevel.code].athlete_limit = this.editedLevel.athlete_limit;

                    $('#modal-edit-level').modal('hide');
                } catch (error) {
                    //console.log(error);
                    this.editedLevel.error = error;
                }
            },

            confirmMerge() {
                let msg = 'Are you sure you want to apply the changes ?';

                if (this.selectedMeet !== null)
                    msg = 'Are you sure you want to assign this sanction to ' + this.selectedMeet.name + ' and apply the changes ?';

                this.confirmAction(
                    msg,
                    'red',
                    'fas fa-exclamation-triangle',
                    () => {
                        this.isLoading = true;
                        let url = '/api/gyms/' + this.sanction_data.gym.id + '/sanctions/usag/' + this.sanction_id + '/merge';

                        let meetDataSwitches = {};
                        for (const field in this.meetDataSwitches) {
                            if (this.meetDataSwitches.hasOwnProperty(field)) {
                                const element = this.meetDataSwitches[field];
                                meetDataSwitches[field] = element.value;
                            }
                        }

                        axios.post(
                            url,
                            {
                                '__managed': this.managed,
                                'meet': (this.selectedMeet !== null ? this.selectedMeet.id : null),
                                'data': this.state,
                                'meet_data_switches': meetDataSwitches
                            }
                        ).then(result => {
                            this.meetDetailsLink = result.data.url;
                        }).catch(error => {
                            let msg = '';
                            if (error.response) {
                                msg = error.response.data.message;
                            } else if (error.request) {
                                msg = 'No server response.';
                            } else {
                                msg = error.message;
                            }
                            this.showAlert(
                                msg,
                                'Oops',
                                'red',
                                'fas fa-times-circle',
                            );
                        }).finally(() => {
                            this.isLoading = false;
                        });
                    },
                    this
                );
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

            toggleAllMeetDataFields(toggle) {
                for (const field in this.meetDataSwitches) {
                    if (this.meetDataSwitches.hasOwnProperty(field)) {
                        const data = this.meetDataSwitches[field];
                        data.value = (toggle === true ? true : false);
                    }
                }
            },
        },
        mounted() {
            this.isLoading = true;
            try {                                
                let state = _.cloneDeep(this.sanction_data);
                let details = state.details;
                let final = state.final;
                let usag_meet_data = state.usag_meet_data;
                let assignable_meets = state.assignable_meets;

                ['initial', 'details', 'final'].forEach(
                    item => state[item + '_count'] = Object.keys(state[item]).length
                );

                state.details = {};
                details.forEach(item => {
                    ['added', 'removed'].forEach(
                        prop => item[prop + '_count'] = Object.keys(item[prop]).length
                    );

                    item.expanded = true;                    
                    item.readable = {};
                    item.timestamp = Moment(item.timestamp);                                    
                    item.readable.timestamp = item.timestamp.format('MM/DD/YYYY h:m:s a');
                });                
                state.details = details;


                state.final = {};
                for (const code in final) {
                    if (final.hasOwnProperty(code)) {
                        let level = final[code];
                        level.can_edit = (
                            (level.action == this.constants.sanctions.levels.actions.ADDED) ||
                            (
                                (level.action == this.constants.sanctions.levels.actions.ENABLED) &&
                                !level.has_registrations
                            )
                        );
                        
                        [
                            'registration_fee',
                            'late_registration_fee',                            
                            'team_registration_fee',
                            'team_late_registration_fee',                            
                            'athlete_limit',

                            'registration_fee_first',
                        ].forEach(field => {
                            level[field] = (level[field] === undefined ? 0 : level[field]);
                        });

                        [
                            'allow_teams',
                            'enable_athlete_limit',
                        ].forEach(field => {
                            level[field] = (level[field] === undefined ? false : level[field]);
                        });
                    }
                }
                state.final = final;


                state.usag_meet_data = {};
                let usag_meet_dates = ['start_date', 'end_date', 'registration_start_date', 'registration_end_date', 'scratch_date'];
                usag_meet_dates.forEach(f => {                    
                    if (usag_meet_data[f] !== null) {
                        usag_meet_data[f] = Moment(usag_meet_data[f]);
                        usag_meet_data[f + '_display'] = usag_meet_data[f].format('MM/DD/YYYY');
                    }
                });
                usag_meet_dates.venue_name = Voca.isBlank(usag_meet_dates.venue_name) ? null : usag_meet_dates.venue_name;
                usag_meet_dates.venue_addr_1 = Voca.isBlank(usag_meet_dates.venue_addr_1) ? null : usag_meet_dates.venue_addr_1;
                usag_meet_dates.venue_addr_2 = Voca.isBlank(usag_meet_dates.venue_addr_2) ? null : usag_meet_dates.venue_addr_2;
                
                state.usag_meet_data = usag_meet_data;
                
                
                state.assignable_meets = [];
                let local_meet_dates = ['start_date', 'end_date', 'registration_start_date', 'registration_end_date', 'registration_scratch_end_date', 'late_registration_start_date', 'late_registration_end_date'];
                assignable_meets.forEach(m => {
                    local_meet_dates.forEach(f => {
                        if (m[f] !== null) {
                            m[f] = Moment(m[f]);
                            m[f + '_display'] = m[f].format('MM/DD/YYYY');
                        }
                    });
                });
                state.assignable_meets = assignable_meets;

                Vue.set(this, 'state', state);

                if (this.state.meet !== null)
                    this.selectedMeet = this.state.meet;

            } catch (error) {
                this.errorMessage = error + '<br/>Please reload this page.';
            } finally {
                this.isLoading = false;
            }
        }
    }
</script>
