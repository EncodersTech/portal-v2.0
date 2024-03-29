<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'AllGymnastics') }}</title>
    @include('PDF.styles.reboot')
    @include('PDF.styles.reports')
</head>
<!-- page-break-after: always; -->
<body>
    <div class="header">
        <div class="header-text">
            <h1 class="mb-0">
                Registration Details Report
            </h1>
            <h2 class="mb-0">
                Meet: {{ $meet->name }}
            </h2>
            <h2 class="mb-0">
                Meet Host: {{$meet->gym->name}}
            </h2>
            <h4 class="mb-0">
                Date: {{ $meet->start_date->format(Helper::AMERICAN_SHORT_DATE) }} -
                {{ $meet->end_date->format(Helper::AMERICAN_SHORT_DATE) }}
            </h4>
        </div>
        <div class="logo-container">
            @include('PDF.host.meet.reports.common_logo_image')
        </div>
    </div>
    @foreach($registrations as $index => $registration)
        <div class="float-parent">
            <div class="float-child" style="padding: 0 10px 0 0 !important;">
                <div>
                    <span><strong>Gym Details</strong></span>
                    <br>
                    <table class="table-0 full-width">
                        <thead>
                            <tr>
                                <th class="col-1">Gym Name</th>
                                <th class="col-1">{{$registration->gym->short_name}}</th>
                            <tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="col-1">Address</td>
                                <td class="col-1">
                                    {{ $registration->gym->addr_1 }},

                                    @if ($registration->gym->addr_2)
                                    {{ $registration->gym->addr_2 }},
                                    @endif

                                    {{ $registration->gym->city }},
                                    {{ (!empty($registration->gym) && !empty( $registration->gym->state_id)) ? $registration->gym->state->code.',' : ''}}
                                    {{ $registration->gym->zipcode }},
                                    {{ $registration->gym->country->name }}
                                </td>
                            <tr>
                            <tr>
                                <td class="col-1">Contact Person</td>
                                <td class="col-1">
                                    #{{ $registration->gym->user->first_name.' '.$registration->gym->user->last_name }}</td>
                            <tr>
                            <tr>
                                <td class="col-1">Club #</td>
                                <td class="col-1">
                                    {{ !empty($registration->gym->usag_membership)?'#USAG:'.$registration->gym->usag_membership:''}}{{!empty($registration->gym->usaigc_membership)?', #USAIGC:'.$registration->gym->usaigc_membership:''}}{{!empty($registration->gym->aau_membership)?', #AAU:'.$registration->gym->aau_membership:''}}{{!empty($registration->gym->nga_membership)?', #NGA:'.$registration->gym->nga_membership:''}}
                                </td>
                            <tr>
                            <tr>
                                <td class="col-1">Email Address</td>
                                <td class="col-1"> <?php echo wordwrap($registration->gym->user->email, 25, "\n", true); ?></td>
                            <tr>
                            <tr>
                                <td class="col-1">Office No.</td>
                                <td class="col-1">{{ $registration->gym->office_phone }}</td>
                            <tr>
                        </tbody>
                    </table>
                </div>
                <br>
            </div>
            <div class="float-child" style="padding: 0 !important;">
                <div>
                    <span><strong>Fees Summary </strong>
                    </span><br>
                    @if ($meet)
                    <table class="table-0  full-width">
                        <thead>
                            <tr>
                                <th class="">Description</th>
                                <th class="">Participant Fees</th>
                                <!-- <th class="">Meet Host Fees</th> -->
                            <tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="">Individual Fees</td>
                                <td class=" text-right">{{number_format($feeArr[$index]['reg_fees'], 2)}}</td>
                                <!-- <td class=" text-right">{{number_format($feeArr[$index]['reg_meet_fees'], 2)}}</td> -->
                            <tr>
                            <tr>
                                <td class="">Specialist Fees</td>
                                <td class=" text-right">{{number_format($feeArr[$index]['specialist_fee'], 2)}}</td>
                                <!-- <td class=" text-right">{{number_format($feeArr[$index]['reg_meet_fees'], 2)}}</td> -->
                            <tr>
                            <tr>
                                <td class="">Team Fees</td>
                                <td class=" text-right">{{number_format($feeArr[$index]['team_fees'], 2)}}</td>
                                <!-- <td class=" text-right">{{number_format($feeArr[$index]['team_meet_fees'], 2)}}</td> -->
                            <tr>
                            <tr>
                                <td class="">Admin Fee*</td>
                                <td class=" text-right">{{number_format($feeArr[$index]['admin_fees'], 2)}}</td>
                                <!-- <td class=" text-right">{{number_format($feeArr[$index]['admin_meet_fees'], 2)}}</td> -->
                            <tr>
                            <tr>
                                <td class="">Card Fee*</td>
                                <td class=" text-right">{{number_format($feeArr[$index]['card_fees'], 2)}}</td>
                                <!-- <td class=" text-right">{{number_format($feeArr[$index]['card_meet_fees'], 2)}}</td> -->
                            <tr>
                            <tr>
                                <td class="">Late Fee*</td>
                                <td class=" text-right">{{number_format($feeArr[$index]['late_fee'], 2)}}</td>
                                <!-- <td class=" text-right">0.00</td> -->
                            <tr>
                            <tr style="color:red;">
                                <td class="">Scratch Credit Used</td>
                                <td class=" text-right">{{number_format($feeArr[$index]['used_credit'], 2)}}</td>
                            </tr>
                        </tbody>
                        <thead>
                            <tr>
                                <th class="col-1">Total</th>
                                <th class="col-1  text-right">{{number_format($feeArr[$index]['total_fees'], 2)}}</th>
                                <!-- <th class="col-1  text-right">{{number_format($feeArr[$index]['total_meet_fees'], 2)}}</th> -->
                            <tr>
                        </thead>
                    </table>
                    @endif
                </div>
                <br><br><br>
            </div>
        </div>
        <hr>
        <div class="float-parent" style="padding-top: 20px !important;">
            <span><strong>Transaction History</strong></span><br>
            <span>Please note: Credits found in the report reflect athlete changes made after initial registration. Please refer only to the refund section or refund report for refund totals</span>
            @if ($registrations->count() > 0)
                <table class="table-0 table-summary full-width">
                    <thead>
                        <tr>
                            <th width="8%">Date</th>
                            <th width="8%">Tran's ID</th>
                            <th width="8%">Type:</th>
                            <th colspan="8" width="60%" class="col-sum-2">Detail</th>
                            <th width="8%">Fee</th>
                        <tr>
                    </thead>
                    <tbody>
                        <?php
                            $total_reg_at = 0;
                            $total_reg_sp_at = 0;
                            $total_reg_fees = 0;
                            $total_participant_fees = 0;
                            $coupon = 0;
                        ?>
                        @foreach ($registration->transactions as $transaction)
                            <?php
                            try{
                                // MODIFICATION => Mod. 
                                $reg_type = $transaction->breakdown['gym']['own_meet_refund'] > 0 ? 'Mod.' : 'Reg.';
                                $symbols = $transaction->breakdown['gym']['own_meet_refund'] > 0 ? '-' : '';
                                $coupon = (isset($transaction->breakdown['gym']['coupon']) && $transaction->breakdown['gym']['coupon'] > 0) ? $transaction->breakdown['gym']['coupon'] : 0;
                                if ($transaction->breakdown['gym']['subtotal'] == 0 && $transaction->breakdown['gym']['coupon'] > 0) {
                                $gym_sub_total = (isset($transaction->breakdown['gym']['deposit_subtotal']) && $transaction->breakdown['gym']['deposit_subtotal'] > 0) ? $transaction->breakdown['gym']['deposit_subtotal'] : $transaction->breakdown['gym']['subtotal'] + $coupon - (($transaction->breakdown['gym']['handling'] + $transaction->breakdown['gym']['processor']) - $transaction->breakdown['gym']['total']);
                                } else {
                                $gym_sub_total = (isset($transaction->breakdown['gym']['deposit_subtotal']) && $transaction->breakdown['gym']['deposit_subtotal'] > 0) ? $transaction->breakdown['gym']['deposit_subtotal'] : $transaction->breakdown['gym']['subtotal'] + $coupon;
                                }

                                $refund_used = $transaction->level_payment_sum - $gym_sub_total;
                            
                                // $refund_used = ($transaction->level_payment_sum - $gym_sub_total) > 0 ? ($transaction->level_payment_sum - $gym_sub_total) : 0;
                            ?>
                            <tr>
                                <td class="col-4">{{$transaction['created_at']->format(Helper::AMERICAN_SHORT_DATE)}}</td>
                                <td class="col-4">{{$transaction->id}}</td>
                                <td class="col-4">{{$reg_type}}</td>
                                <td class="col-1">Level</td>
                                <td class="col-1">Athlete</td>
                                <td class="col-1">Specialist</td>
                                <td class="col-1">Team</td>
                                <td class="col-1">Entry Fee</td>
                                <td class="col-1">Specialist Fee</td>
                                <td class="col-1">Team Fee</td>
                                <td class="col-1">Amount</td>
                                <td class="col-1  text-right">
                                    {{number_format( $gym_sub_total,3)}}
                                    @php
                                        if($refund_used > 0){
                                            echo '<br><span style="color:red; font-weight: bold;">Credit: '.number_format($refund_used,3) . '</span>';
                                        }
                                        else if($refund_used < 0)
                                        {
                                            echo '<br><span style="color:green; font-weight: bold;">Refund: '.number_format(($refund_used * -1),3) . '</span>';
                                        }
                                    @endphp
                                </td>
                            <tr>
                            @if(isset($transaction['level_reg_history']))
                            @foreach ($transaction['level_reg_history'] as $key => $tr_hi)
                            
                                <tr style="{{ $loop->even?'background-color: #ccc;':'' }}">
                                    <td class="col-4" colspan="3"></td>
                                    <td class="col-1">{{$tr_hi['name']}}</td>
                                    <td class="col-1">{{$symbols}}{{$tr_hi['at_count']}}</td>
                                    <td class="col-1">{{$tr_hi['specialists']}}</td>
                                    <td class="col-1">{{$tr_hi['team_count']}}</td>
                                    <td class="col-1 text-right">{{number_format($tr_hi['entry_fee'],2)}}</td>
                                    <td class="col-1 text-right">{{number_format($tr_hi['specialist_registration_fee'],2)}}</td>
                                    <td class="col-1 text-right">{{number_format($tr_hi['team_fee'],2)}}</td>
                                    <td class="col-1 text-right">{{$symbols}}{{number_format($tr_hi['total_fee'],2)}}</td>
                                <tr>
                                <?php
                                    $total_reg_at += $tr_hi['at_count'];
                                    $total_reg_sp_at += $tr_hi['specialists'];
                                ?>
                            @endforeach
                            @endif
                                <?php
                                    $total_reg_fees += (isset($transaction->breakdown['gym']['deposit_subtotal']) && $transaction->breakdown['gym']['deposit_subtotal'] > 0) ? $transaction->breakdown['gym']['deposit_subtotal'] : $transaction->breakdown['gym']['subtotal'];
                                    $total_participant_fees = $transaction->breakdown['gym']['total'];

                                    $coupon = (isset($transaction->breakdown['gym']['coupon']) && $transaction->breakdown['gym']['coupon'] > 0) ? $transaction->breakdown['gym']['coupon'] : 0;
                                    if ($total_reg_fees == 0 && $coupon > 0) {
                                    $total_reg_fees -= (($transaction->breakdown['gym']['handling'] + $transaction->breakdown['gym']['processor']) - $transaction->breakdown['gym']['total']);
                                    }
                                ?>
                        <?php  }
                        catch(Exception $e){
                        }
                        ?>
                        @endforeach
                    </tbody>
                    <thead>
                        <tr>
                            <th class="col-1" colspan="3"></th>
                            <th class="col-1">Total</th>
                            <th class="col-1">{{$total_reg_at}}</th>
                            <th class="col-1">{{$total_reg_sp_at}}</th>
                            <th class="col-1" colspan="5"></th>
                            <th class="col-1  text-right">{{number_format($total_reg_fees + $coupon,2)}}</th>
                        <tr>
                    </thead>
                </table>
            @else
                No Transaction.
            @endif
        </div> 
        <div class="float-parent" style="padding-top: 20px !important;">
            <span><strong>Payment Method</strong> : Full</span><br>
            <table class="table-0 full-width">
                <thead>
                    <tr>
                        <th class="">Date</th>
                        <th class=""></th>
                        <th class="">Amount</th>
                    <tr>
                </thead>
                <tbody>
                    @if ($registrations->count() < 1) <tr>
                        <td class="" colspan="3">No refunds.</td>
                        <tr>
                            @else
                        <?php
                            $total = 0;
                        ?>
                        @foreach ($registration->transactions as $transaction)
                            <?php
                                $gym_total = (isset($transaction->breakdown['gym']['deposit_total']) && $transaction->breakdown['gym']['deposit_total'] > 0) ? $transaction->breakdown['gym']['deposit_total'] : $transaction->breakdown['gym']['total'];
                                $coupon = (isset($transaction->breakdown['gym']['coupon']) && $transaction->breakdown['gym']['coupon'] > 0) ? $transaction->breakdown['gym']['coupon'] : 0;

                                $coupon_text = $coupon > 0 ? " + Coupon: $" . $coupon : '';

                                $is_deposit = $transaction->is_deposit ? " - Deposit" : " ";
                            ?>
                            <tr>
                                <td class="">{{$transaction['created_at']->format(Helper::AMERICAN_SHORT_DATE)}}</td>
                                <td class="">{{$transaction->methodName() }} {{$is_deposit}} {{ $coupon_text }}</td>
                                <td class="  text-right">{{number_format($gym_total+$coupon,2)}}
                                </td>
                            <tr>
                            <?php
                                $total += $gym_total + $coupon; //$transaction->breakdown['gym']['total'];
                            ?>
                        @endforeach
                    @endif
                </tbody>
                <thead>
                    <tr>
                        <th class=" text-right" colspan="2">Total Payment :</th>
                        <th class="  text-right">{{number_format($total, 2)}}</th>
                    <tr>
                </thead>
            </table>
        </div>
        <div class="float-parent" style="padding-top: 20px !important;">
            <span><strong>Refund</strong> : By Check* - all refunds are processed directly between meet host and
                registrant</span><br>
            <table class="table-0 full-width">
                <thead>
                    <tr>
                        <th class="">Type</th>
                        <th class="">Payment Method</th>
                        <th class="">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    @if ($feeArr[$index]['refund_fees'] < 1 && $feeArr[$index]['refund_meet_fees'] < 1) <tr>
                        <td class="" colspan="3">No refunds.</td>
                        </tr>
                        @else
                        <tr>
                            <td class="">Participant Refund</td>
                            <td class="">Manually Outside AllGymnastics</td>
                            <td class="  text-right">{{number_format($feeArr[$index]['refund_fees'],2)}}</td>
                        </tr>
                        <tr>
                            <td class="">Meet Host Refund</td>
                            <td class="">Manually Outside AllGymnastics</td>
                            <td class="  text-right">{{number_format($feeArr[$index]['refund_meet_fees'],2)}}</td>
                        </tr>
                        @endif
                </tbody>
                <thead>
                    <tr>
                        <th class=" text-right"></th>
                        <th class=" text-right">Total Refund:</th>
                        <th class=" text-right">
                            {{number_format($feeArr[$index]['refund_fees']-$feeArr[$index]['refund_meet_fees'], 2)}}</th>
                    </tr>
                </thead>
            </table>
            *Note: @if(number_format($feeArr[$index]['refund_fees']-$feeArr[$index]['refund_meet_fees'], 2) == 0) {{ 'N/A' }}
                    @elseif(number_format($feeArr[$index]['refund_fees']-$feeArr[$index]['refund_meet_fees'], 2) < 0)
                {{ "Club must pay the refund directly to meet host" }} @else
                {{"Meet host must pay the refund directly to club"}} @endif
        </div>
        @if(isset($registration->audit_report))                            
        @foreach($registration->audit_report as $indexs => $change)
            @php
                $count_change = count($change["new"]) + count($change["moved"]) + count($change["scratched"]);
            @endphp
            @if($indexs == "athlete" && $count_change > 0)
            <div class="float-parent" style="padding-top: 20px !important; ">
                <span><strong>Athlete Changes</strong></span><br>
                <table class="table-0 full-width">
                    <thead>
                    <tr>
                        <th >First Name</th>
                        <th >Last Name</th>
                        <th >Current Level</th>
                        <th >Previous Level</th>
                        <th >Sanction</th>
                        <th >Status</th>
                        <th >Fee</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($change as $key => $value) 
                        @foreach($value as $k => $v)
                            <tr>
                                <td >{{$v['first_name']}}</td>
                                <td>{{$v['last_name']}}</td>
                                <td>{{$v['current_level']}}</td>
                                <td>{{$v['previous_level']}}</td>
                                <td>{{$v['sanction']}}</td>
                                <td>{{$key}}</td>
                                <td>{{number_format($v['fee'],2)}}</td>
                            </tr>
                        @endforeach
                    @endforeach
                    </tbody>
                </table>
            </div>                           
            @endif
            @if($indexs == "specialist" && $count_change > 0)
            <div class="float-parent" style="padding-top: 20px !important; ">
                <span><strong>Specialist Changes</strong></span><br>
                <table class="table-0 full-width">
                    <thead>
                    <tr>
                        <th>First Name</th>
                        <th>Last Name</th>
                        <th>Current Level</th>
                        <th>Previous Level</th>
                        <th>Sanction</th>
                        <th>Status</th>
                        <th>Event</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($change as $key => $value) 
                        @foreach($value as $k => $v)
                            <tr>
                                <td>{{$v['first_name']}}</td>
                                <td>{{$v['last_name']}}</td>
                                <td>{{$v['current_level']}}</td>
                                <td>{{$v['previous_level']}}</td>
                                <td>{{$v['sanction']}}</td>
                                <td>{{$key}}</td>
                                <td>
                                @foreach($v['event'] as $s => $t)
                                        {{ $t['name']  .': '. number_format($t['fee'],2)}} </br>
                                @endforeach
                                </td>
                            </tr>
                        @endforeach
                    @endforeach
                    </tbody>
                </table>
            </div>                           
            @endif
            @if($indexs == "coach" && $count_change > 0)
            <div class="float-parent" style="padding-top: 20px !important; ">
                <span><strong>Coach Changes</strong></span><br>
                <table class="table-0 full-width">
                    <thead>
                    <tr>
                        <th>First Name</th>
                        <th>Last Name</th>
                        <th>Status</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($change as $key => $value) 
                        @foreach($value as $k => $v)
                            <tr>
                                <td>{{$v['first_name']}}</td>
                                <td>{{$v['last_name']}}</td>
                                <td>{{$key}}</td>
                            </tr>
                        @endforeach
                    @endforeach
                    </tbody>
                </table>
            </div>                           
            @endif
        @endforeach
        @endif
        <div style="page-break-after: always;"></div>
    @endforeach
    <hr><br>

</body>

<div style="position: absolute; vertical-align: bottom;" >
    * Admin and CC/ACH fees are non-refundable
</div>
</html>