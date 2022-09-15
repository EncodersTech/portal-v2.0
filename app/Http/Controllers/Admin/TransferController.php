<?php

namespace App\Http\Controllers\Admin;

use App\Exceptions\CustomDwollaException;
use App\Http\Controllers\AppBaseController;
use App\Models\User;
use App\Models\UserBalanceTransaction;
use App\Queries\TransferDataTable;
use App\Services\DwollaService;
use DwollaSwagger\models\Transfer;
use Exception;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Yajra\DataTables\DataTables;

/**
 * Class TransferController
 */
class TransferController extends AppBaseController
{
    /**
     * @throws Exception
     */
    public function transfer(Request $request)
    {
        if ($request->ajax()) {
            return DataTables::of((new TransferDataTable())->get())->make(true);
        }

        return view('admin.transfer.index');
    }

    /**
     * @return Application|Factory|View
     */
    public function createTransfer()
    {
        $users = User::where('email_verified_at', '!=', null)->get()->pluck('full_name', 'id')->toArray();

        return view('admin.transfer.create_transfer', compact(['users']));
    }

    /**
     * @param  Request  $request
     *
     * @return JsonResponse
     */
    public function storeTransfer(Request $request): JsonResponse
    {
        try {

            $transaction = null;
            $input = $request->all();
            $request->validate([
                'amount' => ['required', 'numeric', 'min:1'],
            ]);
            $sourceUserId = $input['source_user'];
            $destinationUserId = $input['destination_user'];

            if (empty($input['source_user'])) {
                return $this->sendError('The Source User is required.');
            }
            if (empty($input['destination_user'])) {
                return $this->sendError('The Destination User is required.');
            }
            if ($sourceUserId == $destinationUserId) {
                return $this->sendError('The Source and Destination User are same.');
            }

            if (empty($input['source_wallet_bank']) && empty($input['source_wallet_balance']) && empty($input['allGym_source_balance'])) {
                return $this->sendError('select AtLeast one on bankAccount or dwolla wallet or allGym balance in source user');
            }
            if (!empty($input['source_wallet_bank'])) {
                if (!empty($input['source_wallet_balance'])) {
                    return $this->sendError('Please select any one bank account or allgym balance from source user.');
                }
                if (!empty($input['allGym_source_balance'])) {
                    return $this->sendError('Please select any one bank account or dwolla wallet or allgym balance from source user.');
                }
            }
            if (!empty($input['source_wallet_balance'])) {
                if (!empty($input['allGym_source_balance'])) {
                    return $this->sendError('Please select any one bank account or allgym balance from source user.');
                }
            }

            if (empty($input['source_wallet_bank']) && empty($input['source_wallet_balance']) && empty($input['allGym_source_balance'])) {
                return $this->sendError('select AtLeast one on bankAccount or dwolla wallet or allGym balance');
            }
            if (!empty($input['destination_wallet_bank'])) {
                if (!empty($input['destination_wallet_balance'])) {
                    return $this->sendError('Please select any one bank account or allgym balance from source user.');
                }
                if (!empty($input['allGym_destination_balance'])) {
                    return $this->sendError('Please select any one bank account or dwolla wallet or allgym balance from source user.');
                }
            }
            if (!empty($input['destination_wallet_balance'])) {
                if (!empty($input['allGym_destination_balance'])) {
                    return $this->sendError('Please select any one bank account or allgym balance from source user.');
                }
            }

            if (!empty($input['allGym_source_balance'])) {
                if (empty($input['allGym_destination_balance'])) {
                    return $this->sendError('Transfer only allgym balance to allgym balance.');
                }
            }
            if (!empty($input['allGym_destination_balance'])) {
                if (empty($input['allGym_source_balance'])) {
                    return $this->sendError('Transfer only allgym balance to allgym balance.');
                }
            }

            $amount = $input['amount'];
            /** @var User $sourceUser */
            $sourceUser = User::find($sourceUserId);
            /** @var User $destinationUser */
            $destinationUser = User::find($destinationUserId);
            /** @var DwollaService $dwollaService */
            $dwollaService = resolve(DwollaService::class);

            // All Gym Balance transfers from source to destination
            if (!empty($input['allGym_source_balance']) && !empty($input['allGym_destination_balance'])) {
                DB::beginTransaction();
                if ($amount > $sourceUser->cleared_balance) {
                    return $this->sendError('source user not have enough balance to transfer that amount.');
                }
                // create source user balance transaction
                $sourceUser->balance_transactions()->create([
                    'source_user_id' => $sourceUser->id,
                    'destination_user_id' => $destinationUser->id,
                    'processor_id' => null,
                    'total' => -$amount,
                    'description' => 'All Gym Balance transfer from '.$sourceUser->fullName().' to '.$destinationUser->fullName(),
                    'clears_on' => now(),
                    'type' => UserBalanceTransaction::BALANCE_TRANSACTION_TYPE_ADMIN,
                    'status' => UserBalanceTransaction::BALANCE_TRANSACTION_STATUS_CLEARED
                ]);

                $sourceUser->cleared_balance -= $amount;
                $sourceUser->save();

                // create destination user balance transaction
                $destinationUser->balance_transactions()->create([
                    'processor_id' => null,
                    'total' => $amount,
                    'description' =>'All Gym Balance transfer from '.$sourceUser->fullName().' to '.$destinationUser->fullName(),
                    'clears_on' => now(),
                    'type' => UserBalanceTransaction::BALANCE_TRANSACTION_TYPE_WITHDRAWAL,
                    'status' => UserBalanceTransaction::BALANCE_TRANSACTION_STATUS_CLEARED
                ]);

                $destinationUser->pending_balance += $amount;
                $destinationUser->cleared_balance += $amount;
                $destinationUser->save();
                DB::commit();
                return $this->sendSuccess('Payment Transfer Successfully.');
            }

            // Bank to Bank transfers from source to destination
            if (!empty($input['source_wallet_bank']) && !empty($input['destination_wallet_bank'])) {
                DB::beginTransaction();
                if ($amount > $sourceUser->cleared_balance) {
                    return $this->sendError('source user not have enough balance to transfer that amount.');
                }
                $sourceBankAccount = $sourceUser->getBankAccount($input['source_wallet_bank']);
                if (!Str::endsWith($sourceBankAccount['_links']['customer']['href'], $sourceUser->dwolla_customer_id)) {
                    return $this->sendError('No such bank account linked to source account.');
                }
                $destinationBankAccount = $destinationUser->getBankAccount($input['destination_wallet_bank']);
                if (!Str::endsWith($destinationBankAccount['_links']['customer']['href'], $destinationUser->dwolla_customer_id)) {
                    return $this->sendError('No such bank account linked to destination account.');
                }
                $sourceBalanceTransaction = $sourceUser->balance_transactions()->create([
                    'source_user_id' => $sourceUser->id,
                    'destination_user_id' => $destinationUser->id,
                    'processor_id' => null,
                    'total' => -$amount,
                    'description' => 'Dwolla Bank to Bank transfer from '.$sourceUser->fullName().' to '.$destinationUser->fullName(),
                    'clears_on' => now(),
                    'type' => UserBalanceTransaction::BALANCE_TRANSACTION_TYPE_ADMIN,
                    'status' => UserBalanceTransaction::BALANCE_TRANSACTION_STATUS_PENDING
                ]);
                $sourceUser->cleared_balance -= $amount;
                $sourceUser->save();

                $transaction = $dwollaService->initiateACHTransfer(
                    $sourceBankAccount['_links']['self']['href'],
                    $destinationBankAccount['_links']['self']['href'],
                    $amount,
                    [
                        'type' => 'bank_to_bank',
                        'amount' => $amount,
                        'balance_tx' => $sourceBalanceTransaction->id,
                        'source_id' => $sourceUser->id,
                        'destination_id' => $destinationUser->id
                    ]
                );
                $transaction = $dwollaService->getACHTransfer($transaction);

                $sourceBalanceTransaction->processor_id = $transaction['id'];
                $sourceBalanceTransaction->save();
                DB::commit();
                return $this->sendSuccess('Payment Transfer Successfully.');
            }

            // dwolla wallet to wallet transfer from source to destination
            if (!empty($input['source_wallet_balance']) && !empty($input['destination_wallet_balance'])) {
                DB::beginTransaction();
                if ($amount > $sourceUser->cleared_balance) {
                    return $this->sendError('source user not have enough balance to transfer that amount.');
                }
                $sourceWalletAccount = $sourceUser->getBankAccount($input['source_wallet_balance']);
                if ($amount > $sourceWalletAccount['balance']->value) {
                    return $this->sendError('source user not have enough balance to transfer that amount.');
                }
                if (!Str::endsWith($sourceWalletAccount['_links']['customer']['href'], $sourceUser->dwolla_customer_id)) {
                    return $this->sendError('No such bank account linked to source account.');
                }
                $destinationWalletAccount = $destinationUser->getBankAccount($input['destination_wallet_balance']);
                if (!Str::endsWith($destinationWalletAccount['_links']['customer']['href'], $destinationUser->dwolla_customer_id)) {
                    return $this->sendError('No such bank account linked to destination account.');
                }
                $sourceBalanceTransaction = $sourceUser->balance_transactions()->create([
                    'source_user_id' => $sourceUser->id,
                    'destination_user_id' => $destinationUser->id,
                    'processor_id' => null,
                    'total' => -$amount,
                    'description' => 'Dwolla wallet to wallet transfer from '.$sourceUser->fullName().' to '.$destinationUser->fullName(),
                    'clears_on' => now(),
                    'type' => UserBalanceTransaction::BALANCE_TRANSACTION_TYPE_ADMIN,
                    'status' => UserBalanceTransaction::BALANCE_TRANSACTION_STATUS_PENDING
                ]);
                $sourceUser->cleared_balance -= $amount;
                $sourceUser->save();

                $transaction = $dwollaService->initiateACHTransfer(
                    $sourceWalletAccount['_links']['self']['href'],
                    $destinationWalletAccount['_links']['self']['href'],
                    $amount,
                    [
                        'type' => 'wallet_to_wallet',
                        'amount' => $amount,
                        'balance_tx' => $sourceBalanceTransaction->id,
                        'source_id' => $sourceUser->id,
                        'destination_id' => $destinationUser->id
                    ]
                );
                $transaction = $dwollaService->getACHTransfer($transaction);

                $sourceBalanceTransaction->processor_id = $transaction['id'];
                $sourceBalanceTransaction->save();
                DB::commit();
                return $this->sendSuccess('Payment Transfer Successfully.');
            }

            // bank account to dwolla wallet transfer from source to destination
            if (!empty($input['source_wallet_bank']) && !empty($input['destination_wallet_balance'])) {
                DB::beginTransaction();
                if ($amount > $sourceUser->cleared_balance) {
                    return $this->sendError('source user not have enough balance to transfer that amount.');
                }
                $sourceBankAccount = $sourceUser->getBankAccount($input['source_wallet_bank']);
                if (!Str::endsWith($sourceBankAccount['_links']['customer']['href'], $sourceUser->dwolla_customer_id)) {
                    return $this->sendError('No such bank account linked to source account.');
                }
                $destinationBankAccount = $destinationUser->getBankAccount($input['destination_wallet_balance']);
                if (!Str::endsWith($destinationBankAccount['_links']['customer']['href'], $destinationUser->dwolla_customer_id)) {
                    return $this->sendError('No such bank account linked to destination account.');
                }
                $sourceBalanceTransaction = $sourceUser->balance_transactions()->create([
                    'source_user_id' => $sourceUser->id,
                    'destination_user_id' => $destinationUser->id,
                    'processor_id' => null,
                    'total' => -$amount,
                    'description' => 'Dwolla bank to wallet transfer from '.$sourceUser->fullName().' to '.$destinationUser->fullName(),
                    'clears_on' => now(),
                    'type' => UserBalanceTransaction::BALANCE_TRANSACTION_TYPE_ADMIN,
                    'status' => UserBalanceTransaction::BALANCE_TRANSACTION_STATUS_PENDING
                ]);
                $sourceUser->cleared_balance -= $amount;
                $sourceUser->save();

                $transaction = $dwollaService->initiateACHTransfer(
                    $sourceBankAccount['_links']['self']['href'],
                    $destinationBankAccount['_links']['self']['href'],
                    $amount,
                    [
                        'type' => 'bank_to_wallet',
                        'amount' => $amount,
                        'balance_tx' => $sourceBalanceTransaction->id,
                        'source_id' => $sourceUser->id,
                        'destination_id' => $destinationUser->id
                    ]
                );
                $transaction = $dwollaService->getACHTransfer($transaction);

                $sourceBalanceTransaction->processor_id = $transaction['id'];
                $sourceBalanceTransaction->save();
                DB::commit();
                return $this->sendSuccess('Payment Transfer Successfully.');
            }

            // wallet to bank account transfer from source to destination
            if (!empty($input['source_wallet_balance']) && !empty($input['destination_wallet_bank'])) {
                DB::beginTransaction();
                if ($amount > $sourceUser->cleared_balance) {
                    return $this->sendError('source user not have enough balance to transfer that amount.');
                }
                $sourceWalletAccount = $sourceUser->getBankAccount($input['source_wallet_balance']);
                if ($amount > $sourceWalletAccount['balance']->value) {
                    return $this->sendError('source user not have enough balance to transfer that amount.');
                }
                if (!Str::endsWith($sourceWalletAccount['_links']['customer']['href'], $sourceUser->dwolla_customer_id)) {
                    return $this->sendError('No such bank account linked to source account.');
                }
                $destinationBankAccount = $destinationUser->getBankAccount($input['destination_wallet_bank']);
                if (!Str::endsWith($destinationBankAccount['_links']['customer']['href'], $destinationUser->dwolla_customer_id)) {
                    return $this->sendError('No such bank account linked to destination account.');
                }
                $sourceBalanceTransaction = $sourceUser->balance_transactions()->create([
                    'source_user_id' => $sourceUser->id,
                    'destination_user_id' => $destinationUser->id,
                    'processor_id' => null,
                    'total' => -$amount,
                    'description' => 'Dwolla wallet to bank transfer from '.$sourceUser->fullName().' to '.$destinationUser->fullName(),
                    'clears_on' => now(),
                    'type' => UserBalanceTransaction::BALANCE_TRANSACTION_TYPE_ADMIN,
                    'status' => UserBalanceTransaction::BALANCE_TRANSACTION_STATUS_PENDING
                ]);
                $sourceUser->cleared_balance -= $amount;
                $sourceUser->save();

                $transaction = $dwollaService->initiateACHTransfer(
                    $sourceWalletAccount['_links']['self']['href'],
                    $destinationBankAccount['_links']['self']['href'],
                    $amount,
                    [
                        'type' => 'wallet_to_bank',
                        'amount' => $amount,
                        'balance_tx' => $sourceBalanceTransaction->id,
                        'source_id' => $sourceUser->id,
                        'destination_id' => $destinationUser->id
                    ]
                );
                $transaction = $dwollaService->getACHTransfer($transaction);

                $sourceBalanceTransaction->processor_id = $transaction['id'];
                $sourceBalanceTransaction->save();
                DB::commit();
                return $this->sendSuccess('Payment Transfer Successfully.');
            }

            return $this->sendError('The Given Data not match.');
        } catch (Exception $e) {
            if (DB::transactionLevel() > 0)
                DB::rollBack();

            if ($transaction != null) {
                $cancelFailed = true;
                try {
                    // Try and cancel the transaction.
                    if ($transaction instanceof Transfer){
                        $transaction = $transaction['_links']['self']['href'];
                    };

                    $cancelFailed = !$dwollaService->cancelACHTransfer($transaction);
                } catch (\Throwable $e) {
                    Log::debug('Panic TX Cancelation : ' . $e->getMessage());
                }
            }

            return $this->sendError($e->getMessage());
        }
    }

