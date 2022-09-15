<template>
    <div>
        <div class="d-flex flex-row flex-nowrap align-bodyItems-center my-2 border-bottom">
            <h6 class="flex-grow-1 font-weight-bold mb-0">
                <span class="fas fa-fw fa-filter"></span> Filters
            </h6>
            <a href="#" class="small text-danger px-1" @click="clearFilters">
                <span class="fas fa-fw fa-eraser"></span> Clear
            </a>
        </div>

        <div class="mb-3">
            <ag-gender-filter :gender="filters.gender"
                @gender-changed="updateGender">
            </ag-gender-filter>
        </div>

        <div class="mb-3">
            <ag-level-filters :selected="filters.levels"
                @level-filters-changed="updateLevels">
            </ag-level-filters>
        </div>  
    </div>
</template>
    
<script>
    import GenderFilter from '../Filters/GenderFilter.vue';
    import LevelFilters from '../Filters/LevelFilters.vue';

    export default {
        components: {
            'ag-gender-filter': GenderFilter,
            'ag-level-filters': LevelFilters,
        },
        data() {
            return {
                gender: 'all',
                levels: []
            }
        },
        computed: {
            filters() {
                return {
                    'gender': this.gender,
                    'levels': this.levels
                }
            }
        },
        methods: {
            clearFilters() {
                this.gender = 'all';
                this.levels = [];
                this.emitFiltersChanged();
            },

            updateGender(val) {
                this.gender = val;
                this.emitFiltersChanged();
            },

            updateLevels(val) {
                this.levels = val;
                this.emitFiltersChanged();
            },

            emitFiltersChanged() {
                this.$emit('athlete-filters-changed', this.filters);
            }
        },
        mounted() {
            this.emitFiltersChanged();
        }
    }
</script>
