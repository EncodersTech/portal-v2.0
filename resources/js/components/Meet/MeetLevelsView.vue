<template>
    <div>
        <div class="small" :class="{ 'd-none': !isLoading }">
            <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true">
            </span> Loading {{ plural }}, please wait ...
        </div>

        <div class="alert alert-danger" :class="{ 'd-none': !isError }">
            <span class="fas fa-fw fa-times-circle"></span> Whoops !<br/>
            <div class="mt-1" v-html="errorMessage"></div>
        </div>

        <div :class="{'d-none': isLoading || isError }">
            <div class="alert alert-info" :class="{ 'd-none': !hasNoItems }">
                <span class="fas fa-info-circle"></span> No {{ plural }} added.
            </div>

            <div v-if="!hasNoItems">
                <div v-for="body in items" :key="body.path" class="mb-2">
                    <div class="mb-1">
                        <button class="btn btn-sm btn-dark btn-block text-left left-btn"
                            type="button" @click="body.expanded = !body.expanded">
                            <span class="fas fa-fw fa-receipt"></span>
                            {{ body.name }}
                            <span :class="'fas fa-fw fa-caret-' + (body.expanded ? 'down' : 'right')"></span>
                        </button>
                    </div>

                    <div v-if="body.expanded" class="ml-3">
                        <div v-for="category in body.categories" :key="category.path" class="mb-1">
                            <div class="mb-1">
                                <button class="btn btn-sm btn-info btn-block text-left left-btn"
                                    type="button" @click="category.expanded = !category.expanded">
                                    <span class="fas fa-fw fa-receipt"></span>
                                    {{ category.name }}
                                    <span :class="'fas fa-fw fa-caret-' + (category.expanded ? 'down' : 'right')"></span>
                                </button>
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
                                                    <div v-if="late">
                                                        <strong>Late:</strong>
                                                        ${{ level.late_registration_fee}}
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
        name: 'MeetLevelsView',
        props: {
            meet:{
                type: Object
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
                type: Object,
                default: []
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
                };
            },
        },
        data() {
            return {
                isLoading: false,
                isError: false,
                hasNoItems: false,
                errorMessage: '',
                initiate: true,
                warnMessage: null,
                items: [],
                first_discount_enable: this.meet.registration_first_discount_is_enable,
                second_discount_enable: this.meet.registration_second_discount_is_enable,
                third_discount_enable: this.meet.registration_third_discount_is_enable
            }
        },
        methods: {
            hasSpecialist(body, category) {
                return (body.id == this.constants.bodies.USAIGC)
                    && (category.id == this.constants.categories.GYMNASTICS_WOMEN || body.id == this.constants.bodies.NGA);
            },

            numberFormat(n) {
                try {
                    let fee = Utils.toFloat(n);
                    return (fee === null ? n : fee.toFixed(2));
                } catch (e) {
                    return n;
                }
            },

            levelUniqueId(level) {
                return level.id + (level.male ? '-m' : '') + (level.female ? '-f' : '');
            },
        },
        mounted() {
            try {
                for (let i in this.initial) {
                    if (this.initial.hasOwnProperty(i)) {
                        let body = this.initial[i];

                        body.name = i;
                        body.expanded = true;

                        for (let j in body.categories) {
                            if (body.categories.hasOwnProperty(j)) {
                                let category = body.categories[j];

                                category.name = j;
                                category.expanded = true;

                                for (let k in category.levels) {
                                    if (category.levels.hasOwnProperty(k)) {
                                        let level = category.levels[k];

                                        level.male = level.pivot.allow_men;
                                        level.female = level.pivot.allow_women;
                                        level.registration_fee = Utils.toFloat(level.pivot.registration_fee).toFixed(2);
                                        
                                        level.registration_fee_first = Utils.toFloat(level.pivot.registration_fee_first).toFixed(2);

                                        level.late_registration_fee = Utils.toFloat(level.pivot.late_registration_fee).toFixed(2);
                                        level.allow_specialist = level.pivot.allow_specialist;
                                        level.specialist_registration_fee = Utils.toFloat(level.pivot.specialist_registration_fee).toFixed(2);
                                        level.specialist_late_registration_fee = Utils.toFloat(level.pivot.specialist_late_registration_fee).toFixed(2);
                                        level.allow_team = level.pivot.allow_teams;
                                        level.team_registration_fee = Utils.toFloat(level.pivot.team_registration_fee).toFixed(2);
                                        level.team_late_registration_fee = Utils.toFloat(level.pivot.team_late_registration_fee).toFixed(2);
                                        level.enable_athlete_limit = level.pivot.enable_athlete_limit;
                                        level.athlete_limit = level.pivot.athlete_limit;
                                    }
                                }
                            }
                        }

                        this.items.push(body);
                    }
                }
                this.hasNoItems = this.items.length < 1;
            } catch (error) {
                this.errorMessage = error + '<br/>Please reload this page.';
                this.isError = true;
            }
        }
    }
</script>
