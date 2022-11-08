@extends('layouts.main')

@section('content-header')
    <span class="fas fa-fw fa-user"></span> Schedule Withdraw
@endsection

@section('content-main')
    @include('include.errors')

    <div class="content-main">
    <div>
        @php ($active_tab = 'schedule_withdraw')
        @include('include.user.profile_nav')
    </div>
    <style>
        .custom-text{
            text-align: left;
    font-size: 11px;
    padding: 4px;
    background-color: blanchedalmond;
        }
    </style>
    <div class="p-3">
        @if($dwolla_status != 'verified')
        <div class="alert alert-warning"><span class="fas fa-fw fa-info-circle"></span> Please verify your dwolla account. Unverified dwolla account will limit your available withdrawal amount</div>
        @endif
        <div class="alert alert-info"><span class="fas fa-fw fa-info-circle"></span> Automated Withdrawals allows you to schedule automated transfers of funds from your AllGym balance to the bank account of your choosing. You may select the withdrawal amount, the frequency and to which linked bank account to deposit it into. To add a bank account, select "Payment Options" in the settings above.  To change your min/max limits contact customer service.</div>
        <div class="alert alert-info"><span class="fas fa-fw fa-info-circle"></span> Automated Withdrawal Fee is $<?php echo $auto_withdraw_charge;?></div>

        <div class="row mb-3">
            <div class="col-lg mb-3">
                <label for="available_funds" class="mb-1">
                    <span class="fas fa-fw fa-dollar-sign"></span> Available Funds
                </label> 
                <div class="input-group input-group-sm">
                    <div class="input-group-prepend">
                        <span class="input-group-text">
                            <span class="fas fa-fw fa-dollar-sign"></span>
                        </span>
                    </div>
                    <input id="available_funds" type="text" name="available_funds" value="{{Auth::user()->cleared_balance}}" placeholder="Available Funds" disabled="disabled" autofocus="autofocus" class="form-control"> 
                </div> 
            </div> 
        </div> 
        @if(Auth::user()->withdrawal_freeze)
            <div class="alert alert-danger">Withdrawal Frozen</div>
        @endif
        <div class="row" style="    background-color: aliceblue;">
            <div class="col-lg">
                Account Withdrawal Limits: 
            </div>
        </div>
        <div class="row mb-3">
            <div class="col-lg mb-3">
                <label for="available_funds" class="mb-1">
                    <span class="fas fa-fw fa-dollar-sign"></span> Minimum Withdrawal Amuont: 
                </label> 
                <div class="input-group input-group-sm">
                    <div class="input-group-prepend">
                        <span class="input-group-text">
                            <span class="fas fa-fw fa-dollar-sign"></span>
                        </span>
                    </div>
                    <input id="available_funds" type="text" name="available_funds" value="{{$min_withdraw_limit}}" placeholder="Available Funds" disabled="disabled" autofocus="autofocus" class="form-control"> 
                </div> 
            </div> 
            <div class="col-lg mb-3">
                <label for="available_funds" class="mb-1">
                    <span class="fas fa-fw fa-dollar-sign"></span> Maximum Withdrawal Amount:
                </label> 
                <div class="input-group input-group-sm">
                    <div class="input-group-prepend">
                        <span class="input-group-text">
                            <span class="fas fa-fw fa-dollar-sign"></span>
                        </span>
                    </div>
                    <input id="available_funds" type="text" name="available_funds" value="{{$max_withdraw_limit}}" placeholder="Available Funds" disabled="disabled" autofocus="autofocus" class="form-control"> 
                </div> 
            </div> 
        </div> 
        <form action="{{route('account.withdraw.initiate')}}" method="post">
        @csrf
        @method('POST')
            <div class="row mb-3">
                <div class="col-lg mb-3">
                    <label for="withdrawal_funds" class="mb-1">
                        <span class="fas fa-fw fa-dollar-sign"></span> Withdrawal Amount
                    </label> 
                    <div class="input-group input-group-sm">
                        <div class="input-group-prepend">
                            <span class="input-group-text">
                                <span class="fas fa-fw fa-dollar-sign"></span>
                            </span>
                        </div>
                        <input  type="number"  min="{{$min_withdraw_limit}}" max="{{$max_withdraw_limit}}" id="withdrawal_funds" name="withdrawal_funds" value="" placeholder="Amount to withdraw" class="form-control"> 
                    </div> 
                    <div class="custom-text">Once your account reaches this balance, we will deposit it into the selected bank account</div>
                </div> 
                <div class="col-lg mb-3">
                    <label for="frequency" class="mb-1">
                        <span class="fas fa-fw fa-clock"></span> Choose Frequency
                    </label>
                    <div class="input-group input-group-sm">
                        <div class="input-group-prepend">
                            <span class="input-group-text">
                                <span class="fas fa-fw fa-clock"></span>
                            </span>
                        </div>
                        <select name="frequency" id="frequency" class="form-control" required>
                            <option value="1">Weekly</option>
                            <option value="2">Bi-Weekly</option>
                            <option value="3">Monthly</option>
                        </select>
                    </div> 
                </div> 
                <div class="col-lg mb-3">
                    <label for="bank_id" class="mb-1">
                        <span class="fas fa-fw fa-dollar-sign"></span> Choose Bank to Withdraw
                    </label> 
                    <div class="input-group input-group-sm">
                        <div class="input-group-prepend">
                            <span class="input-group-text">
                                <span class="fas fa-fw fa-dollar-sign"></span>
                            </span>
                        </div>
                        <select id="bank_id" name="bank_id" class="form-control form-control-sm" required>
                            <option value="">(Choose a bank account ...)</option>
                            <?php 
                                if(!empty($bank_accounts))
                                {
                                    foreach ($bank_accounts as $ba) {
                                    echo '<option value="'.$ba->id.'">'.$ba->name.'</option>';
                                }
                            } ?>
                        </select>
                    </div> 
                </div>
                <div class="col-lg mb-3">
                    <label for="bank_id" class="mb-1">
                        <span></span> &nbsp; &nbsp; &nbsp;
                    </label> 
                    <div class="input-group input-group-sm">
                        <div class="input-group-prepend">
                        </div>
                        @if(Auth::user()->withdrawal_freeze)
                        <span class="btn btn-sm btn-danger">Frozen</span>
                        @else
                        <button class="btn btn-sm btn-primary <?php echo $min_withdraw_limit > $max_withdraw_limit ? 'disabled' : ''; ?>" <?php echo $min_withdraw_limit > $max_withdraw_limit ? 'disabled' : ''; ?>>Withdraw</button>
                        @endif
                    </div> 
                </div>
            </div> 
        </form> 
    </div>
    <?php if(count($withdraw_table) > 0){ ?>
    <div class="p-3">
        <div class="row">
            <div class="col-md-12">
            <table class="table">
                <tr>
                    <th>Bank</th>
                    <th>Amount</th>
                    <th>Frequency</th>
                    <th>Number of Withdraw Attempt</th>
                    <th>Last Withdraw Attempt</th>
                    <th>Action</th>
                </tr>
                <?php 
                $frq = array('','Weekly','Bi-Weekly','Monthly');
                foreach ($bank_accounts as $ba) {
                    $bank_array[$ba->id] = $ba->name;
                }
                foreach ($withdraw_table as $k) {
                    $dts = $k->last_attempt == null ? "None" : date("d/m/Y", strtotime($k->last_attempt));
                    $status = $k->is_active ? "<button class='btn btn-sm btn-danger' onclick='setbtnaction(".$k->id.", this);'>Disable</button>" : "<button class='btn btn-sm btn-success' onclick='setbtnaction(".$k->id.", this);'>Enable</button>";
                 echo '<tr>
                 <td>'.$bank_array[$k->bank_id].'</td>
                 <td>$'.$k->amount.'</td>
                 <td>'.$frq[$k->frequency].'</td>
                 <td>'.$k->attempt.'</td>
                 <td>'.$dts.'</td>
                 <td>'.$status.'</td>
                 </tr>';   
                }
                ?>
            </table>
            </div>
        </div>
    </div>
    <?php } ?>
    </div>

@endsection

@section('scripts-main')
    <script src="{{ mix('js/user/account-profile.js') }}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/notify/0.4.2/notify.min.js"></script>
    <script>
        $(document).ready(function() {
            setbtnaction = function(id,e)
            {
                $.ajax({
                type: "post",
                url: '{{ route("account.withdraw.change_status") }}',
                dataType: 'json',
                data: {'id':id},
                beforeSend: function(){
                    return confirm("Are you sure?");
                },
                success: function(re) {
                    $(e).toggleClass("btn-danger btn-success");
                    if ($(e).text() == "Disable")
                    $(e).text("Enable")
                    else
                    $(e).text("Disable");
                    $.notify("Status Updated Successfully", "success");
                },
                error: function(){
                    $.notify("Status Update Failed, Please try again later or contact admin", "error");
                }
            });
                
            }
        })
    </script>
@endsection