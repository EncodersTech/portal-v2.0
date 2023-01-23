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
                                <span class="fas fa-fw fa-dumbbell"></span> Gym
                            </div>
                            <div class="col">
                                @{{ transaction.gym.name }}
                                <span v-if="transaction.gym.user_id == managed" class="text-success small">
                                    (<span class="fas fa-check"></span> Own)
                                </span>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col">
                                <span class="fas fa-fw fa-wallet"></span> Method
                            </div>
                            <div class="col">
                                @{{ constants.transactions.methods[transaction.method] }}
                                <span v-if="transaction.breakdown.gym.check_no">
                                    #@{{ transaction.breakdown.gym.check_no }}
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

                                <div v-else-if="transaction.status == constants.transactions.statuses.Failed">
                                    <span class="badge badge-danger">Failed</span>
                                </div>

                                <div v-else-if="transaction.status == constants.transactions.statuses.WaitlistPending">
                                    <span class="badge badge-warning">Waitlist (Pending)</span>
                                </div>

                                <div v-else>
                                    <span class="badge badge-info">Waitlist (Confirmed)</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div v-if="!transaction.waitlist">
                        <div>
                            <div class="row">
                                <div class="col">
                                    <span class="fas fa-fw fa-tasks"></span> Registration Subtotal
                                </div>
                                <div class="col">
                                    $@{{ numberFormat(transaction.breakdown.host.subtotal) }}
                                </div>
                            </div>
                        </div>
                        <div>
                            <div class="row" v-if="transaction.breakdown.host.coupon > 0">
                                <div class="col">
                                    <span class="fas fa-fw fa-tasks"></span> Coupon Used
                                </div>
                                <div class="col">
                                    $@{{ numberFormat(transaction.breakdown.host.coupon) }}
                                </div>
                            </div>
                        </div>
                        <div v-if="transaction.breakdown.host.deposit_subtotal > 0" class="pt-1 mt-1 border-top">
                            <div class="row">
                                <div class="col">
                                    <span class="fas fa-fw fa-tasks"></span> Deposit Subtotal
                                </div>
                                <div class="col">
                                    $@{{ numberFormat(transaction.breakdown.host.deposit_subtotal) }}
                                </div>
                            </div>
                        </div>

                        <div v-if="transaction.breakdown.host.own_meet_refund > 0" class="pt-1 mt-1 border-top">
                            <div class="row">
                                <div class="col">
                                    <span class="fas fa-fw fa-user-check"></span> Own Meet Refund
                                </div>
                                <div class="col text-danger">
                                    -$@{{ numberFormat(transaction.breakdown.host.own_meet_refund) }}
                                </div>
                            </div>
                        </div>

                        <div v-if="transaction.method == constants.transactions.methods.Check">
                            <div v-if="transaction.breakdown.gym.handling > 0" class="pt-1 mt-1 border-top">
                                <div class="row">
                                    <div class="col">
                                        <span class="fas fa-fw fa-server"></span> Handling Fee Recovered From Gym
                                    </div>
                                    <div class="col text-success">
                                        +$@{{ (transaction.breakdown.gym.deposit_handling == 0 || isNaN(transaction.breakdown.gym.deposit_handling)) ? numberFormat(transaction.breakdown.gym.handling) : numberFormat(transaction.breakdown.gym.deposit_handling)}}
                                    </div>
                                </div>
                            </div>

                            <div v-if="transaction.total_handling_fee > 0" class="pt-1 mt-1 border-top">
                                <div class="row">
                                    <div class="col">
                                        <span class="fas fa-fw fa-server"></span> Handling Fee To Be Paid On Confirmation
                                    </div>
                                    <div class="col text-danger">
                                        -$@{{ numberFormat(transaction.total_handling_fee) }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div v-else-if="transaction.breakdown.host.handling > 0" class="pt-1 mt-1 border-top">
                            <div class="row">
                                <div class="col">
                                    <span class="fas fa-fw fa-server"></span> Handling Fee
                                </div>
                                <div class="col">
                                    $@{{ numberFormat(transaction.breakdown.host.handling) }}
                                </div>
                            </div>
                        </div>

                        <div v-if="transaction.breakdown.host.processor > 0" class="pt-1 mt-1 border-top">
                            <div class="row">
                                <div class="col">
                                    <span class="fas fa-fw fa-file-invoice"></span> Payment Processor Fee
                                </div>
                                <div class="col">
                                    $@{{ numberFormat(transaction.breakdown.host.processor) }}
                                </div>
                            </div>
                        </div>

                        <div class="pt-1 mt-1 border-top border-dark font-weight-bold">
                            <div class="row">
                                <div class="col">
                                    <span class="fas fa-fw fa-coins"></span> Your Profit
                                </div>
                                <!-- <div v-if="transaction.method == constants.transactions.methods.Check" class="col">
                                    $@{{ numberFormat(transaction.breakdown.host.total) - numberFormat(transaction.total_handling_fee * cc_fees / 100) }}
                                </div> -->
                                <div class="col">
                                $@{{ (transaction.breakdown.host.deposit_total == 0 || isNaN(transaction.breakdown.gym.deposit_total)) ? numberFormat(transaction.breakdown.host.total + parseFloat(transaction.breakdown.host.coupon > 0 ? transaction.breakdown.host.coupon : 0)) : numberFormat(transaction.breakdown.host.deposit_total) }}
                                </div>
                            </div>
                        </div>

                        <div v-if="transaction.method == constants.transactions.methods.Check">
                            <div class="row mt-1">
                                <div class="col">
                                    <span class="fas fa-fw fa-money-check-alt"></span> Amount On Check
                                </div>
                                <div class="col">
                                    $@{{ (transaction.breakdown.gym.deposit_total == 0 || isNaN(transaction.breakdown.gym.deposit_total)) ? numberFormat(transaction.check_total) : numberFormat(transaction.breakdown.gym.deposit_total) }}
                                </div>
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

<div class="modal fade" id="modal-confirm-check" tabindex="-1" role="dialog" aria-labelledby="modal-confirm-check" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-primary">
                    <span class="fas fa-check"></span> Confirm Check
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
                    <div class="mt-1">
                        Please double check the following:
                        <ul>
                            <li>
                                <strong>Check Number:</strong>
                                @{{ transaction.breakdown.gym.check_no }}
                            </li>
                            <li>
                                <strong>Amount On Check:</strong>
                                $@{{  numberFormat(transaction.check_total) }}
                            </li>
                        </ul>
                    </div>
                    <div class="mt-1">
                        Charged handling fee :
                        <strong>$@{{ numberFormat(transaction.total_handling_fee) }}
                            </strong>.<br>
                        <div v-if="transaction.breakdown.host.processor != 0">
                            Charged processor fee :
                            <strong>$@{{  numberFormat(transaction.breakdown.host.processor) }}
                                </strong>.<br>
                                                       </div>
                        Total fee : <strong>$@{{ numberFormat(transaction.total_handling_fee + transaction.breakdown.host.processor) }}
                        </strong><br>
                        <!-- Please select a card to place the charge on:
                        <div class="form-group">
                            <select class="form-control form-control-sm" v-model="selected_card">
                                <option value="">(Choose below ...)</option>
                                <option v-for="c in cards" :key="c.id" :value="c">
                                    @{{ c.brand + ' ending with ' + c.last4}}
                                    (Exp. @{{ c.exp_month + '/' + c.exp_year}})
                                </option>
                            </select>
                        </div> -->
                    </div>
                    <div class="text-right mt-3">
                        <button class="btn btn-sm btn-secondary mr-1" data-dismiss="modal">
                            <span class="far fa-fw fa-times-circle"></span> Close
                        </button>
                        <button class="btn btn-sm btn-success"
                            @click="sendCheckConfirmation(transaction)">
                            <!-- @click="sendCheckConfirmation(transaction, selected_card)"> -->
                            <span class="fas fa-fw fa-check"></span> Confirm
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="d-flex flex-row flex-no-wrap mb-3">
    <div class="flex-grow-1">
        <div class="input-group input-group-sm">
            <input type="text" class="form-control search-field"
                :class="{'border-right-0': (transactionsFilters.text != '')}"
                v-model="transactionsFilters.text" placeholder="Transaction ID or Gym name ...">

            <div class="input-group-append">
                <button class="btn btn-outline-danger" :class="{'d-none': (transactionsFilters.text === '')}"
                    @click="transactionsFilters.text = ''" type="button" title="Clear Search Box" >
                    <span class="fas fa-fw fa-eraser"></span>
                </button>
            </div>
        </div>
    </div>

    <div class="ml-1">
        <select class="form-control form-control-sm" v-model="transactionsFilters.method">
            <option value="">All methods</option>
            <option v-for="m in constants.transactions.methods._array" :key="m"
                :value="m">@{{ constants.transactions.methods[m] }}</option>
        </select>
    </div>

    <div class="ml-1">
        <select class="form-control form-control-sm" v-model="transactionsFilters.status">
            <option value="">All statuses</option>
            <option v-for="s in constants.transactions.statuses._array" :key="s"
                :value="s">@{{ constants.transactions.statuses[s] }}</option>
        </select>
    </div>
</div>

<div v-if="transactionsFiltering">
    <div class="small text-center py-3">
        <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true">
        </span> Loading, please wait ...
    </div>
</div>
<div v-else>
    <div v-if="transactions.length > 0">
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
                            
                            @{{ tx.gym.name }}
                        </td>
                        <td class="align-middle">
                            @{{ tx.created_at_display }}
                        </td>

                        <td class="align-middle">
                            <span v-if="tx.waitlist">—</span>
                            <span v-else-if="tx.method == constants.transactions.methods.Check && tx.is_deposit == false" :class="tx.breakdown.host.total < 0 ? 'text-danger' : 'text-success'">
                                $@{{ numberFormat(tx.check_total) }}
                            </span>
                            <span v-else-if="tx.method == constants.transactions.methods.Check && tx.is_deposit == true" :class="tx.breakdown.host.total < 0 ? 'text-danger' : 'text-success'">
                                $@{{ numberFormat(tx.breakdown.gym.deposit_total) }}
                            </span>
                            <span v-else :class="tx.breakdown.host.total < 0 ? 'text-danger' : 'text-success'">
                                $@{{ numberFormat(tx.breakdown.host.total + parseFloat(tx.breakdown.host.coupon>0?tx.breakdown.host.coupon:0)) }}
                            </span>
                        </td>

                        <td class="align-middle">
                            <span v-if="tx.waitlist">—</span>
                            <span v-else>
                                @{{ constants.transactions.methods[tx.method] }}
                                <span v-if="tx.breakdown.gym.check_no">
                                    #@{{ tx.breakdown.gym.check_no }}
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
                                <button v-if="tx.is_pending_check" title="Reject Check"
                                    class="btn btn-sm btn-danger mr-1" @click="rejectCheck(tx)">
                                    <span class="fas fa-fw fa-times"></span>
                                </button>

                                <button v-if="tx.is_pending_check" title="Confirm Check"
                                    class="btn btn-sm btn-success mr-1" @click="confirmCheck(tx, <?php echo $cc_fees; ?> )">
                                    <span class="fas fa-fw fa-check"></span>
                                </button>

                                <button v-if="tx.waitlist" title="Reject Waitlist Entry"
                                    class="btn btn-sm btn-danger mr-1" @click="rejectWaitlistRegistration(tx)">
                                    <span class="fas fa-fw fa-times"></span>
                                </button>

                                <button v-if="tx.status == constants.transactions.statuses.WaitlistPending"
                                    title="Confirm Waitlist Entry" class="btn btn-sm btn-success"
                                    @click="confirmWaitlistRegistration(tx)">
                                    <span class="fas fa-fw fa-check"></span>
                                </button>

                                <button v-if="!tx.waitlist" class="btn btn-sm btn-primary"
                                    @click="showTransactionDetails(tx, <?php echo $cc_fees; ?>)">
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
</div>