    public function getUserBankAccounts(Request $request, bool $throw = true, bool $removed = false): JsonResponse
    {
        try {
            $userId = $request->get('userId');
            if (!empty($userId)) {
                $user = User::find($userId);
                $sourceBankAccounts = resolve(DwollaService::class)->listFundingSources($user->dwolla_customer_id);
                if (empty($sourceBankAccounts)) {
                    $sourceBankAccounts = [];
                }
            } else {
                $sourceBankAccounts = [];
            }
            $banks = [];
            foreach ($sourceBankAccounts as $sourceBankAccount) {
                if (isset($sourceBankAccount->bankAccountType)) {
                    $sourceBankAccount->bankAccountType = $sourceBankAccount->bankAccountType;
                } else {
                    $sourceBankAccount->bankAccountType = null;
                }
                $banks[] = array(
                    'id' => $sourceBankAccount->id,
                    'type' => ($sourceBankAccount->type == 'bank'),
                    'balanceType' => ($sourceBankAccount->type == 'balance'),
                    'bankType' => $sourceBankAccount->bankAccountType,
                    'name' => $sourceBankAccount->name,
                );
            }

            return $this->sendResponse($banks, 'User Bank Accounts Retrieved Successfully.');
        } catch (CustomDwollaException $e) {
            Log::error($e->getMessage());
            $result =' You cannot make changes to your linked bank accounts for the time being. Please contact us as soon as possible.';

            if ($throw)
                return $this->sendError($result);

            return $this->sendError($result);
        }
    }
}