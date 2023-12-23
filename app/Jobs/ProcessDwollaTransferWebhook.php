<?php

namespace App\Jobs;

use App\Exceptions\CustomBaseException;
use App\Models\ErrorCodeCategory;
use App\Models\User;
use App\Models\UserBalanceTransaction;
use App\Services\DwollaService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\HeaderBag;
use Throwable;

/**
 * Class ProcessDwollaTransferWebhook
 */
class ProcessDwollaTransferWebhook implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $payload;
    private $headers;

    /** @var DwollaService */
    private $dwollaService;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(string $payload, HeaderBag $headers)
    {
        $this->payload = $payload;
        $this->headers = $headers;
        $this->dwollaService = resolve(DwollaService::class);
    }

    /**
     * Execute the job.
     *
     * @return void
     * @throws Throwable
     * @throws CustomBaseException
     */
    public function handle()
    {
        // Maybe useFull to use rate limiting.
        $this->handleDwollaTransferWebhook();
    }

    /**
     * @throws Throwable
     * @throws CustomBaseException
     */
    private function handleDwollaTransferWebhook(): bool
    {
        $authentic = $this->dwollaService::verifyWebhookSignature(
            $this->headers->get('x-request-signature-sha-256'),
            config('services.dwolla.webhook_secret'),
            $this->payload
        );
        if (!$authentic)
            throw new CustomBaseException('Webhook payload authentication failed ', -1);

        if (config('logging.log_webhook_payload')) {
            Log::channel('webhooks-dwolla')->info('Processing Dwolla Webhook : ' . $this->payload);
        }

        $this->payload = json_decode($this->payload);
        if ($this->payload === null)
            throw new CustomBaseException('Server error', ErrorCodeCategory::getCategoryBase('General') + 1);

        $timestamp = new \DateTime($this->payload->timestamp);
        switch ($this->payload->topic) {
            // case 'customer_bank_transfer_creation_failed':
            //     return $this->customerTransferFailedOrCanceledHandler(
            //         $this->payload->resourceId,
            //         false,
            //         $timestamp
            //     );
            //     break;

            // case 'customer_bank_transfer_cancelled':
            //     return $this->customerTransferCanceledHandler(
            //         $this->payload->resourceId,
            //         false,
            //         $timestamp
            //     );
            //     break;

            // case 'customer_bank_transfer_failed':
            //     return $this->customerTransferFailedOrCanceledHandler(
            //         $this->payload->resourceId,
            //         false,
            //         $timestamp
            //     );
            //     break;

            // case 'customer_bank_transfer_completed':
            //     return $this->customerTransferCompletedHandler(
            //         $this->payload->resourceId,
            //         $timestamp
            //     );
            //     break;

            case 'customer_transfer_completed':
                return $this->customerTransferCompletedHandler(
                    $this->payload->resourceId,
                    $timestamp
                );
                break;

            case 'customer_transfer_cancelled':
                return $this->customerTransferCanceledHandler(
                    $this->payload->resourceId,
                    false,
                    $timestamp
                );
                break;

            case 'customer_transfer_failed':
                return $this->customerTransferFailedOrCanceledHandler(
                    $this->payload->resourceId,
                    false,
                    $timestamp
                );
                break;

            default:
        }
        return true;
    }

    private function customerTransferFailedOrCanceledHandler(string $txId, bool $failed = true, \DateTime $timestamp): bool
    {
        try {
            $dwollaService = resolve(DwollaService::class); /** @var DwollaService $dwollaService */
            $dwollaTx = $dwollaService->getACHTransfer($txId);
            switch ($dwollaTx->metadata->type) {
                case 'wallet_to_wallet':
                    return $this->transferFailedOrCanceledHandler($txId, $dwollaTx->metadata, $failed, $timestamp);
                    break;

                case 'bank_to_bank':
                    return $this->transferFailedOrCanceledHandler($txId, $dwollaTx->metadata, $failed, $timestamp);
                    break;

                case 'bank_to_wallet':
                    return $this->transferFailedOrCanceledHandler($txId, $dwollaTx->metadata, $failed, $timestamp);
                    break;

                case 'wallet_to_bank':
                    return $this->transferFailedOrCanceledHandler($txId, $dwollaTx->metadata, $failed, $timestamp);
                    break;

                default:
                    break;
            }
        } catch(Throwable $e) {
            throw $e;
        }
        return true;
    }

    private function customerTransferCanceledHandler(string $txId, bool $failed = true, \DateTime $timestamp): bool
    {
        try {
            $dwollaService = resolve(DwollaService::class); /** @var DwollaService $dwollaService */
            $dwollaTx = $dwollaService->getACHTransfer($txId);
            switch ($dwollaTx->metadata->type) {
                case 'wallet_to_wallet':
                    return $this->transferCanceledHandler($txId, $dwollaTx->metadata, $failed, $timestamp);
                    break;

                case 'bank_to_bank':
                    return $this->transferCanceledHandler($txId, $dwollaTx->metadata, $failed, $timestamp);
                    break;

                case 'bank_to_wallet':
                    return $this->transferCanceledHandler($txId, $dwollaTx->metadata, $failed, $timestamp);
                    break;

                case 'wallet_to_bank':
                    return $this->transferCanceledHandler($txId, $dwollaTx->metadata, $failed, $timestamp);
                    break;

                default:
                    break;
            }
        } catch(Throwable $e) {
            throw $e;
        }
        return true;
    }

    /**
     * @throws CustomBaseException
     * @throws Throwable
     */
    private function transferFailedOrCanceledHandler(string $txId, $meta, bool $failed = true, \DateTime $timestamp): bool
    {
        $sourceUser = User::find($meta->source_id);
        if ($sourceUser == null)
            throw new CustomBaseException('No user found');
        $amount = $meta->amount;
        /** @var UserBalanceTransaction $transaction */
        $transaction = UserBalanceTransaction::where('processor_id', $txId)
            ->where('status', UserBalanceTransaction::BALANCE_TRANSACTION_STATUS_PENDING)
            ->first();
        if ($transaction == null) {
            $transaction = UserBalanceTransaction::where('id', $meta->balance_tx)
                ->where('status', UserBalanceTransaction::BALANCE_TRANSACTION_STATUS_PENDING)
                ->first(); /** @var UserBalanceTransaction $transaction */

            if ($transaction == null)
                return true;
                // throw new CustomBaseException('No pending transaction with id ' . $txId);
        }

        DB::beginTransaction();
        try {
            $failedTransfer = $this->dwollaService->getACHFailedTransfer($txId);
            $sourceUser->cleared_balance += $amount;
            $sourceUser->save();
            $transaction->reason = $failedTransfer->code;
            $transaction->status = UserBalanceTransaction::BALANCE_TRANSACTION_STATUS_FAILED;
            $transaction->save();

            DB::commit();
        } catch(Throwable $e) {
            DB::rollBack();
            throw $e;
        }
        return true;
    }

    private function customerTransferCompletedHandler(string $txId, \DateTime $timestamp): bool
    {
        try {
            $dwollaTx = $this->dwollaService->getACHTransfer($txId);
            switch ($dwollaTx->metadata->type) {
                case 'wallet_to_wallet':
                    return $this->transferCompletedWalletToWalletHandler($txId, $dwollaTx->metadata, $timestamp);
                    break;

                case 'bank_to_bank':
                    return  $this->transferCompletedBankToBankHandler($txId, $dwollaTx->metadata, $timestamp);
                    break;

                case 'bank_to_wallet':
                    return  $this->transferCompletedBankToWalletHandler($txId, $dwollaTx->metadata, $timestamp);
                    break;

                case 'wallet_to_bank':
                    return  $this->transferCompletedWalletToBankHandler($txId, $dwollaTx->metadata, $timestamp);
                    break;

                default:
                    break;
            }
        } catch(Throwable $e) {
            throw $e;
        }
        return true;
    }

    /**
     * @throws CustomBaseException
     * @throws Throwable
     */
    public function transferCompletedWalletToWalletHandler(string $txId, $meta, \DateTime $timestamp): bool
    {
        $destinationUser = User::find($meta->destination_id);
        $sourceUser = User::find($meta->source_id);
        $amount = $meta->amount;
        if ($destinationUser == null)
            throw new CustomBaseException('No User Found.');

        /** @var UserBalanceTransaction $transaction */
        $transaction = UserBalanceTransaction::where('processor_id', $txId)
            ->where('status', UserBalanceTransaction::BALANCE_TRANSACTION_STATUS_PENDING)
            ->first();
        if ($transaction == null) {
            /** @var UserBalanceTransaction $transaction */
            $transaction = UserBalanceTransaction::where('id', $meta->balance_tx)
                ->where('status', UserBalanceTransaction::BALANCE_TRANSACTION_STATUS_PENDING)
                ->first();

            if ($transaction == null)
                throw new CustomBaseException('No pending transaction with id ' . $txId);
        }

        DB::beginTransaction();
        try {
            $destinationUser->balance_transactions()->create([
                'source_user_id' => $sourceUser->id,
                'destination_user_id' => $destinationUser->id,
                'processor_id' => null,
                'total' => $amount,
                'description' => 'Dwolla Wallet to Wallet transfer from '.$sourceUser->fullName().' to '.$destinationUser->fullName(),
                'clears_on' => now(),
                'type' => UserBalanceTransaction::BALANCE_TRANSACTION_TYPE_ADMIN,
                'status' => UserBalanceTransaction::BALANCE_TRANSACTION_STATUS_CLEARED
            ]);
            $destinationUser->pending_balance += $amount;
            $destinationUser->cleared_balance += $amount;
            $destinationUser->save();
            $transaction->status = UserBalanceTransaction::BALANCE_TRANSACTION_STATUS_CLEARED;
            $transaction->save();

            DB::commit();
        } catch(\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
        return true;
    }

    /**
     * @throws CustomBaseException
     * @throws Throwable
     */
    public function transferCompletedBankToBankHandler(string $txId, $meta, \DateTime $timestamp): bool
    {
        $destinationUser = User::find($meta->destination_id);
        $sourceUser = User::find($meta->source_id);
        $amount = $meta->amount;
        if ($destinationUser == null)
            throw new CustomBaseException('No User Found.');

        /** @var UserBalanceTransaction $transaction */
        $transaction = UserBalanceTransaction::where('processor_id', $txId)
            ->where('status', UserBalanceTransaction::BALANCE_TRANSACTION_STATUS_PENDING)
            ->first();
        $transaction_2 = UserBalanceTransaction::where('processor_id', $txId."_verified")
            ->where('status', UserBalanceTransaction::BALANCE_TRANSACTION_STATUS_CLEARED)
            ->first();
        if ($transaction == null) {
            /** @var UserBalanceTransaction $transaction */
            $transaction = UserBalanceTransaction::where('id', $meta->balance_tx)
                ->where('status', UserBalanceTransaction::BALANCE_TRANSACTION_STATUS_PENDING)
                ->first();

            if ($transaction == null && $transaction_2 == null)
                throw new CustomBaseException('No pending transaction with id ' . $txId);
        }
        if($transaction_2 == null)
        {
            $transaction->processor_id = $transaction->processor_id . "_verified";
            $transaction->status = UserBalanceTransaction::BALANCE_TRANSACTION_STATUS_CLEARED;
            $transaction->save();
        }
        else
        {
            $transaction->status = UserBalanceTransaction::BALANCE_TRANSACTION_STATUS_CLEARED;
            $transaction->save();
        }
        
        DB::beginTransaction();
        try {
            if($transaction_2 == null)
            {
                $destinationUser->balance_transactions()->create([
                    'source_user_id' => $sourceUser->id,
                    'destination_user_id' => $destinationUser->id,
                    'processor_id' => $txId,
                    'total' => $amount,
                    'description' => 'Dwolla Bank to Bank transfer from '.$sourceUser->fullName().' to '.$destinationUser->fullName(),
                    'clears_on' => now(),
                    'type' => UserBalanceTransaction::BALANCE_TRANSACTION_TYPE_ADMIN,
                    'status' => UserBalanceTransaction::BALANCE_TRANSACTION_STATUS_PENDING
                    // 'status' => UserBalanceTransaction::BALANCE_TRANSACTION_STATUS_CLEARED
                ]);
                $destinationUser->pending_balance += $amount;
                $destinationUser->cleared_balance += $amount;
                $destinationUser->save();
            }
            // $transaction->status = UserBalanceTransaction::BALANCE_TRANSACTION_STATUS_CLEARED;
            // $transaction->save();

            DB::commit();
        } catch(\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
        return true;
    }

    /**
     * @throws CustomBaseException
     * @throws Throwable
     */
    public function transferCompletedBankToWalletHandler(string $txId, $meta, \DateTime $timestamp): bool
    {
        $destinationUser = User::find($meta->destination_id);
        $sourceUser = User::find($meta->source_id);
        $amount = $meta->amount;
        if ($destinationUser == null)
            throw new CustomBaseException('No User Found.');

        /** @var UserBalanceTransaction $transaction */
        $transaction = UserBalanceTransaction::where('processor_id', $txId)
            ->where('status', UserBalanceTransaction::BALANCE_TRANSACTION_STATUS_PENDING)
            ->first();
        if ($transaction == null) {
            /** @var UserBalanceTransaction $transaction */
            $transaction = UserBalanceTransaction::where('id', $meta->balance_tx)
                ->where('status', UserBalanceTransaction::BALANCE_TRANSACTION_STATUS_PENDING)
                ->first();

            if ($transaction == null)
                throw new CustomBaseException('No pending transaction with id ' . $txId);
        }

        DB::beginTransaction();
        try {
            $destinationUser->balance_transactions()->create([
                'source_user_id' => $sourceUser->id,
                'destination_user_id' => $destinationUser->id,
                'processor_id' => null,
                'total' => $amount,
                'description' => 'Dwolla Bank to Wallet transfer from '.$sourceUser->fullName().' to '.$destinationUser->fullName(),
                'clears_on' => now(),
                'type' => UserBalanceTransaction::BALANCE_TRANSACTION_TYPE_ADMIN,
                'status' => UserBalanceTransaction::BALANCE_TRANSACTION_STATUS_CLEARED
            ]);
            $destinationUser->pending_balance += $amount;
            $destinationUser->cleared_balance += $amount;
            $destinationUser->save();
            $transaction->status = UserBalanceTransaction::BALANCE_TRANSACTION_STATUS_CLEARED;
            $transaction->save();

            DB::commit();
        } catch(\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
        return true;
    }

    /**
     * @throws CustomBaseException
     * @throws Throwable
     */
    public function transferCompletedWalletToBankHandler(string $txId, $meta, \DateTime $timestamp): bool
    {
        $destinationUser = User::find($meta->destination_id);
        $sourceUser = User::find($meta->source_id);
        $amount = $meta->amount;
        if ($destinationUser == null)
            throw new CustomBaseException('No User Found.');

        /** @var UserBalanceTransaction $transaction */
        $transaction = UserBalanceTransaction::where('processor_id', $txId)
            ->where('status', UserBalanceTransaction::BALANCE_TRANSACTION_STATUS_PENDING)
            ->first();
        if ($transaction == null) {
            /** @var UserBalanceTransaction $transaction */
            $transaction = UserBalanceTransaction::where('id', $meta->balance_tx)
                ->where('status', UserBalanceTransaction::BALANCE_TRANSACTION_STATUS_PENDING)
                ->first();

            if ($transaction == null)
                throw new CustomBaseException('No pending transaction with id ' . $txId);
        }

        DB::beginTransaction();
        try {
            $destinationUser->balance_transactions()->create([
                'source_user_id' => $sourceUser->id,
                'destination_user_id' => $destinationUser->id,
                'processor_id' => null,
                'total' => $amount,
                'description' => 'Dwolla Wallet to Bank transfer from '.$sourceUser->fullName().' to '.$destinationUser->fullName(),
                'clears_on' => now(),
                'type' => UserBalanceTransaction::BALANCE_TRANSACTION_TYPE_ADMIN,
                'status' => UserBalanceTransaction::BALANCE_TRANSACTION_STATUS_CLEARED
            ]);
            $destinationUser->pending_balance += $amount;
            $destinationUser->cleared_balance += $amount;
            $destinationUser->save();
            $transaction->status = UserBalanceTransaction::BALANCE_TRANSACTION_STATUS_CLEARED;
            $transaction->save();

            DB::commit();
        } catch(\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
        return true;
    }


    private function transferCanceledHandler(string $txId, $meta, bool $failed = true, \DateTime $timestamp): bool
    {
        $sourceUser = User::find($meta->source_id);
        $amount = $meta->amount;
        /** @var UserBalanceTransaction $transaction */
        $transaction = UserBalanceTransaction::where('processor_id', $txId)
            ->where('status', UserBalanceTransaction::BALANCE_TRANSACTION_STATUS_PENDING)
            ->first();
        if ($transaction == null) {
            $transaction = UserBalanceTransaction::where('id', $meta->balance_tx)
                ->where('status', UserBalanceTransaction::BALANCE_TRANSACTION_STATUS_PENDING)
                ->first(); /** @var UserBalanceTransaction $transaction */

            if ($transaction == null)
                return true;
                // throw new CustomBaseException('No pending transaction with id ' . $txId);
        }

        DB::beginTransaction();
        try {
            $sourceUser->cleared_balance += $amount;
            $sourceUser->save();
            $transaction->status = UserBalanceTransaction::BALANCE_TRANSACTION_STATUS_UNCONFIRMED;
            $transaction->save();

            DB::commit();
        } catch(Throwable $e) {
            DB::rollBack();
            throw $e;
        }
        return true;
    }
}