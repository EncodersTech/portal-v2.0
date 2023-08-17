<div class="mt-2">
    <h5 class="pb-1 border-bottom"><span class="fas fa-fw fa-file-pdf"></span> Reports</h5>

    <div class="text-info small mt-1 mb-3">
        <span class="fas fa-info-circle"></span> You can generate up to 5 reports per minute.
        If you encounter a "Too Many Request", please wait a minute and retry.
    </div>

    <div class="row">
        <div class="col-md-3 mb-3">
            <button class="btn btn-block btn-primary" @click="generateReport(constants.reports.types.Summary)">
                Meet Summary
            </button>
        </div>

        <div class="col-md-3 mb-3">
            <button class="btn btn-block btn-secondary" @click="generateReport(constants.reports.types.MeetEntry)">
                Meet Entries
            </button>
        </div>

        <div class="col-md-3 mb-3">
            <button class="btn btn-block btn-info" @click="generateReport(constants.reports.types.RegistrationDetail)">
                Registration Detail
            </button>
        </div>

        <div class="col-md-3 mb-3">
            <button class="btn btn-block btn-warning" @click="generateReport(constants.reports.types.Entry)">
                Team Participation (Athletes)
            </button>
        </div>

        <div class="col-md-3 mb-3">
            <button class="btn btn-block btn-success" @click="generateReport(constants.reports.types.EntryNonAthletes)">
                Team Participation (Not Athletes)
            </button>
        </div>

        <div class="col-md-3 mb-3">
            <button class="btn btn-block btn-warning" @click="generateReport(constants.reports.types.Coaches)">
                Attending Gyms &amp; Coaches
            </button>
        </div>

        <div class="col-md-3 mb-3">
            <button class="btn btn-block btn-danger" @click="generateReport(constants.reports.types.Specialists)">
                Event Specialists
            </button>
        </div>
        <div class="col-md-3 mb-3" v-if="typeof constants.bodies.USAIGC !== 'undefined'">
            <button class="btn btn-block btn-warning" @click="generateReport(constants.reports.types.CoachSignin)">
                USAIGC Coach Sign In
            </button>
        </div>
        <div class="col-md-3 mb-3">
            <button class="btn btn-block btn-primary" @click="generateReport(constants.reports.types.Scratch)">
                Scratches
            </button>
        </div>

        <div class="col-md-3 mb-3">
            <button class="btn btn-block btn-secondary" @click="generateReport(constants.reports.types.Refunds)">
               Refunds
            </button>
        </div>

        <div class="col-md-3 mb-3">
            <button class="btn btn-block btn-success" @click="generateReport(constants.reports.types.ProscoreExport)">
                ProScore Export
            </button>
        </div>
        <div class="col-md-3 mb-3">
            <button class="btn btn-block btn-info" @click="generateReport(constants.reports.types.LeoTShirt)">
                Leo/T-Shirt Summary
            </button>
        </div>
        <div class="col-md-3 mb-3">
            <button class="btn btn-block btn-success" @click="generateReport(constants.reports.types.LeoTShirtGym)">
                Leo/T-Shirt Gym
            </button>
        </div>

    </div>
</div>
