<table>
    <thead>
        <!-- <tr><td><img src="img/logos/red_and_white_transparent.png" width="400" alt="AllGymnastics" srcset=""></td></tr> -->
        <tr><td colspan="12" style="text-align:center;"><b>Registration Details Report</b></td></tr>
        <tr><td colspan="12">Meet: {{ $meet->name }}</td></tr>
        <tr><td colspan="12">Meet Host: {{$meet->gym->name}}</td></tr>
        <tr><td colspan="12">Date: {{ $meet->start_date->format(Helper::AMERICAN_SHORT_DATE) }} -
        {{ $meet->end_date->format(Helper::AMERICAN_SHORT_DATE) }}</td></tr>
        
    </thead>
    <tbody>
    @foreach($registrations as $index => $registration)
        <!-- GYM DETAILS and FEES SUMMARY START -->
        <tr></tr>
        <tr>
            <td colspan="6" style="text-align: center; background-color:gray;"><b>Gym Details</b></td>
            <td colspan="6" style="text-align: center; background-color:gray;"><b>Fees Summary</b></td>
        </tr>
        <tr>
            <td colspan="3"><b>Gym Name</b></td>
            <td colspan="3">{{$registration->gym->short_name}}</td>
            <td colspan="3"><b>Description</b></td>
            <td colspan="3"><b>Participant Fees</b></td>
        </tr>
        <tr>
            <td colspan="3">Address</td>
            <td colspan="3">
                {{ $registration->gym->addr_1 }},

                @if ($registration->gym->addr_2)
                {{ $registration->gym->addr_2 }},
                @endif

                {{ $registration->gym->city }},
                {{ (!empty($registration->gym) && !empty( $registration->gym->state_id)) ? $registration->gym->state->code.',' : ''}}
                {{ $registration->gym->zipcode }},
                {{ $registration->gym->country->name }}
            </td>
            <td colspan="3">Individual Fees</td>
            <td colspan="3">{{number_format($feeArr[$index]['reg_fees'], 2)}}</td>
        </tr>
        <tr>
            <td colspan="3">Contact Person</td>
            <td colspan="3">#{{ $registration->gym->user->first_name.' '.$registration->gym->user->last_name }}</td>
            <td colspan="3">Specialist Fees</td>
            <td colspan="3">{{number_format($feeArr[$index]['specialist_fee'], 2)}}</td>
        </tr>
        <tr>
            <td colspan="3">Club #</td>
            <td colspan="3">{{ !empty($registration->gym->usag_membership)?'#USAG:'.$registration->gym->usag_membership:''}}{{!empty($registration->gym->usaigc_membership)?', #USAIGC:'.$registration->gym->usaigc_membership:''}}{{!empty($registration->gym->aau_membership)?', #AAU:'.$registration->gym->aau_membership:''}}{{!empty($registration->gym->nga_membership)?', #NGA:'.$registration->gym->nga_membership:''}}</td>
            <td colspan="3">Team Fees</td>
            <td colspan="3">{{number_format($feeArr[$index]['team_fees'], 2)}}</td>
        </tr>
        <tr>
            <td colspan="3">Email Address</td>
            <td colspan="3"><?php echo wordwrap($registration->gym->user->email, 25, "\n", true); ?></td>
            <td colspan="3">Admin Fee*</td>
            <td colspan="3">{{number_format($feeArr[$index]['admin_fees'], 2)}}</td>
        </tr>
        <tr>
            <td colspan="3"></td>
            <td colspan="3"></td>
            <td colspan="3">Card Fee*</td>
            <td colspan="3">{{number_format($feeArr[$index]['card_fees'], 2)}}</td>
        </tr>
        <tr>
            <td colspan="3"></td>
            <td colspan="3"></td>
            <td colspan="3">Late Fee*</td>
            <td colspan="3">{{number_format($feeArr[$index]['late_fee'], 2)}}</td>
        </tr>
        <tr>
            <td colspan="3"></td>
            <td colspan="3"></td>
            <td colspan="3">Scratch Credit Used</td>
            <td colspan="3">{{number_format($feeArr[$index]['used_credit'], 2)}}</td>
        </tr>
        <tr>
            <td colspan="3"></td>
            <td colspan="3"></td>
            <td colspan="3"><b>Total</b></td>
            <td colspan="3">{{number_format($feeArr[$index]['total_fees'], 2)}}</td>
        </tr>
        <!-- GYM DETAILS and FEES SUMMARY END -->

        <!-- Transaction History Start -->
        <tr></tr>
        <tr><td colspan="12" style="text-align: center; background-color:gray;"><b>Transaction History</b></td></tr>
        @if ($registrations->count() > 0)
        <tr>
            <td><b>Date</b></td>
            <td><b>Tran's ID</b></td>
            <td><b>Type:</b></td>
            <td colspan="8" style="text-align: center;"><b>Details</b></td>
            <td><b>Fee</b></td>
        </tr>
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
            <td>{{$transaction['created_at']->format(Helper::AMERICAN_SHORT_DATE)}}</td>
            <td>{{$transaction->id}}</td>
            <td>{{$reg_type}}</td>
            <td>Level</td>
            <td>Athlete</td>
            <td>Specialist</td>
            <td>Team</td>
            <td>Entry Fee</td>
            <td>Specialist Fee</td>
            <td>Team Fee</td>
            <td>Amount</td>
            <td>
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
        </tr>
        @if(isset($transaction['level_reg_history']))
            @foreach ($transaction['level_reg_history'] as $key => $tr_hi)
            <tr>
                <td></td>
                <td></td>
                <td></td>
                <td>{{$tr_hi['name']}}</td>
                <td>{{$symbols}}{{$tr_hi['at_count']}}</td>
                <td>{{$tr_hi['specialists']}}</td>
                <td>{{$tr_hi['team_count']}}</td>
                <td>{{number_format($tr_hi['entry_fee'],2)}}</td>
                <td>{{number_format($tr_hi['specialist_registration_fee'],2)}}</td>
                <td>{{number_format($tr_hi['team_fee'],2)}}</td>
                <td>{{$symbols}}{{number_format($tr_hi['total_fee'],2)}}</td>
            </tr>
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
        }
        catch(Exception $e){
        }
        ?>
        @endforeach
        <tr>
            <th colspan="3"></th>
            <th><b>Total</b></th>
            <th>{{$total_reg_at}}</th>
            <th>{{$total_reg_sp_at}}</th>
            <th colspan="5"></th>
            <th>{{number_format($total_reg_fees + $coupon,2)}}</th>
        </tr>
        @else
        <td class="" colspan="12">No transaction history.</td>
        @endif
        <!-- Transaction History End -->

        <!-- Payment Method Start -->
        <tr></tr>
        <tr><td colspan="12" style="text-align: center; background-color:gray;"><b><strong>Payment Method</strong> : Full</b></td></tr>
        <tr>
            <td colspan="4"><b>Date</b></td>
            <td colspan="4"><b>Method</b></td>
            <td colspan="4"><b>Amount</b></td>
        </tr>
        @if ($registrations->count() < 1)
        <td class="" colspan="12">No payment method.</td>
        @else
        <?php $total = 0; ?>
        @foreach ($registration->transactions as $transaction)
        <?php
            $gym_total = (isset($transaction->breakdown['gym']['deposit_total']) && $transaction->breakdown['gym']['deposit_total'] > 0) ? $transaction->breakdown['gym']['deposit_total'] : $transaction->breakdown['gym']['total'];
            $coupon = (isset($transaction->breakdown['gym']['coupon']) && $transaction->breakdown['gym']['coupon'] > 0) ? $transaction->breakdown['gym']['coupon'] : 0;

            $coupon_text = $coupon > 0 ? " + Coupon: $" . $coupon : '';

            $is_deposit = $transaction->is_deposit ? " - Deposit" : " ";
        ?>
        <tr>
            <td colspan="4">{{$transaction['created_at']->format(Helper::AMERICAN_SHORT_DATE)}}</td>
            <td colspan="4">{{$transaction->methodName() }} {{$is_deposit}} {{ $coupon_text }}</td>
            <td colspan="4">{{number_format($gym_total+$coupon,2)}}</td>
        </tr>
        @endforeach
        @endif
        
        <!-- Payment Method End -->

        <!-- Refund Start -->
        <tr></tr>
        <tr><td colspan="12" style="text-align: center; background-color:gray;"><b><strong>Refund</strong> : By Check* - all refunds are processed directly between meet host and
        registrant</b></td></tr>
        <tr>
            <td colspan="4"><b>Type</b></td>
            <td colspan="4"><b>Payment Method</b></td>
            <td colspan="4"><b>Amount</b></td>
        </tr>
        @if ($feeArr[$index]['refund_fees'] < 1 && $feeArr[$index]['refund_meet_fees'] < 1) <tr>
            <tr><td colspan="12">No refunds.</td></tr>
        @else
            <tr>
                <td colspan="4">Participant Refund</td>
                <td colspan="4">Manually Outside AllGymnastics</td>
                <td colspan="4">{{number_format($feeArr[$index]['refund_fees'],2)}}</td>
            </tr>
            <tr>
                <td colspan="4">Meet Host Refund</td>
                <td colspan="4">Manually Outside AllGymnastics</td>
                <td colspan="4">{{number_format($feeArr[$index]['refund_meet_fees'],2)}}</td>
            </tr>
            <tr>
                <th colspan="4"></th>
                <th colspan="4"><b>Total Refund:</b></th>
                <th colspan="4">{{number_format($feeArr[$index]['refund_fees']-$feeArr[$index]['refund_meet_fees'], 2)}}</th>
            </tr>
            <tr>
                <td colspan="12">
                *Note: @if(number_format($feeArr[$index]['refund_fees']-$feeArr[$index]['refund_meet_fees'], 2) == 0) {{ 'N/A' }}
                    @elseif(number_format($feeArr[$index]['refund_fees']-$feeArr[$index]['refund_meet_fees'], 2) < 0)
                {{ "Club must pay the refund directly to meet host" }} @else
                {{"Meet host must pay the refund directly to club"}} @endif
                </td>
            </tr>
        @endif
        <!-- Refund End -->
        <!-- Audit report start -->
         <tr></tr>
        @if(isset($registration->audit_report)) 
            @foreach($registration->audit_report as $indexs => $change)
                @php
                    $count_change = count($change["new"]) + count($change["moved"]) + count($change["scratched"]);
                @endphp
                @if($indexs == "athlete" && $count_change > 0)
                    <tr><td colspan="12" style="text-align: center; background-color:gray;"><b>Athlete Changes</b></td></tr>
                    <tr>
                        <th colspan="2"><b>First Name</b></th>
                        <th colspan="2"><b>Last Name</b></th>
                        <th colspan="2"><b>Current Level</b></th>
                        <th colspan="2"><b>Previous Level</b></th>
                        <th colspan="2"><b>Sanction</b></th>
                        <th><b>Status</b></th>
                        <th><b>Fee</b></th>
                    </tr>
                    @foreach($change as $key => $value) 
                        @foreach($value as $k => $v)
                            <tr>
                                <td colspan="2">{{$v['first_name']}}</td>
                                <td colspan="2">{{$v['last_name']}}</td>
                                <td colspan="2">{{$v['current_level']}}</td>
                                <td colspan="2">{{$v['previous_level']}}</td>
                                <td colspan="2">{{$v['sanction']}}</td>
                                <td>{{$key}}</td>
                                <td>{{number_format($v['fee'],2)}}</td>
                            </tr>
                        @endforeach
                    @endforeach
                @endif
                @if($indexs == "specialist" && $count_change > 0)
                    <tr><td colspan="12" style="text-align: center; background-color:gray;"><b>Specialist Changes</b></td></tr>
                    <tr>
                        <th colspan="2"><b>First Name</b></th>
                        <th colspan="2"><b>Last Name</b></th>
                        <th colspan="2"><b>Current Level</b></th>
                        <th colspan="2"><b>Previous Level</b></th>
                        <th colspan="2"><b>Sanction</b></th>
                        <th><b>Status</b></th>
                        <th><b>Event</b></th>
                    </tr>
                    @foreach($change as $key => $value) 
                        @foreach($value as $k => $v)
                            <tr>
                                <td colspan="2">{{$v['first_name']}}</td>
                                <td colspan="2">{{$v['last_name']}}</td>
                                <td colspan="2">{{$v['current_level']}}</td>
                                <td colspan="2">{{$v['previous_level']}}</td>
                                <td colspan="2">{{$v['sanction']}}</td>
                                <td>{{$key}}</td>
                                <td>
                                @foreach($v['event'] as $s => $t)
                                        {{ $t['name']  .': '. number_format($t['fee'],2)}} </br>
                                @endforeach
                                </td>
                            </tr>
                        @endforeach
                    @endforeach
                @endif
                @if($indexs == "coach" && $count_change > 0)
                    <tr><td colspan="12" style="text-align: center; background-color:gray;"><b>Coach Changes</b></td></tr>
                    <tr>
                        <th colspan="4"><b>First Name</b></th>
                        <th colspan="4"><b>Last Name</b></th>
                        <th colspan="4"><b>Status</b></th>
                    </tr>
                    @foreach($change as $key => $value) 
                        @foreach($value as $k => $v)
                            <tr>
                                <td colspan="4">{{$v['first_name']}}</td>
                                <td colspan="4">{{$v['last_name']}}</td>
                                <td colspan="4">{{$key}}</td>
                            </tr>
                        @endforeach
                    @endforeach
                @endif
            @endforeach
        @endif
        <tr></tr>
        <tr><td colspan="12" style="text-align: center; background-color:gray;"></td></tr>
        <tr><td colspan="12" style="text-align: center; background-color:gray;"><b>END OF GYM REPORT</b></td></tr>
        <tr><td colspan="12" style="text-align: center; background-color:gray;"></td></tr>
        <!-- Audit report ends -->
    @endforeach
    </tbody>
</table>