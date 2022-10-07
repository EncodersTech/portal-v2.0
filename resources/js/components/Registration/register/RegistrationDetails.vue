<template>
    <div>
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
                        <div>
                            <select class="form-control form-control-sm" v-model="add_athlete_athlete">
                                <option value="">(Choose ...)</option>
                                <option v-for="athlete in filtreredAthletesForAddModal" :key="'modal-' + athlete.id"
                                    :value="athlete">
                                    {{ athlete.first_name }} {{ athlete.last_name }}
                                </option>
                            </select>
                        </div>

                        <div class="text-right mt-3">
                            <button v-if="this.add_athlete_athlete"
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
                <div class="alert alert-warning small" v-if="meet.is_waitlist">
                    <strong>
                        <span class="fas fa-exclamation-triangle"></span> You will be put in a wait-list
                        for this meet.
                    </strong><br/>
                    This meet is either closed to new registrations, or has reached its athlete limit.
                </div>



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
                                {{ freeSlots(meet, true) }} free slots.
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
                    <div class="alert alert-light" v-if="usag_route">
                    <button @click="loadUsagReservation()" class="btn btn-primary"><span class="fas fa-fw fa-route"></span> Go to USAG to complete registration</button>
                    
                    </div>

                    <div v-for="body in bodies" :key="body.path" class="mb-2">
                        <div class="mb-1">
                            <button class="btn btn-sm btn-dark btn-block text-left left-btn"
                                type="button" @click="body.expanded = !body.expanded">
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
                                        type="button" @click="category.expanded = !category.expanded">
                                        <span class="fas fa-fw fa-cubes"></span>
                                        {{ category.name }}
                                        <span :class="'fas fa-fw fa-caret-' + (category.expanded ? 'down' : 'right')"></span>
                                    </button>
                                </div>

                                <div v-if="category.expanded" class="ml-3 mb-2">
                                    <div v-for="level in category.levels" :key="level.uid" class="mb-1">
                                        <div class="mb-2">
                                            <button class="btn btn-sm btn-secondary btn-block text-left left-btn"
                                                type="button" @click="level.expanded = !level.expanded">

                                                <span v-if="level.enable_athlete_limit && (level.remainingSlots() < 0)"
                                                    class="text-danger">
                                                    <span class="fas fa-fw fa-exclamation-triangle"></span>
                                                </span>
                                                <span v-else>
                                                    <span class="fas fa-fw fa-layer-group"></span>
                                                </span>

                                                <span class="mr-2">
                                                    {{ level.name }}
                                                </span> |
                                                <span class="ml-2 mr-2">
                                                    <span v-if="level.male && level.female">Both</span>
                                                    <span v-else-if="level.male">Male</span>
                                                    <span v-else-if="level.female">Female</span>
                                                </span> |
                                                <span class="ml-2 mr-2">
                                                    {{ level.selectedAthletes() }} Athletes
                                                </span> |
                                                <span class="ml-2 mr-2">
                                                    <span v-if="level.enable_athlete_limit">
                                                        {{ level.remainingSlots(true) }} free slots
                                                    </span>
                                                    <span v-else>
                                                        No limit
                                                    </span>
                                                </span>
                                                <span :class="'fas fa-fw fa-caret-' + (level.expanded ? 'down' : 'right')"></span>
                                            </button>
                                        </div>

                                        <div v-if="level.expanded" class="ml-3 mb-1">
                                            <div class="alert alert-warning small" v-if="hasWaitlistAthletes(level)">
                                                <strong>
                                                    <span class="fas fa-exclamation-triangle"></span> The athletes with an exclamation mark will
                                                    go into the waitlist for this meet.
                                                </strong><br/>
                                                The number of athletes you chose to register in this level is higher than the number of free
                                                slots available.<br/>
                                                You can select which athletes use the available spots select them first.
                                            </div>
                                            <div v-if="level.allow_team" class="mb-2">
                                                <div class="form-check">
                                                    <input :id="'level-team-' + level.uid" class="form-check-input"
                                                        type="checkbox" v-model="level.team"
                                                        @change="calculateLevelSubtotal(level)">
                                                    <label class="form-check-label" :for="'level-team-' + level.uid">
                                                        Register athletes as a <strong>Team</strong>
                                                    </label>
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

                                            <div class="table-responsive-lg small">
                                                <table class="table table-sm table-hover">
                                                    <thead class="bg-primary text-light">
                                                        <tr>
                                                            <th scope="col" class="align-middle">
                                                                <span v-if="level.has_specialist && level.allow_specialist">
                                                                    Event
                                                                </span>
                                                                <span v-else>
                                                                    Register
                                                                </span>
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
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <tr v-if="level.athletes.length < 1">
                                                            <td colspan="8">
                                                                No athletes.
                                                            </td>
                                                        </tr>

                                                        <tr v-for="athlete in level.athletes" :key="level.uid + '-' + athlete.id">
                                                            <td>
                                                                <div class="form-check">
                                                                    <input v-model="athlete.checked" @change="toggleAthleteAllAround(level, athlete)"
                                                                        :id="level.uid + '-athlete-' + athlete.id"
                                                                        class="form-check-input" type="checkbox">
                                                                    <label v-if="level.has_specialist && level.allow_specialist"
                                                                        class="form-check-label" :for="level.uid + '-athlete-' + athlete.id">
                                                                        All Around
                                                                    </label>
                                                                    <span v-if="athlete.to_waitlist" class="text-warning">
                                                                        <span class="fas fa-exclamation-triangle"></span>
                                                                    </span>
                                                                </div>

                                                                <div v-if="level.has_specialist && level.allow_specialist" class="ml-3">
                                                                    <div v-for="event in athlete.specialist" :key="event.id" class="form-check">
                                                                        <input v-model="event.checked" @change="toggleAthleteSpecialist(level, athlete)"
                                                                            :id="level.uid + '-athlete-' + athlete.id + '-event-' + event.id"
                                                                            class="form-check-input" type="checkbox">
                                                                        <label class="form-check-label"
                                                                            :for="level.uid + '-athlete-' + athlete.id + '-event-' + event.id">
                                                                            {{ event.name }}
                                                                        </label>
                                                                    </div>
                                                                </div>
                                                            </td>
                                                            <td class="align-middle clickable"
                                                                @click="toggleAthleteOnNameClick(level, athlete)">
                                                                <span class="text-danger font-weight-bold d-none">
                                                                    <span v-if="athlete.waitlist_by_level">L</span>
                                                                    <span v-if="athlete.waitlist_by_meet">M</span>
                                                                    <span v-if="athlete.waitlist_by_waitlist">W</span>
                                                                </span>
                                                                {{ athlete.first_name }}
                                                            </td>
                                                            <td class="align-middle clickable"
                                                                @click="toggleAthleteOnNameClick(level, athlete)">
                                                                {{ athlete.last_name}}
                                                            </td>

                                                            <td class="align-middle">
                                                                {{ athlete.dob_display }}
                                                            </td>

                                                            <td class="align-middle">
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
                                                            </td>

                                                            <td v-if="meet.tshirt_chart != null" scope="col" class="align-middle">
                                                                <div class="input-group input-group-sm">
                                                                    <select v-model="athlete.tshirt_size_id" class="form-control form-control-sm"
                                                                        :disabled="!athlete.editing_tshirt"
                                                                        @blur="athlete.editing_tshirt = false"
                                                                        :ref="level.uid + '-' + athlete.id + '-tshirt'">
                                                                        <option value="-1">
                                                                            (Choose ...)
                                                                        </option>
                                                                        <option v-for="size in meet.tshirt_chart.sizes"
                                                                            :key="size.id" :value="size.id">
                                                                            {{ size.size }}
                                                                        </option>
                                                                    </select>
                                                                    <div class="input-group-append">
                                                                        <button class="btn btn-secondary" type="button"
                                                                            @click="editAthleteTshirt(level, athlete)">
                                                                            <span class="fas fa-edit"></span>
                                                                        </button>
                                                                    </div>
                                                                </div>
                                                            </td>
                                                            <td v-if="meet.leo_chart != null" scope="col" class="align-middle">
                                                                <div v-if="athlete.gender == 'male'">—</div>
                                                                <div v-else>
                                                                    <div class="input-group input-group-sm">
                                                                        <select v-model="athlete.leo_size_id" class="form-control form-control-sm"
                                                                            :disabled="!athlete.editing_leo"
                                                                            @blur="athlete.editing_leo = false"
                                                                            :ref="level.uid + '-' + athlete.id + '-leo'">
                                                                            <option value="-1">(Choose ...)</option>
                                                                            <option v-for="size in meet.leo_chart.sizes"
                                                                                :key="size.id" :value="size.id">
                                                                                {{ size.size }}
                                                                            </option>
                                                                        </select>

                                                                        <div class="input-group-append">
                                                                            <button class="btn btn-secondary" type="button"
                                                                                @click="editAthleteLeo(level, athlete)">
                                                                                <span class="fas fa-edit"></span>
                                                                            </button>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </td>

                                                            <td class="align-middle">
                                                                ${{ numberFormat(athlete.fee) }}
                                                            </td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>

                                            <div class="d-flex flex-row flew-nowrap mt-1 mb-3 p-1 border-top">
                                                <div class="flex-grow-1">
                                                    <button class="btn btn-sm btn-success"
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
                    <div class="alert alert-info small ml-3">
                        <span class="fas fa-fw fa-info-circle"></span>
                        Please make sure a valid sanction number is provided for every sanctioning body the coach is coaching under for this meet.
                    </div>

                    <div class="table-responsive-lg small ml-3">
                        <table class="table table-sm table-hover">
                            <thead class="bg-success text-light">
                                <tr>
                                    <th scope="col" class="align-middle">
                                        Register
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
                                        <input v-model="coach.checked" type="checkbox">
                                    </td>
                                    <td class="align-middle clickable"
                                        @click="coach.checked = !coach.checked">
                                        {{ coach.first_name }}
                                    </td>
                                    <td class="align-middle clickable"
                                        @click="coach.checked = !coach.checked">
                                        {{ coach.last_name}}
                                    </td>

                                    <td class="align-middle">
                                        {{ coach.dob_display }}
                                    </td>

                                    <td v-if="meet.tshirt_chart != null" scope="col" class="align-middle">
                                        <div class="input-group input-group-sm">
                                            <select v-model="coach.tshirt_size_id" class="form-control form-control-sm"
                                                :disabled="!coach.editing_tshirt"
                                                @blur="coach.editing_tshirt = false"
                                                :ref="'coach-' + coach.id + '-tshirt'">
                                                <option value="-1">
                                                    (Choose ...)
                                                </option>
                                                <option v-for="size in meet.tshirt_chart.sizes"
                                                    :key="size.id" :value="size.id">
                                                    {{ size.size }}
                                                </option>
                                            </select>
                                            <div class="input-group-append">
                                                <button class="btn btn-secondary" type="button"
                                                    @click="editCoachTshirt(coach)">
                                                    <span class="fas fa-edit"></span>
                                                </button>
                                            </div>
                                        </div>
                                    </td>

                                    <td class="align-middle">
                                        <div v-if="coach.usag_no != null">
                                            {{ coach.usag_no }}
                                        </div>
                                        <div v-else>—</div>
                                    </td>

                                    <td class="align-middle">
                                        <div v-if="coach.usaigc_no != null">
                                            {{ coach.usaigc_no }}
                                        </div>
                                        <div v-else>—</div>
                                    </td>

                                    <td class="align-middle">
                                        <div v-if="coach.aau_no != null">
                                            {{ coach.aau_no }}
                                        </div>
                                        <div v-else>—</div>
                                    </td>

                                    <td class="align-middle">
                                        <div v-if="coach.nga_no != null">
                                            {{ coach.nga_no }}
                                        </div>
                                        <div v-else>—</div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="d-flex flex-row flew-nowrap mt-3 mb-2 p-3 rounded bg-primary">
                    <div class="flex-grow-1 text-uppercase">
                        <span class="text-secondary mr-1">
                            <span class="fas fa-coins"></span> Total :
                        </span>
                        <span class="text-white font-weight-bold">${{ numberFormat(total) }}</span>
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
    export default {
        name: 'RegistrationDetails',
        props: {
            managed: {
                default: null,
                type: Number
            },
            available_bodies: {
                type: Object,
                default: []
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
            gymId: {
                type: String,
                default: ''
            },
            meetId: {
                type: Number,
                default: ''
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

                    settings: {
                        defaultExpand: false
                    }
                };
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
                    result = this.available_athletes.filter(a => {
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
                isLoading: false,
                isError: false,
                errorMessage: '',
                warnMessage: null,
                bodies: [],
                meet: null,
                gym: null,
                gymBodyFilter: {},
                total: 0.00,
                specialist_events: [],
                available_levels: [],
                available_athletes: [],
                coaches: [],
                add_athlete_level: null,
                add_athlete_athlete: '',
                showAthletes: true,
                showCoaches: false,
                usag_route: false,
                usag_url: ''
            }
        },
        watch: {
            gymId() {
                if (this.gymId) {
                    this.isError = false;

                    if (this.meet.athlete_limit !== null) {
                        this.meet.slots = this.meet.athlete_limit - this.meet.used_slots.total;
                    }

                    this.loadGymDetails();
                    this.loadGymAthletes();
                    this.loadGymCoaches();
                }
            },
            loading() {
                this.isLoading = this.loading;
            },
            error() {
                this.isError = this.error;
            },
            errormessage() {
                this.errorMessage = this.errormessage;
            },
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

            freeSlots(el, readable) {
                let r = el.slots - el.non_waitlist_reserved_slots();
                return (readable && (r < 1) ? 'No' : r);
            },

            hasWaitlistAthletes(level) {
                let flag = false;
                level.athletes.some(a => {
                    flag = a.to_waitlist;
                    return flag;
                })
                return flag;
            },

            calculateAthleteFee(level, athlete, stop) {
                let total = 0;

                if (!athlete.to_waitlist) {
                    if (athlete.checked) {
                        total += level.registration_fee;
                        if (this.late)
                            total += level.late_registration_fee;
                    }

                    if (level.has_specialist && level.allow_specialist) {
                        athlete.specialist.forEach(event => {
                            if (event.checked) {
                                total += level.specialist_registration_fee;

                                if (this.late)
                                    total += level.specialist_late_registration_fee;
                            }
                        });
                    }
                }

                athlete.fee = total;
                if (stop !== true)
                    this.calculateLevelSubtotal(level);
            },

            calculateLevelSubtotal(level, stop) {
                let total = 0;
                let levelHasSpecialistOnly = true;

                level.athletes.forEach(a => {
                    total += a.fee;

                    if (a.checked)
                        levelHasSpecialistOnly = false;
                });

                if ((total > 0) && level.allow_team /*&& !levelHasSpecialistOnly*/) {
                    if (level.team) {
                        total += level.team_registration_fee;
                        if (this.late)
                            total += level.team_late_registration_fee;
                    }
                }

                level.subtotal = total;

                if (stop !== true)
                    this.calculateMeetTotal();
            },

            calculateMeetTotal() {
                let total = 0;

                this.available_levels.forEach(l => {
                    total += l.subtotal;
                });

                if (total > 0) {
                    if (this.late)
                        total += this.meet.late_registration_fee;
                }

                this.total = total;

                this.$emit('total-changed', total);
            },

            toggleAthleteOnNameClick(level, athlete) {
                athlete.checked = !athlete.checked;
                this.toggleAthleteAllAround(level, athlete);
            },

            toggleAthleteAllAround(level, athlete) {
                let wasSelected = !athlete.checked; // prev state
                if (level.has_specialist && level.allow_specialist) {
                    if (athlete.checked) {
                        athlete.specialist.forEach(event => event.checked = false);
                    }
                }

                this.athleteClickWaitlistLogic(level, athlete, wasSelected);
                this.calculateAthleteFee(level, athlete)
            },

            toggleAthleteSpecialist(level, athlete) {
                let previousState = athlete.checked;
                athlete.specialist.forEach(event => {
                    if (event.checked)
                        athlete.checked = false;
                });

                this.athleteClickWaitlistLogic(level, athlete, previousState);
                this.calculateAthleteFee(level, athlete)
            },

            athleteClickWaitlistLogic(level, athlete, previousState) {
                let checked = athlete.checked;
                if (this.meet.is_waitlist) {
                    athlete.specialist.some(event => {
                        if (event.checked)
                            checked = true;
                        return checked;
                    });
                }

                let levelAtLimit = level.enable_athlete_limit && (this.freeSlots(level, false) < 0);
                let meetAtLimit = (this.meet.athlete_limit !== null) && (this.freeSlots(this.meet, false) < 0);
                /*console.log({
                    levelAtLimit: levelAtLimit,
                    meetAtLimit: meetAtLimit
                });*/
                let slotRedeemed = false;
                if (checked) {
                    athlete.waitlist_by_level = levelAtLimit;
                    athlete.waitlist_by_meet = meetAtLimit;
                    athlete.waitlist_by_waitlist = this.meet.is_waitlist;
                    athlete.to_waitlist = this.meet.is_waitlist || levelAtLimit || meetAtLimit;
                } else if (previousState || this.meet.is_waitlist) {
                    if (athlete.to_waitlist) {
                        athlete.to_waitlist = false;
                        athlete.waitlist_by_level = false;
                        athlete.waitlist_by_meet = false;
                        athlete.waitlist_by_waitlist = false;
                    } else if (!this.meet.is_waitlist) {
                        level.athletes.some(a => {
                            if (a.to_waitlist) {
                                a.to_waitlist = false;
                                a.waitlist_by_level = levelAtLimit;
                                a.waitlist_by_meet = meetAtLimit;
                                a.waitlist_by_waitlist = this.meet.is_waitlist;
                                this.calculateAthleteFee(level, a);
                                slotRedeemed = true;
                            }
                            return slotRedeemed;
                        });
                        /*console.log({
                            slotRedeemed: slotRedeemed,
                            meetFreeSlots: this.freeSlots(this.meet, false)
                        })*/
                        if (!slotRedeemed && (this.meet.athlete_limit !== null) && (this.freeSlots(this.meet, false) > 0)) {
                            this.available_levels.some(l => {
                                if (l.uid == level.uid)
                                    return false;

                                levelAtLimit = l.enable_athlete_limit && (this.freeSlots(l, false) < 0);

                                l.athletes.some(a => {
                                    if (a.to_waitlist && a.waitlist_by_meet &&
                                        !a.waitlist_by_level && !a.waitlist_by_waitlist) {
                                        a.to_waitlist = false;
                                        a.waitlist_by_level = levelAtLimit;
                                        a.waitlist_by_meet = meetAtLimit;
                                        a.waitlist_by_waitlist = this.meet.is_waitlist;
                                        this.calculateAthleteFee(l, a);
                                        slotRedeemed = true;
                                    }
                                    return slotRedeemed;
                                });
                                return slotRedeemed;
                            });
                        }
                    }
                }

                meetAtLimit = (this.meet.athlete_limit !== null) && (this.freeSlots(this.meet, false) < 1);
                this.available_levels.forEach(l => {
                    levelAtLimit = l.enable_athlete_limit && (this.freeSlots(l, false) <= 0);
                    l.athletes.forEach(a => {
                        if (a.to_waitlist) {
                            a.waitlist_by_level = levelAtLimit;
                            a.waitlist_by_meet = meetAtLimit;
                            a.waitlist_by_waitlist = this.meet.is_waitlist;
                        }
                    });
                });
            },
            loadUsagReservation()
            {
                // var url = "https://usagym.org/app/addmeetregistration.html?id="+this.+"&clubId=023566";
                window.open(this.usag_url, '_blank').focus();
            },
            loadGymDetails() {
                this.isLoading = true;

                axios.get('/api/gyms/' + this.gymId, {
                    'params': {
                        '__managed': this.managed
                    }
                }).then(result => {
                    this.gym = result.data.gym;

                    this.gymBodyFilter = {
                        [this.constants.bodies.USAG]: (this.gym.usag_membership !== null),
                        [this.constants.bodies.USAIGC]: (this.gym.usaigc_membership !== null),
                        [this.constants.bodies.AAU]: (this.gym.aau_membership !== null),
                        [this.constants.bodies.NGA]: (this.gym.nga_membership !== null),
                    };
                    for(var j in this.meet.meet_categories)
                    {
                        var jt = this.meet.meet_categories[j];
                        if(jt.sanctioning_body_id == 1 && jt.sanction_no != null && this.gym.usag_membership != null) //come
                        {
                            this.usag_route = true;
                            this.usag_url = "https://usagym.org/app/AddMeetRegistration.html?id="+jt.sanction_no+"&clubId="+this.gym.usag_membership;
                        }
                    }
                }).catch(error => {
                    let msg = '';
                    if (error.response) {
                        msg = error.response.data.message;
                    } else if (error.request) {
                        msg = 'No server response.';
                    } else {
                        msg = error.message;
                    }
                    this.errorMessage = msg + '<br/>Please reload this page.';
                    this.isError = true;
                }).finally(() => {
                    this.isLoading = false;
                });
            },

            loadGymAthletes() {
                this.isLoading = true;
                this.available_athletes = [];
                let firstCategoryExpand = true;
                let firstLevelExpand = true;
                this.bodies.forEach(b => {
                    b.categories.forEach(c => {
                        if(firstCategoryExpand){
                            c.expanded = true;
                        }else {
                            c.expanded = false;
                        }
                        firstCategoryExpand = false;
                        c.levels.forEach(l => {
                            l.athletes = [];
                            l.subtotal = 0;
                            if(firstLevelExpand){
                                l.expanded = true;
                            }else {
                                l.expanded = false;
                            }
                            firstLevelExpand = false;

                            if (l.enable_athlete_limit) {
                                let gender = ((l.male && l.female) ? 'both' : (l.male ? 'male' : 'female'));
                                l.slots = l.athlete_limit - this.meet.used_slots[l.id][gender]['count'];
                            }
                        });
                    });
                });
                this.total = 0;

                axios.get('/api/gyms/' + this.gymId + '/athletes', {
                    'params': {
                        '__managed': this.managed
                    }
                }).then(result => {
                    for (let i in result.data.athletes) {
                        let athlete = result.data.athletes[i];

                        athlete.gender_display = athlete.gender.charAt(0).toUpperCase() + athlete.gender.slice(1)
                        athlete.dob = Moment(athlete.dob);
                        athlete.dob_display = athlete.dob.format('MM/DD/YYYY');
                        athlete.checked = false;
                        athlete.fee = 0;

                        athlete.to_waitlist = false;

                        athlete.waitlist_by_level = false;
                        athlete.waitlist_by_meet = false;
                        athlete.waitlist_by_waitlist = false;

                        if (this.meet.tshirt_chart != null) {
                            athlete.editing_tshirt = false;
                            if (
                                (athlete.tshirt == null) ||
                                (this.meet.tshirt_chart.id != athlete.tshirt.clothing_size_chart_id)
                            ) {
                                athlete.tshirt_size_id = -1;
                            }
                        }

                        if (this.meet.leo_chart != null) {
                            athlete.editing_leo = false;
                            if (
                                (athlete.leo == null) ||
                                (this.meet.leo_chart.id != athlete.leo.clothing_size_chart_id)
                            ) {
                                athlete.leo_size_id = -1;
                            }
                        }


                        this.available_athletes.push(athlete);

                        let matchedLevels = this.available_levels.filter(l =>
                            (
                                (athlete.usag_active && (athlete.usag_level_id == l.id)) ||
                                (athlete.usaigc_active && (athlete.usaigc_level_id == l.id)) ||
                                (athlete.aau_active && (athlete.aau_level_id == l.id)) ||
                                (athlete.nga_active && (athlete.nga_level_id == l.id))
                            ) && (
                                ((l.male) && (athlete.gender == 'male')) ||
                                ((l.female) && (athlete.gender == 'female'))
                            )
                        );

                        for (let j in matchedLevels) {
                            let level = matchedLevels[j];

                            this.addAthleteToLevel(athlete, level);
                        }
                    }
                }).catch(error => {
                    let msg = '';
                    if (error.response) {
                        msg = error.response.data.message;
                    } else if (error.request) {
                        msg = 'No server response.';
                    } else {
                        msg = error.message;
                    }
                    this.errorMessage = msg + '<br/>Please reload this page.';
                    this.isError = true;
                }).finally(() => {
                    this.isLoading = false;
                });
            },

            loadGymCoaches() {
                this.isLoading = true;
                this.coaches = [];
                axios.get('/api/gyms/' + this.gymId + '/coaches', {
                    'params': {
                        '__managed': this.managed
                    }
                }).then(result => {
                    for (let i in result.data.coaches) {
                        let coach = result.data.coaches[i];

                        coach.gender_display = coach.gender.charAt(0).toUpperCase() + coach.gender.slice(1)
                        coach.dob = Moment(coach.dob);
                        coach.dob_display = coach.dob.format('MM/DD/YYYY');

                        if (this.meet.tshirt_chart != null) {
                            coach.editing_tshirt = false;
                            if (
                                (coach.tshirt == null) ||
                                (this.meet.tshirt_chart.id != coach.tshirt.clothing_size_chart_id)
                            ) {
                                coach.tshirt_size_id = -1;
                            }
                        }

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

                        coach.checked = false;

                        this.coaches.push(coach);
                    }
                }).catch(error => {
                    let msg = '';
                    if (error.response) {
                        msg = error.response.data.message;
                    } else if (error.request) {
                        msg = 'No server response.';
                    } else {
                        msg = error.message;
                    }
                    this.errorMessage = msg + '<br/>Please reload this page.';
                    this.isError = true;
                }).finally(() => {
                    this.isLoading = false;
                });
            },

            showAddModal(level) {
                this.add_athlete_level = level;
                this.add_athlete_athlete = '';
                $('#modal-registration-add-athlete').modal('show');
            },

            addModalAthlete() {
                $('#modal-registration-add-athlete').modal('hide');

                if (this.add_athlete_athlete) {
                    let exists = this.add_athlete_level.athletes
                                    .filter(a => a.id == (this.add_athlete_athlete.id))
                                    .length > 0;
                    if (exists) {
                        this.showAlert(
                            'This athlete is already added to this level.',
                            'Whoops !',
                            'red',
                            'fas fa-times-circle'
                        );
                        return;
                    }
                    this.addAthleteToLevel(this.add_athlete_athlete, this.add_athlete_level);
                }
            },

            addAthleteToLevel(athlete, level) {
                let levelAthlete = {...athlete};

                levelAthlete.specialist = [];
                if (level.has_specialist && level.allow_specialist) {
                    for (let k in this.specialist_events) {
                        let event = this.specialist_events[k];

                        if (event.sanctioning_body.id != level.sanctioning_body_id)
                            continue;

                        if ((level.male && event.male) || (level.female && event.female))
                            levelAthlete.specialist.push({...event, checked: false});
                    }
                }

                level.athletes.push(levelAthlete);
            },

            toggleItems(toggle) {
                this.bodies.forEach(b => {
                    b.expanded = toggle;
                    b.categories.forEach(c => {
                        c.expanded = toggle;
                        c.levels.forEach(l => l.expanded = toggle);
                    });
                });
            },

            editAthleteTshirt(level, athlete) {
                athlete.editing_tshirt = true;
                this.$nextTick(() => this.$refs[level.uid + '-' + athlete.id + '-tshirt'][0].focus());
            },

            editAthleteLeo(level, athlete) {
                athlete.editing_leo = true;
                this.$nextTick(() => this.$refs[level.uid + '-' + athlete.id + '-leo'][0].focus());
            },

            editCoachTshirt(coach) {
                coach.editing_tshirt = true;
                this.$nextTick(() => this.$refs['coach-' + coach.id + '-tshirt'][0].focus());
            },

            levelUniqueId(level) {
                return level.id + (level.male ? '-m' : '') + (level.female ? '-f' : '');
            },

            validateRegistration() {
                try {
                    let athleteCount = 0;

                    let hasWaitlist = false;
                    this.available_levels.some(l => {
                        hasWaitlist = this.hasWaitlistAthletes(l);
                        return hasWaitlist;
                    });

                    let result = {
                        total: this.total,
                        meet: this.meet,
                        gym: this.gymId,
                        levels: [],
                        coaches: [],
                        waitlist: hasWaitlist,
                    };

                    this.available_levels.forEach(l => {
                        let selectedAthletes = l.athletes.filter(a => {
                            let flag = false;

                            if (l.has_specialist && l.allow_specialist) {
                                a.specialist.some(s => {
                                    if (s.checked) {
                                        flag = true;
                                        return true;
                                    }
                                });
                            }

                            return a.checked || flag;
                        });

                        let athletes = [];
                        if (selectedAthletes.length > 0) {
                            selectedAthletes.forEach(a => {
                                let athlete = {
                                    id: a.id,
                                    waitlist: a.to_waitlist,
                                };

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

                                if (l.has_specialist && l.allow_specialist)
                                    athlete.specialist = a.specialist.filter(s => s.checked == true).map(s => s.id);

                                athletes.push(athlete);
                            });

                            result.levels.push({
                                id: l.id,
                                male: l.male,
                                female: l.female,
                                team: l.allow_team && l.team,
                                athletes: athletes
                            });

                            athleteCount += selectedAthletes.length;
                        }
                    });

                    let neededSanctions = [];
                    /*
                    for (let body in this.available_bodies) {
                        if (this.bodies.hasOwnProperty(body)) {
                            body = this.available_bodies[body];

                            if (
                                (body == this.constants.bodies.USAIGC) ||
                                (body == this.constants.bodies.USAG)
                            )
                                continue;

                            neededSanctions.push(this.constants.bodies[body.id].toLowerCase());
                        }
                    };
                    */

                    let selectedCoaches = this.coaches.filter(c => c.checked);

                    if (neededSanctions.length > 0) {
                        selectedCoaches.forEach(c => {
                            let flag = false;
                            neededSanctions.forEach(body => {
                                flag = flag || (c[body + '_no'] !== null);
                            });

                            if (!flag)
                                throw 'No sanction number was provided for coach ' + c.first_name + ' ' + c.last_name + '. Please update your coach details in your roster.';
                        });
                    }

                    result.coaches = selectedCoaches.map(c => {
                        return {
                            id: c.id,
                            tshirt: c.tshirt_size_id
                        };
                    });

                    if (athleteCount < 1)
                        throw 'You need to select at least one athlete to compete.';

                    if (result.coaches.length < 1)
                        throw 'Please select at least one coach to attend competition.';

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
        mounted() {
            try {
                this.isLoading = true;
                axios.get('/api/app/meet/' + this.meetId).then(result => {
                    if (result.data.meets.length != 1)
                        throw 'Something went wrong while loading this meet\'s details.';

                    this.meet = result.data.meets[0];
                    this.meet.late_registration_fee = Utils.toFloat(this.meet.late_registration_fee);

                    axios.get('/api/app/specialist').then(result => {
                        this.specialist_events = result.data.events;

                        for (const i in this.available_bodies) {
                            if (this.available_bodies.hasOwnProperty(i)) {
                                let body = this.available_bodies[i];

                                if (this.requires_sanction[body.id])
                                    continue;

                                body.name = i;
                                body.expanded = true;

                                let categories = [];
                                for (let j in body.categories) {
                                    if (body.categories.hasOwnProperty(j)) {
                                        let category = body.categories[j];

                                        let meetCategory = this.meet.categories.find(c => {
                                            return (c.pivot.sanctioning_body_id == body.id) &&
                                                    (c.id == category.id);
                                        });
                                        if (meetCategory === undefined)
                                            throw 'Something went wrong (can\'t find category)';

                                        if (meetCategory.pivot.officially_sanctioned || meetCategory.pivot.frozen)
                                            continue;

                                        category.name = j;
                                        category.expanded = this.constants.settings.defaultExpand;

                                        let levels = [];
                                        for (let k in category.levels) {
                                            if (category.levels.hasOwnProperty(k)) {
                                                let level = category.levels[k];

                                                level.male = level.pivot.allow_men;
                                                level.female = level.pivot.allow_women;
                                                level.discount_fee = false;
                                                if(typeof(level.pivot.registration_fee_update) != undefined && level.pivot.registration_fee_update != null)
                                                {
                                                    level.registration_fee = Utils.toFloat(level.pivot.registration_fee_update);
                                                    level.discount_fee = true;
                                                }
                                                else
                                                    level.registration_fee = Utils.toFloat(level.pivot.registration_fee_update);

                                                level.late_registration_fee = Utils.toFloat(level.pivot.late_registration_fee);
                                                level.allow_specialist = level.pivot.allow_specialist;
                                                level.specialist_registration_fee = Utils.toFloat(level.pivot.specialist_registration_fee);
                                                level.specialist_late_registration_fee = Utils.toFloat(level.pivot.specialist_late_registration_fee);
                                                level.allow_team = level.pivot.allow_teams;
                                                level.team_registration_fee = Utils.toFloat(level.pivot.team_registration_fee);
                                                level.team_late_registration_fee = Utils.toFloat(level.pivot.team_late_registration_fee);
                                                level.enable_athlete_limit = level.pivot.enable_athlete_limit;
                                                level.athlete_limit = level.pivot.athlete_limit;

                                                level.expanded = this.constants.settings.defaultExpand;
                                                level.uid = this.levelUniqueId(level);
                                                level.has_specialist = this.hasSpecialist(body, category);
                                                level.athletes = [];
                                                level.team = false;
                                                level.subtotal = 0;

                                                level.selectedAthletes = function() {
                                                    return this.athletes.filter(a => a.checked).length;
                                                };

                                                level.remainingSlots = function(readable) {
                                                    let r = this.slots - level.selectedAthletes();
                                                    return (readable && (r < 1) ? 'No' : r);
                                                };

                                                level.non_waitlist_reserved_slots = function() {
                                                    return this.athletes.filter(a => a.checked && !a.to_waitlist).length;
                                                };

                                                if (level.enable_athlete_limit) {
                                                    let gender = ((level.male && level.female) ? 'both' : (level.male ? 'male' : 'female'));
                                                    level.slots = level.athlete_limit - this.meet.used_slots[level.id][gender]['count'];
                                                    level.reserved_slots = level.selectedAthletes;
                                                } else {
                                                    level.slots = null;
                                                }

                                                levels.push(level);
                                                this.available_levels.push(level);
                                            }
                                        }

                                        category.levels = levels;
                                        categories.push(category);
                                    }
                                }

                                body.categories = categories;
                                this.bodies.push(body);
                            }
                        }

                        if (this.meet.athlete_limit !== null) {
                            this.meet.slots = this.meet.athlete_limit - this.meet.used_slots.total;
                            this.meet.reserved_slots = () => {
                                let count = 0;
                                this.available_levels.forEach(l => count += l.selectedAthletes());
                                return count;
                            };

                            this.meet.non_waitlist_reserved_slots = () => {
                                let count = 0;
                                this.available_levels.forEach(l => count += l.non_waitlist_reserved_slots());
                                return count;
                            };
                        }
                    }).catch(error => {
                        let msg = '';
                        if (error.response) {
                            msg = error.response.data.message;
                        } else if (error.request) {
                            msg = 'No server response.';
                        } else if ((typeof msg) == 'string') {
                            msg = error;
                        } else {
                            msg = error.message;
                        }
                        this.errorMessage = msg + '<br/>Please reload this page.';
                        this.isError = true;
                    }).finally(() => {
                        this.isLoading = false;
                    });
                }).catch(error => {
                    let msg = '';
                    if (error.response) {
                        msg = error.response.data.message;
                    } else if (error.request) {
                        msg = 'No server response.';
                    } else {
                        msg = error.message;
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
