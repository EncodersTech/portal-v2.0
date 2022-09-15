<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
// use App\Services\DwollaService;
use App\Services\StripeService;
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

/**
 * Class ProcessStripeTransferWebhook
 */
class ProcessStripeTransferWebhook implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $payload;
    private $headers;

    /** @var StripeService */
    private $stripeService;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(string $payload, HeaderBag $headers)
    {
        Log::info('webhook', $payload);
        $this->payload = $payload;
        $this->headers = $headers;
        $this->stripeService = resolve(StripeService::class);
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
        $this->handleStripeTransferWebhook();
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
    /**
     * @throws Throwable
     * @throws CustomBaseException
     */
    private function handleStripeTransferWebhook(): bool
    {
        if($pd->data->object->object == "account")
            $key = "whsec_FYtjBfB2FBNaL0p3i0F0OSjREcvLmnSK";
        $authentic = $this->stripeService::verifyWebhookSignature(
            $this->headers->get('stripe-signature'),
            $key==null ? config('services.stripe.webhook_secret') : $key,
            $this->payload
        );
        if (!$authentic)
            throw new CustomBaseException('Webhook payload authentication failed ', -1);

        $this->payload = json_decode($this->payload);
        if ($this->payload === null)
        throw new CustomBaseException('Server error', ErrorCodeCategory::getCategoryBase('General') + 1);
        
        switch ($this->payload->type) {
            case 'charge.succeeded':
                return $this->registrationTransferCompletedHandler(
                    $this->payload->data->object->id
                );
                break;

            case 'charge.failed':
                return $this->registrationTransferFailedOrCanceleddHandler(
                    $this->payload->data->object->id,
                    true
                );
                break;

            case 'charge.expired':
                return $this->registrationTransferFailedOrCanceleddHandler(
                    $this->payload->data->object->id,
                    false
                );
                break;
            case 'account.updated':
                Log::info('webhook', $this->payload->data->object);
                return $this->updateConnectAccount(
                    $this->payload->data->object
                );
            default:
        }
        return true;
    }

    

    public function registrationTransferCompletedHandler(string $txId): bool
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

    private function registrationTransferFailedOrCanceleddHandler(string $txId, bool $failed = true)
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
}
