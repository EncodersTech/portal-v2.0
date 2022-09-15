<template>
    <div>
        <div class="small" :class="{ 'd-none': !isLoading }">
            <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true">
            </span> Importing coaches, please wait ...
        </div>

        <div class="alert alert-danger" :class="{ 'd-none': !isError }">
            <span class="fas fa-fw fa-times-circle"></span> Whoops !<br/>
            <div class="mt-1" v-html="errorMessage"></div>
        </div>

        <div :class="{'d-none': isLoading || isError }" class="mt-1">
            <div :class="{'d-none': step != 1}">
                <div class="alert alert-warning small" :class="{'d-none' : usaigc_no != null }">
                    <strong>
                        <span class="fas fa-exclamation-triangle"></span> USAIGC Server import is unavailable
                    </strong>
                    because you have not specified your gym's membership number.
                    <br/>
                    Please provide your USAIGC membership in your 
                    <a :href="'/gyms/' + gym" class="alert-link">
                        <span class="fas fa-link"></span> Gym Profile Page
                    </a> to enable server imports.
                </div>

                <div class="mb-3 mt-2">
                    <span class="fas fa-fw fa-check-double"></span> I want to import :
                </div>

                <div class="mb-2">
                    <div  :class="{'d-none': usaigc_no == null }">
                        <button class="btn btn-info w-100" @click="importServer('usaigc')">
                            <span class="fas fa-server"></span> USAIGC Membership IGC{{ usaigc_no }}
                        </button>

                        <div class="border-bottom border-secondary mt-3 mb-3">
                        </div>
                    </div>
                    <div  :class="{'d-none': nga_no == null }">
                        <button class="btn btn-info w-100" @click="importServer('nga')">
                            <span class="fas fa-server"></span> NGA Membership No {{ nga_no }}
                        </button>

                        <div class="border-bottom border-secondary mt-3 mb-3">
                        </div>
                    </div>

                    <button class="btn btn-primary w-100" @click="importCSV">
                        a <span class="fas fa-file-csv"></span> CSV file
                    </button>
                </div>
            </div>

            <div :class="{'d-none': step != 2}" class="mt-1">
                <form ref="form" :action="'/gyms/' + gym + '/coaches/import'"
                    method="POST" enctype="multipart/form-data">
                    
                    <input type="hidden" name="_token" :value="csrf">
                    <input type="hidden" name="body" :value="body">
                    <input type="hidden" name="method" :value="requiresFile ? 'csv' : 'api'">
                    <input type="hidden" name="duplicates" :value="duplicates">
                    <input type="hidden" name="delimiter" :value="delimiter">

                    <div v-if="requiresFile">                
                        <div class="mb-1">
                            <span class="fas fa-fw fa-file-upload"></span> Choose a file to upload :
                        </div>
                        
                        <div class="mb-2">
                            <div class="form-group">
                                <div class="custom-file">
                                    <input type="file" class="custom-file-input" id="import-file"
                                        accept=".csv" name="csv_file" @change="processFile($event)">
                                    <label class="custom-file-label" for="import-file">
                                        {{ chosenFile != null ? chosenFile.name : 'Choose a CSV file' }}
                                    </label>
                                </div>
                                <div class="text-danger small mt-1 mb-1" :class="{'d-none': !showFileError}">
                                    Please choose a file to upload.
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mb-1">
                        <span class="fas fa-fw fa-question-circle"></span>
                        When duplicates of existing coaches are encountered :
                    </div>

                    <div class="mb-2">
                        <div class="form-check">
                            <input class="form-check-input" type="radio" id="duplicates-ignore" value="ignore"
                                v-model="duplicates" checked>
                            <label class="form-check-label" for="duplicates-ignore">
                                Keep existing data (ignore imported duplicate)
                            </label>
                        </div>

                        <div class="form-check">
                            <input class="form-check-input" type="radio" id="duplicates-overwrite" 
                                value="overwrite" v-model="duplicates" checked>
                            <label class="form-check-label" for="duplicates-overwrite">
                                Overwrite with imported data
                            </label>
                        </div>

                        <div class="form-check">
                            <input class="form-check-input" type="radio" id="duplicates-fail" value="fail"
                                v-model="duplicates" checked>
                            <label class="form-check-label" for="duplicates-fail">
                                Add to failed import list and let me decide later
                            </label>
                        </div>
                    </div>

                    <div class="small mt-1 mb-1 pt-1 border-top border-secondary" :class="{'d-none': !requiresFile}">
                        <label class="control-label" for="delimiter-select">
                            (Optional) Select a delimiter :
                        </label>
                        <select class="form-control form-control-sm w-auto ml-1 d-inline-block"
                            id="delimiter-select" v-model="delimiter">
                            <option v-for="d in validDelimiters" :key="d.id" :value="d.value">
                                {{ d.name }}
                            </option>
                        </select>
                    </div>
                </form>
            </div>

            <div class="d-flex flex-row flex-nowrap mt-3">
                <div class="flex-grow-1">
                    <button class="btn btn-secondary" :class="{'d-none': (step < 2)}" @click="previousStep">
                        <span class="fas fa-fw fa-chevron-left"></span> Back
                    </button>
                </div>

                <div class="">
                    <button class="btn btn-success" :class="{'d-none': (step == 1)}" @click="nextStep">
                        <span :class="{'d-none': step == maxStep}">
                            Next <span class="fas fa-fw fa-chevron-right"></span>
                        </span>
                        <span :class="{'d-none': step != maxStep}">
                            <span class="fas fa-fw fa-file-import"></span> Import
                        </span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>
    
<script>
    export default {
        name: 'AthleteImport',
        props: {
            usaigc_no: {
                default: null,
                type: String
            },
            nga_no: {
                default: null,
                type: String
            },
            gym: Number,
            csrf: String,
        },
        data() {
            return {
                isLoading: false,
                isError: false,
                errorMessage: '',
                fileInputLabel: 'Choose a CSV file to import',
                step: 1,
                maxStep: 2,
                body: null,
                duplicates: 'ignore',
                requiresFile: false,
                chosenFile: null,
                showFileError: false,
                delimiter: ',',
                validDelimiters: [
                    {
                        id: 0,
                        name: 'Comma (Default)',
                        value: ','
                    },
                    {
                        id: 1,
                        name: 'Semicolon',
                        value: ';'
                    }
                ],
            }
        },
        methods: {
            importCSV(body) {
                this.body = null;
                this.requiresFile = true;
                this.step++;
            },

            importServer(body) {
                this.body = body;
                this.requiresFile = false;
                this.step++;
            },

            processFile(e) {
                let files = e.target.files;

                this.showFileError = false;

                if (files.length > 0)
                    this.chosenFile = files[0];
                else
                    this.chosenFile = null;
            },

            nextStep() {
                if ((this.step == 2) && (this.requiresFile && (this.chosenFile == null))) {
                    this.showFileError = true;
                    return;
                }

                if (this.step == this.maxStep) {
                    this.isLoading = true;
                    this.$refs.form.submit();
                    return;
                }

                this.step++;
            },

            previousStep() {
                this.step--;
            }
        },
        mounted() {                  
            
        }
    }
</script>
