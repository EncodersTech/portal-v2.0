<?php
namespace App\Models;

use App\Exceptions\CustomBaseException;
use App\Helper;
use App\Mail\Host\HostReceiveMeetRegistrationMailable;
use App\Mail\Host\RegistrationUpdateMailable;
use App\Mail\Registrant\TransportHelpMailable;
use App\Mail\Registrant\GymRegisteredMailable;
use App\Mail\Registrant\GymRegistrationUpdatedMailable;
use App\Mail\Registrant\HandlingFeeChargeFailedMailable;
use App\Mail\Registrant\HandlingFeeChargeMailable;
use App\Mail\Registrant\TransactionExecutedMailable;
use App\Models\Deposit;
use App\Models\MeetTransaction;
use App\Services\DwollaService;
use App\Services\IntellipayService;
use App\Services\StripeService;
use App\Traits\Excludable;
use DwollaSwagger\models\FundingSource;
use DwollaSwagger\models\Transfer;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use App\Models\Setting;
use App\Mail\Registrant\TransactionCompletedMailable;
use App\Mail\Registrant\TransactionFailedMailable;

class IntellipayModel extends Model
{
    use Excludable;

    public function clear_payment()
    {
        $intellipayService = resolve(IntellipayService::class);
        $data = MeetTransaction::where('method', MeetTransaction::PAYMENT_METHOD_ONETIMEACH)->where('status', MeetTransaction::STATUS_PENDING)->get();

        // dd($data);
        foreach($data as $d)
        {
            Log::info('Checking Onetime ACH Payment Status : ' . $d->id);
            $response = $intellipayService->clear_payment_by_id($d->processor_id);
            if($response['status'] == 200 )
            {
                if($response['data']['status'] >= 0)
                {
                    if($response['data']['state'] == 5)
                    {
                        // $d->status = MeetTransaction::STATUS_COMPLETED;
                        $this->registrationTransferCompletedHandler($d->processor_id);
                        Log::info('Onetime ACH Payment Status Cleared : ' . $d->id);
                    }
                    else if($response['data']['state'] == 3)
                    {
                        // $d->status = MeetTransaction::STATUS_FAILED;
                        $this->registrationTransferFailedOrCanceleddHandler($d->processor_id);
                        Log::info('Onetime ACH Payment Status Failed : ' . $d->id);
                    }
                    $d->save();
                }
                else
                {
                    Log::info('Onetime ACH Payment Status Failed : ' . $d->id . ' :: ' . $response['data']['message']);
                }
                
            }
            else
            {
                Log::info('Onetime ACH Payment Status Failed : ' . $d->id . ' :: ' . $response['data']);
            }
        }
    }
    private function registrationTransferCompletedHandler(string $txId): bool
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
?>