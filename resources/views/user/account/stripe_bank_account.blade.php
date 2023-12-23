<div class="modal fade" id="modal-linked-bank" tabindex="-1" role="dialog"
        aria-labelledby="modal-linked-bank" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title text-primary">
                    <span class="fas fa-plus"></span> Link a Bank Account
                </h5>
                <button type="button" class="close modal-linked-bank-close" aria-label="Close">
                    <span class="fas fa-times" aria-hidden="true"></span>
                </button>
            </div>
            
            <div class="modal-body">
                    <div class="form-row">
                        <label for="account_name">Account Holder Name</label>
                        <input type="text" class="form-control" name="account_name" id="account_name" placeholder="Name" required value="bill">
                        
                        <label for="account_type">Account Type</label>
                        <select name="account_type" id="account_type" class="form-control">
                            <option value="individual" selected>Individual</option>
                            <option value="company">Company</option>
                        </select>

                        <label for="account_no">Account No</label>
                        <input type="text" class="form-control" name="account_no" id="account_no" value="000123456789" required>
                        
                        <label for="routing_no">Routing No</label>
                        <input type="text" class="form-control" name="routing_no" id="routing_no" value="110000000" required>


                        <div id="stripe-bank-link-bank-errors" class="text-danger mt-1 small" role="alert"></div>
                    </div>

                    <div class="form-row">
                        <div class="text-info small">
                            <span class="fas fa-info-circle mt-2 mb-2"></span> Your bank account information is securely
                            sent to our payment provider and never transits through our servers.
                        </div>
                    </div>
                    
                    <div class="modal-footer pb-0 pr-0">
                        <div class="text-right">
                            <button class="btn btn-primary" id="stripe-bank-link-form">
                                <span class="spinner-border spinner-border-sm mr-1" role="status" aria-hidden="true"
                                        id="modal-linked-bank-spinner" style="display: none;">
                                </span>
                                <span class="fas fa-plus"></span> Link
                            </button>
                        </div>
                    </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modal-verify-bank" tabindex="-1" role="dialog"
        aria-labelledby="modal-verify-bank" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-primary">
                    <span class="fas fa-plus"></span> Verify Micro Deposits
                </h5>
                <button type="button" class="close modal-verify-bank-close" aria-label="Close">
                    <span class="fas fa-times" aria-hidden="true"></span>
                </button>
            </div>
            
            <div class="modal-body">
                <div class="form-row">
                    <div class="text-info small">
                        <span class="fas fa-info-circle mt-2 mb-2"></span> Your bank account should have two micro-deposits. 
                        It may take upto 2-3 days to view in your bank statement. Submit those two deposited amount here to verify your account.
                    </div>
                </div>
                <form action="{{ route('account.stripe.bank.verify') }}" method="post" id="bank-verify-form">
                    @csrf
                    <input type="hidden" id="stripe-bank-verify-token" name="bank_token">                           
                    <div class="form-row">
                        <div class="col-md-12">

                            <label for="first_deposit">First Deposit Amount - cents</label>
                            <input type="number" class="form-control" id="first_deposit" name="first_deposit" required placeholder="32">
                            <label for="second_deposit">Second Deposit Amount - cents</label>
                            <input type="number" class="form-control" id="second_deposit" name="second_deposit" required placeholder="45">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="text-info small">
                            <span class="fas fa-info-circle mt-2 mb-2"></span> Your bank account information is securely
                            sent to our payment provider and never transits through our servers.
                        </div>
                    </div>
                    <div class="modal-footer pb-0 pr-0">
                        <div class="text-right">
                            <button class="btn btn-primary" id="stripe-bank-verify-form">
                                <span class="spinner-border spinner-border-sm mr-1" role="status" aria-hidden="true"
                                        id="modal-linked-bank-spinner" style="display: none;">
                                </span>
                                <span class="fas fa-plus"></span> Verify
                            </button>
                        </div>
                    </div>
                </form>      
                
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col">
        <h5 class="border-bottom"><span class="fab fa-cc-stripe"></span> Linked Bank Accounts
    <?php 
        // if(isset($stripe_connect) && $stripe_connect["code"] == 4)
        // {
        // echo '<a href="'.$stripe_connect["url"].'" class="btn btn-sm btn-primary">
        // <span class="fas fa-plus"></span> Verification Need Attention
        // </a>';
        // }
        // else if(isset($stripe_connect) && $stripe_connect["code"] == 2)
        // {
        //     echo '
        //     <span class="fas fa-plus alert alert-info"> Account Verification in Progress</span> 
        //     ';
        // }
        // else if(isset($stripe_connect) && $stripe_connect["code"] == 3){
        //     echo '
        //     <span class="fas fa-check alert alert-sm alert-success"> Account Verified ( Withdrawal Enabled )</span> 
        //     ';
        // }
        // else if(isset($stripe_connect) && $stripe_connect["code"] == 1)
        // {
        //     echo '
        //     <span class="fas fa-ban alert alert-sm alert-danger"> Account Unverified ( Please link a bank account )</span> 
        //     ';
        // }
    ?>
    </h5>
    </div>
    <!-- <a href="" class="">Verify Stripe Account for Withdrawal</a> -->
    <div class="col d-none">
        <input type="hidden" id="stripe-publishable-key" value="{{ config('services.stripe.public') }}">
    </div>
