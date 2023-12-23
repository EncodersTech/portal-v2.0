<div>
    <div id="accordion">
        <div class="card">
            <div class="card-header" id="headingOne">
                <h5 class="mb-0">
                    <a href="Javascript:void(0)" data-toggle="collapse" data-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                        <h6 class="clickable m-0 py-2 border-bottom">
                            <i class="fas fa-fw fa-money-check-alt"></i> ACH
                        </h6>
                    </a>
                </h5>
            </div>

            <div id="collapseOne" class="collapse show" aria-labelledby="headingOne" data-parent="#accordion">
                <div class="card-body">
                    @if(!empty($destinationBankAccounts))
                        @foreach($destinationBankAccounts as $bankAccount)
                            @if($bankAccount->type == 'bank')
                                <div class="py-1 px-2 mb-2 border bg-white rounded">
                                    <div class="form-check">
                                        <input type="checkbox" id="destination_wallet_bank_{{ $bankAccount->id }}" class="form-check-input">
                                        <label for="destination_wallet_bank_{{ $bankAccount->id }}" class="form-check-label">
                                            <div class="py-1 border-bottom border-light hoverable clickable">
                                                <div class="row">
                                                    <div class="col-auto">
                                                        <span class="fas fa-fw fa-university"></span>
                                                    </div>
                                                    <div class="col-auto">
                                                        {{ $bankAccount->name }}
                                                    </div>
                                                    <div class="col-auto">
                                                        {{ $bankAccount->bankAccountType }}
                                                    </div>
                                                </div>
                                            </div>
                                        </label>
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    @else
                        <div class="py-1 small">
                            <i class="fas fa-exclamation-circle"></i>
                            This User have no bank accounts.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @if(!empty($destinationBankAccounts))
        @foreach($destinationBankAccounts as $bankAccount)
            @if($bankAccount->type == 'balance')
                <div class="py-1 px-2 mb-2 border bg-white rounded">
                    <div class="form-check">
                        <input type="checkbox" id="destination_wallet_balance" class="form-check-input">
                        <label for="destination_wallet_balance" class="form-check-label">
                            <span class="fas fa-fw fa-coins"></span>
                            Use Dwolla Wallet towards this payment
                        </label>
                    </div>
                </div>
            @endif
        @endforeach
    @endif
    <div class="py-1 px-2 mb-2 border bg-white rounded">
        <div class="form-check">
            <input type="checkbox" id="allGym_destination_balance" class="form-check-input">
            <label for="allGym_destination_balance" class="form-check-label">
                <span class="fas fa-fw fa-coins"></span>
                Use User Allgymnastics.com balance towards this payment
            </label>
        </div>
    </div>
</div>
