<template>
    <div>
        <div class="input-group input-group-sm">
            <input type="text" class="form-control search-field"
                :class="{'border-right-0': (searchText != '')}"
                v-model="searchText" :placeholder="placeholder">
            
            <div class="input-group-append">
                <button class="btn btn-outline-danger" :class="{'d-none': (searchText === '')}"
                    @click="searchText = ''" type="button" title="Clear Search Box" >
                    <span class="fas fa-fw fa-eraser"></span>
                </button>
            </div>
        </div>
    </div>
</template>

<style scoped>
    .search-field:focus {
        box-shadow: none;
    }
</style>
   
<script>
    export default {
        name: 'SearchField',
        props: {
            placeholder: {
                type: String,
                default: 'Search ...'
            },
            identifier: {
                type: String,
                default: null
            },
        },
        watch: {
            searchText() {
                this.$emit(
                    (this.identifier !== null ? this.identifier + '-' : '') +
                    'search-field-text-changed', this.searchText
                );
            }
        },
        data() {
            return {
                searchText: ''
            }
        },
        methods: {

        }
    }
</script>
