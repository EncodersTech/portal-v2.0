<?php

namespace App\Http\Controllers\Webhook;

use App\Exceptions\CustomBaseException;
use App\Helper;
use App\Jobs\ProcessDwollaTransferWebhook;
use App\Jobs\ProcessStripeTransferWebhook;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use App\Jobs\ProcessDwollaWebhook;
use App\Models\USAGSanction;
use App\Services\USAGService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Services\DwollaService;
use Symfony\Component\HttpFoundation\HeaderBag;
use App\Models\AuditEventType;
use App\Models\User;
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

class WebhookTestController extends \App\Http\Controllers\Webhook\BaseWebhookController
{
    public const WEBHOOK_ERROR_RESSOURCE_NOT_FOUND = 404;
    public const WEBHOOK_ERROR_INVALID_VALUE = 400;

    public function test(){
		return array(
            "test" => config('services.usag.log_payloads', false)
        );
	}
    public function dwolla()
    {
        try {
            $timestamp = new \DateTime(request()->timestamp);
            $this->registrationTransferCompletedHandler(request()->resourceId, $timestamp);

           return $this->success(['message' => 'Event handled successfully']);
        } catch (\Throwable $e) {
            //Log::critical(self::class . '@dwolla : ' . $e->getTraceAsString(), $e);
            return $this->error([
                'message' => $e->getMessage(),
                'code' => $e->getCode()
            ]);
        }
    }

    public function registrationTransferCompletedHandler(string $txId, \DateTime $timestamp)
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

            // Mail::to($transaction->meet_registration->gym->user->email)
            //     ->send(new TransactionCompletedMailable($transaction));

            // TODO : Mail to host

            DB::commit();            
        } catch(\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
        return true;
    }




}