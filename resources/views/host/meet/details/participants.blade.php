<div class="d-flex flex-row flex-no-wrap mb-2">
    <div class="flex-grow-1">
        <div class="input-group input-group-sm">
            <input type="text" class="form-control search-field"
                :class="{'border-right-0': (registrationFilters.text != '')}"
                v-model="registrationFilters.text" placeholder="Gym name ...">

            <div class="input-group-append">
                <button class="btn btn-outline-danger" :class="{'d-none': (registrationFilters.text === '')}"
                    @click="registrationFilters.text = ''" type="button" title="Clear Search Box" >
                    <span class="fas fa-fw fa-eraser"></span>
                </button>
            </div>
        </div>
    </div>
    <div>
        <div class="form-check small ml-2">
            <input class="form-check-input" type="checkbox" id="registration-filter-pending"
                v-model="registrationFilters.pending">
            <label class="form-check-label" for="registration-filter-pending">
                Pending Only
            </label>
        </div>
    </div>
</div>

<div class="small text-success mb-2">
    <strong><span class="far fa-lightbulb"></span> Tip :</strong>
    Click on the status of athletes / coaches to quickly
    jump to the associated transaction.
</div>

<div v-if="registrationFiltering">
    <div class="small text-center py-3">
        <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true">
        </span> Loading, please wait ...
    </div>
