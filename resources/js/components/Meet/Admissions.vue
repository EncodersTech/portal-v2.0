<template>
    <div>
        <input type="hidden" :name="field" :value="output" :disabled="isError">

        <div class="alert alert-danger" :class="{ 'd-none': !isError }">
            <span class="fas fa-fw fa-times-circle"></span> Whoops !<br/>
            <div class="mt-1" v-html="errorMessage"></div>
        </div>

        <div class="alert alert-warning mb-1" v-if="warnMessage != null">
            <span class="fas fa-fw fa-exclamation-triangle"></span>
            <span v-html="warnMessage"></span>
        </div>

        <div :class="{'d-none': isError }">
            <div class="alert alert-info" :class="{ 'd-none': !hasNoItems }">
                <span class="fas fa-info-circle"></span> You do not have any active {{ plural }}
            </div>

            <div class="table-responsive-lg" :class="{ 'd-none': hasNoItems }">
                <table class="table table-sm table-borderless">
                    <tbody>
                        <tr v-for="item in items" :key="item.id">
                            <td class="align-middle">
                                <input placeholder="Adult / Child / Weekend / ..."
                                    class="form-control form-control-sm" 
                                    v-model="item.name" type="text" required>
                            </td>

                            <td class="align-middle">
                                <select class="form-control form-control-sm" v-model="item.type" required>
                                    <option value="">(Choose below ...)</option>
                                    <option v-for="type in types" :value="type.value" :key="type.value">
                                        {{ type.name }}
                                    </option>
                                </select>
                            </td>

                            <td class="align-middle">
                                <div class="input-group input-group-sm">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">
                                            <span class="fas fa-fw fa-dollar-sign"></span>
                                        </span>
                                    </div>
                                    <input placeholder="0.00" v-model="item.amount" type="text"
                                        class="form-control" :disabled="item.type != 2" required>
                                </div>
                            </td>

                            <td class="buttons-column text-right align-middle">
                                <button class="btn btn-block btn-sm btn-danger" title="Remove"
                                    @click="removeItem(item)" type="button">
                                    <span class="fas fa-fw fa-trash"></span>
                                </button>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="3" class="small text-muted align-middle">
                                <span class="fas fa-info-circle"></span>
                                Click the button to the right to add more
                            </td>
                            <td class="buttons-column align-middle">
                                <button class="btn btn-block btn-sm btn-success" title="Add one"
                                    @click="addItem" type="button" >
                                    <span class="fas fa-fw fa-plus"></span>
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</template>

<style scoped>
    .buttons-column {
        width: 52px
    }

    .invisible-field {
        height: 1px;
        widows: 100%;
        visibility: hidden;
    }
</style>


<script>
import { constants } from 'crypto';
    export default {
        name: 'Admissions',
        props: {
            singular: {
                type: String,
                default: 'item'
            },
            plural: {
                type: String,
                default: 'items'
            },
            values: {
                type: Array,
                default: []
            },
            field: {
                type: String,
                default: 'admissions'
            }
        },
        computed: {
            types() {
                return {
                    free: {
                        name: 'Free',
                        value: 1
                    },
                    paid: {
                        name: 'Fee',
                        value: 2
                    },
                    tbd: {
                        name: 'TBD',
                        value: 3
                    }
                };
            },
            output() {
                try {
                    this.warnMessage = null;
                    let items = this.items.filter(item => (item.name != ''));

                    if (this.isError)
                        return '';

                    for (let i in this.items) {
                        if (this.items.hasOwnProperty(i)) {
                            let item = this.items[i];                            
                            if (item.type == this.types.paid.value) {
                                let fee = Utils.toFloat(item.amount);
                                if ((fee === null) || (fee < 0))
                                    throw 'Please enter a valid admission fee';
                            }
                        }
                    }
                        
                    return (items.length < 1 ? '' : JSON.stringify(items));
                } catch (error) {
                    this.warnMessage = 'Something went wrong while compiling your admissions.<br/>' + error;
                };
            }
        },
        data() {
            return {
                isError: false,
                isBusy: false,
                hasNoItems: false,
                errorMessage: '',
                items: [],
                debounce_delay: 500,
                warnMessage: null,
            }
        },
        methods: {
            addItem() {
                if (this.items.length > 0) {
                    let item = this.items[this.items.length - 1];
                    if (item.name == '')
                        return;
                }

                this.items.push({
                    name: '',
                    type: this.types.free.value,
                    amount: 0.00
                });
            },

            removeItem(item) {
                let itemIndex = this.items.indexOf(item);
                this.items.splice(itemIndex, 1);

                if (this.items.length < 1)
                    this.addItem();
            },
        },
        mounted() {
            try {
                let issues = [];
                for (let i in this.values) {
                    if (this.values.hasOwnProperty(i)) {
                        let item = this.values[i];
                        item.id = _.uniqueId('item-');
                        
                        if (item.type == this.types.paid.value) {
                            let fee = Utils.toFloat(item.amount);
                            if ((fee === null) || (fee < 0))
                                issues.push('Invalid admission fee');
                            item.amount = fee.toFixed(2);
                        }

                        this.items.push(item);
                    }
                }

                if (this.items.length < 1)
                    this.addItem();

                if (issues.length > 0)
                    throw 'Invalid initial data';
            } catch (error) {
                this.warnMessage = 'Something went wrong while loading your existing admissions.<br/>';
            };
        }
    }
</script>
