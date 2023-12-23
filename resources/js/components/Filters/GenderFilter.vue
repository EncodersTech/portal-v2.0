<template>
    <div>
        <label for="filter_gender" class="small mb-1">
            <span class="fas fa-fw fa-venus-mars"></span> Gender
        </label>
        <select id="filter_gender" v-model="genderInternal" @change="$emit(`gender-changed`, genderInternal)"
            class="form-control form-control-sm">
            <option value="all">All</option>
            <option value="female">Female</option>
            <option value="male">Male</option>            
        </select>
    </div>
</template>
    
<script>

    export default {
        name: 'GenderFilter',
        data() {
            return {
                genderInternal: 'all'
            }
        },
        props: {
            gender: {
                defaul: 'male',
                type: String
            }
        },
        watch: {
            gender() {
                this.genderInternal = this.gender;
            }   
        },
        methods: {
        },
        beforeMount() {
            
        }
    }
</script>
