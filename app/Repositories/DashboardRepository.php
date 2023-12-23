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
}
