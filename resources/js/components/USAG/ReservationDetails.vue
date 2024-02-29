<template>
    <div>
        <div class="modal fade" id="modal-coupon" tabindex="-1" role="dialog" aria-labelledby="modal-coupon" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title text-primary">
                            <span class="fas fa-check"></span> Coupon
                        </h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span class="fas fa-times" aria-hidden="true"></span>
                        </button>
                    </div>

                    <div class="modal-body">
                        <div class="d-flex flex-row flex-no-wrap mb-3">
                            <div style="width: 50%;">
                                Enter Your Coupon Code
                            </div>

                            <div class="ml-1">
                                <input type="text" class="form-control form-control-sm" v-model="coupon"  placeholder="XXXXXXXX" value="">
                            </div>
                        </div>
                        <div class="container-fluid">
                            <div class="text-right mt-3">
                                <button class="btn btn-sm btn-secondary mr-1" data-dismiss="modal">
                                    <span class="far fa-fw fa-times-circle"></span> Close
                                </button>
                                <button class="btn btn-sm btn-success"
                                    @click="checkCoupon(coupon)">
                                    <span class="fas fa-fw fa-check"></span> Confirm
                                </button>
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
        <div v-else-if="state !== null">
            <div>
                <div class="alert alert-info small mb-3">
                    <span class="fas fa-info-circle"></span> ACH (Saved Account Info) and One Time ACH (Easy Pay)
                </div>
                <h5 class="pb-1 border-bottom border-secondary font-weight-bold">
                    <span class="fas fa-fw fa-info-circle"></span>
                    Reservation Info
                </h5>
                <div class="mb-2 ml-3">
                    <div class="">
                        <strong>Gym :</strong> {{ state.gym.name }}
                    </div>
                    <div class="">
                        <strong>Category :</strong> {{ state.category.name }}
                    </div>
                    <div class="">
                        <strong>Meet :</strong>
                        <span>{{ state.meet.name }}</span>
                    </div>
                </div>
            </div>

            <div v-if="registrationDetailsLink !== null">
                <div class="alert alert-success">
                    Changes were successfully applied.
                    <div class="text-right">
                        <a :href="registrationDetailsLink" class="btn btn-small btn-info">
                            <span class="fas fa-eye"></span> View Registration Details
                        </a>
                    </div>
                </div>
            </div>
            <div v-else>
                <div v-if="step == 1">
                    <div>
                        <h5 class="pb-1 border-bottom border-secondary font-weight-bold">
                            <span class="fas fa-fw fa-stream"></span>
                            Reservation Details
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
                                <div v-for="(l, lid) in state.initial.levels" :key="'initial-level-' + lid" class="mb-2">
                                    <div class="btn btn-sm btn-secondary btn-block text-left" @click="l.expanded = !l.expanded"> 
                                        <div class="d-flex flex-no-wrap flex-row">
                                            <div class="flex-grow-1">
                                                <span>
                                                    <span class="fas fa-fw fa-layer-group"></span>
                                                    {{ l.name }}
                                                </span> |
                                                <span class="ml-2 mr-2">
                                                    <span v-if="l.male && l.female">Both</span>
                                                    <span v-else-if="l.male">Male</span>
                                                    <span v-else-if="l.female">Female</span>
                                                </span>
                                                <span :class="'fas fa-fw fa-caret-' + (l.expanded ? 'down' : 'right')"></span>
                                            </div>
                                        </div>
                                    </div>

                                    <div v-if="l.expanded" class="ml-3 mb-1">
                                        <div v-if="l.allow_team" class="d-flex flex-row flex-no-wrap mt-2 mb-1">
                                            <div>
                                                <div v-if="l.has_team" class="text-info">
                                                    <span class="fas fa-users"></span>
                                                    Athletes in this level are registered as a team.
                                                </div>
                                                <div v-else class="">
                                                    <span class="far fa-user"></span>
                                                    Athletes in this level are NOT registered as a team.
                                                </div>
                                            </div>
                                        </div>

                                        <div class="table-responsive-lg">
                                            <table class="table table-sm table-hover">
                                                <thead class="bg-primary text-light">
                                                    <tr>
                                                        <th scope="col" class="align-middle">
                                                            First Name
                                                        </th>
                                                        <th scope="col" class="align-middle">
                                                            Last Name
                                                        </th>
                                                        <th scope="col" class="align-middle">
                                                            Gender
                                                        </th>
                                                        <th scope="col" class="align-middle">
                                                            Date Of Birth
                                                        </th>
                                                        <th scope="col" class="align-middle">
                                                            USAG No.
                                                        </th>
                                                        <th scope="col" class="align-middle">
                                                            Status
                                                        </th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr v-if="l.athlete_count() < 1">
                                                        <td colspan="9">
                                                            No athletes.
                                                        </td>
                                                    </tr>

                                                    <tr v-for="athlete in l.athletes" :key="lid + '-' + athlete.id">
                                                        <td class="align-middle">
                                                            {{ athlete.first_name }}
                                                        </td>
                                                        <td class="align-middle">
                                                            {{ athlete.last_name}}
                                                        </td>

                                                        <td class="align-middle">
                                                            {{ ucfirst(athlete.gender) }}
                                                        </td>

                                                        <td class="align-middle">
                                                            {{ athlete.dob_display }}
                                                        </td>

                                                        <td class="align-middle">
                                                            {{ athlete.usag_no }}
                                                        </td>

                                                        <td class="align-middle">
                                                            <div class="d-inline-block">
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
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>

                                </div>

                                <div class="mb-2">
                                    <div class="btn btn-sm btn-success btn-block text-left mb-1" @click="expanded.coaches.initial = !expanded.coaches.initial">
                                        <div class="d-flex flex-no-wrap flex-row">
                                            <div class="flex-grow-1">
                                                <span>
                                                    <span class="fas fa-fw fa-chalkboard-teacher"></span>
                                                    Coaches
                                                </span>
                                                <span :class="'fas fa-fw fa-caret-' + (expanded.coaches.initial ? 'down' : 'right')"></span>
                                            </div>
                                        </div>
                                    </div>

                                    <div v-if="expanded.coaches.initial" class="ml-3 mb-1">
                                        <div class="table-responsive-lg">
                                            <table class="table table-sm table-hover">
                                                <thead class="bg-secondary text-dark">
                                                    <tr>
                                                        <th scope="col" class="align-middle">
                                                            First Name
                                                        </th>
                                                        <th scope="col" class="align-middle">
                                                            Last Name
                                                        </th>
                                                        <th scope="col" class="align-middle">
                                                            Gender
                                                        </th>
                                                        <th scope="col" class="align-middle">
                                                            Date Of Birth
                                                        </th>
                                                        <th scope="col" class="align-middle">
                                                            USAG No.
                                                        </th>
                                                        <th scope="col" class="align-middle">
                                                            Status
                                                        </th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr v-if="state.initial.coach_count() < 1">
                                                        <td colspan="9">
                                                            No coaches.
                                                        </td>
                                                    </tr>

                                                    <tr v-for="(coach, usag_no) in state.initial.coaches" :key="'initial-coach-' + coach.id">
                                                        <td class="align-middle">
                                                            {{ coach.first_name }}
                                                        </td>
                                                        <td class="align-middle">
                                                            {{ coach.last_name}}
                                                        </td>

                                                        <td class="align-middle">
                                                            {{ ucfirst(coach.gender) }}
                                                        </td>

                                                        <td class="align-middle">
                                                            {{ coach.dob_display }}
                                                        </td>

                                                        <td class="align-middle">
                                                            {{ usag_no }}
                                                        </td>

                                                        <td class="align-middle">
                                                            <div class="d-inline-block">
                                                                <div v-if="coach.status == constants.coaches.statuses.Registered">
                                                                    <span class="badge badge-success">Registered</span>
                                                                </div>

                                                                <div v-else-if="coach.status == constants.coaches.statuses.NonReserved">
                                                                    <span class="badge badge-warning">
                                                                        <span v-if="coach.in_waitlist">Waitlist</span>
                                                                        <span v-else>Pending</span>
                                                                        (Non-Reserved)
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
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mb-2">
                            <div class="mb-2">
                                <button class="btn btn-sm btn-dark btn-block text-left"
                                    type="button" @click="expanded.final = !expanded.final">
                                    <span class="fas fa-fw fa-th-list"></span>
                                    Final State Preview
                                    <span :class="'fas fa-fw fa-caret-' + (expanded.final ? 'down' : 'right')"></span>
                                </button>
                            </div>

                            <div v-if="expanded.final" class="ml-3">
                                <div v-if="meet.is_waitlist" class="alert alert-warning small mb-1">
                                    <span class="fas fa-fw fa-exclamation-triangle"></span>
                                    This meet is either closed for registrations or has no more free slots. Athletes and coaches in this registration will go into a waitlist.
                                    <button class="btn btn-sm btn-primary float-right" style="margin-top:-5px;" type="button" @click="removeAllFromWaitList()">Unmark All Waitlist</button>
                                </div>

                                <div v-if="meet.slots_needed_in_waitlist > 0" class="text-danger mb-1">
                                    <span class="fas fa-fw fa-exclamation-triangle"></span>
                                    {{ meet.slots_needed_in_waitlist }} more athlete(s) in this meet need to go in the waitlist.
                                </div>

                                <div class="small mb-3">
                                    <div v-if="late">
                                        <div class="d-inline-block mr-1">
                                            <strong>Meet Late Registration Fee:</strong>
                                            {{ meet.late_registration_fee }}
                                        </div>
                                    </div>
                                </div>

                                <div v-for="(l, lid) in state.final.levels" :key="'final-level-' + lid" class="mb-2">
                                    <div class="btn btn-sm btn-secondary btn-block text-left" @click="changeExpand(lid)">
                                        <div class="d-flex flex-no-wrap flex-row">
                                            <div class="flex-grow-1">
                                                <span>
                                                    <span class="fas fa-fw fa-layer-group"></span>
                                                    {{ l.name }}
                                                </span> |
                                                <span class="ml-2 mr-2">
                                                    <span v-if="l.male && l.female">Both</span>
                                                    <span v-else-if="l.male">Male</span>
                                                    <span v-else-if="l.female">Female</span>
                                                </span> |
                                                <span class="ml-2 mr-2">
                                                    {{ l.athlete_count() }} Athletes
                                                </span> |
                                                <span class="ml-2 mr-2">
                                                    <span v-if="l.enable_athlete_limit">
                                                        {{ l.free_slots(true) }} free slots
                                                    </span>
                                                    <span v-else>
                                                        No limit
                                                    </span>
                                                </span>
                                                <span :id="'expand_'+lid" :class="'fas fa-fw fa-caret-down'"></span>
                                            </div>
                                        </div>
                                    </div>
                                    <div :id="'level_'+lid" class="ml-3 mb-1" > 
                                        <div v-if="l.allow_team" class="d-flex flex-row flex-no-wrap mt-2 mb-1">
                                            <div>
                                                <div v-if="l.has_team" class="text-info">
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
                                               <button v-if="!l.changes.team && l.has_team" 
                                                    class="dropdown-item text-danger" type="button" style="border: 1px solid;"
                                                    @click="toggleTeam(l, false)">
                                                    <span class="fas fa-fw fa-eraser"></span> Scratch Team
                                                </button>
                                            </div>
                                            <div class="btn-group" v-if="l.allow_team">
                                                <button v-if="!l.changes.team && !l.has_team"
                                                    class="dropdown-item text-success" type="button" style="border: 1px solid;"
                                                    @click="toggleTeam(l, true)">
                                                    <span class="fas fa-fw fa-users"></span> Register as Team
                                                </button>
                                                <button v-else class="dropdown-item" type="button" style="border: 1px solid;"
                                                    @click="revertLevelTeam(l)">
                                                    <span class="fa fa-fw fa-undo-alt"></span> Revert Changes
                                                </button>
                                            </div>
                                        </div>

                                        <div class="small mb-1">
                                            <div>
                                                <div class="d-inline-block mr-1">
                                                    <strong>Regular fee:</strong>
                                                    ${{ numberFormat(l.registration_fee)}}
                                                </div>
                                                <div class="d-inline-block" v-if="late">
                                                    | <strong>Late:</strong>
                                                    ${{ numberFormat(l.late_registration_fee)}}
                                                </div>
                                            </div>
                                            <div v-if="l.has_specialist">
                                                <div class="d-inline-block mr-1">
                                                    <strong>Specialist:</strong>
                                                    {{ l.allow_specialist ? 'Allowed' : 'Not Allowed'}}
                                                </div>
                                                <div class="d-inline-block" v-if="l.allow_specialist">
                                                    |
                                                    <div class="d-inline-block mr-1">
                                                        <strong>Specialist regular fee:</strong>
                                                        ${{ numberFormat(l.specialist_registration_fee)}}
                                                    </div>
                                                    <div class="d-inline-block mr-1" v-if="late">
                                                        | <strong>Specialist late fee:</strong>
                                                        ${{ numberFormat(l.specialist_late_registration_fee)}}
                                                    </div>
                                                </div>
                                            </div>
                                            <div>
                                                <div class="d-inline-block mr-1">
                                                    <strong>Team:</strong>
                                                    {{ l.allow_team ? 'Allowed' : 'Not Allowed'}}
                                                </div>
                                                <div class="d-inline-block" v-if="l.allow_team">
                                                    |
                                                    <div class="d-inline-block">
                                                        <strong>Team regular fee:</strong>
                                                        ${{ numberFormat(l.team_registration_fee)}}
                                                    </div>
                                                    <div class="d-inline-block" v-if="late">
                                                        | <strong>Team late fee:</strong>
                                                        ${{ numberFormat(l.team_late_registration_fee)}}
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div v-if="l.slots_needed_in_waitlist > 0" class="text-danger mb-2">
                                            <span class="fas fa-fw fa-exclamation-triangle"></span>
                                            {{ l.slots_needed_in_waitlist }} more athlete(s) in this level need to go in the waitlist.
                                        </div>

                                        <div class="table-responsive-lg">
                                            <table class="table table-sm table-hover">
                                                <thead class="bg-primary text-light">
                                                    <tr>
                                                        <th scope="col" class="align-middle">
                                                            First Name
                                                        </th>
                                                        <th scope="col" class="align-middle">
                                                            Last Name
                                                        </th>
                                                        <th scope="col" class="align-middle">
                                                            Gender
                                                        </th>
                                                        <th scope="col" class="align-middle">
                                                            Date Of Birth
                                                        </th>
                                                        <th scope="col" class="align-middle">
                                                            USAG No.
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
                                                    <tr v-if="l.athlete_count() < 1">
                                                        <td colspan="9">
                                                            No athletes.
                                                        </td>
                                                    </tr>

                                                    <tr v-for="athlete in l.athletes" :key="lid + '-' + athlete.id">
                                                        <td class="align-middle">
                                                            {{ athlete.first_name }}
                                                        </td>
                                                        <td class="align-middle">
                                                            {{ athlete.last_name}}
                                                        </td>

                                                        <td class="align-middle">
                                                            {{ ucfirst(athlete.gender) }}
                                                        </td>

                                                        <td class="align-middle">
                                                            {{ athlete.dob_display }}
                                                        </td>

                                                        <td class="align-middle">
                                                            {{ athlete.usag_no }}
                                                        </td>

                                                        <td v-if="meet.tshirt_chart != null" scope="col" class="align-middle">
                                                            <select v-model="athlete.tshirt_size_id" class="form-control form-control-sm">
                                                                <option :value="null">
                                                                    (Choose ...)
                                                                </option>
                                                                <option v-for="size in meet.tshirt_chart.sizes"
                                                                    :key="size.id" :value="size.id">
                                                                    {{ size.size }}
                                                                </option>
                                                            </select>
                                                        </td>

                                                        <td v-if="meet.leo_chart != null" scope="col" class="align-middle">
                                                            <div v-if="athlete.gender == 'male'">—</div>
                                                            <div v-else>
                                                                <select v-model="athlete.leo_size_id" class="form-control form-control-sm">
                                                                    <option :value="null">
                                                                        (Choose ...)
                                                                    </option>
                                                                    <option v-for="size in meet.leo_chart.sizes"
                                                                        :key="size.id" :value="size.id">
                                                                        {{ size.size }}
                                                                    </option>
                                                                </select>
                                                            </div>
                                                        </td>

                                                        <td class="align-middle">
                                                            ${{ numberFormat(athlete.total) }}
                                                        </td>

                                                        <td class="align-middle">
                                                            <div class="d-inline-block">
                                                                <div v-if="athlete.old_level !== null">
                                                                    <span class="badge badge-info">
                                                                        Moved
                                                                    </span>
                                                                </div>

                                                                <div v-if="athlete.is_new">
                                                                    <span class="badge badge-info">New</span>

                                                                    <span v-if="athlete.to_waitlist" class="badge badge-warning">Marked for waitlist</span>
                                                                </div>

                                                                <div v-else-if="athlete.status == constants.athletes.statuses.Registered">
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
                                                        </td>

                                                        <td class="align-middle text-right">
                                                            <span v-if="athlete.to_waitlist" title="Waitlist"
                                                                class="fas fa-fw fa-exclamation-triangle text-warning">
                                                            </span>
                                                            <div class="btn-group dropdown" v-if="athlete.is_new">
                                                                <button type="button" class="btn btm-sm btn-link" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                                    <span class="fas fa-fw fa-ellipsis-v"></span>
                                                                </button>

                                                                <div class="dropdown-menu dropdown-menu-right">
                                                                    <div >
                                                                        <button v-if="athlete.to_waitlist" class="dropdown-item text-primary" type="button" :id="'unmark_'+athlete.id" @click="removeFromWaitlist(athlete, l)">
                                                                            <span class="fas fa-fw fa-times"></span> Unmark for waitlist
                                                                        </button>
                                                                        <button v-else class="dropdown-item text-danger" type="button" @click="addToWaitlist(athlete, l)">
                                                                            <span class="fas fa-fw fa-plus"></span> Mark for waitlist
                                                                        </button>
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
                                            </div>
                                            <div>
                                                <span class="text-grat-500 mr-1">
                                                    <span class="fas fa-coins"></span> Level Subtotal :
                                                </span>
                                                <span class="text-dark font-weight-bold">
                                                    ${{ numberFormat(l.subtotal) }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>

                                </div>

                                <div class="mb-2">
                                    <div class="btn btn-sm btn-success btn-block text-left mb-1" @click="expanded.coaches.final = !expanded.coaches.final">
                                        <div class="d-flex flex-no-wrap flex-row">
                                            <div class="flex-grow-1">
                                                <span>
                                                    <span class="fas fa-fw fa-chalkboard-teacher"></span>
                                                    Coaches
                                                </span> |
                                                <span class="ml-2 mr-2">
                                                    {{ state.final.coach_count() }} Coaches
                                                </span>
                                                <span :class="'fas fa-fw fa-caret-' + (expanded.coaches.final ? 'down' : 'right')"></span>
                                            </div>
                                        </div>
                                    </div>

                                    <div v-if="expanded.coaches.final" class="ml-3 mb-1">
                                        <div class="table-responsive-lg">
                                            <table class="table table-sm table-hover">
                                                <thead class="bg-dark text-light">
                                                    <tr>
                                                        <th scope="col" class="align-middle">
                                                            First Name
                                                        </th>
                                                        <th scope="col" class="align-middle">
                                                            Last Name
                                                        </th>
                                                        <th scope="col" class="align-middle">
                                                            Gender
                                                        </th>
                                                        <th scope="col" class="align-middle">
                                                            Date Of Birth
                                                        </th>
                                                        <th scope="col" class="align-middle">
                                                            USAG No.
                                                        </th>
                                                        <th v-if="meet.tshirt_chart != null" scope="col" class="align-middle">
                                                            T-shirt
                                                        </th>
                                                        <th scope="col" class="align-middle">
                                                            Status
                                                        </th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr v-if="state.final.coach_count() < 1">
                                                        <td colspan="9">
                                                            No coaches.
                                                        </td>
                                                    </tr>

                                                    <tr v-for="coach in state.final.coaches" :key="'final-coach-' + coach.id">
                                                        <td class="align-middle">
                                                            {{ coach.first_name }}
                                                        </td>
                                                        <td class="align-middle">
                                                            {{ coach.last_name }}
                                                        </td>

                                                        <td class="align-middle">
                                                            <select v-model="coach.gender" class="form-control form-control-sm">
                                                                <option :value="null">
                                                                    (Choose ...)
                                                                </option>
                                                                <option v-for="g in ['male', 'female']" :key="'final-gender-' + g" :value="g">
                                                                    {{ ucfirst(g) }}
                                                                </option>
                                                            </select>
                                                        </td>

                                                        <td class="align-middle">
                                                            {{ coach.dob_display }}
                                                        </td>

                                                        <td class="align-middle">
                                                            {{ coach.usag_no }}
                                                        </td>

                                                        <td v-if="meet.tshirt_chart != null" scope="col" class="align-middle">
                                                            <select v-model="coach.tshirt_size_id" class="form-control form-control-sm">
                                                                <option :value="null">
                                                                    (Choose ...)
                                                                </option>
                                                                <option v-for="size in meet.tshirt_chart.sizes"
                                                                    :key="size.id" :value="size.id">
                                                                    {{ size.size }}
                                                                </option>
                                                            </select>
                                                        </td>

                                                        <td class="align-middle">
                                                            <div class="d-inline-block">
                                                                <div v-if="coach.is_new">
                                                                    <span class="badge badge-info">New</span>
                                                                </div>

                                                                <div v-else-if="coach.status == constants.coaches.statuses.Registered">
                                                                    <span class="badge badge-success">Registered</span>
                                                                </div>

                                                                <div v-else-if="coach.status == constants.coaches.statuses.NonReserved">
                                                                    <span class="badge badge-warning">
                                                                        <span v-if="coach.in_waitlist">Waitlist</span>
                                                                        <span v-else>Pending</span>
                                                                        (Non-Reserved)
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
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex flex-row flew-nowrap mt-3 mb-2 p-3 rounded bg-primary">
                        <div class="flex-grow-1 text-uppercase">
                            <span class="text-secondary mr-1">
                                <span class="fas fa-coins"></span> Total :
                            </span>
                            <span class="text-white font-weight-bold">${{ numberFormat(total) }}</span>
                        </div>

                        <div class="">
                            <button class="btn btn-sm btn-success" @click="nextStep" :disabled="!canProceed()">
                                Next <span class="fas fa-long-arrow-alt-right"></span>
                            </button>
                        </div>
                    </div>
                </div>

                <div v-if="step == 2">
                    <div v-if="paymentOptionsLoading" class="text-center p-3">
                        <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                        Loading payment options, please wait ...
                    </div>

                    <div v-else-if="paymentProcessedMessage != null">
                        <div class="alert alert-success">
                            <strong>
                                <span class="fas fa-check-circle"></span> Thank you !
                            </strong><br/>
                            {{ this.paymentProcessedMessage }}
                            <div class="text-right">
                                <a :href="registrationUrl"
                                    class="btn btn-small btn-info">
                                    <span class="fas fa-eye"></span> View Registration Details
                                </a>
                            </div>
                        </div>
                    </div>

                    <div v-else-if="isProcessingPayment" class="alert alert-warning">
                        <strong>
                            <span class="fas fa-exclamation-circle"></span> Do not close or refresh this window.
                        </strong><br/>
                        Your payment is being processed.
                        <div class="text-center p-3">
                            <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                            Processing, please wait ...
                        </div>
                    </div>

                    <div v-else>
                        <div v-if="(paymentOptions != null)">
                            <div>
                                <h5 class="border-bottom">
                                    <span class="fas fa-fw fa-file-invoice-dollar"></span> Choose a payment method
                                </h5>

                                <div v-if="paymentOptions.methods.card"
                                    class="py-1 px-2 mb-2 border bg-white rounded">

                                    <h6 class="clickable m-0 py-2" :class="{'border-bottom': (optionsExpanded == 'card')}"
                                        @click="optionsExpanded = 'card'">
                                        <span class="fas fa-fw fa-credit-card"></span> Credit or Debit Card
                                        <span :class="'fas fa-fw fa-caret-' + (optionsExpanded == 'card' ? 'down' : 'right')"></span>
                                    </h6>

                                    <div v-if="optionsExpanded == 'card'">
                                        <div v-if="paymentOptions.methods.card.cards.length < 1" class="py-1 small">
                                            <span class="fas fa-exclamation-circle"></span> You have no cards stored in
                                            your account.
                                        </div>

                                        <div v-else v-for="card in paymentOptions.methods.card.cards" :key="card.id"
                                            class="py-1 border-bottom border-light hoverable clickable"
                                            @click="useCard(card)">
                                            <div class="row">
                                                <div class="col-auto">
                                                    <img class="credit-card-brand-image" :src="card.image"
                                                        :alt="card.brand" :title="card.brand">
                                                </div>
                                                <div class="col">
                                                    XXXX—{{ card.last4 }}
                                                </div>
                                                <div class="col">
                                                    {{ card.expires.month }}/{{ card.expires.year }}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div v-if="paymentOptions.methods.ach"
                                    class="py-1 px-2 mb-2 border bg-white rounded">

                                    <h6 class="clickable m-0 py-2" :class="{'border-bottom': (optionsExpanded == 'ach')}"
                                        @click="optionsExpanded = 'ach'">
                                        <span class="fas fa-fw fa-money-check-alt"></span> ACH
                                        <span :class="'fas fa-fw fa-caret-' + (optionsExpanded == 'ach' ? 'down' : 'right')"></span>
                                    </h6>

                                    <div v-if="optionsExpanded == 'ach'">
                                        <div v-if="paymentOptions.methods.ach.accounts.length < 1" class="py-1 small">
                                            <span class="fas fa-exclamation-circle"></span> You have no bank accounts
                                            stored in your account.
                                        </div>

                                        <div v-else v-for="account in paymentOptions.methods.ach.accounts" :key="account.id"
                                            class="py-1 border-bottom border-light hoverable clickable"
                                            @click="useACH(account)">
                                            <div class="row">
                                                <div class="col-auto">
                                                    <span class="fas fa-fw fa-university"></span>
                                                </div>
                                                <div class="col">
                                                    {{ account.name }}
                                                </div>
                                                <div class="col">
                                                    {{ ucfirst(account.type) }}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!-- one time ach payment -->
                                <div v-if="paymentOptions.methods.onetimeach" class="py-1 px-2 mb-2 border bg-white rounded" @click="useOneTimeACH()">

                                    <h6 class="clickable m-0 py-2" :class="{'border-bottom': (optionsExpanded == 'onetimeach')}"
                                        @click="optionsExpanded = 'onetimeach'">
                                        <span class="fas fa-fw fa-money-check-alt"></span> One Time ACH
                                        <span :class="'fas fa-fw fa-caret-' + (optionsExpanded == 'onetimeach' ? 'down' : 'right')"></span>
                                    </h6>

                                    <div v-if="optionsExpanded == 'onetimeach'">
                                        <div>
                                            <div>
                                                <label for="routingNumber">Routing Number:</label>
                                                <input type="text" class="form-control" id="routingNumber" v-model="routingNumber" required>
                                            </div>

                                            <div>
                                                <label for="accountNumber">Account Number:</label>
                                                <input type="text"  class="form-control" id="accountNumber" v-model="accountNumber" required>
                                            </div>

                                            <div>
                                                <label for="accountType">Account Type:</label>
                                                <select id="accountType"  class="form-control" v-model="accountType" required>
                                                    <option value="c" selected="selected">Checking</option>
                                                    <option value="s">Savings</option>
                                                </select>
                                            </div>

                                            <div>
                                                <label for="accountName">Account Name:</label>
                                                <input type="text"  class="form-control" id="accountName" v-model="accountName" required>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div v-if="paymentOptions.methods.paypal"
                                    class="py-1 px-2 mb-2 border bg-white rounded">

                                    <h6 class="clickable hoverable m-0 py-2" @click="usePaypal()">
                                        <span class="fab fa-fw fa-paypal"></span> Paypal
                                        <span class="small muted">(coming soon)</span>
                                    </h6>
                                </div>

                                <!-- <div v-if="paymentOptions.methods.check"
                                    class="py-1 px-2 mb-2 border bg-white rounded">
                                    <div @click="useCheck()">
                                        <h6 class="clickable m-0 py-2"  :class="{'border-bottom': (optionsExpanded == 'check')}"
                                            @click="optionsExpanded = 'check'">
                                            <span class="fas fa-fw fa-money-check-alt"></span> Mailed Check
                                        </h6>

                                        <div v-if="optionsExpanded == 'check'">
                                            <div class="form-group small mt-1 ml-3">
                                                <label class="control-label" for="check_no">
                                                    <span class="fas fa-fw fa-money-check-alt"></span>
                                                    Check # <span class="text-danger">*</span>
                                                </label>
                                                <input type="text" class="form-control form-control-sm"
                                                    v-model="checkNo" id="check_no">
                                            </div>

                                            <div class="small ml-3">
                                                <strong>
                                                    <span class="fas fa-info-circle"></span> Instructions Provided By Host :
                                                </strong>
                                                <p class="preserve-new-lines m-0">{{ meet.mailed_check_instructions }}</p>
                                            </div>
                                        </div>
                                    </div>
                                </div> -->

                                <div v-if="paymentOptions.methods.balance && (paymentOptions.methods.balance.current > 0)"
                                    class="py-1 px-2 mb-2 border bg-white rounded">
                                    <div v-if="chosenMethod && (chosenMethod.type == 'check')" class="text-danger">
                                        <span class="fas fa-exclamation-circle"></span>
                                        Allgymnastics.com balance cannot be used with mailed checks.
                                    </div>
                                    <div v-else class="form-check">
                                        <input class="form-check-input" type="checkbox" id="use_balance"
                                            v-model="useBalance" @change="recalculateTotals()">
                                        <label class="form-check-label" for="use_balance">
                                            <span class="fas fa-fw fa-coins"></span>
                                            Use my Allgymnastics.com balance towards this payment
                                        </label>
                                    </div>
                                </div>

                                <!-- <div class="py-1 px-2 mb-2 border bg-white rounded">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="enable_travel_arrangements"
                                            v-model="enable_travel_arrangements">
                                        <label class="form-check-label" for="enable_travel_arrangements">
                                            <span class="fas fa-fw fa-plane"></span>
                                            Interested in travel arrangements?
                                        </label>
                                    </div>
                                </div> -->

                            </div>

                            <div v-if="summary != null" class="mb-3">
                                <h5 class="border-bottom">
                                    <span class="fas fa-fw fa-clipboard-list"></span> Summary
                                </h5>

                                <div class="row">
                                    <div class="col">
                                        <span class="fas fa-fw fa-file-invoice-dollar"></span> Chosen Method :
                                    </div>
                                    <div class="col">
                                        <div v-if="chosenMethod.type == 'card'">
                                            Card ending with {{ chosenMethod.last4 }}
                                        </div>

                                        <div v-else-if="chosenMethod.type == 'ach'">
                                            {{ ucfirst(chosenMethod.accountType) }} bank account "{{ chosenMethod.name }}"
                                        </div>
                                        <div v-else-if="chosenMethod.type == 'onetimeach'">
                                            One Time ACH Payment
                                        </div>

                                        <div v-else-if="chosenMethod.type == 'paypal'">
                                            PayPal
                                        </div>

                                        <div v-else>
                                            Mailed Check #{{ checkNo }}
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col">
                                        <span class="fas fa-fw fa-tasks"></span> Registration Subtotal :
                                    </div>
                                    <div class="col">
                                        ${{ numberFormat(summary.subtotal) }}
                                    </div>
                                </div>

                                <div v-if="summary.own_meet_refund > 0" class="row">
                                    <div class="col">
                                        <span class="fas fa-fw fa-user-check"></span> Own Meet Refund :
                                    </div>
                                    <div class="col">
                                        <span class="text-success">-${{ numberFormat(summary.own_meet_refund) }}</span>
                                    </div>
                                </div>
                                
                                <div v-if="(summary.processor + summary.handling) > 0" class="row" v-on:click="toggleDiv()" style="cursor: pointer;">
                                    <div class="col">
                                        <span class="fas fa-fw fa-file-invoice"></span> Fees 
                                        <span class="fas fa-fw fa-caret-down" id="caret-div" > </span> :
                                    </div>
                                    <div class="col">
                                        ${{ numberFormat(summary.processor + summary.handling) }}
                                        
                                        <span v-if="this.summary.saving != ''" class="alert alert-success" style="padding:0px 5px;">
                                            {{ this.summary.saving }}
                                        </span>

                                    </div>
                                </div>
                                <div v-if="display_div">
                                    <div v-if="summary.handling > 0" class="row">
                                        <div class="col">
                                            <span class="fas fa-fw fa-server"></span> Handling Fee :
                                        </div>
                                        <div class="col">
                                            ${{ numberFormat(summary.handling) }}
                                        </div>
                                    </div>
                                    <div v-if="summary.processor > 0" class="row">
                                        <div class="col">
                                            <span class="fas fa-fw fa-file-invoice"></span> Payment Processor Fee :
                                        </div>
                                        <div class="col">
                                            ${{ numberFormat(summary.processor) }}
                                        </div>
                                    </div>
                                </div>



                                <div v-if="summary.used_balance != 0" class="row">
                                    <div class="col">
                                        <span class="fas fa-fw fa-coins"></span> Balance :
                                    </div>
                                    <div :class="'col text-' + (summary.used_balance > 0 ? 'success' : 'danger')">
                                        ${{ numberFormat(-summary.used_balance) }}
                                    </div>
                                </div>

                                
                                <div v-if="couponValue > 0" class="row">
                                    <div class="col">
                                        <span class="fas fa-fw fa-file-invoice"></span> Coupon :
                                    </div>
                                    <div class="col">
                                        - ${{ numberFormat(couponValue) }}
                                    </div>
                                </div>

                                <div class="d-flex flex-row flew-nowrap mt-3 mb-2 p-3 rounded bg-primary">
                                    <div class="flex-grow-1 text-uppercase">
                                        <span class="text-secondary mr-1">
                                            <span class="fas fa-coins"></span> Total :
                                        </span>
                                        <span class="text-white font-weight-bold">${{ numberFormat(summary.total) }}</span>
                                    </div>

                                    <div>
                                        <button v-if="!this.couponSuccess" id="couponBtn" class="btn btn-sm btn-info mr-1" @click="haveCoupon">
                                            <span class="fas fa-ticket-alt"></span> Have a Coupon?
                                        </button>
                                        <button class="btn btn-sm btn-secondary" @click="previousStep">
                                            <span class="fas fa-long-arrow-alt-left"></span> Back
                                        </button>

                                        <div class="d-inline-block">
                                            <button class="btn btn-sm btn-success"
                                                @click="mergeReservation">
                                                <span class="fas fa-file-invoice-dollar"></span> Proceed To Payment
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <div v-if="summary.total < 0" class="d-flex flex-row flew-nowrap mt-3 mb-2 p-3 rounded bg-warning">
                                    The Refund Amount Will Be Processed After Meet End By The Host.
                                </div>
                            </div>
                        </div>
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
    export default {
        name: 'ReservationDetails',
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

            late: {
                type: Boolean,
                default: false,
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

            hasRegistration() {
                return (this.state !== null && (this.state.registration_id !== null));
            }
        },
        data() {
            return {
                isLoading: false,
                errorMessage: null,
                state: null,
                expanded: {
                    initial: false,
                    final: true,
                    coaches: {
                        initial: true,
                        final: true,
                    },
                },
                meet: null,
                registration: null,
                registrationDetailsLink: null,
                genderAwareMeetLevelMatrix: {},
                registrationLevelToMeetLevelMatrix: {},
                permissions: {
                    change_details: false,
                    change_level:false,
                    change_number:false,
                    change_specialist_events:false,
                    scratch:false,
                },

                optionsExpanded: null,
                paymentOptions: null,
                paymentOptionsLoading: false,
                paymentProcessedMessage: null,
                isProcessingPayment: false,
                chosenMethod: null,
                summary: null,
                useBalance: false,
                checkNo: null,
                registrationUrl: null,

                total: 0,
                grandTotal: 0,
                step: 1,
                scratch_athlete: false,
                old_data_athletes: [],
                coupon: "",
                couponSuccess: false,
                couponValue: 0,   
                display_div: false,             
                competitions: null,
                enable_travel_arrangements: 0,
                onetimeach: null,
                routingNumber: '',
                accountNumber: '',
                accountType: 's', // Default to savings
                accountName: '',
                previous_registration_credit_amount: 0,
                changes_fees: 0,
            }
        },
        methods: {
            changeExpand: function (lid) {
                $("#expand_" + lid).toggleClass("fa-caret-down fa-caret-right");
                $("#level_" + lid).toggleClass("d-none");
            },
            getCompetitions: function(){
                axios.get('/api/competitions-info/').then(result => {
                    this.competitions = result.data;
                });
            },
            toggleDiv: function() {
                this.display_div = !this.display_div;
                if(!this.display_div)
                    $("#caret-div").removeClass("fa-caret-up").addClass("fa-caret-down");
                else
                    $("#caret-div").removeClass("fa-caret-down").addClass("fa-caret-up");
            },
            removeAllFromWaitList()
            {
                document.querySelectorAll("button[id^='unmark_']").forEach(function(button) {
                    button.click();
                });
            },
            levelUniqueId(level) {
                return level.id + (level.male ? '-m' : '') + (level.female ? '-f' : '');
            },

            numberFormat(n) {
                try {
                    let fee = Utils.toFloat(n);
                    return (fee === null ? n : fee.toFixed(2));
                } catch (e) {
                    return n;
                }
            },

            canProceed () {
                switch (this.step) {
                    case 1:
                        if (this.meet.slots_needed_in_waitlist > 0)
                            return false;

                        for (const lid in this.state.final.levels) {
                            if (this.state.final.levels.hasOwnProperty(lid)) {
                                const level = this.state.final.levels[lid];
                                if (level.slots_needed_in_waitlist > 0)
                                    return false;
                            }
                        }

                        return true;
                        break;
                }

                return false;
            },

            nextStep() {
                if (!this.canProceed())
                    return;

                switch (this.step) {
                    case 1:
                        if (this.validateData())
                            this.step++;
                        break;
                }
            },

            previousStep() {
                switch (this.step) {
                    case 2:
                        this.step--;
                        break;
                }
            },

            calculateMeetNeededWaitlistSlots() {
                let result = 0;
                let eligible = 0;

                for (const lid in this.state.final.levels) {
                    if (this.state.final.levels.hasOwnProperty(lid)) {
                        const level = this.state.final.levels[lid];
                        this.calculateLevelNeededWaitlistSlots(level);

                        eligible += level.waitlist_eligible_count;
                    }
                }

                if ((this.meet.athlete_limit !== null) && (this.meet.slots < 0)) {
                    let slot_deficit = -this.meet.slots;
                    result = (slot_deficit > eligible ? eligible : slot_deficit);
                }

                this.meet.waitlist_eligible_count = eligible;
                this.meet.slots_needed_in_waitlist = result;
            },

            calculateLevelNeededWaitlistSlots(level) {
                let result = 0;
                let eligible = 0;

                for (const i in level.athletes) {
                    if (level.athletes.hasOwnProperty(i)) {
                        const athlete = level.athletes[i];
                        let flag = athlete.is_new;
                        flag = flag && !(athlete.in_waitlist || athlete.to_waitlist);
                        flag = flag && (
                            (athlete.status == this.constants.athletes.statuses.Registered) ||
                            (athlete.status == this.constants.athletes.statuses.Reserved)
                        );

                        if (flag)
                            eligible++;
                    }
                }

                if (level.enable_athlete_limit && (level.slots < 0)) {
                    let slot_deficit = -level.slots;

                    result = (slot_deficit > eligible ? eligible : slot_deficit);
                }

                level.waitlist_eligible_count = eligible;
                level.slots_needed_in_waitlist = result;
            },

            toggleTeam(level, toggle) {
                if (!this.permissions.scratch && toggle == false)
                    return;
                if(toggle == false && level.has_team == false)
                    return;

                level.has_team = toggle;
                level.changes.team = (level.has_team != level.original_data.has_team);
                // this.calculateSubtotal();

                for(var i in this.registrationLevelToMeetLevelMatrix) {
                    if(this.registrationLevelToMeetLevelMatrix[i] == level.uid) {
                    this.state.final.levels[i] = level;
                    
                    break;
                    }
                }

                if(level.changes.team)
                    this.changes_fees += level.team_fee;
                else
                    this.changes_fees -= level.team_fee;
                this.calculateSubtotal();
                this.$forceUpdate(); 
            },

            revertLevelTeam(level) {
                if(level.has_team == true && level.changes.team == false) return;
                
                level.has_team = level.original_data.has_team;
                level.changes.team = false;
                

                this.changes_fees -= level.team_fee;

                for(var i in this.registrationLevelToMeetLevelMatrix) {
                    if(this.registrationLevelToMeetLevelMatrix[i] == level.uid) {
                    this.state.final.levels[i] = level;
                    this.$forceUpdate(); 
                    break;
                    }
                }
                this.calculateSubtotal();
            },

            async loadMeetDetails(state) {
                let result = await axios.get('/api/app/meet/' + state.meet.id);

                if (result.data.meets.length != 1)
                    throw 'Something went wrong while loading this meet\'s details.';

                let vm = this;
                let meet = result.data.meets[0];

                Vue.set(this, 'permissions', meet.editing_abilities);

                meet.late_registration_fee = Utils.toFloat(meet.late_registration_fee);
                meet.waitlist_slots = 0;

                let bodies = {};
                for (let i in meet.levels) {
                    let level = meet.levels[i];

                    if ((level.sanctioning_body_id != this.constants.bodies.USAG) && (state.category.id != level.level_category.id))
                        continue;

                    let meetCategory = meet.categories.find(c => {
                        return (c.pivot.sanctioning_body_id == this.constants.bodies.USAG) &&
                                (c.id == level.level_category.id) &&
                                c.pivot.officially_sanctioned &&
                                !c.pivot.frozen;
                    });
                    if (meetCategory === undefined)
                        throw 'Something went wrong (can\'t find category)';

                    let f_date =  Moment(meet.registration_first_discount_end_date, 'YYYY-MM-DD');
                    let s_date =  Moment(meet.registration_second_discount_end_date, 'YYYY-MM-DD');
                    let t_date =  Moment(meet.registration_third_discount_end_date, 'YYYY-MM-DD');
                    let c_date = Moment(new Date(),"YYYY-MM-DD");

                    let f_d = Moment(f_date.format("YYYY-MM-DD")).format('x');
                    let s_d = Moment(s_date.format("YYYY-MM-DD")).format('x');
                    let t_d = Moment(t_date.format("YYYY-MM-DD")).format('x');
                    let c_d = Moment(c_date.format("YYYY-MM-DD")).format('x');    
                    
                    let registration_updated_fee = null;
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
                        has_team: false,
                        was_late: this.late,
                        team_fee: 0,
                        team_late_fee: 0,
                        team_refund: 0,
                        team_late_refund: 0,
                        subtotal: 0,
                        expanded: false,
                        waitlist_slots: 0,
                    };

                    if (level.enable_athlete_limit) {
                        let gender = ((level.male && level.female) ? 'both' : (level.male ? 'male' : 'female'));
                        level.slots = level.athlete_limit - meet.used_slots[level.id][gender]['count'];
                    } else {
                        level.slots = null;
                    }

                    level.uid = this.levelUniqueId(level);

                    level.original_data = _.cloneDeep(level);

                    this.genderAwareMeetLevelMatrix[level.uid] = level;
                }

                if (meet.athlete_limit !== null)
                    meet.slots = meet.athlete_limit - meet.used_slots.total;

                Vue.set(this, 'meet', meet);
            },

            async loadRegistrationDetails(state) {
                if (state.registration_id === null) {
                    Vue.set(this, 'registration', {
                        id: null,
                        was_late: this.late,
                        late_fee: 0,
                        late_refund: 0,
                        subtotal: 0,
                    });
                    return;
                }

                let result = await axios.get('/api/registration/' + state.registration_id, {
                        'params': {
                            '__managed': this.managed
                        }
                });

                let vm = this;
                let registration = result.data;
                this.old_data_athletes = result.data;
                this.previous_registration_credit_amount = registration.previous_registration_credit_amount;
                registration.late_fee = Utils.toFloat(registration.late_fee);
                registration.late_refund = Utils.toFloat(registration.late_refund);

                for(let i in state.final.levels)
                {
                    let level = state.final.levels[i];
                    for(let j in level.athletes)
                    {
                        let athlete = level.athletes[j];
                        if(athlete.status == vm.constants.athletes.statuses.Scratched && !athlete.processed_already )
                        {
                            this.changes_fees += parseFloat(athlete.fee) + parseFloat(athlete.late_fee);
                        }
                        
                    }
                }
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
                    
                    if(this.constants.bodies[athlete.registration_level.level.sanctioning_body_id] != null){
                        athlete.sanction_no = this.constants.bodies[
                                                athlete.registration_level.level.sanctioning_body_id
                                            ].toLowerCase() + '_no';
                    }

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
                        scratch: function() {
                            return athlete.is_new || (
                                vm.permissions.scratch &&
                                (athlete.status == vm.constants.athletes.statuses.Registered)
                            );
                        },
                    };

                    athlete.original_data = _.cloneDeep(athlete);

                    let athleteLevel = this.genderAwareMeetLevelMatrix[
                        this.registrationLevelToMeetLevelMatrix[athlete.registration_level.id]
                    ];
                    if(athleteLevel == null)
                        continue;
                    athleteLevel.team_fee = Utils.toFloat(athlete.registration_level.team_fee);
                    athleteLevel.team_late_fee = Utils.toFloat(athlete.registration_level.team_late_fee);
                    athleteLevel.team_refund = Utils.toFloat(athlete.registration_level.team_refund);
                    athleteLevel.team_late_refund = Utils.toFloat(athlete.registration_level.team_late_refund);
                    athleteLevel.has_team = athlete.registration_level.has_team;
                    athleteLevel.original_data.has_team = athleteLevel.has_team;

                    athlete.locked = athleteLevel.locked;

                    athlete.new_fee = 0;
                    athlete.new_refund = 0;
                    athlete.new_late_fee = 0;
                    athlete.new_late_refund = 0;

                    athlete.original_level = athleteLevel.uid;
                    athlete.total = Utils.toFloat(athlete.new_fee) + Utils.toFloat(athlete.new_late_fee)
                                - Utils.toFloat(athlete.new_refund) - Utils.toFloat(athlete.new_late_refund);
                    athleteLevel.athletes.push(athlete);
                }

                for (let i in registration.coaches) {
                    let coach = _.cloneDeep(registration.coaches[i]);

                    coach.is_scratched = function() {
                        return this.status == vm.constants.coaches.statuses.Scratched
                    };
                    if (coach.is_scratched())
                        continue;

                    coach.locked = false;
                    coach.is_new = false;
                    coach.to_waitlist = false;
                    coach.gender_display = coach.gender.charAt(0).toUpperCase() + coach.gender.slice(1)
                    coach.dob = Moment(coach.dob);
                    coach.dob_display = coach.dob.format('MM/DD/YYYY');

                    if (this.meet.tshirt_chart != null) {
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

                    registration.coaches.push(coach);
                }

                Vue.set(this, 'registration', registration);
            },

            async loadPaymentOptions(state) {
                try {
                    this.paymentOptionsLoading = true;

                    let result = await axios.get('/api/registration/payment/options/' + state.meet.id + '/' + state.gym.id, {
                        'params': {
                            '__managed': this.managed
                        }
                    });

                    Vue.set(this, 'paymentOptions', result.data);
                } catch(error) {
                    console.error(error);
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
                } finally {
                    this.paymentOptionsLoading = false;
                };
            },

            addToWaitlist(a, l) {
                if (a.is_new && !a.to_waitlist) {
                    a.total = 0;
                    a.to_waitlist = true;
                    l.freed_slots++;
                    l.slots++;
                    this.meet.slots++;
                    this.calculateMeetNeededWaitlistSlots();
                    this.calculateSubtotal();
                }
            },


            removeFromWaitlist(a, l) {
                if (a.is_new && a.to_waitlist) {
                    a.total = a.fee + a.late_fee - (a.refund + a.late_refund);
                    a.to_waitlist = false;
                    l.freed_slots--;
                    l.slots--;
                    this.meet.slots--;
                    this.calculateMeetNeededWaitlistSlots();
                    this.calculateSubtotal();
                }
            },

            calculateAthleteFee(level, athlete, stop) {
                let total = 0;

                let flag = athlete.include_in_calculation;
                flag = flag && !(athlete.to_waitlist || athlete.in_waitlist);
                flag = flag && (athlete.status != this.constants.athletes.statuses.Scratched);

                if (flag) {
                    total = Utils.toFloat(athlete.fee) + Utils.toFloat(athlete.late_fee)
                            - Utils.toFloat(athlete.refund) - Utils.toFloat(athlete.late_refund);
                }

                let athleteTotal = total;
                //#region - if athlete moved and fee amount is different than count fee different.
                for (let a_i in this.old_data_athletes.athletes) {
                    let old_a = _.cloneDeep(this.old_data_athletes.athletes[a_i]);
                    if (old_a.id === athlete.id) {
                        if(old_a.registration_level.level.id == level.id)
                            athleteTotal = 0;
                        else
                            athleteTotal = level.registration_fee - athlete.fee;

                        if(athleteTotal < 0)
                            athleteTotal = 0;
                        else if(athleteTotal > 0)
                            athlete.include_in_calculation = true;
                        // if (athlete.was_late && (athleteTotal > athlete.late_fee)) {
                        //     let athleteFee = 0;
                        //     athleteFee = athleteTotal - athlete.late_fee;
                        //     if (old_a.fee == athleteFee) {
                        //         athleteTotal = athlete.late_fee;
                        //     }
                        // }
                    }
                }
                //#endregion
                athlete.total = athleteTotal;

                if (athlete.old_level !== null) {
                    let old_level_fee = this.state["final"].levels[athlete.old_level].registration_fee;
                    let current_level_fee = level.registration_fee;
                    if (old_level_fee > current_level_fee) {
                        if(this.changes_fees == 0)
                            this.changes_fees += parseFloat(old_level_fee) - parseFloat(current_level_fee);
                    }
                }


                if (stop !== true)
                    this.calculateLevelSubtotal(level);
            },

            calculateLevelSubtotal(level, stop) {
                let total = 0;

                for (const i in level.athletes) {
                    if (level.athletes.hasOwnProperty(i)) {
                        let a = level.athletes[i];

                        let flag = a.include_in_calculation;
                        flag = flag && !(a.to_waitlist || a.in_waitlist);
                        flag = flag && (a.status != this.constants.athletes.statuses.Scratched);

                        if (flag)
                            total += a.total;
                    }
                }

                if (level.allow_team) {
                    if (level.has_team) {
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

                for (let lid in this.state.final.levels) {
                    let l = this.state.final.levels[lid];
                        total += l.subtotal;
                }

                if ((total > 0) && (this.late)) {
                    let diff = (this.registration.late_fee - this.registration.late_refund);
                    if (diff == 0)
                        total += this.meet.late_registration_fee;
                }

                var p_credit = this.previous_registration_credit_amount + this.changes_fees;
                if(total >= p_credit)
                    total = total - p_credit;
                else
                    total = 0;

                this.total = total;
            },

            calculateSubtotal() {
                for (let lid in this.state.final.levels) {
                    let l = this.state.final.levels[lid];

                    for (const i in l.athletes) {
                        if (l.athletes.hasOwnProperty(i)) {
                            let athlete = l.athletes[i];
                            this.calculateAthleteFee(l, athlete, true);
                        }
                    }
                    this.calculateLevelSubtotal(l, true);
                }
                this.calculateMeetTotal();

                this.recalculateTotals();
            },


            recalculateTotals(deposit_ratio = 100, previous_deposit = 0) {
                if(deposit_ratio != 100)
                {
                    this.coupon = '';
                    previous_deposit = 0;
                }
                if(this.couponValue != 0 && deposit_ratio == 100)
                    previous_deposit = this.couponValue;

                if ((this.paymentOptions == null) || (this.chosenMethod == null))
                    return;

                this.summary = {
                    subtotal: (deposit_ratio == 100 ? this.total : (this.total * deposit_ratio)/100),
                    own_meet_refund: (this.paymentOptions.is_own ? this.total : 0),
                    handling: 0,
                    used_balance: 0,
                    processor: 0,
                    total: 0,
                    discount: this.paymentOptions.discount,
                    saving: ''
                };

                if (this.paymentOptions.defer.handling || this.paymentOptions.is_own) {
                    this.summary.handling = this.applyFeeMode(
                        this.summary.subtotal,
                        this.paymentOptions.handling.fee,
                        this.paymentOptions.handling.mode
                    );
                }

                let localTotal = this.summary.subtotal - this.summary.own_meet_refund + this.summary.handling;
                let currentBalance = Utils.toFloat(this.paymentOptions.methods.balance.current);

                if (this.paymentOptions.methods.balance && (this.chosenMethod.type != 'check')) {
                    if (currentBalance < 0) {
                        this.summary.used_balance = currentBalance;
                    } else if (this.useBalance) {
                        this.summary.used_balance = (
                            currentBalance >= localTotal ?
                            localTotal : currentBalance
                        );
                    }
                }

                localTotal -= (this.summary.used_balance + this.paymentOptions.discount);
                if (localTotal > 0) {
                    if (this.paymentOptions.defer.processor || this.paymentOptions.is_own) {
                        this.summary.processor = this.applyFeeMode(
                            localTotal,
                            this.chosenMethod.fee,
                            this.chosenMethod.mode
                        );
                    } else if (this.summary.used_balance < 0) {
                        this.summary.processor = this.applyFeeMode(
                            -this.summary.used_balance,
                            this.chosenMethod.fee,
                            this.chosenMethod.mode
                        );
                    }
                }

                this.summary.total = localTotal + this.summary.processor;
                if(this.summary.total - previous_deposit < 0)
                {
                    this.showAlert("Coupon cannot be used if value is greater then total", 'Whoops', 'red', 'fas fa-exclamation-triangle');
                }
                else
                {
                    this.summary.total -= previous_deposit;

                }
                let sum_h_p = this.summary.handling + this.summary.processor;
                var flg = 0;
                if (sum_h_p > 0) {
                    let totalsave = 0;
                    this.summary.saving += 'Saved ' ;
                    for(let key in this.competitions){
                        let values = this.competitions[key];
                        let _cc = values[0];
                        let _af = values[1];
                        let _sf = _cc + _af;
                        let _saved_total_fee = (this.summary.subtotal * _sf) / 100; 
                        if(_saved_total_fee > sum_h_p)
                        {
                            totalsave += _saved_total_fee - sum_h_p;
                            // this.summary.saving += '$'+(_saved_total_fee - sum_h_p).toFixed(2) + ' than ' + key +',';
                            flg = 1;
                        }
                    }
                    if(flg == 1 && totalsave > 0)
                    {
                        this.summary.saving += '$'+totalsave.toFixed(2) + ' compared to competitors';
                        this.summary.saving = this.summary.saving.slice(0, -1);
                    }
                    else
                    this.summary.saving = '';
                }

            },

            applyFeeMode(amount, fee, mode) {
                return (
                    mode == 'flat' ?            // flat || percent
                    fee :
                    amount * (fee / 100)
                );
            },

            useCard(card) {
                this.chosenMethod = {
                    ...card,
                    fee: this.paymentOptions.methods.card.fee,
                    mode: this.paymentOptions.methods.card.mode,
                    type: 'card'
                };
                this.recalculateTotals();
            },

            useACH(bank) {
                this.chosenMethod = {
                    ...bank,
                    accountType: bank.type,
                    fee: this.paymentOptions.methods.ach.fee,
                    mode: this.paymentOptions.methods.ach.mode,
                    type: 'ach'
                };
                this.recalculateTotals();
            },
            useOneTimeACH(){
                this.chosenMethod = {
                    accountType: '',
                    name: 'One Time Payment',
                    fee: this.paymentOptions.methods.ach.fee,
                    mode: this.paymentOptions.methods.ach.mode,
                    type: 'onetimeach'
                };
                this.recalculateTotals();
            },

            usePaypal() {
                this.chosenMethod = {
                    fee: this.paymentOptions.methods.paypal.fee,
                    mode: this.paymentOptions.methods.paypal.mode,
                    type: 'paypal'
                };
                this.optionsExpanded = 'paypal';
                this.recalculateTotals();
            },

            useCheck() {
                this.chosenMethod = {
                    fee: this.paymentOptions.methods.check.fee,
                    mode: this.paymentOptions.methods.check.mode,
                    type: 'check'
                };
                this.useBalance = false;
                this.recalculateTotals();
            },
            haveCoupon()
            {
                $('#modal-coupon').modal('show');
            },
            checkCoupon()
            {
                axios.post(
                    '/api/registration/register/coupon',
                    {
                        '__managed': this.managed,
                        meet_id:this.meet.id,
                        gym_id:this.state.gym.id,
                        coupon:this.coupon.trim().toUpperCase()
                    }
                ).then(result => {
                    this.couponValue = result.data.value;
                    $('#deposit').prop('checked', false);
                    $('#fullAmount').prop('checked', true);
                    $('#deposit').attr("disabled",true);
                    this.recalculateTotals(100,result.data.value);
                    this.showAlert("Coupon Successfully Applied", 'Success', 'green', 'fas fa-check');
                    this.couponSuccess = true;
                    $('#couponBtn').hide();
                }).catch(error => {
                    let msg = '';
                    if (error.response) {
                        msg = error.response.data.message;
                    } else if (error.request) {
                        msg = 'No server response.';
                    } else {
                        msg = error.message;
                    }
                    this.showAlert(msg, 'Whoops', 'red', 'fas fa-exclamation-triangle');
                }).finally(() => {
                    $('#modal-coupon').modal('hide');
                });
            },
            validateData() {
                let result = false;

                try {
                    let tshirtRequired = (this.meet.tshirt_chart != null);
                    let leoRequired = (this.meet.leo_chart != null);

                    for (const i in this.state.final.levels) {
                        if (this.state.final.levels.hasOwnProperty(i)) {
                            const level = this.state.final.levels[i];

                            for (const j in level.athletes) {
                                if (level.athletes.hasOwnProperty(j)) {
                                    const athlete = level.athletes[j];

                                    if (tshirtRequired) {
                                        if ((athlete.tshirt_size_id === null) || (athlete.tshirt_size_id == -1))
                                            throw 'Invalid Tshirt size for athlete <strong>' + athlete.first_name + ' ' +
                                                athlete.last_name + '</strong>.<br/>Tshirt sizes are required for this meet.';
                                    }

                                    if (leoRequired && (athlete.gender == 'female')) {
                                        if ((athlete.leo_size_id === null) || (athlete.leo_size_id == -1))
                                            throw 'Invalid Leo size for athlete <strong>' + athlete.first_name + ' ' +
                                                athlete.last_name + '</strong>.<br/>Leo sizes are required for this meet.';
                                    }
                                }
                            }
                        }
                    }

                    for (const i in this.state.final.coaches) {
                        if (this.state.final.coaches.hasOwnProperty(i)) {
                            const coach = this.state.final.coaches[i];

                            if (!['male', 'female'].includes(coach.gender)) {
                                throw 'Invalid gender for coach <strong>' + coach.first_name + ' ' +
                                        coach.last_name + '</strong>';
                            }

                            if (tshirtRequired) {
                                if ((coach.tshirt_size_id === null) || (coach.tshirt_size_id == -1))
                                    throw 'Invalid Tshirt size for coach <strong>' + coach.first_name + ' ' +
                                        coach.last_name + '</strong>.<br/>Tshirt sizes are required for this meet.';
                            }
                        }
                    }

                    result = true;
                } catch (error) {
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
                    this.showAlert(msg, 'Whoops', 'red', 'fas fa-exclamation-triangle');
                } finally {
                    return result;
                }
            },

            mergeReservation() {
                if (this.chosenMethod.type == 'check') {
                    if (!this.checkNo) {
                        let msg = 'Please provide a check number';
                        this.showAlert(msg, 'Whoops', 'red', 'fas fa-exclamation-triangle');
                        return;
                    }

                    this.chosenMethod.id = this.checkNo;
                }

                if(this.chosenMethod.type == 'onetimeach')
                {
                    this.onetimeach = {
                        routingNumber: this.routingNumber,
                        accountNumber: this.accountNumber,
                        accountType: this.accountType,
                        accountName: this.accountName
                    }
                }

                this.confirmAction(
                    'Are you sure you want to proceed with the payment ?',
                    'orange',
                    'fas fa-question-circle',
                    () => {
                        this.isProcessingPayment = true;
                        axios.post(
                            '/api/gyms/' + this.state.gym.id + '/reservations/usag/' + this.sanction_id + '/merge',
                            {
                                '__managed': this.managed,
                                summary: this.summary,
                                method: {
                                    type: this.chosenMethod.type,
                                    id: (this.chosenMethod.id ? this.chosenMethod.id : null)
                                },
                                data: this.state.final,
                                use_balance: this.useBalance,
                                coupon: this.coupon.trim().toUpperCase(),
                                enable_travel_arrangements: this.enable_travel_arrangements,
                                onetimeach: this.onetimeach,
                                changes_fees: this.changes_fees
                            }
                        ).then(result => {
                            this.registrationUrl = result.data.url;
                            this.paymentProcessedMessage = result.data.message;
                        }).catch(error => {
                            let msg = '';
                            if (error.response) {
                                msg = error.response.data.message;
                            } else if (error.request) {
                                msg = 'No server response.';
                            } else {
                                msg = error.message;
                            }
                            this.showAlert(msg, 'Whoops', 'red', 'fas fa-exclamation-triangle');
                        }).finally(() => {
                            this.isProcessingPayment = false;
                        });
                    },
                    this
                );

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

            ucfirst(s) {
                if (typeof s !== 'string') return ''
                return s.charAt(0).toUpperCase() + s.slice(1)
            }
        },
        beforeMount(){
            this.getCompetitions();
        },
        async mounted() {
            try {
                this.isLoading = true;

                let state = _.cloneDeep(this.sanction_data);
                this.state = state;
                let initial = state.initial;
                let final = state.final;

                await this.loadMeetDetails(state);
                await this.loadRegistrationDetails(state);

                state.initial = {
                    levels: {},
                    coaches: {},
                };

                state.final = {
                    levels: {},
                    coaches: {},
                };

                //#region INITIAL
                for (const lid in initial.levels) {
                    if (initial.levels.hasOwnProperty(lid)) {
                        let l = initial.levels[lid];
                        let level = null;

                        if (state.initial.levels.hasOwnProperty(lid)) { // check if level was processed before
                            level = state.initial.levels[lid];
                        } else if (this.registrationLevelToMeetLevelMatrix.hasOwnProperty(lid)) { // check if level exists in registration (lid in registrationLevelToMeetLevelMatrix)
                            level = {
                                ... _.cloneDeep(this.genderAwareMeetLevelMatrix[this.registrationLevelToMeetLevelMatrix[lid]]),
                                athletes: {},
                            };
                        } else if (this.genderAwareMeetLevelMatrix.hasOwnProperty(l.uid)) { // if not, check if level exists in meet (uid in genderAwareMeetLevelMatrix)
                            level = {
                                ... _.cloneDeep(this.genderAwareMeetLevelMatrix[l.uid]),
                                athletes: {},
                            }
                        } else {
                            throw 'Invalid level ' + l.code;
                        }

                        level.expanded = true;
                        for (const usag_no in l.athletes) {
                            if (l.athletes.hasOwnProperty(usag_no)) {
                                let a = l.athletes[usag_no];
                                let athlete = {
                                    ... a,
                                };
                                athlete.usag_no = usag_no;
                                athlete.dob = Moment(athlete.dob);
                                athlete.dob_display = athlete.dob.format('MM/DD/YYYY');

                                level.athletes[usag_no] = athlete;
                            }
                        }

                        level.athlete_count = function () {
                            return Object.keys(this.athletes).length;
                        };

                        state.initial.levels[lid] = level;
                    }
                }

                for (const usag_no in initial.coaches) {
                    if (initial.coaches.hasOwnProperty(usag_no)) {
                        let c = initial.coaches[usag_no];

                        let coach = {
                            ... c,
                        };

                        coach.dob = Moment(coach.dob);
                        coach.dob_display = coach.dob.format('MM/DD/YYYY');

                        state.initial.coaches[usag_no] = coach;
                    }
                }

                state.initial.coach_count = function () {
                    return Object.keys(this.coaches).length;
                };
                //#endregion

                //#region FINAL
                let freed_slots_tracker = {};
                let added_slots_tracker = {};
                for (const lid in final.levels) {
                    if (final.levels.hasOwnProperty(lid)) {
                        let l = final.levels[lid];
                        let level = null;

                        if (state.final.levels.hasOwnProperty(lid)) { // check if level was processed before
                            level = state.final.levels[lid];
                        } else if (this.registrationLevelToMeetLevelMatrix.hasOwnProperty(lid)) { // check if level exists in registration (lid in registrationLevelToMeetLevelMatrix)
                            level = {
                                ... _.cloneDeep(this.genderAwareMeetLevelMatrix[this.registrationLevelToMeetLevelMatrix[lid]]),
                                is_new: false,
                                athletes: {},
                            };
                        } else if (this.genderAwareMeetLevelMatrix.hasOwnProperty(l.uid)) { // if not, check if level exists in meet (uid in genderAwareMeetLevelMatrix)
                            level = {
                                ... _.cloneDeep(this.genderAwareMeetLevelMatrix[l.uid]),
                                is_new: true,
                                athletes: {},
                            }
                        } else {
                            throw 'Invalid level ' + l.code;
                        }

                        for (const usag_no in l.athletes) {
                            if (l.athletes.hasOwnProperty(usag_no)) {
                                let a = l.athletes[usag_no];
                                
                                let added = 0
                                if (final.ids.added.athletes.hasOwnProperty(usag_no))
                                    added = final.ids.added.athletes[usag_no];

                                let old_level = null;
                                if (final.ids.moved.hasOwnProperty(usag_no))
                                {
                                    old_level = final.ids.moved[usag_no];
                                }


                                let scratched = 0
                                if (final.ids.scratched.athletes.hasOwnProperty(usag_no))
                                    scratched = final.ids.scratched.athletes[usag_no];

                                let athlete = {
                                    ... a,
                                    is_new: false,
                                    include_in_calculation: false,
                                    fee: Utils.toFloat(a.fee),
                                    late_fee: Utils.toFloat(a.late_fee),
                                    refund: Utils.toFloat(a.refund),
                                    late_refund: Utils.toFloat(a.late_refund),
                                    to_waitlist: false,
                                    old_level: old_level,
                                };

                                athlete.dob = Moment(athlete.dob);
                                athlete.dob_display = athlete.dob.format('MM/DD/YYYY');

                                if (this.meet.tshirt_chart != null) {
                                    let sizing_id = _.map(this.meet.tshirt_chart.sizes, 'id');
                                    if (!sizing_id.includes(athlete.tshirt_size_id))
                                        athlete.tshirt_size_id = null;
                                }

                                if (this.meet.leo_chart != null) {
                                    let sizing_id = _.map(this.meet.leo_chart.sizes, 'id');
                                    if (!sizing_id.includes(athlete.leo_size_id))
                                        athlete.leo_size_id = null;
                                }

                                //#region - if old athlete t-shirt_size set then use old athlete t-shirt-size otherwise null.
                                for (let a_i in this.old_data_athletes.athletes) {
                                    let old_a = _.cloneDeep(this.old_data_athletes.athletes[a_i]);
                                    if (old_a.id === athlete.id) {
                                        athlete.tshirt_size_id = old_a.tshirt_size_id ?? null;
                                        athlete.leo_size_id = old_a.leo_size_id ?? null;
                                        break;
                                    }
                                }
                                //#endregion

                                let tmp = added - scratched;
                                if ((old_level !== null) && (old_level != lid) && (tmp > 0 )) { // If athlete was moved to a different level and is not a new addition
                                    athlete.include_in_calculation = true;
                                    athlete.was_late = athlete.was_late || this.late;

                                    let tmp_fee = level.registration_fee - athlete.fee;
                                    athlete.refund = tmp_fee < 0 ? tmp_fee * (-1) : 0; //athlete.fee;
                                    athlete.late_refund = athlete.late_fee;
                                    athlete.fee =  tmp_fee < 0 ? 0 : tmp_fee;
                                    if (athlete.was_late)
                                        athlete.late_fee = level.late_registration_fee;

                                    athlete.status = (athlete.to_waitlist || athlete.in_waitlist) ? this.constants.athletes.statuses.NonReserved : this.constants.athletes.statuses.Registered;

                                    if (!athlete.in_waitlist) {
                                        freed_slots_tracker[old_level] = freed_slots_tracker.hasOwnProperty(old_level) ? freed_slots_tracker[old_level] + 1 : 1;
                                        added_slots_tracker[lid] = added_slots_tracker.hasOwnProperty(lid) ? added_slots_tracker[lid] + 1 : 1;
                                    }
                                }

                                if (tmp < 0) { // athlete was scratched
                                    athlete.refund = athlete.fee;
                                    athlete.late_refund = athlete.late_fee;
                                    athlete.status = this.constants.athletes.statuses.Scratched;

                                    if (!athlete.in_waitlist) {
                                        freed_slots_tracker[lid] = freed_slots_tracker.hasOwnProperty(lid) ? freed_slots_tracker[lid] + 1 : 1;
                                    }
                                } else if (tmp > 0) { // athlete was added
                                    athlete.include_in_calculation = true;
                                    athlete.is_new = true;
                                    athlete.to_waitlist = this.meet.is_waitlist;
                                    athlete.was_late = this.late;

                                    athlete.refund = athlete.fee;
                                    athlete.late_refund = athlete.late_fee;

                                    athlete.fee += level.registration_fee;
                                    if (athlete.was_late)
                                        athlete.late_fee += level.late_registration_fee;

                                    athlete.status = athlete.in_waitlist ? this.constants.athletes.statuses.NonReserved : this.constants.athletes.statuses.Registered;

                                    if (!athlete.in_waitlist) {
                                        added_slots_tracker[lid] = added_slots_tracker.hasOwnProperty(lid) ? added_slots_tracker[lid] + 1 : 1;
                                    }
                                }  else { // no status change
                                }

                                level.athletes[usag_no] = athlete;
                            }
                        }

                        state.final.levels[lid] = level;
                    }
                }

                for (const lid in state.final.levels) {
                    if (state.final.levels.hasOwnProperty(lid)) {
                        let l = state.final.levels[lid];

                        //#region - if any athlete is scratch then this.scratch_athlete value is true and break loop.
                        for (const j in l.athletes) {
                            if (l.athletes.hasOwnProperty(j)) {
                                const new_a = l.athletes[j];

                                for (let a_i in this.old_data_athletes.athletes) {
                                    let old_a = _.cloneDeep(this.old_data_athletes.athletes[a_i]);

                                    if(new_a.status != old_a.status){
                                        this.scratch_athlete = true;
                                        break;
                                    }
                                }
                            }
                        }
                        //#endregion

                        l.freed_slots = freed_slots_tracker.hasOwnProperty(lid) ? freed_slots_tracker[lid] : 0;
                        l.added_slots = added_slots_tracker.hasOwnProperty(lid) ? added_slots_tracker[lid] : 0;

                        l.slots += l.freed_slots;
                        this.meet.slots += l.freed_slots;

                        l.slots -= l.added_slots;
                        this.meet.slots -= l.added_slots;

                        l.expanded = true;
                        l.athlete_count = function () {
                            return Object.keys(this.athletes).length;
                        };

                        l.active_athletes = function () {
                            let count = 0;

                            for (const i in this.athletes) {
                                if (this.athletes.hasOwnProperty(i)) {
                                    const athlete = this.athletes[i];

                                    let flag = !(athlete.in_waitlist || athlete.to_waitlist);
                                    flag = flag && (athlete.status != this.constants.statuses.athletes.Scratched);

                                    if (flag)
                                        count++;
                                }
                            }
                            return count;
                        };

                        l.free_slots = function(readable) {
                            if (!this.enable_athlete_limit)
                                return;

                            let r = this.slots;
                            return (readable && (r < 1) ? 'No' : r)
                        }

                        l.changes = {
                            team: false,
                            scratch_athlete: this.scratch_athlete,
                        }
                    }
                }

                for (const usag_no in final.coaches) {
                    if (final.coaches.hasOwnProperty(usag_no)) {
                        let c = final.coaches[usag_no];

                        let added = 0
                        if (final.ids.added.coaches.hasOwnProperty(usag_no))
                            added = final.ids.added.coaches[usag_no];

                        let scratched = 0
                        if (final.ids.scratched.coaches.hasOwnProperty(usag_no))
                            scratched = final.ids.scratched.coaches[usag_no];

                        let coach = {
                            ... c,
                            is_new: false,
                        };

                        coach.dob = Moment(coach.dob);
                        coach.dob_display = coach.dob.format('MM/DD/YYYY');

                        //#region - if old athlete t-shirt_size set then use old athlete t-shirt-size otherwise null.
                        for (let c_i in this.old_data_athletes.coaches) {
                            let old_c = _.cloneDeep(this.old_data_athletes.coaches[c_i]);
                            if (old_c.id === coach.id){
                                coach.tshirt_size_id = old_c.tshirt_size_id ?? null;
                                break;
                            }
                        }
                        //#endregion

                        let tmp = added - scratched;

                        if (tmp < 0) { // coach was scratched
                            coach.status = this.constants.coaches.statuses.Scratched;
                        } else if (tmp > 0) { // coach was added
                            coach.is_new = true;
                            coach.to_waitlist = this.meet.is_waitlist;
                            coach.was_late = this.late;

                            coach.status = coach.in_waitlist ? this.constants.coaches.statuses.NonReserved : this.constants.coaches.statuses.Registered;
                        }  else { // no status change
                        }

                        if (coach.is_new)
                            coach.gender = null;

                        state.final.coaches[usag_no] = coach;
                    }
                }

                state.final.coach_count = function () {
                    return Object.keys(this.coaches).length;
                };
                //#endregion

                Vue.set(this, 'state', state);
                this.calculateMeetNeededWaitlistSlots();
                this.calculateSubtotal();
            } catch (error) {
                console.error(error);
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
            } finally {
                this.isLoading = false;
            }

            await this.loadPaymentOptions(this.state);
        }
    }
</script>
