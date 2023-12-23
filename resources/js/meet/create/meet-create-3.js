require('../../main');

window.Vue = require('vue');

Vue.component('ag-meet-categories', require('../../components/Meet/MeetCategories.vue').default);
Vue.component('ag-meet-levels', require('../../components/Meet/MeetLevels.vue').default);

$(document).ready(e => {   

    const app = new Vue({
        el: '#app',
        data: {
            today: new Date(),
            isError: false,
            isLoading: false,
            errorMessage: '',
            bodies: [],
            selected_categories: [],
            step: 1,
            sanc: [],
            sanc_body_no:[],
            is_selected_body: []
        },
        computed: {
            bodyCategories() {
                let result = {};

                for (const i in this.bodies) {
                    const body = this.bodies[i];
                    entries = [];
                    for (const j in body.categories) {
                        const category = body.categories[j];
                        entries.push({
                            id: category.id,
                            name: category.name,
                            path: category.path
                        });
                    }
                    result[body.name] = {
                        name: body.name,
                        id: body.id,
                        categories: entries,
                        sanction_name:this.sanc_body_no
                    };
                }
                return result;
            },
        },
        methods: {
            onMeetCategoriesChanged(val) {
                this.selected_categories = val;
                
                this.sanc.forEach(function (e) {
                    $('#sanc-' + e[0] + '-'+ e[1]).hide();
                }); 
                this.sanc = []

                this.selected_categories.forEach(e=>{
                    if(e.body_id != 1)
                    {
                        this.sanc.push([e.body_id, e.id]);
                    }
                });

                this.sanc.forEach(e=>{
                    $('#sanc-' + e[0] + '-'+ e[1]).show();
                });

                // console.log(this.selected_categories);
            },

            nextStep() {
                try {
                    switch (this.step) {
                        case 1:
                            if (this.selected_categories.length < 1)
                                throw 'Please select at least one category to proceed';
                            this.sanc_body_no = [];
                            let sbn = [];
                            let prev_record=[];
                            this.selected_categories.forEach(function (e) {
                                if (e.body_id != 1 && $.inArray(e.body_id, prev_record) == -1) {
                                    prev_record.push(e.body_id);
                                    var sv = $('#sanc-body-' + e.body_id + '-' + e.id).val();
                                    if (sv.trim().length > 0) 
                                        sbn.push([e.body_id,e.id,sv]);
                                    // else throw 'Please enter the sanction no of selected category to proceed';
                                }
                            });
                            this.sanc_body_no = sbn;
                            $('#sanction_body_no').val(JSON.stringify(sbn));
                            this.step++;
                            break;
                    
                        case 2:
                            this.$refs.competitionSettingsForm.submit();
                            break;
                    }
                } catch(e) {
                    this.showAlert(e, 'Whoops !', 'red', 'fas fa-times-circle');
                }                
            },

            previousStep() {
                switch (this.step) {
                    case 1:
                        window.location = this.$refs.previousStepLink.href;
                        break;
                
                    case 2:
                        this.step--;
                        break;
                }
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
    
            showAlert(msg, title, color, icon) {            
                $.alert({
                    title: title,
                    content: msg,
                    icon: icon,
                    type: color,
                    typeAnimated: true
                });
            },
        },
        mounted() {
            this.isLoading = true;
            axios.get('/api/app/levels').then(result => {
                for (let bodyInitialism in result.data.levels) {
                    let body = result.data.levels[bodyInitialism];
                    body.name = bodyInitialism;

                    for (let categoryName in body.categories) {
                        let category = body.categories[categoryName];
                        category.name = categoryName;
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
    });

    setupCompetitionFormatBehavior();

    function setupCompetitionFormatBehavior() {
        let select = $('#meet_competition_format_id');
        let otherField = $('#meet_competition_format_other');

        select.change(e => {
            let disable = select.val() != select.data('other');
            otherField.prop('disabled', disable);

            if (disable)
                otherField.val('');
        });

        select.change();
    }
});