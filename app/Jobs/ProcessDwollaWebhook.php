<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Services\DwollaService;
use Symfony\Component\HttpFoundation\HeaderBag;
use Illuminate\Support\Facades\Log;
use App\Models\AuditEventType;
use App\Models\User;
use App\Exceptions\CustomBaseException;
use App\Mail\Registrant\TransactionCompletedMailable;
use App\Mail\Registrant\TransactionFailedMailable;
use App\Mail\User\VerificationStatusUpdated;
use App\Mail\User\WithdrawalCompletedMailable;
use App\Mail\User\WithdrawalFailedMailable;
use App\Models\ErrorCodeCategory;
use App\Models\AuditEvent;
use App\Models\DwollaVerificationAttempt;
use App\Models\Gym;
use App\Models\MeetRegistration;
use App\Models\MeetTransaction;
use App\Models\RegistrationAthlete;
use App\Models\RegistrationCoach;
use App\Models\RegistrationSpecialistEvent;
use App\Models\Setting;
use App\Models\UserBalanceTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class ProcessDwollaWebhook implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public const RATE_LIMIT_KEY = 'dwolla_webhooks';

    private $payload;
    private $headers;
    
    /** @var DwollaService */
    private $dwollaService;
    
    public $tries = 3;
    public $retryAfter = 300;

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
     */
    public function handle()
    {
        // Maybe usefull to use rate limiting.
        $this->handleDwollaWebhook();
    }

    public function failed(\Exception $e)
    {
        Log::critical(
            self::class . ' job failed',
            [
                'exception' => $e,
                'payload' => $this->payload,
                'headers' => $this->headers
            ]
        );
    }

    private function handleDwollaWebhook() {
        $authentic = $this->dwollaService::verifyWebhookSignature(
            $this->headers->get('x-request-signature-sha-256'),
            config('services.dwolla.webhook_secret'),
            $this->payload
        );

        if (!$authentic)
            throw new CustomBaseException('Webhook payload authentication failed', -1);

        if (config('logging.log_webhook_payload')) {
            Log::channel('webhooks-dwolla')->info('Processing Dwolla Webhook : ' . $this->payload);
        }

        $this->payload = json_decode($this->payload);
        if ($this->payload === null)
            throw new CustomBaseException('Server error', ErrorCodeCategory::getCategoryBase('General') + 1);

        $timestamp = new \DateTime($this->payload->timestamp);
        switch ($this->payload->topic) {
            case 'customer_verified':
            case 'customer_reverification_needed':
            case 'customer_verification_document_needed':
            case 'customer_suspended':
                return $this->customerVerificationAttempt(
                    $this->payload->resourceId,
                    $timestamp
                );
                break;

            case 'customer_verification_document_failed':
            case 'customer_verification_document_approved':
                return $this->customerDocumentEvent(
                    $this->payload->_links->customer->href,
                    $this->payload->resourceId,
                    $timestamp
                );
                break;

            case 'customer_funding_source_added':
                return $this->fundingSourceEventHandler(
                    $this->payload->resourceId,
                    AuditEventType::TYPE_BANK_LINK,
                    $timestamp
                );
                break;

            case 'customer_funding_source_verified':
                return $this->fundingSourceEventHandler(
                    $this->payload->resourceId,
                    AuditEventType::TYPE_BANK_VERIFIED,
                    $timestamp
                );
                break;

            case 'customer_funding_source_removed':
                return $this->fundingSourceEventHandler(
                    $this->payload->resourceId,
                    AuditEventType::TYPE_BANK_UNLINK,
                    $timestamp
                );
                break;

            case 'customer_transfer_completed':
                return $this->customerTransferCompletedHandler(
                    $this->payload->resourceId,
                    $timestamp
                );
                break;

            case 'customer_transfer_cancelled':
                return $this->customerTransferFailedOrCanceleddHandler(
                    $this->payload->resourceId,
                    false,
                    $timestamp
                );
                break;

            case 'customer_transfer_failed':
                return $this->customerTransferFailedOrCanceleddHandler(
                    $this->payload->resourceId,
                    false,
                    $timestamp
                );
                break;

            default:
        }
        return true;
    }
    
    private function fundingSourceEventHandler(string $source_id, int $type, \DateTime $timestamp)
    {
        $bankAccount = $this->dwollaService->getFundingSource($source_id);
        $customer = $this->dwollaService->retrieveCustomer($bankAccount->_links['customer']->href);
        $user = User::where('dwolla_customer_id', $customer->id)->first();
        
        if ($user == null)
            throw new CustomBaseException('No such user with linked Dwolla account ' . $customer->id);
        
        switch ($type) {
            case AuditEventType::TYPE_BANK_LINK:
                AuditEvent::bankLinked($bankAccount, $user, $timestamp);
                break;

            case AuditEventType::TYPE_BANK_VERIFIED:
                AuditEvent::bankVerified($bankAccount, $user, $timestamp);
                break;

            case AuditEventType::TYPE_BANK_UNLINK:
                AuditEvent::bankUnlinked($bankAccount, $user, $timestamp);
                break;

            default:
                throw new CustomBaseException('fundingSourceEventHandler() : Invalid event type ' . $type);
        }

        return true;
    }

    private function customerTransferCompletedHandler(string $txId, \DateTime $timestamp)
    {
        try {
            $dwollaService = resolve(DwollaService::class); /** @var DwollaService $dwollaService */
            $dwollaTx = $dwollaService->getACHTransfer($txId);
            switch ($dwollaTx->metadata->type) {
                case 'registration':
                    return $this->registrationTransferCompletedHandler($txId, $timestamp);
                    break;

                case 'withrawal':
                    return $this->withrawalTransferCompletedHandler($txId, $dwollaTx->metadata, $timestamp);
                    break;

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
        } catch(\Throwable $e) {
            throw $e;
        }
        return true;
    }

    private function customerTransferFailedOrCanceleddHandler(string $txId, bool $failed = true,
        \DateTime $timestamp)
    {
        try {     
            $dwollaService = resolve(DwollaService::class); /** @var DwollaService $dwollaService */
            $dwollaTx = $dwollaService->getACHTransfer($txId);
            switch ($dwollaTx->metadata->type) {
                case 'registration':
                    return $this->registrationTransferFailedOrCanceleddHandler($txId, $failed, $timestamp);
                    break;

                case 'withrawal':
                    return $this->withrawalTransferFailedOrCanceleddHandler($txId, $dwollaTx->metadata, $failed, $timestamp);
                    break;

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
        } catch(\Throwable $e) {
            throw $e;
        }
        return true;
    }

    private function registrationTransferCompletedHandler(string $txId, \DateTime $timestamp)
    {
        $transaction = MeetTransaction::where('processor_id', $txId)
                        ->where('status', MeetTransaction::STATUS_PENDING)
                        ->first(); /** @var MeetTransaction $transaction */
        if ($transaction == null)
            throw new CustomBaseException('No pending transaction with id ' . $txId);
        
        DB::beginTransaction();
        try {
            $registration = $transaction->meet_registration; /** @var MeetRegistration $registration */
            $host = User::lockForUpdate()->find($transaction->meet_registration->meet->gym->user->id); /** @var User $host */
            if ($host == null)
                throw new CustomBaseException('No such host');
            
            foreach ($transaction->athletes as $athlete) { /** @var RegistrationAthlete $athlete */
                if ($athlete->status != RegistrationAthlete::STATUS_PENDING_RESERVED)
                    throw new CustomBaseException('Invalid athlete status');
                $athlete->status = RegistrationAthlete::STATUS_REGISTERED;
                $athlete->save();
            }

            $events = $transaction->specialist_events;
            foreach ($events as $event) { /** @var RegistrationSpecialistEvent $event */
                if ($event->status != RegistrationSpecialistEvent::STATUS_SPECIALIST_PENDING)
                    throw new CustomBaseException('Invalid specialist status');

                $event->status = RegistrationSpecialistEvent::STATUS_SPECIALIST_REGISTERED;
                $event->save();
            }

            foreach ($transaction->coaches as $coach) { /** @var RegistrationCoach $coach */
                if ($coach->status != RegistrationCoach::STATUS_PENDING_RESERVED)
                    throw new CustomBaseException('Invalid coach status');
                $coach->status = RegistrationCoach::STATUS_REGISTERED;
                $coach->save();
            }

            $transaction->status = MeetTransaction::STATUS_COMPLETED;
            $transaction->save();

            if ($transaction->breakdown['gym']['used_balance'] != 0) {
                $balanceTransaction = $registration->user_balance_transaction()
                                        ->find($transaction->breakdown['gym']['used_balance_tx_id']);
                if ($balanceTransaction == null)
                    throw new CustomBaseException('No such balance transaction');

                $balanceTransaction->status = UserBalanceTransaction::BALANCE_TRANSACTION_STATUS_CLEARED;
                $balanceTransaction->save();
            }

            if ($transaction->breakdown['host']['total'] != 0) {
                $description = 'Revenue from ' . $transaction->meet_registration->gym->name .
                                '\'s registration in ' . $transaction->meet_registration->meet->name;
                $transaction->host_balance_transaction()->create([
                    'user_id' => $host->id,
                    'total' => $transaction->breakdown['host']['total'],
                    'description' =>  $description,
                    'clears_on' => now()->addDays(Setting::userBalanceHoldDuration()),
                    'type' => UserBalanceTransaction::BALANCE_TRANSACTION_TYPE_REGISTRATION_REVENUE,
                    'status' => UserBalanceTransaction::BALANCE_TRANSACTION_STATUS_PENDING
                ]);

                $host->pending_balance += $transaction->breakdown['host']['total'];
                $host->save();
            }

            Mail::to($transaction->meet_registration->gym->user->email)
                ->send(new TransactionCompletedMailable($transaction));

            // TODO : Mail to host

            DB::commit();            
        } catch(\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
        return true;
    }

    private function registrationTransferFailedOrCanceleddHandler(string $txId, bool $failed = true,
        \DateTime $timestamp)
    {
        $transaction = MeetTransaction::where('processor_id', $txId)
                        ->where('status', MeetTransaction::STATUS_PENDING)
                        ->first(); /** @var MeetTransaction $transaction */
        if ($transaction == null)
            throw new CustomBaseException('No pending transaction with id ' . $txId);
        
        DB::beginTransaction();
        try {
            $registration = $transaction->meet_registration; /** @var MeetRegistration $registration */

            $registrant = User::lockForUpdate()->find($registration->gym->user->id); /** @var MeetRegistration $registration */
            if ($registrant == null)
                throw new CustomBaseException('No such registrant');

            $registrationLateFee = $transaction->breakdown['registration_late_fee'];
            $registration->late_fee -= $registrationLateFee;
            $registration->save();

            $levelTeamFees = $transaction->breakdown['level_team_fees'];
            if (count($levelTeamFees) > 0) {
                $levelIds = array_keys($levelTeamFees);
                $levels = $registration->levels()->wherePivotIn('id', $levelIds)->get();
                foreach ($levels as $l) { /** @var AthleteLevel $l */
                    $l->pivot->team_fee -= $levelTeamFees[$l->pivot->id]['fee'];
                    $l->pivot->team_late_fee -= $levelTeamFees[$l->pivot->id]['late'];
                    $l->pivot->save();
                }
            }

            $transaction->status = ($failed ? MeetTransaction::STATUS_FAILED : MeetTransaction::STATUS_CANCELED);
            $transaction->save();

            if ($transaction->breakdown['gym']['used_balance'] != 0) {
                $balanceTransaction = $registration->user_balance_transaction()
                                        ->find($transaction->breakdown['gym']['used_balance_tx_id']);
                if ($balanceTransaction == null)
                    throw new CustomBaseException('No such balance transaction');

                $balanceTransaction->status = UserBalanceTransaction::BALANCE_TRANSACTION_STATUS_FAILED;
                $balanceTransaction->save();

                $registrant->cleared_balance += -$balanceTransaction->total;
                $registrant->save();
            }

            Mail::to($transaction->meet_registration->gym->user->email)
                ->send(new TransactionFailedMailable($transaction));

            // TODO : Mail to host

            DB::commit();            
        } catch(\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
        return true;
    }

    private function withrawalTransferCompletedHandler(string $txId, $meta, \DateTime $timestamp)
    {
        $transaction = UserBalanceTransaction::where('processor_id', $txId)
                        ->where('status', UserBalanceTransaction::BALANCE_TRANSACTION_STATUS_PENDING)
                        ->first(); /** @var UserBalanceTransaction $transaction */
        if ($transaction == null) {
            $transaction = UserBalanceTransaction::where('id', $meta->balance_tx)
                ->where('status', UserBalanceTransaction::BALANCE_TRANSACTION_STATUS_PENDING)
                ->first(); /** @var UserBalanceTransaction $transaction */

            if ($transaction == null)
                throw new CustomBaseException('No pending transaction with id ' . $txId);
        }
        
        DB::beginTransaction();
        try {
            $transaction->status = UserBalanceTransaction::BALANCE_TRANSACTION_STATUS_CLEARED;
            $transaction->save();

            Mail::to($transaction->user->email)
                ->send(new WithdrawalCompletedMailable($transaction));

            DB::commit();            
        } catch(\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
        return true;
    }

    private function withrawalTransferFailedOrCanceleddHandler(string $txId, $meta, bool $failed = true,
        \DateTime $timestamp)
    {
        $transaction = UserBalanceTransaction::where('processor_id', $txId)
                        ->where('status', UserBalanceTransaction::BALANCE_TRANSACTION_STATUS_PENDING)
                        ->first(); /** @var UserBalanceTransaction $transaction */
        if ($transaction == null) {
            $transaction = UserBalanceTransaction::where('id', $meta->balance_tx)
                ->where('status', UserBalanceTransaction::BALANCE_TRANSACTION_STATUS_PENDING)
                ->first(); /** @var UserBalanceTransaction $transaction */

            if ($transaction == null)
                throw new CustomBaseException('No pending transaction with id ' . $txId);
        }
        
        DB::beginTransaction();
        try {
            $user = User::lockForUpdate()->find($transaction->user_id); /** @var User $user */
            if ($user == null)
                throw new CustomBaseException('No such user');

            $user->cleared_balance += -$transaction->total;
            $user->save();

            $transaction->status = UserBalanceTransaction::BALANCE_TRANSACTION_STATUS_FAILED;
            $transaction->save();

            Mail::to($user->email)
                ->send(new WithdrawalFailedMailable($transaction));

            DB::commit();            
        } catch(\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
        return true;
    }

    private function customerDocumentEvent(string $customer, string $id, \DateTime $timestamp)
    {
        $dwollaService = resolve(DwollaService::class); /** @var DwollaService $dwollaService */
        $dwollaCustomer = $dwollaService->retrieveCustomer($customer);
        return $this->customerVerificationAttempt('', $timestamp, $dwollaCustomer);
    }

    private function customerVerificationAttempt(string $id, \DateTime $timestamp, $customer = null)
    {
        DB::beginTransaction();
        try {
            if ($customer === null) {
                $dwollaService = resolve(DwollaService::class);
                $dwollaCustomer = $dwollaService->retrieveCustomer($id);
            } else {
                $dwollaCustomer = $customer;
            } /** @var DwollaService $dwollaService */

            $user = User::where('dwolla_customer_id', $dwollaCustomer->id)->first(); /** @var User $user */
            if ($user == null)
                throw new CustomBaseException('No such user');

            $attempt = $user->dwolla_verification_attempts()
                    ->where('status', DwollaVerificationAttempt::STATUS_PENDING)
                    ->orderBy('created_at', 'DESC')
                    ->first();
            if ($attempt == null)
                throw new CustomBaseException('No such verification attempt.');

            $succeeded = $dwollaCustomer->status == DwollaService::STATUS_VERIFIED;

            $attempt->resulting_status = $dwollaCustomer->status;
            $attempt->status = (
                $succeeded ?
                DwollaVerificationAttempt::STATUS_SUCCEEDED :
                DwollaVerificationAttempt::STATUS_FAILED
            );
            $attempt->save();

            Mail::to($user->email)
                ->send(new VerificationStatusUpdated($succeeded, $attempt));

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
