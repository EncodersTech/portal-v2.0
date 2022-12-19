<div class="modal fade" id="modal-confirm-deposit" tabindex="-1" role="dialog" aria-labelledby="modal-confirm-check" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-primary">
                    <span class="fas fa-check"></span> Confirm Deposit
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span class="fas fa-times" aria-hidden="true"></span>
                </button>
            </div>

            <div class="modal-body">
                <div>
                    Are you sure to add this deposit amount ?
                </div>
                <div class="container-fluid">
                    <div class="text-right mt-3">
                        <button class="btn btn-sm btn-secondary mr-1" data-dismiss="modal">
                            <span class="far fa-fw fa-times-circle"></span> Close
                        </button>
                        <button class="btn btn-sm btn-success"
                            @click="sendDepositConfirmation(depositVar)">
                            <span class="fas fa-fw fa-check"></span> Confirm
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modal-confirm-deposit-edit" tabindex="-1" role="dialog" aria-labelledby="modal-confirm-check-edit" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-primary">
                    <span class="fas fa-check"></span> Edit Deposit @{{ depositVarEdit.gym_id }}
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span class="fas fa-times" aria-hidden="true"></span>
                </button>
            </div>

            <div class="modal-body">
                <div class="d-flex flex-row flex-no-wrap mb-3">
                    <div style="width: 50%;">
                        <select class="form-control form-control-sm" id="select3" ref="selectedItem" > 
                            <option v-for="(item , index) in allGym" v-bind:key="index"  
                                :value="item.id" :selected="depositVarEdit.gym_id" >
                                @{{ item.name + ' - ' +item.city +', '+ item.state.code +', '+depositVarEdit.gym_id }}
                            </option>
                        </select>
                    </div>

                    <div class="ml-1">
                        <input type="number" class="form-control form-control-sm" v-model="depositVarEdit.amount" value="depositVar.amount"  placeholder="Deposit Amount">
                    </div>
                </div>
                <div class="container-fluid">
                    <div class="text-right mt-3">
                        <button class="btn btn-sm btn-secondary mr-1" data-dismiss="modal">
                            <span class="far fa-fw fa-times-circle"></span> Close
                        </button>
                        <button class="btn btn-sm btn-success"
                            @click="sendDepositEdit(depositVarEdit)">
                            <span class="fas fa-fw fa-check"></span> Confirm
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<div class="d-flex flex-row flex-no-wrap mb-3">
    <div style="width: 33%;">                
        <select class="form-control form-control-sm" id="select2" v-model="depositVar.gymId">
            <option value="">Select Gym</option>
            <option v-for="g in allGym" :key="g['id']"
                :value="g['id']">@{{ g['name'] + ' - ' +g['city'] +', ' + g['email']}}
            </option>
        </select>
    </div>

    <div class="ml-1">
        <input type="number" class="form-control form-control-sm" v-model="depositVar.amount"  placeholder="Deposit Amount">
    </div>

    <div class="ml-2">
        <button type="submit" class="btn btn-success btn-sm" @click="confirmGeneral(depositVar)">Deposit</button>
    </div>
</div>


<div v-if="transactionsFiltering">
    <div class="small text-center py-3">
        <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true">
        </span> Loading, please wait ...
    </div>
</div>
<div v-else>
    <div v-if="depositGym.length > 0">
        <div class="table-responsive-lg">
        
            <table class="table table-sm table-striped">
                <thead class="thead-dark">
                    <tr>
                        <th scope="col" class="align-middle" @click="sortBy('name')">
                            Gym
                            <span v-if="sortColumn == 'name'">
                                <span :class="'fas fa-fw fa-caret-' + sortDirection"></span>
                            </span>
                        </th>
                        <th scope="col" class="align-middle">
                            Created At
                        </th>
                        <th scope="col" class="align-middle">
                            Amount
                        </th>
                        <th scope="col" class="align-middle">
                            Status
                        </th>
                        <th scope="col" class="align-middle">
                            Updated At
                        </th>
                        <th scope="col text-right" class="align-middle">
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="tx in depositGym" :key="tx.processor_id">
                        <td class="align-middle">
                            
                            @{{ tx.gym_name }}
                        </td>
                        <td class="align-middle">
                            @{{ tx.created_at_display }}
                        </td>
                        <td class="align-middle">
                            $@{{ tx.amount }}
                        </td>
                        <td class="align-middle">
                            <div v-if="!tx.is_enable">
                                <span class="badge badge-danger">Disabled</span>
                            </div>
                            <div v-else-if="tx.is_used">
                                <span class="badge badge-success">Used</span>
                            </div>
                            <div v-else>
                                <span class="badge badge-warning">Unused</span>
                            </div>
                        </td>

                        <td class="align-middle">
                            @{{ tx.updated_at_display }}
                        </td>
                        <td scope="col" class="align-middle">
                            <div class="text-right">
                                <button v-if="!tx.is_used && tx.is_enable" title="Disable Deposit"
                                    class="btn btn-sm btn-danger mr-1" @click="disableDeposit(tx)">
                                    <span class="fas fa-fw fa-times"></span>
                                </button>

                                <button v-if="!tx.is_used && !tx.is_enable" title="Enable Deposit"
                                    class="btn btn-sm btn-success mr-1" @click="enableDeposit(tx)">
                                    <span class="fas fa-fw fa-check"></span>
                                </button>

                                <button v-if="!tx.is_used && tx.is_enable" title="Edit"
                                    class="btn btn-sm btn-success mr-1" @click="editDeposit(tx)">
                                    <span class="fas fa-fw fa-edit"></span>
                                </button>

                                

                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
    <div v-else class="text-info">
        <span class="fas fa-info-circle"></span> No Deposit.
    </div>
</div>