<template>

    <div>
        <div class="alert alert-danger" v-if="isError">
            <span class="fas fa-fw fa-times-circle"></span> Whoops !<br/>
            <div class="mt-1" v-html="errorMessage"></div>
        </div>

        <div class="alert alert-warning" v-if="warnMessage != null">
            <span class="fas fa-fw fa-exclamation-triangle"></span>
            <span v-html="warnMessage"></span>
        </div>

        <div v-if="!isError">
            <div class="row mb-3">
                <div class="col">
                    <h6 class="border-bottom">
                        <span class="fas fa-fw fa-file-upload"></span>
                        Schedule
                    </h6>
                    <input type="hidden" name="keep_schedule" :value="keepSchedule ? 1 : 0">
                    <div v-if="schedule == null">
                        <div class="custom-file">
                            <input type="file" class="custom-file-input" id="meet_schedule"
                                name="schedule" ref="schedule_input" @change="updateScheduleLabel">
                            <label class="custom-file-label hidden-overflow" for="meet_schedule"
                                ref="schedule_label">
                                Choose a file ...
                            </label>
                        </div>
                    </div>
                    <div v-else class="d-flex flex-row flex-nowrap pl-3">
                        <div class="flex-grow-1 align-middle">
                            <a :href="schedule.path" target="_blank">
                                <span class="fas fa-file"></span> {{ schedule.name }}
                            </a>
                        </div>
                        <div class="ml-3">                                            
                            <button class="btn btn-sm btn-danger" type="button"
                                title="Remove" @click="removeSchedule">
                                <span class="fas fa-fw fa-trash"></span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col">
                    <h6 class="border-bottom">
                        <span class="fas fa-fw fa-cloud-upload-alt"></span>
                        Attachments
                    </h6>
                    <div class="text-right mb-1">
                        <button class="btn btn-sm btn-success" type="button"
                            title="Add File" @click="addFile()">
                            <span class="fas fa-fw fa-plus"></span> Add a File
                        </button>
                    </div>
                    <div v-if="files.length < 1" class="alert alert-info font-weight-bold">
                        <span class="fas fa-info-circle"></span> No files added.<br/>
                        If you would like to add additional files (i.e. Directions, Sizing Charts, Order Forms, etc.) please select "Add a file".
                        You will be able to elect a file and enter a title/description.
                    </div>
                    <div v-else>
                        <table class="table table-sm table-hover">
                            <thead class="thead-light">
                                <tr>
                                    <th scope="col" class="align-middle">
                                        Name
                                    </th>
                                    <th scope="col" class="align-middle">
                                        Description
                                    </th>
                                    <th scope="col" class="text-right align-middle"></th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="file in files" :key="file.id">
                                    <td class="align-middle">
                                        <div v-if="file.isNew" class="custom-file">
                                            <input type="file" class="custom-file-input" :id="'input-' + file.id"
                                                name="files[]" :ref="'input-' + file.id"
                                                @change="updateInputLabel(file)" required>
                                            <label class="custom-file-label hidden-overflow"
                                                for="'input-' + file.id">
                                                    {{ file.name != null ? file.name : 'Choose a file ...' }}
                                            </label>
                                        </div>
                                        <div v-else>
                                            <input type="hidden" name="uploaded_files[]" :value="file.path">
                                            <a :href="file.path" target="_blank">
                                                <span class="fas fa-file"></span> {{ file.name }}
                                            </a>
                                        </div>
                                    </td>
                                    <td class="align-middle">
                                        <div v-if="file.isNew">
                                            <input type="text" name="description[]" placeholder="Description ..."
                                                class="form-control" v-model="file.description"
                                                :disabled="file.name == null">
                                        </div>
                                        <div v-else>
                                            {{ file.description }}
                                        </div>
                                    </td>
                                    <td class="text-right align-middle">
                                        <button class="btn btn-sm btn-danger" type="button"
                                            title="Remove" @click="removeFile(file)">
                                            <span class="fas fa-fw fa-trash"></span>
                                        </button>
                                    </td>                                   
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
    .column-split:first-child {
        border: none;
    }

    .hidden-overflow {
        overflow: hidden;
    }
</style>


<script>
    export default {
        name: 'MeetFiles',
        props: {
            limit: {
                type: Number,
                default: 5,
            },
            initial_files: {
                type: Array,
                default: []
            },
            initial_schedule: {
                type: Object,
                default: null
            }
        },
        watch: {
        },
        data() {
            return {
                isError: false,
                errorMessage: false,
                files: [],
                schedule: null,
                keepSchedule: true,
                warnMessage: null,
            }
        },
        computed: {
        },
        methods: {
            addFile() {
                if (this.files.length > 0) {
                    if (this.files.length == this.limit)
                        return;

                    let newFiles = this.files.filter(f => {
                        if (!f.isNew)
                            return false;

                        return (this.$refs['input-' + f.id][0].files.length < 1);
                    });

                    if (newFiles.length > 0)
                        return;
                }

                this.files.push({
                    id: _.uniqueId(),
                    name: null,
                    path: null,
                    description: null,
                    isNew: true
                });
            },

            removeSchedule() {
                this.confirmAction(
                    'Do you really want to remove ' + this.schedule.name + ' ?',
                    'red',
                    'fas fa-trash',
                    () => {                      
                        this.schedule = null;
                        this.keepSchedule = false;
                    },
                    this
                );
            },

            removeFile(file) {
                this.confirmAction(
                    'Do you really want to remove ' + file.name + ' ?',
                    'red',
                    'fas fa-trash',
                    () => {                      
                        let itemIndex = this.files.indexOf(file);
                        this.files.splice(itemIndex, 1);
                    },
                    this
                );
            },

            updateInputLabel(file) {
                let input = this.$refs['input-' + file.id][0];

                if (input.files && (input.files.length > 0))
                    file.name = input.files[0].name;
            },

            updateScheduleLabel() {
                let input = this.$refs.schedule_input;
                let label = this.$refs.schedule_label;

                if (input.files && (input.files.length > 0))
                    label.innerHTML = input.files[0].name;
            },

            confirmAction(msg, color, icon, callback, context) {            
                $.confirm({
                    title: 'Are you sure ?',
                    content: msg,
                    icon: icon,
                    type: color,
                    typeAnimated: true,
                    buttons: {
                        no: function () {
                            this.close();
                        },
                        confirm:  {
                            text: 'Yes',
                            btnClass: 'btn-' + color,
                            action: function () {
                                callback();
                            }
                        }
                    }
                });
            },
        },
        mounted() {
            try {

                if (this.initial_schedule != null) {
                    this.schedule = {...this.initial_schedule};
                }

                if (this.initial_files.length > 0) {
                    this.files = [...this.initial_files];

                    this.files.forEach(f => {
                        f.id = _.uniqueId()
                        f.isNew = false;
                    });
                    
                    
                    if (this.files.length > this.limit) {
                        this.files.length = this.limit;
                        this.warnMessage = 'More than ' + this.limit + ' files have been uploaded.' +
                        ' The extraneous items have been discarded.';
                    }
                }
            } catch (error) {
                this.warnMessage = 'Something went wrong while loading your uploaded files.<br/>' +
                    'Please select them again or, alternatively, reload this page to try again.<br/>' + 
                    '<span class="small">Details: ' + error + '</span>';
            }
        }
    }
</script>
