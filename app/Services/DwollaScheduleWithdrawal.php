<?php

namespace App\Services;

use App\Models\ErrorCodeCategory;
use DwollaSwagger\Configuration;
use DwollaSwagger\ApiException;
use DwollaSwagger\ApiClient;
use DwollaSwagger\CustomersApi;
use GuzzleHttp\Client as Guzzle;
use App\Exceptions\CustomDwollaException;
use DwollaSwagger\FundingsourcesApi;
use App\Exceptions\CustomBaseException;
use DwollaSwagger\models\Transfer;
use DwollaSwagger\RootApi;
use DwollaSwagger\TransfersApi;
use DwollaSwagger\WebhooksApi;
use DwollaSwagger\WebhooksubscriptionsApi;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use App\Mail\MemberInvitationAccepted;
use App\Models\Gym;
use App\Models\MeetRegistration;
use App\Models\Notification;
use App\Models\SanctioningBody;
use Illuminate\Http\Request;
use \App\Models\User;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Laravel\Passport\TransientToken;
use Illuminate\Auth\Events\Registered;
use App\Services\DwollaService;
use Illuminate\Support\Facades\Hash;
use App\Helper;
use App\Mail\User\WithdrawalRequestedMailable;
use App\Models\Setting;
use App\Models\UserBalanceTransaction;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class DwollaScheduleWithdrawal {

    public function withdrawBalanceSchedule($user, $request, $fee = 0) {
        
        try {
            DB::beginTransaction();

            // $user = User::lockForUpdate()->find(auth()->user()->id); /** @var User $user */
            if ($user == null)
                throw new CustomBaseException('No such user with id `' . $user()->id . '`');

            $bankAccount = $request['bank_id'];
            if (!isset($bankAccount) || ($bankAccount == ''))
            {
                Log::info('Invalid bank account', [$bankAccount]);
                throw new CustomBaseException('Invalid bank account', -1);
            }

            $amount = $request['amount'];
            if (Helper::isFloat($amount)) {
                $amount = (float) $amount;
                if ($amount < 0)
                {
                    Log::info('Invalid amount: Amount needs to be a positive value', [$amount]);
                    throw new CustomBaseException('Invalid amount: Amount needs to be a positive value', -1);
                }
                // if ($amount > 5000.0 && !$isDwollaVerified)
                //     throw new CustomBaseException('Dwolla unverified: Amount needs to be less than $5000', -1);

                if (($amount+$fee) > $user->cleared_balance)
                {
                    Log::info('You do not have enough balance to withdraw that amount.', [$amount]);
                    throw new CustomBaseException('You do not have enough balance to withdraw that amount.', -1);
                }
            } else {
                Log::info('Invalid amount: Amount needs to be a positive value', [$amount]);
                throw new CustomBaseException('Invalid amount', -1);
            }

            $amount = $user->cleared_balance - $fee;
            $featuredFee = $user->meetFeaturedWithdrawalFeeWithdraw($user)['total_net_value'];
            $amount = $amount - $featuredFee;
            
            
            $bankAccount = $user->getBankAccount($bankAccount);
            // if (!Str::endsWith($bankAccount['_links']['customer']['href'], $user->dwolla_customer_id))
            if ( $bankAccount == null )
            throw new CustomBaseException('No such bank account linked to your account.', -1);
            $loop_count = 1;
            $full_amount = $amount;
            $extra_amount = 0;
            if($amount > 10000)
            {
                $loop_count = (int)($amount / 10000);
                $full_amount = $loop_count * 10000;
                $extra_amount = $amount - $full_amount;
                for($i=0;$i<$loop_count;$i++)
                {
                    if($i == 0)
                        $fees = $fee;
                    else
                        $fees = 0;
                        
                    $this->process_payment($user,10000,$featuredFee,  $fees,$bankAccount);
                }
                if($extra_amount > 0)
                {
                    Log::info('Fees : ', [$fee]);
                    $this->process_payment($user,$extra_amount,$featuredFee, 0,$bankAccount);
                }
            }
            else
            {
                Log::info('Fees : ', [$fee]);
                $this->process_payment($user,$amount,$featuredFee, $fee,$bankAccount);
            }

            return array("code"=>200);
        } catch(\Throwable $e) {
            throw $e;
        }
    }
    public function process_payment($user,$amount,$featuredFee, $fee,$bankAccount)
    {
        $dwollaService = resolve(DwollaService::class); /** @var DwollaService $dwollaService */

        $transaction = null;
        $balanceTransaction = null;
        $isDwollaVerified = null;
        try{
        $source = $dwollaService->getFundingSource(config('services.dwolla.master')); // trackthis
        $now = now();
        $extra = $fee > 0 ? "+ fees ($".$fee.")" : '';
        $balanceTransaction = $user->balance_transactions()->create([
            'processor_id' => null,
            'total' => - ($amount + $fee),
            'description' => 'Balance automatic withdrawal $' . number_format($amount, 2) . $extra,
            'clears_on' => $now,
            'type' => UserBalanceTransaction::BALANCE_TRANSACTION_TYPE_WITHDRAWAL,
            'status' => UserBalanceTransaction::BALANCE_TRANSACTION_STATUS_PENDING
        ]); /** @var UserBalanceTransaction $balanceTransaction */

        // create entry for feature fee charges during withdraw balance
        if ($featuredFee > 0) {
            $featuredFeeChargeEntry = $user->balance_transactions()->create([
                'processor_id' => null,
                'total' => -$featuredFee,
                'description' => 'Featured fee charge when withdraw balance',
                'clears_on' => $now,
                'type' => UserBalanceTransaction::BALANCE_TRANSACTION_TYPE_WITHDRAWAL,
                'status' => UserBalanceTransaction::BALANCE_TRANSACTION_STATUS_PENDING
            ]);
        }

        $user->cleared_balance -= ($amount + $featuredFee + $fee);
        $user->save();

        // is_withdrawal status update in meet transaction table
        $currentUser = User::with(['gyms.meets.registrations.transactions'])->where('id', $user->id)->get();
        $meetTransactions = $currentUser->pluck('gyms')->collapse()
            ->pluck('meets')->collapse()->where('is_featured',true)
            ->pluck('registrations')->collapse()
            ->pluck('transactions')->collapse()->where('is_withdrawal',false);

        foreach ($meetTransactions as $meetTransaction) {
            $meetTransaction->update(['is_withdrawal' => true]);
        }

        $transaction = $dwollaService->initiateACHTransfer(
            $source['_links']['self']['href'],
            $bankAccount['_links']['self']['href'],
            $amount,
            [
                'type' => 'withrawal',
                'withdrawn' => $amount,
                'fee' => $fee,
                'balance_tx' => $balanceTransaction->id
            ]
        );
        
        DB::commit();

        $transaction = $dwollaService->getACHTransfer($transaction);

        $balanceTransaction->processor_id = $transaction['id'];
        $balanceTransaction->save();

        try {
            Mail::to($user->email)
                ->send(new WithdrawalRequestedMailable($balanceTransaction));
        } catch (\Throwable $th) {
        }
    }catch(\Throwable $e) {
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

        throw $e;
    }
    }

}