</div>
<div v-else v-for="r in registrations" :key="r.id" class="">
    <div class="mb-1">
        <div>
            <div class="btn btn-primary btn-block text-left left-btn">
                <div class="d-flex flex-wrap flex-row">
                    <div class="pr-2">
                        <img class="gym-picture rounded-circle" alt="Gym Picture"
                                :src="r.gym.profile_picture" title="Gym Picture">
                    </div>
                    <div class="flex-grow-1 d-flex align-items-center" @click="r.expanded = !r.expanded">
                        <strong>
                            @{{ r.gym.name }}
                        </strong>
                        <span class="text-secondary ml-1">
                            | <span>
                                @{{ r.athletes.length }} Athletes
                            </span>
                            | <span>
                                @{{ r.coaches.length }} Coaches
                            </span>
                        </span>
                        <span :class="'fas fa-fw fa-caret-' + (r.expanded ? 'down' : 'right')"></span>
                    </div>

                    <div class="d-flex align-items-center pr-1">
                        <div class="dropdown">
                            <button class="btn btn-sm btn-success ml-1 dropdown-toggle"
                                type="button" id="verificationDropdownMenuButton"
                                data-toggle="dropdown">
                                <span class="fas fa-fw fa-user-check"></span> Verify
                            </button>
                            <div class="dropdown-menu dropdown-menu-right" aria-labelledby="verificationDropdownMenuButton">
                                <div v-if="r.bodies[constants.bodies.USAG]">
                                    <h6 class="dropdown-header font-weight-bold">USAG</h6>
                                    <div class="dropdown-item"
                                        @click="handleVerificationRequest(constants.bodies.USAG, 'athletes', r.verifications[constants.bodies.USAG].athletes, r)"
                                        :class="{disabled: (r.bodies[constants.bodies.USAG].athlete_count < 1)}">
                                        <span class="fas fa-fw fa-running"></span>
                                        <span v-if="r.bodies[constants.bodies.USAG].athlete_count < 1">
                                            No Athletes
                                        </span>
                                        <span v-else>
                                            <span v-if="!r.verifications[constants.bodies.USAG].athletes">
                                                Verify Athletes
                                            </span>
                                            <span v-else-if="r.verifications[constants.bodies.USAG].athletes.status == constants.verifications.statuses.Done">
                                                Show Athletes Results
                                            </span>
                                            <span v-else class="text-secondary">
                                                Processing Athletes ...
                                            </span>
                                        </span>
                                    </div>

                                    <div class="dropdown-item" @click="handleVerificationRequest(constants.bodies.USAG, 'coaches', r.verifications[constants.bodies.USAG].coaches, r)"
                                        :class="{disabled: (r.bodies[constants.bodies.USAG].coach_count < 1)}">
                                        <span class="fas fa-fw fa-chalkboard-teacher"></span>
                                        <span v-if="r.bodies[constants.bodies.USAG].coach_count < 1">
                                            No Coaches
                                        </span>
                                        <span v-else>
                                            <span v-if="!r.verifications[constants.bodies.USAG].coaches">
                                                Verify Coaches
                                            </span>
                                            <span v-else-if="r.verifications[constants.bodies.USAG].coaches.status == constants.verifications.statuses.Done">
                                                Show Coaches Results
                                            </span>
                                            <span v-else class="text-secondary">
                                                Processing Coaches ...
                                            </span>
                                        </span>
                                    </div>
                                </div>

                                <div class="dropdown-divider"></div>

                                <div v-if="r.bodies[constants.bodies.USAIGC]">
                                    <h6 class="dropdown-header font-weight-bold">USAIGC</h6>
                                    <div class="dropdown-item" @click="handleVerificationRequest(constants.bodies.USAIGC, 'athletes', r.verifications[constants.bodies.USAIGC].athletes, r)"
                                        :class="{disabled: (r.bodies[constants.bodies.USAIGC].athlete_count < 1)}">
                                        <span class="fas fa-fw fa-running"></span>
                                        <span v-if="r.bodies[constants.bodies.USAIGC].athlete_count < 1">
                                            No USAIGC Athletes
                                        </span>
                                        <span v-else>
                                            <span v-if="!r.verifications[constants.bodies.USAIGC].athletes">
                                                Verify Athletes
                                            </span>
                                            <span v-else-if="r.verifications[constants.bodies.USAIGC].athletes.status == constants.verifications.statuses.Done">
                                                Show Athletes Results
                                            </span>
                                            <span v-else class="text-secondary">
                                                Processing Athletes ...
                                            </span>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex align-items-center pr-1">
                        <div class="dropdown">
                            <button class="btn btn-sm btn-warning ml-1 dropdown-toggle"
                                type="button" id="reportsDropdownMenuButton"
                                data-toggle="dropdown">
                                <span class="fas fa-fw fa-file-pdf"></span> Reports
                            </button>
                            <div class="dropdown-menu dropdown-menu-right" aria-labelledby="reportsDropdownMenuButton">
                                <div class="dropdown-item" @click="generateReport(constants.reports.types.MeetEntry, r.gym.id)"
                                     :class="{disabled: (r.status != constants.registrations.statuses.Registered)}">
                                    <span class="fas fa-fw fa-file"></span>Meet Entry
                                </div>
                                <div class="dropdown-item" @click="generateReport(constants.reports.types.RegistrationDetail, r.gym.id)"
                                     :class="{disabled: (r.status != constants.registrations.statuses.Registered)}">
                                    <span class="fas fa-fw fa-file"></span>Registration Detail
                                </div>
                                <div class="dropdown-item" @click="generateReport(constants.reports.types.Scratch, r.gym.id)"
                                    :class="{disabled: (r.status != constants.registrations.statuses.Registered)}">
                                    <span class="fas fa-fw fa-file"></span> Scratches
                                </div>
                                <div class="dropdown-item" @click="generateReport(constants.reports.types.Refunds, r.gym.id)"
                                     :class="{disabled: (r.status != constants.registrations.statuses.Registered)}">
                                    <span class="fas fa-fw fa-file"></span> Refunds
                                </div>
                                <div class="dropdown-item" @click="generateReport(constants.reports.types.LeoTShirtGym, r.gym.id)"
                                    :class="{disabled: (r.status != constants.registrations.statuses.Registered)}">
                                    <span class="fas fa-fw fa-file"></span> Leo/T-Shirt Gym
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex align-items-center pr-1">
                        <button type="button" class="btn btn-sm btn-info ml-1" title="Send email"
                            @click="sendEmailTo(r)">
                            <span class="fas fa-fw fa-envelope"></span>
                        </button>
                    </div>

                    <div v-if="r.anyPending" class="d-flex align-items-center">
                        <span class="fas fa-fw fa-exclamation-triangle text-warning"></span>
                    </div>
                </div>
            </div>
        </div>

        <div v-if="r.expanded" class="p-1 pl-3 custom-panel-body">
            <div v-for="body in r.bodies" :key="body.path" class="mb-2">
                <div class="mb-1">
                    <div class="btn btn-sm btn-dark btn-block text-left left-btn"
                        @click="body.expanded = !body.expanded">
                        <div class="d-flex flex-no-wrap flex-row">
                            <div class="flex-grow-1">
                                <span class="fas fa-fw fa-receipt"></span>
                                @{{ body.name }}
                                <span :class="'fas fa-fw fa-caret-' + (body.expanded ? 'down' : 'right')"></span>
                                <span class="badge badge-pill badge-light small">@{{ body.athlete_count }}</span>
                            </div>
                            <div v-if="body.hasPending">
                                <span class="fas fa-fw fa-exclamation-triangle text-warning"></span>
                            </div>
                        </div>
                    </div>
                </div>

                <div v-if="body.expanded" class="ml-3">
                    <div v-for="category in body.categories" :key="category.path" class="mb-1">
                        <div class="mb-1">
                            <div class="btn btn-sm btn-info btn-block text-left left-btn"
                                @click="category.expanded = !category.expanded">
                                <div class="d-flex flex-no-wrap flex-row">
                                    <div class="flex-grow-1">
                                        <span class="fas fa-fw fa-cubes"></span>
                                        @{{ category.name }}
                                        <span :class="'fas fa-fw fa-caret-' + (category.expanded ? 'down' : 'right')"></span>
                                        <span class="badge badge-pill badge-light small">@{{ category.athlete_count }}</span>
                                    </div>
                                    <div v-if="category.hasPending">
                                        <span class="fas fa-fw fa-exclamation-triangle text-warning"></span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div v-if="category.expanded" class="ml-3 mb-2">
                            <div v-for="level in category.levels" :key="level.uid" class="mb-1"
                                :data-bang="level.uid">
                                <div class="mb-2">
                                    <div class="btn btn-sm btn-secondary btn-block text-left left-btn"
                                        @click="level.expanded = !level.expanded">
                                        <div class="d-flex flex-no-wrap flex-row">
                                            <div class="flex-grow-1">
                                                <span class="fas fa-fw fa-layer-group"></span>
                                                <span class="mr-2">
                                                    @{{ level.name }}
                                                </span> |
                                                <span class="ml-2 mr-2">
                                                    <span v-if="level.male && level.female">Both</span>
                                                    <span v-else-if="level.male">Male</span>
                                                    <span v-else-if="level.female">Female</span>
                                                </span> |
                                                <span class="ml-2 mr-2">
                                                    @{{ level.athletes.length }} Athletes
                                                </span>
                                                <span :class="'fas fa-fw fa-caret-' + (level.expanded ? 'down' : 'right')"></span>
                                            </div>
                                            <div v-if="level.hasPending">
                                                <span class="fas fa-fw fa-exclamation-triangle text-danger"></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div v-if="level.expanded" class="ml-3 mb-1">
                                    <div v-if="level.allow_team && level.has_team /*&& level.team_paid_for*/"
                                        class="mb-1 text-info small">
                                        <span class="fas fa-users"></span>
                                        Athletes in this level are registered as a team.
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
                                                        @{{ body.name }} No.
                                                    </th>
                                                    <th v-if="r.has_tshirts" scope="col" class="align-middle">
                                                        T-shirt
                                                    </th>
                                                    <th v-if="r.has_leos" scope="col" class="align-middle">
                                                        Leo
                                                    </th>
                                                    <th scope="col" class="align-middle">
                                                        Fee
                                                    </th>
                                                    <th scope="col" class="align-middle">
                                                        Status
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
                                                    <td v-if="level.has_specialist && level.allow_specialist" class="ml-3">
                                                        <div v-if="athlete.is_specialist">
                                                            <div v-for="event in athlete.events" :key="event.id"
                                                                class="clickable my-1" @click="showRelatedTransaction(event.transaction_id)">
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

                                                                @{{ specialist_events[event.event_id].name }}
                                                                <span v-if="event.was_late" class="text-danger">(Late)</span>
                                                            </div>
                                                        </div>
                                                        <div v-else>
                                                            All Around
                                                        </div>
                                                    </td>
                                                    <td class="align-middle clickable">
                                                        @{{ athlete.first_name }}
                                                    </td>
                                                    <td class="align-middle clickable">
                                                        @{{ athlete.last_name}}
                                                    </td>

                                                    <td class="align-middle">
                                                        @{{ athlete.dob_display }}
                                                    </td>

                                                    <td class="align-middle">
                                                        <span v-if="body.id == constants.bodies.USAG">
                                                            @{{ athlete.usag_no }}
                                                        </span>
                                                        <span v-if="body.id == constants.bodies.USAIGC">
                                                            @{{ athlete.usaigc_no }}
                                                        </span>
                                                        <span v-if="body.id == constants.bodies.AAU">
                                                            @{{ athlete.aau_no }}
                                                        </span>
                                                        <span v-else>
                                                            @{{ athlete.nga_no }}
                                                        </span>
                                                    </td>

                                                    <td v-if="r.has_tshirts" scope="col" class="align-middle">
                                                        <div v-if="!athlete.tshirt">—</div>
                                                        <div v-else>
                                                            @{{ athlete.tshirt.size }}
                                                        </div>
                                                    </td>

                                                    <td v-if="r.has_leos" scope="col" class="align-middle">
                                                        <div v-if="athlete.leo">
                                                            <div v-if="(athlete.gender == 'male') || !athlete.leo">—
                                                            </div>
                                                            <div v-else>
                                                                @{{ athlete.leo.size }}
                                                            </div>
                                                        </div>
                                                        <div v-else>—</div>
                                                    </td>

                                                    <td class="align-middle">
                                                        $@{{ numberFormat(athlete.total) }}
                                                        <span v-if="athlete.was_late" class="text-danger">(Late)</span>
                                                    </td>

                                                    <td class="align-middle">
                                                        <div v-if="athlete.is_specialist">
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
                                                        <div v-else class="clickable" @click="showRelatedTransaction(athlete.transaction_id)">
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
                        </div>
                    </div>
                </div>
            </div>

            <div class="mb-1">
                <div class="btn btn-sm btn-success btn-block text-left left-btn"
                    @click="r.coachesExpanded = !r.coachesExpanded">
                    <div class="d-flex flex-no-wrap flex-row">
                        <div class="flex-grow-1">
                            <span class="fas fa-fw fa-chalkboard-teacher"></span> Coaches
                            <span :class="'fas fa-fw fa-caret-' + (r.coachesExpanded ? 'down' : 'right')"></span>
                            <span class="badge badge-pill badge-light small">@{{ r.coaches.length }}</span>
                        </div>
                        <div v-if="r.hasPendingCoaches">
                            <span class="fas fa-fw fa-exclamation-triangle text-warning"></span>
                        </div>
                    </div>
                </div>
            </div>

            <div v-if="r.coachesExpanded" class="ml-3">
                <div class="table-responsive-lg">
                    <table class="table table-sm table-hover">
                        <thead class="thead-light">
                            <tr>
                                <th scope="col" class="align-middle">
                                    First Name
                                </th>
                                <th scope="col" class="align-middle">
                                    Last Name
                                </th>
                                <th scope="col" class="align-middle">
                                    Date Of Birth
                                </th>
                                <th v-if="r.has_tshirts" scope="col" class="align-middle">
                                    T-shirt
                                </th>
                                <th scope="col" class="align-middle">
                                    USAG No.
                                </th>
                                <th scope="col" class="align-middle">
                                    USAIGC No.
                                </th>
                                <th scope="col" class="align-middle">
                                    AAU No.
                                </th>
                                <th scope="col" class="align-middle">
                                    NGA No.
                                </th>
                                <th scope="col" class="align-middle">
                                    Status
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="c in r.coaches" :key="c.id">
                                <td class="align-middle">
                                    @{{ c.first_name }}
                                </td>
                                <td class="align-middle">
                                    @{{ c.last_name}}
                                </td>

                                <td class="align-middle">
                                    @{{ c.dob_display }}
                                </td>

                                <td v-if="r.has_tshirts" scope="col" class="align-middle">
                                    <div v-if="!c.tshirt">—</div>
                                    <div v-else>
                                        @{{ c.tshirt.size }}
                                    </div>
                                </td>

                                <td class="align-middle">
                                    <div v-if="c.usag_no != null">
                                        @{{ c.usag_no }}
                                    </div>
                                    <div v-else>—</div>
                                </td>

                                <td class="align-middle">
                                    <div v-if="c.usaigc_no != null">
                                        @{{ c.usaigc_no }}
                                    </div>
                                    <div v-else>—</div>
                                </td>

                                <td class="align-middle">
                                    <div v-if="c.aau_no != null">
                                        @{{ c.aau_no }}
                                    </div>
                                    <div v-else>—</div>
                                </td>

                                <td class="align-middle">
                                    <div v-if="c.nga_no != null">
                                        @{{ c.nga_no }}
                                    </div>
                                    <div v-else>—</div>
                                </td>

                                <td class="align-middle">
                                    <div v-if="c.status == constants.coaches.statuses.Registered">
                                        <span class="badge badge-success">Registered</span>
                                    </div>

                                    <div v-else-if="c.status == constants.coaches.statuses.NonReserved">
                                        <span class="badge badge-warning">
                                            <span v-if="c.in_waitlist">Waitlist</span>
                                            <span v-else>Pending</span>
                                            <br/>(Non-Reserved)
                                        </span>
                                    </div>

                                    <div v-else-if="c.status == constants.coaches.statuses.Reserved">
                                        <span class="badge badge-secondary">Pending<br/>(Reserved)</span>
                                    </div>

                                    <div v-else>
                                        <span class="badge badge-danger">Scratched</span>
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
