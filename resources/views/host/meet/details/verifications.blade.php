<div v-if="verification_details != null" class="font-weight-bold mb-1">
    <h4 class="d-inline-block">
        @{{ verification_details.registration.gym.name }}
    </h4>
    <h5 class="d-inline-block text-secondary">
        — @{{ constants.bodies[verification_details.body] }} @{{ verification_details.type }}
    </h5>
</div>

<div class="d-flex flex-row mt-1 mb-2">
    <div class="flex-grow-1">
        <button v-if="verification_details" class="btn btn-sm btn-primary"
                @click="handleVerificationRequest(
                    verification_details.body,
                    verification_details.type,
                    verification_details.registration.verifications[verification_details.body][verification_details.type],
                    verification_details.registration,
                    true
                )">
            <span class="fas fa-fw fa-sync-alt"></span> Reverify
        </button>
    </div>
    <div class="text-right">
        <button class="btn btn-sm btn-secondary" @click="switchToTab('participants')">
            <span class="fas fa-fw fa-chevron-left"></span> Back
        </button>
    </div>
</div>

<div>
    <div v-if="verification_details == null" class="text-red">
        <span class="fas fa-times-circle"></span> Something went wrong. Please try again later.
    </div>
    <div v-else-if="verification_details.results.status != 'success'" class="text-red">
        <span class="fas fa-times-circle"></span>
        The verification failed. Please try running the verification again in a few minutes.
        If the problem persists, please contact us.
    </div>
    <div v-else>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th scope="col">
                        <span class="fas fa-fw fa-user-check"></span>
                    </th>
                    <th scope="col">Name</th>
                    <th scope="col">Gender</th>
                    <th scope="col">DoB</th>
                    <th scope="col">No.</th>
                    <th scope="col">Issues</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for='(entrant, number) in verification_details.results.data' :key='number'>
                    <td>
                        <span v-if="typeof(entrant.valid) == 'object'">
                            <span v-for="(v, s) in entrant.valid" :key="s" class="d-block">
                                <span v-if="v" class="text-success nobreak">
                                    <span class="fas fa-fw fa-check"></span> @{{s}}
                                </span>
                                <span v-else class="text-danger nobreak">
                                    <span class="fas fa-fw fa-times"></span> @{{s}}
                                </span>
                            </span>
                        </span>
                        <span v-else>
                            <span v-if="entrant.valid" class="fas fa-fw fa-check text-success"></span>
                            <span v-else class="fas fa-fw fa-times text-danger"></span>
                        </span>
                    </td>
                    <td>@{{entrant.name}}</td>
                    <td>@{{entrant.gender_display}}</td>
                    <td>@{{entrant.dob}}</td>
                    <td>@{{entrant.number}}</td>
                    <td>
                        <ul v-if="Array.isArray(entrant.issues)">
                            <li v-for="(issue, index) in entrant.issues" :key="index">
                                @{{issue}}
                            </li>
                        </ul>
                        <ul v-else>
                            <li v-for="(issues, sanction) in entrant.issues" :key="sanction">
                                Sanction <span>@{{sanction}}</span> :
                                <ul>
                                    <li v-for="(issue, index) in issues" :key="index">
                                        <span>@{{issue}}</span>
                                    </li>
                                </ul>
                            </li>
                        </ul>
                    </td>
                </tr>
            </tbody>
        </table>                    
    </div>
</div>