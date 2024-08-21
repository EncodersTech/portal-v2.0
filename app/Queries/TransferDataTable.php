<?php

namespace App\Queries;

use App\Models\UserBalanceTransaction;
use Illuminate\Support\Facades\Auth;

/**
 * Class TransferDataTable
 */
class TransferDataTable
{
    /*
    * @return mixed
    */
    public function get()
    {
        /** @var UserBalanceTransaction $query */
        return UserBalanceTransaction::with(['user', 'sourceUser', 'destinationUser'])
            ->where('type', UserBalanceTransaction::BALANCE_TRANSACTION_TYPE_ADMIN)
            ->where('description', '!=','Overdraft Adjustment by Admin')
            ->orderByDesc('created_at')
            ->select('user_balance_transactions.*');
    }
}
