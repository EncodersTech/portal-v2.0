<div v-if="registration">
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
            </div>
            <div>
                <div class="d-inline-block mr-2 ml-2">
                    <button class="btn btn-sm btn-primary" title="Collapse All" @click="toggleItems(false)"
                        type="button">
                        <span class="fas fa-fw fa-compress"></span>
                    </button>
                    <button class="btn btn-sm btn-primary" title="Expand All" @click="toggleItems(true)" type="button">
                        <span class="fas fa-fw fa-expand"></span>
                    </button>
                </div>
            </div>
        </div>

        <div v-for="body in bodies" :key="body.path" class="mb-2">
            <div class="mb-1">
                <button class="btn btn-sm btn-dark btn-block text-left left-btn" type="button"
                    @click="body.expanded = !body.expanded">
                    <span class="fas fa-fw fa-receipt"></span>
                    @{{ body.name }}
                    <span :class="'fas fa-fw fa-caret-' + (body.expanded ? 'down' : 'right')"></span>
                </button>
            </div>

            <div v-if="body.expanded" class="ml-3">
                <div v-for="category in body.categories" :key="category.path" class="mb-1">
                    <div class="mb-1">
                        <button class="btn btn-sm btn-info btn-block text-left left-btn" type="button"
                            @click="category.expanded = !category.expanded">
                            <span class="fas fa-fw fa-cubes"></span>
                            @{{ category.name }}
                            <span :class="'fas fa-fw fa-caret-' + (category.expanded ? 'down' : 'right')"></span>
                        </button>
                    </div>

                    <div v-if="category.expanded" class="ml-3 mb-2">
                        <div v-for="level in category.levels" :key="level.uid" class="mb-1" :data-bang="level.uid">
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
                                            <span
                                                :class="'fas fa-fw fa-caret-' + (level.expanded ? 'down' : 'right')"></span>
                                        </div>
                                        <div v-if="level.disabled" class="text-danger font-weight-bold"
                                            title="This level was disabled by the meet host">
                                            <span class="fas fa-fw fa-exclamation-triangle"></span> Disabled
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div v-if="level.expanded" class="ml-3 mb-1">
                                <div v-if="level.disabled" class="class alert alert-danger small">
                                    <span class="fas fa-fw fa-exclamation-triangle"></span>
                                    This level was disabled by the meet host. Please reach out to them to find out how
                                    to proceed.
                                </div>

                                <div v-if="level.allow_team && level.has_team /*&& level.team_paid_for*/"
                                    class="mb-1 text-info small">
                                    <span class="fas fa-users"></span>
                                    Athletes in this level are registered as a team.
                                </div>
                                <div class="small mb-1">
                                    <div>
                                        <div class="d-inline-block mr-1">
                                            <strong>Registration fee:</strong>
                                            $@{{ numberFormat(level.registration_fee)}}
                                        </div>
                                        <div class="d-inline-block">
                                            | <strong>Late:</strong>
                                            $@{{ numberFormat(level.late_registration_fee)}}
                                        </div>
                                    </div>
                                    <div v-if="level.has_specialist">
                                        <div class="d-inline-block mr-1">
                                            <strong>Specialist:</strong>
                                            @{{ level.allow_specialist ? 'Allowed' : 'Not Allowed'}}
                                        </div>
                                        <div class="d-inline-block" v-if="level.allow_specialist">
                                            |
                                            <div class="d-inline-block mr-1">
                                                <strong>Specialist regular fee:</strong>
                                                $@{{ numberFormat(level.specialist_registration_fee)}}
                                            </div>
                                            <div class="d-inline-block mr-1">
                                                | <strong>Specialist late fee:</strong>
                                                $@{{ numberFormat(level.specialist_late_registration_fee)}}
                                            </div>
                                        </div>
                                    </div>
                                    <div>
                                        <div class="d-inline-block mr-1">
                                            <strong>Team:</strong>
                                            @{{ level.allow_team ? 'Allowed' : 'Not Allowed'}}
                                        </div>
                                        <div class="d-inline-block" v-if="level.allow_team">
                                            |
                                            <div class="d-inline-block">
                                                <strong>Team regular fee:</strong>
                                                $@{{ numberFormat(level.team_registration_fee)}}
                                            </div>
                                            <div class="d-inline-block">
                                                | <strong>Team late fee:</strong>
                                                $@{{ numberFormat(level.team_late_registration_fee)}}
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
                                                    @{{ body.name }} No.
                                                </th>
                                                <th v-if="registration.has_tshirts" scope="col" class="align-middle">
                                                    T-shirt
                                                </th>
                                                <th v-if="registration.has_leos" scope="col" class="align-middle">
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
                                                        <div v-for="event in athlete.events" :key="event.id">
                                                            <span
                                                                v-if="event.status == constants.specialists.statuses.Registered"
                                                                class="fas fa-fw fa-check text-success"
                                                                title="Registered">
                                                            </span>

                                                            <span
                                                                v-else-if="event.status == constants.specialists.statuses.Pending">
                                                                <span class="fas fa-clock text-warning"
                                                                    title="Pending"></span>
                                                                <span v-if="event.in_waitlist"
                                                                    class="fas fa-exclamation-triangle text-warning"
                                                                    title="Waitlist">
                                                                </span>
                                                            </span>

                                                            <span v-else class="fas fa-fw fa-times text-danger"
                                                                title="Scratched"></span>

                                                            @{{ specialist_events[event.event_id].name }}
                                                            <span v-if="event.was_late"
                                                                class="text-danger">(Late)</span>
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

                                                <td v-if="registration.has_tshirts" scope="col" class="align-middle">
                                                    <div v-if="athlete.tshirt">
                                                        @{{ athlete.tshirt.size }}
                                                    </div>
                                                    <div v-else>—</div>
                                                </td>

                                                <td v-if="registration.has_leos" scope="col" class="align-middle">
                                                    <div v-if="athlete.leo">
                                                        <div v-if="athlete.gender == 'male'">—</div>
                                                        <div v-else-if="athlete.leo.size">
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
                                                    <div v-if="level.disabled" class="d-inline-block text-danger mr-1"
                                                        title="This level was disabled by the meet host">
                                                        <span class="fas fa-fw fa-exclamation-triangle"></span>
                                                    </div>
                                                    <div class="d-inline-block">
                                                        <div v-if="athlete.is_specialist">
                                                            <div
                                                                v-if="athlete.status == constants.specialists.statuses.Registered">
                                                                <span class="badge badge-success">Registered</span>
                                                            </div>

                                                            <div
                                                                v-else-if="athlete.status == constants.specialists.statuses.Pending">
                                                                <span class="badge badge-warning">Pending</span>
                                                            </div>

                                                            <div
                                                                v-else-if="athlete.status == constants.specialists.statuses.Scratched">
                                                                <span class="badge badge-danger">Scratched</span>
                                                            </div>

                                                            <div v-else>
                                                                <span class="badge badge-secondary">Mixed</span>
                                                            </div>
                                                        </div>
                                                        <div v-else>
                                                            <div
                                                                v-if="athlete.status == constants.athletes.statuses.Registered">
                                                                <span class="badge badge-success">Registered</span>
                                                            </div>

                                                            <div
                                                                v-else-if="athlete.status == constants.athletes.statuses.NonReserved">
                                                                <span class="badge badge-warning">
                                                                    <span v-if="athlete.in_waitlist">Waitlist</span>
                                                                    <span v-else>Pending</span>
                                                                    <br />(Non-Reserved)
                                                                </span>
                                                            </div>

                                                            <div
                                                                v-else-if="athlete.status == constants.athletes.statuses.Reserved">
                                                                <span
                                                                    class="badge badge-secondary">Pending<br />(Reserved)</span>
                                                            </div>

                                                            <div v-else>
                                                                <span class="badge badge-danger">Scratched</span>
                                                            </div>
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
    </div>
</div>