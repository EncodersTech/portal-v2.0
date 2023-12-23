<script id="sourceUserBankTemplate" type="text/x-jsrender">
    <div id="sourceAccordion">
        <div class="card">
            <div class="card-header" id="headingOne">
                <h5 class="mb-0">
                    <a href="Javascript:void(0)" data-toggle="collapse" data-target="#sourceCollapseOne" aria-expanded="true"                                                       aria-controls="sourceCollapseOne">
                        <h6 class="clickable m-0 py-2 border-bottom">
                            <i class="fas fa-fw fa-money-check-alt"></i> ACH
                        </h6>
                    </a>
                </h5>
            </div>

            <div id="sourceCollapseOne" class="collapse show" aria-labelledby="headingOne" data-parent="#sourceAccordion">
                <div class="card-body">
                    {{for bankAccounts}}
                        {{if type}}
                                <div class="py-1 px-2 mb-2 border bg-white rounded">
                                    <div class="form-check">
                                        <input type="radio" id="source_wallet_bank_{{:id}}" class="form-check-input" name="source_wallet_bank" value="{{:id}}">
                                        <label for="source_wallet_bank_{{:id}}" class="form-check-label">
                                            <div class="py-1 border-bottom border-light hoverable clickable">
                                                <div class="row">
                                                    <div class="col-auto">
                                                        <span class="fas fa-fw fa-university"></span>
                                                    </div>
                                                    <div class="col-auto">
                                                        {{:name}}
                                                    </div>
                                                    <div class="col-auto">
                                                            {{:bankType}}
                                                    </div>
                                                </div>
                                            </div>
                                        </label>
                                    </div>
                                </div>
                        {{/if}}
                        {{/for}}
                </div>
            </div>
        </div>
    </div>
    <div class="py-1 px-2 mb-2 border bg-white rounded">
        <div class="form-check">
            <input type="checkbox" id="allGym_source_balance" class="form-check-input" name="allGym_source_balance" value="1">
            <label for="allGym_source_balance" class="form-check-label">
                <span class="fas fa-fw fa-coins"></span>
                Use User Allgymnastics.com balance towards this payment
            </label>
        </div>
    </div>
</script>


<script id="destinationUserBankTemplate" type="text/x-jsrender">
    <div id="destinationAccordion">
        <div class="card">
            <div class="card-header" id="headingOne">
                <h5 class="mb-0">
                    <a href="Javascript:void(0)" data-toggle="collapse" data-target="#destinationCollapseOne" aria-expanded="true"                                                       aria-controls="destinationCollapseOne">
                        <h6 class="clickable m-0 py-2 border-bottom">
                            <i class="fas fa-fw fa-money-check-alt"></i> ACH
                        </h6>
                    </a>
                </h5>
            </div>

            <div id="destinationCollapseOne" class="collapse show" aria-labelledby="headingOne" data-parent="#destinationAccordion">
                <div class="card-body">
                    {{for bankAccounts}}
                        {{if type}}
                                <div class="py-1 px-2 mb-2 border bg-white rounded">
                                    <div class="form-check">
                                        <input type="radio" id="destination_wallet_bank_{{:id}}" class="form-check-input" name="destination_wallet_bank" value="{{:id}}">
                                        <label for="destination_wallet_bank_{{:id}}" class="form-check-label">
                                            <div class="py-1 border-bottom border-light hoverable clickable">
                                                <div class="row">
                                                    <div class="col-auto">
                                                        <span class="fas fa-fw fa-university"></span>
                                                    </div>
                                                    <div class="col-auto">
                                                        {{:name}}
                                                    </div>
                                                    <div class="col-auto">
                                                            {{:bankType}}
                                                    </div>
                                                </div>
                                            </div>
                                        </label>
                                    </div>
                                </div>
                        {{/if}}
                        {{/for}}
                </div>
            </div>
        </div>
    </div>
    <div class="py-1 px-2 mb-2 border bg-white rounded">
        <div class="form-check">
            <input type="checkbox" id="allGym_destination_balance" class="form-check-input" name="allGym_destination_balance" value="1">
            <label for="allGym_destination_balance" class="form-check-label">
                <span class="fas fa-fw fa-coins"></span>
                Use User Allgymnastics.com balance towards this payment
            </label>
        </div>
    </div>
</script>