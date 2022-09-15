<template>
    <div>
        <div>
            <label for="filter_gender" class="small mb-1">
                <span class="fas fa-fw fa-layer-group"></span> Levels
                <span class="small">(caret to expand)</span>
            </label>
        </div>
        
        <div class="small" :class="{ 'd-none': !isLoading}">
            <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true">
            </span> Loading levels, please wait ...
        </div>

        <div class="alert alert-danger small" :class="{ 'd-none': !isError}">
            <span class="fas fa-fw fa-times-circle"></span> <span v-html="errorMessage"></span>
        </div>

        <div class="html-tree mb-3" :class="{ 'd-none': isLoading || isError}">
            <div v-for="body in bodies" :key="body.path" class="border-bottom border-secondary mb-2 pb-1">
                <div class="form-check">
                    <input :id="'filter-body-' + body.path" class="form-check-input" type="checkbox"
                        :indeterminate.prop="body.intermediate" v-model="body.checked"
                        @change="bodyCheckChanged($event, body)">
                    <label class="form-check-label font-weight-bold" :for="'filter-body-' + body.path">
                        {{ body.name }}
                        <a :href="'#filter-body-' + body.path + '-collape'" data-toggle="collapse"
                            class="text-dark collapsed">
                            <span class="fas fa-fw tree-dropdown-caret"></span>
                        </a>
                    </label>
                </div>

                <div class="collapse ml-1 pl-2 border-left" :id="'filter-body-' + body.path + '-collape'">
                    <div v-for="category in body.categories" :key="category.path">
                        <div class="form-check">
                            <input :id="'filter-category-' + category.path" class="form-check-input"
                                @change="categoryCheckChanged($event, category)" type="checkbox"
                                v-model="category.checked" :indeterminate.prop="category.intermediate">
                            <label class="form-check-label" :for="'filter-category-' + category.path">
                                {{ category.name }}
                                <a :href="'#filter-category-' + category.path + '-collape'"
                                    data-toggle="collapse" class="text-dark collapsed">
                                    <span class="fas fa-fw tree-dropdown-caret"></span>
                                </a>
                            </label>
                        </div>
                        <div class="collapse ml-1 pl-2 border-left" :id="'filter-category-' + category.path + '-collape'">
                            <div v-for="level in category.levels" :key="level.id">
                                <div class="form-check">
                                    <input class="form-check-input" :id="'filter-level-' + level.id"
                                        @change="levelCheckChanged($event, level)" type="checkbox"
                                        v-model="level.checked">
                                    <label class="form-check-label" :for="'filter-level-' + level.id">
                                        {{ level.name }}
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
    
<script>

    export default {
        name: 'LevelFilters',
        data() {
            return {
                bodies: [],
                isLoading: false,
                isError: false,
                errorMessage: '',
                levels: []
            };
        },
        props: {
            selected: {
                default: [],
                type: Array
            }
        },
        watch: {
            selected() {
                if (this.selected.length == 0) {
                    for(let b in this.bodies) {
                        let body = this.bodies[b];
                        body.checked = false;
                        body.intermediate = false;
                        for (let c in body.categories) {
                            let category = body.categories[c];
                            category.checked = false;
                            category.intermediate = false;
                            for (let l in category.levels) {
                                let level = category.levels[l];
                                level.checked = false;
                            }
                        }
                    }
                    this.levels = [];
                }
            }
        },
        methods: {
            bodyCheckChanged(e, body) {
                for (let categoryName in body.categories) {
                    let category = body.categories[categoryName];
                    category.checked =  body.checked;
                    this.toggleAllLevels(category);
                }

                this.$emit('level-filters-changed', this.levels);
            },

            categoryCheckChanged(e, category) {
                this.toggleAllLevels(category);

                let categories = category.body.categories;
                let allChecked = true;
                let noneChecked = true;
                for (let categoryName in categories) {
                    let category = categories[categoryName];
                    noneChecked &= !category.checked;
                    allChecked &= category.checked;                        
                }

                category.body.checked = allChecked;
                category.body.intermediate = (!noneChecked && !allChecked);

                this.$emit('level-filters-changed', this.levels);
            },

            levelCheckChanged(e, level) {
                this.addRemoveLevel(level);

                let levels = level.category.levels;
                let allChecked = true;
                let noneChecked = true;
                for (let levelIndex in levels) {
                    let level = levels[levelIndex];
                    noneChecked &= !level.checked;
                    allChecked &= level.checked;                        
                }

                level.category.checked = allChecked;
                level.category.intermediate = (!noneChecked && !allChecked);

                this.$emit('level-filters-changed', this.levels);
            },

            toggleAllLevels(category) {
                for (let levelIndex in category.levels) {
                    let level = category.levels[levelIndex];
                    level.checked = category.checked;
                    this.addRemoveLevel(level);
                }
            },

            addRemoveLevel(level) {
                let levelIndex = this.levels.indexOf(level.id);
                let hasLevel = ( levelIndex > -1)

                if (level.checked) {
                    if (!hasLevel)
                        this.levels.push(level.id);
                } else {
                    if (hasLevel)
                        this.levels.splice(levelIndex, 1);
                }
            }
        },
        mounted() {
            this.isLoading = true;
            axios.get('/api/app/levels').then(result => {
                for (let bodyInitialism in result.data.levels) {
                    let body = result.data.levels[bodyInitialism];
                    body.name = bodyInitialism;
                    body.checked = false;
                    body.intermediate = false;

                    for (let categoryName in body.categories) {
                        let category = body.categories[categoryName];
                        category.name = categoryName;
                        category.checked = false;
                        category.body = body;
                        category.intermediate = false;

                        for (let levelIndex in category.levels) {
                            let level = category.levels[levelIndex];
                            level.checked = false;
                            level.category = category;
                        }
                    }
                    this.bodies.push(body);
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
        }
    }
</script>
