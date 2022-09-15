<template>

    <div>
        <input type="hidden" :name="field" :value="output" :disabled="isError">
        <div class="small" :class="{ 'd-none': !isLoading }">
            <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true">
            </span> Loading athletes, please wait ...
        </div>

        <div class="alert alert-danger" :class="{ 'd-none': !isError }">
            <span class="fas fa-fw fa-times-circle"></span> Whoops !<br/>
            <div class="mt-1" v-html="errorMessage"></div>
        </div>

        <div class="alert alert-warning" v-if="warnMessage != null">
            <span class="fas fa-fw fa-exclamation-triangle"></span>
            <span v-html="warnMessage"></span>
        </div>

        <div class="row" :class="{ 'd-none': isError}">
            <div v-for="body in bodies" :key="body.id" class="col-lg mb-3 column-split">
                <div>
                    <span class="fas fa-clipboard-check"></span> {{ body.name }}
                </div>
                <div v-for="category in body.categories" :key="category.path" class="mt-1">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" :id="'category-' + category.path"
                            v-model="category.check" :disabled="category.locked">
                        <label class="form-check-label" :for="'category-' + category.path">
                            {{ category.name }}<br/>
                            <span v-if="category.check && category.officially_sanctioned" class="small mb-1">
                                Assigned sanction: <strong>{{ category.sanction }}</strong>
                            </span>
                        </label>
                    </div>
                </div>
                
                <div :id="'sanc-' + body.id" v-bind:class="(body.id == 1) ? 'hide-this' : '' ">
                    <input class="form-control-sm" :id="'sanc-body-'+body.id" :readonly="body.locked" placeholder="Sanction no" :name="'sanction_body_no['+body.id+']'" v-model="body.categories[0].sanction"/>
                </div>
            </div>
        </div>
    </div>
</template>

<style>
.hide-this{
    display: none;
}
</style>
<style scoped>
.column-split:first-child {
    border: none;
}
</style>


<script>
    export default {
        name: 'MeetCategories',
        props: {
            available_bodies: {
                type: Object,
                default: {},
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
                default: 'categories'
            },
            restricted: {
                type: Boolean,
                default: false
            },
            restricted_bodies: {
                type: Object,
                default: () => ({
                    1: {},   // USAG
                    2: {},   // USAIGC
                    3: {},   // AAU
                    4: {},   // NGA
                })
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
        watch: {
            loading() {
                this.isLoading = this.loading;
            },
            error() {
                this.isError = this.error;
            },
            errormessage() {
                this.errorMessage = this.errormessage;
            },
            bodies: {
                deep: true,
                handler() {
                    let result = [];

                    for (const i in this.bodies) {
                        let body = this.bodies[i];

                        for (const j in body.categories) {
                            let category = body.categories[j];

                            if (category.check) {
                                result.push({
                                    body_id: body.id,
                                    id: category.id,
                                    sanction: category.sanction, //(body.id == this.constants.bodies.USAG ? category.sanction : null),
                                    officially_sanctioned: category.officially_sanctioned,
                                });
                            }
                        }
                    }
                    this.selected = result;

                    this.$emit('meet-categories-changed', this.selected);
                }
            }
        },
        data() {
            return {
                isLoading: false,
                isError: false,
                errorMessage: false,
                bodies: [],
                selected: [],
                warnMessage: null,
            }
        },
        computed: {
            constants() {
                return {
                    bodies: {
                        USAG: 1,
                        USAIGC: 2,
                        AAU: 3,
                        NGA: 4,
                    },
                };
            },
            output() {
                try {
                    if (this.isError)
                        return '';

                    return (this.selected.length < 1 ? '' : JSON.stringify(this.selected));
                } catch (error) {
                    this.errorMessage = 'Something went wrong while compiling your admissions.';
                    this.isError = true;
                };
            }
        },
        methods: {
        },
        mounted() {
            try {
                let isWarn = false;
                let locked = true;
                for (const i in this.available_bodies) {
                    let body = this.available_bodies[i];
                    locked = false;
                    for (const j in body.categories) {
                        let category = body.categories[j];

                        category.check = false;
                        category.sanction = null;
                        category.officially_sanctioned = false;
                        category.locked = Boolean(this.restricted_bodies[body.id][category.id]) ||
                                        (
                                            this.requires_sanction[body.id]
                                            && category.officially_sanctioned
                                        );
                        if(category.locked)
                            locked = true;
                    }
                    body.locked = locked;
                    this.bodies.push(body);
                }

                for (const i in this.initial) {
                    const item = this.initial[i];

                    let body = this.bodies.find(body => body.id == item.body_id);
                    if (!body)
                        throw "Invalid initial data (body)";

                    let category = body.categories.find(category => category.id == item.id);
                    if (!category)
                        throw "Invalid initial data (category)";

                    category.check = true;
                    category.sanction = item.sanction;
                    category.officially_sanctioned = Boolean(item.officially_sanctioned);
                    category.locked = this.restricted_bodies[body.id][category.id] ||
                                        (
                                            this.requires_sanction[body.id]
                                            && category.officially_sanctioned
                                        );
                }
            } catch (error) {
                this.warnMessage = 'Something went wrong while loading your selected categories.<br/>' +
                    'Please select them again or, alternatively, reload this page to try again.<br/>' +
                    '<span class="small">Details: ' + error + '</span>';
            }
        }
    }
</script>