</div>

@if ($stripe_error)
    <div class="alert alert-danger">
        <strong><span class="fas fa-times-circle"></span> Ooh !</strong><br/>
        {{ $stripe_error }}
    </div>
@elseif ($stripe_banks == null)
    <div class="alert alert-info">
        <strong><span class="fas fa-exclamation-triangle"></span> Whoops !</strong><br/>
        It looks like you do not have any banks linked to your account yet.
        You can do so by clicking the button below.
    </div>
@else
    <div class="table-responsive-lg">
        <table class="table table-sm table-hover">
            <thead class="thead-dark">
                <tr>
                    <th scope="col" class="align-middle">Last 4</th>
                    <th scope="col" class="align-middle">Account Holder Name</th>
                    <th scope="col" class="align-middle">Account Holder Type</th>
                    <th scope="col" class="align-middle">Bank Name</th>
                    <th scope="col" class="align-middle">Status</th>
                    <th scope="col" class="text-right align-middle"></th>
                    <th scope="col" class="text-right align-middle"></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($stripe_banks as $bank)
                    <tr>
                        <td class="align-middle">
                            <span class="font-weight-light">XXXX</span>-{{ $bank->last4 }}
                        </td>
                        <td class="align-middle">
                            <span class="font-weight-light">{{ $bank['account_holder_name'] }}
                        </td>
                        <td class="align-middle">
                            <span class="font-weight-light">{{ $bank['account_holder_type'] }}
                        </td>
                        <td class="align-middle">
                            <span class="font-weight-light">{{ $bank['bank_name'] }}
                        </td>
                        <td class="align-middle">
                            <span class="font-weight-light">{{ $bank['status'] }}
                            
                        </td>
                        <td class="align-middle">
                            @if($bank['status'] != 'verified')
                            <button class="btn btn-success" id="verify-stripe-ach" data-card="{{ $bank['id'] }}"><span class="fas fa-user-check"></span> Verify</button>
                            <!-- <form action="{{ route('account.stripe.bank.verify') }}"
                                    data-bank="{{ $bank['id'] }}" class="d-none" method="post">
                                @csrf
                                <input type="hidden" id="stripe-bank-link-token" name="bank_token" value="{{$bank['id']}}">                           
                                <input type="number" name="first_deposit" class="form-control">                           
                                <input type="number" name="second_deposit" class="form-control">                           
                            </form> -->
                            @endif
                        </td>

                        <td class="text-right credit-card-remove align-middle">
                            <button class="btn btn-sm btn-danger" data-card="{{ $bank['id'] }}" title="Remove" id="bank-remove-btn">
                                <span class="fas fa-trash"></span>
                            </button>
                            <form action="{{ route('account.card.remove', ['id' => $bank['id']]) }}"
                                    data-card="{{ $bank['id'] }}" class="d-none" method="post">
                                @csrf
                                @method('DELETE')                            
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endif

<div class="text-right">
    <form id="stripe-bank-link-add-form" action="{{ route('account.stripe.bank.add') }}"
            class="d-none" method="post">
        @csrf
        <input type="hidden" id="stripe-bank-link-token" name="bank_token" value="">
        <input type="hidden" id="stripe-bank-account-name" name="account_name" value="">
    </form>
    <a href="#modal-linked-bank" class="btn btn-sm btn-primary" data-toggle="modal"
        data-backdrop="static" data-keyboard="false">
        <span class="fas fa-plus"></span> Link an Account
    </a>
</div>