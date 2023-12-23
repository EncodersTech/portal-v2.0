<div v-if="coaches && (coaches.length > 0)">
    <div class="row clickable mt-3" @click="showCoaches = !showCoaches">
        <div class="col">
            <h5 class="border-bottom">
                <span class="fas fa-fw fa-chalkboard-teacher"></span> Coaches
                <span :class="'fas fa-fw fa-caret-' + (showCoaches ? 'down' : 'right')"></span>
            </h5>
        </div>
    </div>
    <div v-if="showCoaches">
        <div class="table-responsive-lg">
            <table class="table table-sm table-hover">
                <thead class="bg-success text-light">
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
                        <th scope="col" class="align-middle">
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
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="coach in coaches" :key="coach.id">
                        <td class="align-middle clickable"
                            @click="coach.checked = !coach.checked">
                            @{{ coach.first_name }}
                        </td>
                        <td class="align-middle clickable"
                            @click="coach.checked = !coach.checked">
                            @{{ coach.last_name}}
                        </td>
                        <td class="align-middle clickable">
                            @{{ coach.dob_display}}
                        </td>
                        <td scope="col" class="align-middle">
                            <div v-if="!coach.tshirt">—</div>
                            <div v-else>
                                @{{ coach.tshirt.size }}
                            </div>
                        </td>

                        <td class="align-middle small">
                            <div v-if="coach.usag_no != null">
                                @{{ coach.usag_no }}
                                <!--
                                <div>
                                    <strong>USAG No: </strong> @{{ coach.usag_no }}
                                </div>
                                <div>
                                    <strong>Active: </strong>
                                    @{{ coach.usag_active ? 'Yes' : 'No'}}
                                </div>
                                <div class="ml-3">
                                    <div>
                                        <strong>Expiry: </strong>
                                        @{{ coach.usag_expiry != null ? coach.usag_expiry_display : '—' }}
                                    </div>
                                    <div>
                                        <strong>Safety Expiry: </strong>
                                        @{{ coach.usag_safety_expiry != null ? coach.usag_safety_expiry_display : '—' }}
                                    </div>
                                    <div>
                                        <strong>Safesport Expiry: </strong>
                                        @{{ coach.usag_safesport_expiry != null ? coach.usag_safesport_expiry_display : '—' }}
                                    </div>
                                    <div>
                                        <strong>Background Expiry: </strong>
                                        @{{ coach.usag_background_expiry != null ? coach.usag_background_expiry_display : '—' }}
                                    </div>
                                    <div>
                                        <strong>U100: </strong>
                                        @{{ coach.usag_u100_certification ? 'Yes' : 'No'}}
                                    </div>
                                </div>
                                -->
                            </div>
                            <div v-else>—</div>
                        </td>

                        <td class="align-middle small">
                            <div v-if="coach.usaigc_active">
                                <span class="badge badge-success">Active</span>
                                <!--
                                <div>
                                    <strong>USAIGC No: </strong> @{{ coach.usaigc_no }}
                                </div>
                                <div>
                                    <strong>Background Check: </strong>
                                    @{{ coach.usaigc_background_check ? 'Yes' : 'No'}}
                                </div>
                                -->
                            </div>
                            <div v-else>—</div>
                        </td>

                        <td class="align-middle small">
                            <div v-if="coach.aau_no != null">
                                @{{ coach.aau_no }}
                                <!--
                                <div>
                                    <strong>AAU No: </strong> @{{ coach.aau_no }}
                                </div>
                                -->
                            </div>
                            <div v-else>—</div>
                        </td>

                        <td class="align-middle small">
                            <div v-if="coach.nga_no != null">
                                @{{ coach.nga_no }}
                                <!--
                                <div>
                                    <strong>NGA No: </strong> @{{ coach.nga_no }}
                                </div>
                                -->
                            </div>
                            <div v-else>—</div>
                        </td>

                        <td class="align-middle">
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
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
