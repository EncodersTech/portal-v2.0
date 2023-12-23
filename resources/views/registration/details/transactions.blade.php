<div class="modal fade" id="modal-transaction-details" tabindex="-1" role="dialog" aria-labelledby="modal-transaction-details" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-primary">
                    <span class="fas fa-exchange-alt"></span> Transaction Details
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span class="fas fa-times" aria-hidden="true"></span>
                </button>
            </div>
            
            <div class="modal-body">
                <div v-if="transaction == null">
                    Something went wrong. Please try again later.
                </div>
                <div v-else class="container-fluid">
                    <div class="mb-3">
                        <div class="row">
                            <div class="col">
                                <span class="fas fa-fw fa-wallet"></span> Method
                            </div>
                            <div class="col">
                                @{{ constants.transactions.methods[transaction.method] }}

                                <span v-if="transaction.breakdown.gym.check_no">
                                    #@{{ transaction.breakdown.gym.check_no }}
                                </span>

                                <span v-if="transaction.breakdown.gym.last4">
                                    ending with @{{ transaction.breakdown.gym.last4 }}
                                </span>
                            </div>
                        </div>

                        <div class="row mt-1">
                            <div class="col">
                                <span class="fas fa-fw fa-calendar-day"></span> Initiated
                            </div>
                            <div class="col">
                                @{{ transaction.created_at_display }}
                            </div>
                        </div>

                        <div class="row mt-1">
                            <div class="col">
                                <span class="fas fa-fw fa-calendar-check"></span> Updated
                            </div>
                            <div class="col">
                                @{{ transaction.updated_at_display }}
                            </div>
                        </div>

                        <div class="row mt-1">
                            <div class="col">
                                <span class="fas fa-fw fa-info-circle"></span> Status
                            </div>
                            <div class="col">
                                <div v-if="transaction.status == constants.transactions.statuses.Completed">
                                    <span class="badge badge-success">Completed @{{ (transaction.is_deposit) ? "- Deposit" :"" }}</span>
                                </div>
        
                                <div v-else-if="transaction.status == constants.transactions.statuses.Pending">
                                    <span class="badge badge-warning">Pending</span>
                                </div>
        
                                <div v-else-if="transaction.status == constants.transactions.statuses.Canceled">
                                    <span class="badge badge-secondary">Canceled</span>
                                </div>
        
                                <div v-else>
                                    <span class="badge badge-danger">Failed</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div>
                        <div class="row">
                            <div class="col">
                                <span class="fas fa-fw fa-tasks"></span> Registration Subtotal
                            </div>
                            <div class="col">
                                $@{{numberFormat(transaction.breakdown.gym.subtotal) }}
                                
                                </div>
                        </div>
                    </div>
                    <div v-if="transaction.breakdown.gym.deposit_subtotal > 0" class="pt-1 mt-1 border-top">
                        <div class="row">
                            <div class="col">
                                <span class="fas fa-fw fa-tasks"></span> Deposit Subtotal
                            </div>
                            <div class="col">
                                $@{{ numberFormat(transaction.breakdown.gym.deposit_subtotal) }}
                            </div>
                        </div>
                    </div>
                    <div v-if="transaction.breakdown.gym.coupon > 0" class="pt-1 mt-1 border-top">
                        <div class="row">
                            <div class="col">
                                <span class="fas fa-fw fa-tasks"></span> Coupon Used
                            </div>
                            <div class="col">
                                $@{{ numberFormat(transaction.breakdown.gym.coupon) }}
                            </div>
                        </div>
                    </div>
                    <div v-if="transaction.breakdown.gym.own_meet_refund > 0" class="pt-1 mt-1 border-top">
                        <div class="row">
                            <div class="col">
                                <span class="fas fa-fw fa-user-check"></span> Own Meet Refund
                            </div>
                            <div class="col">
                                <span class="text-success">
                                    -$@{{ numberFormat(transaction.breakdown.gym.own_meet_refund) }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <div v-if="transaction.breakdown.gym.handling > 0" class="pt-1 mt-1 border-top">
                        <div class="row">
                            <div class="col">
                                <span class="fas fa-fw fa-server"></span> Handling Fee
                            </div>
                            <div class="col">
                                $@{{ (transaction.breakdown.gym.deposit_handling == 0 || isNaN(transaction.breakdown.gym.deposit_handling)) ? numberFormat(transaction.breakdown.gym.handling) : numberFormat(transaction.breakdown.gym.deposit_handling)}}
                            </div>
                        </div>
                    </div>
                    
                    <div v-if="transaction.breakdown.gym.used_balance != 0" class="pt-1 mt-1 border-top">
                        <div class="row">
                            <div class="col">
                                <span class="fas fa-fw fa-coins"></span> Balance
                            </div>
                            <div :class="'col text-' + (transaction.breakdown.gym.used_balance > 0 ? 'success' : 'danger')">
                                $@{{ numberFormat(-transaction.breakdown.gym.used_balance) }}
                            </div>
                        </div>
                    </div>

                    <div v-if="transaction.breakdown.gym.processor > 0" class="pt-1 mt-1 border-top">
                        <div class="row">
                            <div class="col">
                                <span class="fas fa-fw fa-file-invoice"></span> Payment Processor Fee
                            </div>
                            <div class="col">
                                $@{{ numberFormat(transaction.breakdown.gym.processor) }}
                            </div>
                        </div>
                    </div>

                    <div class="pt-1 mt-1 border-top border-dark font-weight-bold">
                        <div class="row">
                            <div class="col">
                                <span class="fas fa-fw fa-coins"></span> Total
                            </div>
                            <div class="col">
                                $@{{ (transaction.breakdown.gym.deposit_total == 0 || isNaN(transaction.breakdown.gym.deposit_total)) ? numberFormat(transaction.breakdown.gym.total) : numberFormat(transaction.breakdown.gym.deposit_total) }}
                            </div>
                        </div>
                    </div>

                    <div class="text-right mt-3">
                        <button class="btn btn-sm btn-secondary" data-dismiss="modal">
                            <span class="far fa-fw fa-times-circle"></span> Close
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@if ($registration->hasPendingTransactions())
    <div class="alert alert-info">
        <strong>
            <span class="fas fa-info-circle"></span>
            This registration has a pending transaction.
        </strong><br/>

        The registration flow differs slightly depending on which payment option is used for a
        transaction :
        <ul>
            <li>
                <strong>Mailed Check</strong><br/>
                The slots for the athletes associated with this payment are not reserved until
                the meet host approves the mailed check.<br/>
                You are not registered in the meet until the meet host approves the mailed check.<br/>
                Please get in touch with the meet host for further information.
            </li>
            <li>
                <strong>ACH</strong><br/>
                The slots for the athletes associated with this payment are reserved.<br/>
                You are not registered in the meet until the ACH transaction clears,
                this usually takes less than one business week.<br/>
                If the transaction fails, you will be asked to retry.
            </li>
        </ul>
    </div>            
@endif

<div v-if="transactions.length > 0">
    <div class="table-responsive-lg">
        <table class="table table-sm table-striped">
            <thead class="thead-dark">
                <tr>
                    <th scope="col" class="align-middle">
                        Created At
                    </th>
                    <th scope="col" class="align-middle">
                        Amount
                    </th>
                    <th scope="col" class="align-middle">
                        Method
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
                <tr v-for="tx in transactions" :key="tx.processor_id">
                    <td class="align-middle">
                        @{{ tx.created_at_display }}
                    </td>

                    <td class="align-middle">
                        <span v-if="tx.waitlist">—</span>
                        <span v-else :class="tx.breakdown.gym.total < 0 ? 'text-danger' : 'text-success'">
                            $@{{ (tx.breakdown.gym.deposit_total == 0 || isNaN(tx.breakdown.gym.deposit_total)) ? numberFormat(tx.breakdown.gym.total) : numberFormat(tx.breakdown.gym.deposit_total)}}
                            
                        </span>
                    </td>

                    <td class="align-middle">
                        <span v-if="tx.waitlist">—</span>
                        <span v-else>
                            @{{ constants.transactions.methods[tx.method] }}

                            <span v-if="tx.breakdown.gym.check_no">
                                #@{{ tx.breakdown.gym.check_no }}
                            </span>

                            <span v-if="tx.breakdown.gym.last4">
                                ending with @{{ tx.breakdown.gym.last4 }}
                            </span>
                        </span>
                    </td>

                    <td class="align-middle">
                        <div v-if="tx.status == constants.transactions.statuses.Completed">
                            <span class="badge badge-success">Completed @{{ (tx.is_deposit) ? "- Deposit" :"" }}</span>
                        </div>

                        <div v-else-if="tx.status == constants.transactions.statuses.Pending">
                            <span class="badge badge-warning">Pending</span>
                        </div>

                        <div v-else-if="tx.status == constants.transactions.statuses.Canceled">
                            <span class="badge badge-secondary">Canceled</span>
                        </div>

                        <div v-else-if="tx.status == constants.transactions.statuses.Failed">
                            <span class="badge badge-danger">Failed</span>
                        </div>

                        <div v-else-if="tx.status == constants.transactions.statuses.WaitlistPending">
                            <span class="badge badge-warning">Waitlist (Pending)</span>
                        </div>

                        <div v-else>
                            <span class="badge badge-info">Waitlist (Confirmed)</span>
                        </div>
                    </td>

                    <td class="align-middle">
                        @{{ tx.updated_at_display }}
                    </td>
                    <th scope="col" class="align-middle">
                        <div class="text-right">
                            <a v-if="tx.repayable" class="btn btn-sm btn-success mr-1" title="Re-pay"
                                :href="'/gym/{{ $gym->id }}/registration/{{$registration->id}}/pay/' + tx.id">
                                <span class="fas fa-fw fa-shopping-cart"></span>
                            </a>

                            <button v-if="!tx.waitlist" class="btn btn-sm btn-primary"
                                @click="showTransactionDetails(tx)">
                                <span class="fas fa-fw fa-info"></span>
                            </button>
                        </div>
                    </th>
                </tr>
            </tbody>
        </table>
    </div>
</div>
<div v-else class="text-info">
    <span class="fas fa-info-circle"></span> No transactions.
</div>