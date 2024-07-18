<?php

namespace App\Repositories;

use App\Models\MeetTransaction;
use App\Models\Setting;
use App\Models\User;
use App\Models\UserBalanceTransaction;
use Barryvdh\Snappy\Facades\SnappyPdf as PDF;
use Barryvdh\Snappy\PdfWrapper;

/**
 * Class DashboardRepository
 */
class DashboardRepository
{
    public function getDashboardData()
    {
        $data = null;

        //Total collected handling fee(Admin_fee)
        $data['admin_fee'] = 0;

        //Total collected process fee (CC_fee)
        $data['process_fee'] = 0;

        //Total Stripe fees
        $data['stripe_cc_fee'] = 0;

        $meetTransactions = MeetTransaction::where('status',MeetTransaction::STATUS_COMPLETED);

        $data['admin_fee'] = $meetTransactions->sum('handling_fee');
        $data['process_fee'] = $meetTransactions->sum('processor_fee');
        $data['stripe_cc_fee'] = $meetTransactions->sum('processor_charge_fee');

        //pending withdrawal balance (Available fund)
        $data['withdrawal_balance'] = User::where('cleared_balance','>',0)->sum('cleared_balance');

        $data['pending_withdrawal_request'] = UserBalanceTransaction::where('type', UserBalanceTransaction::BALANCE_TRANSACTION_TYPE_WITHDRAWAL)->where('status', UserBalanceTransaction::BALANCE_TRANSACTION_STATUS_PENDING)->sum('total');

        //pending withdrawal cc balance
        $withdrawn_cc_fee = Setting::getSetting('all_time_withdrawn_credit_fee');
        $data['pending_withdrawal_cc'] = $data['process_fee'] + $data['admin_fee'] - $data['stripe_cc_fee'] - $withdrawn_cc_fee->value;

        return $data;

    }

    public function pendingWithdrawalBalanceReport(): PdfWrapper
    {
        $data['users'] = User::where('cleared_balance','>',0)->where('cleared_balance','!=',0)->orderBy('cleared_balance','ASC')->get();
        $data['total_balance'] = User::where('cleared_balance','>',0)->sum('cleared_balance');

        return PDF::loadView('admin.reports.pending_withdrawal_balance.report', $data);
        /** @var PdfWrapper $pdf */
    }
    public function individualPendingWithdrawalBalanceReport($id): PdfWrapper
    {
        $data['user'] = User::find($id);

        $data['revenue'] = UserBalanceTransaction::where('user_id',$id)
                            ->where('type', UserBalanceTransaction::BALANCE_TRANSACTION_TYPE_REGISTRATION_REVENUE)
                            ->where('status', UserBalanceTransaction::BALANCE_TRANSACTION_STATUS_CLEARED)
                            ->sum('total');
        $data['allgym_balance_received'] = UserBalanceTransaction::where('user_id',$id)
                            ->where('type', UserBalanceTransaction::BALANCE_TRANSACTION_TYPE_REGISTRATION_PAYMENT)
                            ->where('status', UserBalanceTransaction::BALANCE_TRANSACTION_STATUS_CLEARED)
                            ->where('total','>',0)
                            ->sum('total');
        $data['registration_payment'] = UserBalanceTransaction::where('user_id',$id)
                            ->where('type', UserBalanceTransaction::BALANCE_TRANSACTION_TYPE_REGISTRATION_PAYMENT)
                            ->where('total','<',0)
                            ->sum('total') * -1;
        $data['dwolla_verification_fee'] = UserBalanceTransaction::where('user_id',$id)
                            ->where('type', UserBalanceTransaction::BALANCE_TRANSACTION_TYPE_DWOLLA_VERIFICATION_FEE)
                            ->sum('total') * -1;
        $data['admin_transaction'] = UserBalanceTransaction::where('user_id',$id)
                            ->where('type', UserBalanceTransaction::BALANCE_TRANSACTION_TYPE_ADMIN)
                            ->where('description','!=','Overdraft Adjustment by Admin')
                            ->sum('total') * -1;
        $data['withdrawal'] = UserBalanceTransaction::where('user_id',$id)
                            ->where('type', UserBalanceTransaction::BALANCE_TRANSACTION_TYPE_WITHDRAWAL)
                            ->sum('total') * -1;

        $data['overdraft'] = UserBalanceTransaction::where('user_id',$id)
                                ->where('type', UserBalanceTransaction::BALANCE_TRANSACTION_TYPE_ADMIN)
                                ->where('description', 'Overdraft Adjustment by Admin')
                                ->sum('total') * -1;

        if($data['allgym_balance_received'] > 0)
            $data['revenue'] += $data['allgym_balance_received'] ;

        $data['total'] = $data['overdraft'] + $data['revenue'] - $data['registration_payment'] - $data['dwolla_verification_fee'] - $data['admin_transaction'] - $data['withdrawal'];

        $data['total_cleared_balance'] = $data['user']->cleared_balance;

        // dd($data);

        return PDF::loadView('admin.reports.pending_withdrawal_balance.pending_individual_report', $data);
        /** @var PdfWrapper $pdf */
    }
}
