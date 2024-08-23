<?php

namespace App\Http\Controllers;

use App\Services\StripeService;
use App\Services\IntellipayService;
use App\Exceptions\CustomStripeException;
use App\Exceptions\CustomDwollaException;
use App\Exceptions\CustomBaseException;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Services\DwollaService;
use App\Services\DwollaScheduleWithdrawal;
use App\Helper;
use App\Models\Gym;
use App\Models\DwollaVerificationAttempt;
use App\Models\Setting;
use App\Models\MemberUser;
use App\Models\State;
use App\Models\UserBalanceTransaction;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use App\Models\MeetRegistration;
use App\Repositories\MeetRepository;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\HostDashBoardReport;
use Barryvdh\Snappy\Facades\SnappyPdf as PDF;
class UserAccountController extends Controller
{
    public function showProfile()
    {
        return view('user.profile', [
            'profile_picture_max_size' => Helper::formatByteSize(Setting::profilePictureMaxSize() * 1024),
            'current_page' => 'profile'
        ]);
    }
    public function stripebank()
    {
        $user = auth()->user(); 
        $banks = $user->getStripeBankAccounts(true);
        print_r($banks);
    }
    public function stripebankadd()
    {
        $attr = request()->validate(StripeService::BANK_TOKEN_RULES);
        auth()->user()->stripeAddBank($attr['bank_token'],$attr['account_name']);
        return back()->with('success', 'Your bank account is linked.');
    }
    public function stripebankverify()
    {
        $attr = request()->validate(StripeService::BANK_VERIFY_RULES);
        $isVerified = auth()->user()->stripeVerifyBank($attr['bank_token'],$attr['first_deposit'],$attr['second_deposit']);
        if($isVerified)
            return back()->with('success', 'Your bank account is Verified.');
        else
            return back()->with('error', 'Your bank account verification failed');
    }

    public function showPaymentOptions()
    {
        $user = auth()->user();     /** @var User $user */
        $settings = Setting::where('key','cc_gateway')->first();

        $cards = null;
        $bankAccounts = null;
        $stripe_error = null;
        $stripe_banks = null;
        $dwolla_error = null;

        $dwollaService = null;
        $dwollaCustomer = null;
        $dwollaAttempts = null;
        $dwollaCanVerify = null;

        if($settings->value == 0) // stripe
        {
            try {
                $cards = $user->getCards(true);
                //$stripe_banks = $user->getStripeBankAccounts(true);
            } catch (CustomStripeException $e) {
                $stripe_error = $e->getMessage();
            }
        }
        else // process for intellipay
        {
            $intellipayService = resolve(IntellipayService::class); /** @var IntellipayService $intellipayService */
            $cards = $intellipayService->getCards();
            // dd($cards);
        }
        
        try {
            $dwollaService = resolve(DwollaService::class); /** @var DwollaService $dwollaService */
            $dwollaCustomer = $dwollaService->retrieveCustomer($user->dwolla_customer_id);

            $dwollaAttempts = $user->dwolla_verification_attempts()
                                    ->orderBy('created_at', 'DESC')
                                    ->get();

            $dwollaCanVerify = in_array($dwollaCustomer->status, [
                DwollaService::STATUS_UNVERIFIED,
                DwollaService::STATUS_RETRY,
                DwollaService::STATUS_DOCUMENT
            ]) && (
                ($dwollaAttempts->count() < 1) ||
                ($dwollaAttempts[0]->status != DwollaVerificationAttempt::STATUS_PENDING)
            );

            
            $reason = null;
            if($dwollaCustomer->status == 'retry')
            {
                $reason = $dwollaService->getDocumentStatus($user->dwolla_customer_id);
            }
            $bankAccounts = $user->getBankAccounts(true);
            
            if ($bankAccounts != null) {
                foreach ($bankAccounts as $i => $ba) {
                    // print_r($ba);
                    // if (!in_array('ach', $ba->channels))
                    // unset($bankAccounts[$i]);
                    if (!in_array('ach', $ba->channels))
                        unset($bankAccounts[$i]);

                    // if ( $ba->object != 'bank_account')
                        // unset($bankAccounts[$i]);
                }
            }
            // die();
            // print_r($bankAccounts); die();

            // $stripe_connect = $user->getStripeConnectInfo();

        } catch (CustomDwollaException $e) {
            $dwolla_error = $e->getMessage();
        }
        $is_fake = false;
        if(strpos($user->stripe_customer_id,'fake_') !== false)
            $is_fake = true;

        return view('user.payment_options', [
            'dwolla' => $dwollaCustomer,
            'reason' => $reason,
            'dwollaCanVerify' => $dwollaCanVerify,
            'dwollaAttempts' => $dwollaAttempts,
            'cards' => $cards,
            // 'stripe_banks' => $bankAccounts,
            // 'stripe_connect' => $stripe_connect,
            'bank_accounts' => $bankAccounts,
            'stripe_error' => $stripe_error,
            'dwolla_error' => $dwolla_error,
            'current_page' => 'profile',
            'is_error' => $is_fake,
            'cc_gateway' => $settings->value
        ]);
    }

    public function showAccessManagement()
    {
        $user = auth()->user();     /** @var User $user */
        $account_managers = null;
        $managed_accounts = null;

        $account_managers = $user->members;
        if ($account_managers->count() < 1) {
            $account_managers = null;
        } else {
            foreach ($account_managers as $manager ) {
                $perms = $manager->pivot->permissions();
                $manager->permissions = $perms['permissions'];
                $manager->activePrmissionCount = $perms['active_count'];
            }
        }

        $member_invitations = $user->memberInvitations;
        if ($member_invitations->count() < 1)
            $member_invitations = null;

        $managed_accounts = $user->memberOf;
        if ($managed_accounts->count() < 1) {
            $managed_accounts = null;
        } else {
            foreach ($managed_accounts as $account ) {
                $perms = $account->pivot->permissions();
                $account->permissions = $perms['permissions'];
                $account->activePrmissionCount = $perms['active_count'];
            }
        }

        return view('user.access_management', [
            'member_invitations' => $member_invitations,
            'account_managers' => $account_managers,
            'account_manager_permission_list' => MemberUser::PIVOT_FIELDS_OF_INTEREST,
            'managed_accounts' => $managed_accounts,
            'current_page' => 'profile'
        ]);
    }
    public function isDwollaVerified()
    {
        $user = auth()->user();
        try{
            $dwollaService = resolve(DwollaService::class); /** @var DwollaService $dwollaService */
            $dwollaCustomer = $dwollaService->retrieveCustomer($user->dwolla_customer_id);
            return  $dwollaCustomer->status == DwollaService::STATUS_VERIFIED ? true : false;
            
        }
        catch(CustomDwollaException $e)
        {
            return  false;
        
        }
        
    }
    public function showBalanceTransactions()
    {
        $user = auth()->user();
        $meetRegistrationSavings = resolve(MeetRegistration::class)->getMeetRegistrationSavings($user->id);
        return view('user.balance_transactions', [
            'current_page' => 'profile',
            'isDwollaVerified' => $this->isDwollaVerified(),
            'h'=> $meetRegistrationSavings
        ]);
    }
    public function get_min_max_withdraw_limit($user)
    {
        
        $key = 'min_verified_withdraw_limit and key=min_unverified_withdraw_limit and
        key=max_verified_withdraw_limit and key=max_unverified_withdraw_limit and key=is_schedule_withdraw_enabled';
        $min_verified_withdraw_limit = Setting::where('key','min_verified_withdraw_limit')->first();
        $min_unverified_withdraw_limit = Setting::where('key','min_unverified_withdraw_limit')->first();
        $max_verified_withdraw_limit = Setting::where('key','max_verified_withdraw_limit')->first();
        $max_unverified_withdraw_limit = Setting::where('key','max_unverified_withdraw_limit')->first();
        $is_schedule_withdraw_enabled = Setting::where('key','is_schedule_withdraw_enabled')->first();
        $auto_withdraw_charge = Setting::where('key','auto_withdraw_charge')->first();
        $dwollaService = resolve(DwollaService::class); /** @var DwollaService $dwollaService */
        $dwollaCustomer = $dwollaService->retrieveCustomer($user->dwolla_customer_id);


        if($dwollaCustomer->status == 'verified')
        {
            if($user->cleared_balance > ($max_verified_withdraw_limit->value + $auto_withdraw_charge->value))
                $max_limit = $max_verified_withdraw_limit->value;
            else
                $max_limit = $user->cleared_balance - $auto_withdraw_charge->value;
        }
        else
        {
            if($user->cleared_balance > ($max_unverified_withdraw_limit->value + $auto_withdraw_charge->value))
                $max_limit = $max_unverified_withdraw_limit->value;
            else
                $max_limit = $user->cleared_balance - $auto_withdraw_charge->value;
        }
        return array(
            'is_withdraw_enabled' => $is_schedule_withdraw_enabled->value,
            'dwolla_status' => $dwollaCustomer->status,
            'min_withdraw_limit' => $dwollaCustomer->status == 'verified' ? $min_verified_withdraw_limit->value : $min_unverified_withdraw_limit->value,
            'max_withdraw_limit' =>  $max_limit,
            'auto_withdraw_charge' => $auto_withdraw_charge->value
        );
    }
    public function schedule_withdraw()
    {
        $user = auth()->user();
        $withdraw_limit = $this->get_min_max_withdraw_limit($user);

        $bankAccounts = $user->getBankAccounts(true);

        if ($bankAccounts != null) {
            foreach ($bankAccounts as $i => $ba) {
                if ( $ba->status != 'verified')
                    unset($bankAccounts[$i]);
            }
        }
        $withdraw_table = DB::table('withdraw_scheduler')
        ->where('user_id',$user->id)
        ->get();

        return view('user.schedule_withdraw', [
            'current_page' => 'schedule_withdraw',
            'is_withdraw_enabled' => $withdraw_limit['is_withdraw_enabled'],
            'dwolla_status' => $withdraw_limit['dwolla_status'],
            'min_withdraw_limit' => $withdraw_limit['min_withdraw_limit'],
            'max_withdraw_limit' =>  $withdraw_limit['max_withdraw_limit'],
            'bank_accounts' => $bankAccounts,
            'withdraw_table' => $withdraw_table,
            'auto_withdraw_charge' => $withdraw_limit['auto_withdraw_charge'],
        ]);
    }

    public function toogleWithSchedule(Request $request)
    {
        $data = DB::table('withdraw_scheduler')
        ->where('id',$request->id)
        ->first();
        
        if($data->user_id == auth()->user()->id)
        {
            $k = DB::table('withdraw_scheduler')
            ->where('id', $request->id)
            ->update(['is_active' => !$data->is_active]);
            if($k)
            {
                echo http_response_code(200); 
                exit();
            }
            else
            {
                echo http_response_code(400); 
                exit();
            }
        }
        else
        {
            echo http_response_code(400); 
            exit();
        }
    }
    public function initiate_withdraw_schedule(Request $request)
    {
        $user = auth()->user();
        $withdraw_limit = $this->get_min_max_withdraw_limit($user);
        if($request['withdrawal_funds'] >= $withdraw_limit['min_withdraw_limit'] && $request['withdrawal_funds'] <= $withdraw_limit['max_withdraw_limit'])
        {
            $data = DB::table('withdraw_scheduler')
            ->where('user_id',$user->id)
            ->where('is_active', true)
            ->first();
            if($data == null)
            {
                DB::table('withdraw_scheduler')->insert([
                    'user_id' => auth()->user()->id,
                    'amount' => $request['withdrawal_funds'],
                    'frequency' => $request['frequency'],
                    'bank_id' => $request['bank_id'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                return back()->with('success', 'Withdraw Schedule Enabled');
            }
            else
            {
                return back()->with('error', 'Another Withdraw Schedule is Active');
            }
        }
        else
        {
            return back()->with('error', 'Withdraw Limit Exceeded');
        }

    }
    
    // public function testT()
    // {
    //     $is_schedule_withdraw_enabled = Setting::where('key','is_schedule_withdraw_enabled')->first();
    //     $auto_withdraw_charge = Setting::where('key','auto_withdraw_charge')->first();

    //     if($is_schedule_withdraw_enabled->value)
    //     {
    //         $data = DB::table('withdraw_scheduler')
    //         ->where('is_active', true)
    //         ->get();
    
    //         foreach ($data as $k) {
    //             // print_r($k->user_id);
    //             if($k->last_attempt == null)
    //                 $k->last_attempt = $k->created_at;
                
    //             $date1 = new \DateTime($k->last_attempt);
    //             $date2 = new \DateTime(now());
    //             $interval = $date1->diff($date2);

    //             $dwollaScheduleWithdrawal = resolve(DwollaScheduleWithdrawal::class); /** @var DwollaScheduleWithdrawal $dwollaScheduleWithdrawal */
    //             $user = User::find($k->user_id);
    //             if($k->frequency == 1) // && $interval->d >= 1
    //             {
    //                 DB::table('withdraw_scheduler')
    //                     ->where('id', $k->id)
    //                     ->update(['last_attempt' => now() , 'attempt' => $k->attempt + 1, 'updated_at' => now()]);
    
    //                 print_r($dwollaScheduleWithdrawal->withdrawBalanceSchedule($user, (array) $k, $auto_withdraw_charge->value));
    //             }
    //             else if($k->frequency == 2 && $interval->d >= 14)
    //             {
    
    //             }
    //             else if($k->frequency == 3 && $interval->d >= 30)
    //             {
    
    //             }
    
    //         }
    //     }
        
    // }
    // public function withdrawBalanceSchedule($user, $request, $fee = 0) {
    //     $dwollaService = resolve(DwollaService::class); /** @var DwollaService $dwollaService */
    //     // $stripeService = resolve(StripeService::class); /** @var DwollaService $dwollaService */
    //     $transaction = null;
    //     $balanceTransaction = null;
    //     $isDwollaVerified = null;
    //     try {
    //         DB::beginTransaction();

    //         // $user = User::lockForUpdate()->find(auth()->user()->id); /** @var User $user */
    //         if ($user == null)
    //             throw new CustomBaseException('No such user with id `' . auth()->user()->id . '`');

    //         $bankAccount = $request['bank_id'];
    //         if (!isset($bankAccount) || ($bankAccount == ''))
    //         {
    //             Log::info('Invalid bank account', [$bankAccount]);
    //             throw new CustomBaseException('Invalid bank account', -1);
    //         }

    //         $amount = $request['amount'];
    //         if (Helper::isFloat($amount)) {
    //             $amount = (float) $amount;
    //             if ($amount < 0)
    //             {
    //                 Log::info('Invalid amount: Amount needs to be a positive value', [$amount]);
    //                 throw new CustomBaseException('Invalid amount: Amount needs to be a positive value', -1);
    //             }
    //             // if ($amount > 5000.0 && !$isDwollaVerified)
    //             //     throw new CustomBaseException('Dwolla unverified: Amount needs to be less than $5000', -1);

    //             if ($amount > $user->cleared_balance)
    //             {
    //                 Log::info('You do not have enough balance to withdraw that amount.', [$amount]);
    //                 throw new CustomBaseException('You do not have enough balance to withdraw that amount.', -1);
    //             }
    //         } else {
    //             Log::info('Invalid amount: Amount needs to be a positive value', [$amount]);
    //             throw new CustomBaseException('Invalid amount', -1);
    //         }

    //         $featuredFee = Auth::user()->meetFeaturedWithdrawalFee()['total_net_value'];
    //         $amount = $amount - $featuredFee;

    //         $bankAccount = $user->getBankAccount($bankAccount);
    //         // if (!Str::endsWith($bankAccount['_links']['customer']['href'], $user->dwolla_customer_id))
    //         if ( $bankAccount == null )
    //         throw new CustomBaseException('No such bank account linked to your account.', -1);
            

    //         $source = $dwollaService->getFundingSource(config('services.dwolla.master')); // trackthis
    //         $now = now();
    //         $deduct_amount = $amount + $fee;
    //         $balanceTransaction = $user->balance_transactions()->create([
    //             'processor_id' => null,
    //             'total' => -$deduct_amount,
    //             'description' => 'Balance withdrawal $' . number_format($amount, 2),
    //             'clears_on' => $now,
    //             'type' => UserBalanceTransaction::BALANCE_TRANSACTION_TYPE_WITHDRAWAL,
    //             'status' => UserBalanceTransaction::BALANCE_TRANSACTION_STATUS_PENDING
    //         ]); /** @var UserBalanceTransaction $balanceTransaction */

    //         // create entry for feature fee charges during withdraw balance
    //         if ($featuredFee > 0) {
    //             $featuredFeeChargeEntry = $user->balance_transactions()->create([
    //                 'processor_id' => null,
    //                 'total' => -$featuredFee,
    //                 'description' => 'Featured fee charge when withdraw balance',
    //                 'clears_on' => $now,
    //                 'type' => UserBalanceTransaction::BALANCE_TRANSACTION_TYPE_WITHDRAWAL,
    //                 'status' => UserBalanceTransaction::BALANCE_TRANSACTION_STATUS_PENDING
    //             ]);
    //         }

    //         $user->cleared_balance -= ($calculatedTotal + $featuredFee + $fee);
    //         $user->save();

    //         // is_withdrawal status update in meet transaction table
    //         $currentUser = User::with(['gyms.meets.registrations.transactions'])->where('id', Auth::user()->id)->get();
    //         $meetTransactions = $currentUser->pluck('gyms')->collapse()
    //             ->pluck('meets')->collapse()->where('is_featured',true)
    //             ->pluck('registrations')->collapse()
    //             ->pluck('transactions')->collapse()->where('is_withdrawal',false);

    //         foreach ($meetTransactions as $meetTransaction) {
    //             $meetTransaction->update(['is_withdrawal' => true]);
    //         }

    //         $transaction = $dwollaService->initiateACHTransfer(
    //             $source['_links']['self']['href'],
    //             $bankAccount['_links']['self']['href'],
    //             $calculatedTotal,
    //             [
    //                 'type' => 'withrawal',
    //                 'withdrawn' => $amount,
    //                 'fee' => $fee,
    //                 'balance_tx' => $balanceTransaction->id
    //             ]
    //         );
            
    //         DB::commit();

    //         $transaction = $dwollaService->getACHTransfer($transaction);

    //         $balanceTransaction->processor_id = $transaction['id'];
    //         $balanceTransaction->save();

    //         try {
    //             Mail::to($user->email)
    //                 ->send(new WithdrawalRequestedMailable($balanceTransaction));
    //         } catch (\Throwable $th) {
    //         }

    //         return $this->success();
    //     } catch(\Throwable $e) {
    //         if (DB::transactionLevel() > 0)
    //             DB::rollBack();

    //         if ($transaction != null) {
    //             $cancelFailed = true;
    //             try {
    //                 // Try and cancel the transaction.
    //                 if ($transaction instanceof Transfer){
    //                     $transaction = $transaction['_links']['self']['href'];
    //                 };

    //                 $cancelFailed = !$dwollaService->cancelACHTransfer($transaction);
    //             } catch (\Throwable $e) {
    //                 Log::debug('Panic TX Cancelation : ' . $e->getMessage());
    //             }
    //         }

    //         throw $e;
    //     }
    // }
    public function profile()
    {
        $user = auth()->user();
        $attr = request()->validate($user->getUpdateRules());

        if ($user->updateProfile($attr))
            return back()->with('success', 'Your profile was updated.');
        else
            return back()->with('error', 'There was an error while updating your profile');
    }

    public function clearProfilePicture()
    {
        if (auth()->user()->clearProfilePicture())
            return back()->with('success', 'Your profile picture was removed.');
        else
            return back()->with('error', 'There was an error while removing your profile picture');
    }

    public function changeProfilePicture()
    {
        $attr = request()->validate([
            'profile_picture' => auth()->user()->getProfilePictureRules()
        ]);

        if (!isset($attr['profile_picture']))
            return back();
        elseif (auth()->user()->storeProfilePicture($attr['profile_picture']))
            return back()->with('success', 'Your profile picture was updated.');
        else
            return back()->with('error', 'There was an error while updating your profile picture');
    }

    public function resetPassword() {
        $user = auth()->user();
        $attr = request()->validate(User::PASSWORD_UPDATE_RULES);

        if (!Hash::check($attr['old_password'], $user->password))
            throw new CustomBaseException('Wrong password', -1);

        if ($user->resetPassword($attr['old_password'], $attr['password']))
            return back()->with('success', 'Your password was updated.');

        return back()->with('error', 'An error occurred while updating your password.');
    }

    public function storeIntellipayCard()
    {
        $attr = request()->validate(IntellipayService::CARD_RULES);
        $expiry_date = explode('/', $attr['cardexpirydate']);
        if($expiry_date[1] < date('y'))
            return back()->with('error', 'Your card is expired.');
        else if($expiry_date[1] == date('y') && $expiry_date[0] <= date('m'))
            return back()->with('error', 'Your card is expired.');
        else if($expiry_date[0] > 12)
            return back()->with('error', 'Invalid expiry date (month).');
        else if($expiry_date[0] < 1 || $expiry_date[1] < 0)
            return back()->with('error', 'Invalid expiry date.');

        $card_number = str_replace(' ', '', $attr['cardnumber']);
        if(!is_numeric($card_number))
            return back()->with('error', 'Invalid card number.');

        $attr['cardnumber'] = trim($card_number);
        $attr['cardexpirydate'] = trim($expiry_date[0] . $expiry_date[1]);
        try{
            $intellipayService = resolve(IntellipayService::class);
            $response = $intellipayService->addCard($attr);

            if($response['status'] == 400)
                return back()->with('error', $response['message']);
            else
                return back()->with('success', $response['message']);
        }
        catch(\Exception $e)
        {
            return back()->with('error', $e->getMessage());
        }
    }
    public function storeCard()
    {
        $attr = request()->validate(StripeService::CARD_TOKEN_RULES);
        $cards = auth()->user()->addCard($attr['card_token']);
        return back()->with('success', 'Your card was linked.');
    }

    public function deleteCard(string $id) {
        $cards = auth()->user()->removeCard($id);
        if($id[0] == 'b')
            return back()->with('success', 'Your bank account was unlinked.');
        else
            return back()->with('success', 'Your card was unlinked.');
    }

    public function deleteBankAccount(string $id) {
        $bankAccounts = auth()->user()->removeBankAccount($id);
        return back()->with('success', 'Your bank account was unlinked.');
    }
    public function addBankAccount(Request $request)
    {
        $attr = $request->validate(
            DwollaService::BANK_ACCOUNT_ADD_RULES
        );
        $attr['user_dwolla_id'] = auth()->user()->dwolla_customer_id;
        $fundingSource = resolve(DwollaService::class)->addBankAccount($attr);
        return back()->with('success', 'Your bank account is Added Successfully.');
    }

    public function verifyMicroDeposits() {
        $attr = request()->validate(
            DwollaService::BANK_ACCOUNT_RULES + DwollaService::MICRO_DEPOSITS_RULES
        );
        $verification = auth()->user()->verifyMicroDeposits(
            $attr['bank_account'], $attr['amount1'], $attr['amount2']
        );
        return back()->with('success', 'Your bank account was verified.');
    }

    public function inviteMember()
    {
        $attr = request()->validate(User::MEMBER_INVITE_RULES);

        if (auth()->user()->inviteMember($attr['invite_email']))
            return back()->with('success', 'Invitation sent to ' . $attr['invite_email']);
        else
            return back()->with('error', 'There was an error while sending your Invitation.');
    }

    public function acceptInvite(string $token) {
        try {
            $sender = auth()->user()->acceptInvite($token);
            return redirect(route('dashboard'))->with('success', 'Invitation from ' . $sender->fullName() . ' accepted.');
        } catch (CustomBaseException $e) {
            return redirect(route('dashboard'))->with('error', $e->getMessage() . ' (code: ' . $e->getCode() . ').');
        }
    }

    public function removeInvite(string $id) {
        if (auth()->user()->removeInvite($id))
            return back()->with('success', 'Invite removed.');

        return back()->with('error', 'An error occurred while removing your invite.');
    }

    public function resendInvite(string $id) {
        if (auth()->user()->resendInvite($id))
            return back()->with('success', 'Invite sent.');

        return back()->with('error', 'An error occurred while sending your invite.');
    }

    public function removeMember(string $id)
    {
        if (auth()->user()->removeMember($id))
            return back()->with('success', 'Member removed');

        return back()->with('error', 'An error occurred while removing this member.');
    }

    public function changeMemberPermissions()
    {
        $attr = request()->validate(MemberUser::getPivotFieldsRules() + [
            'member' => ['required', 'integer']
        ]);

        if (auth()->user()->changeMemberPermissions($attr))
            return back()->with('success', 'Permissions updated');

        return back()->with('error', 'An error occurred while updating this member\'s permissions.');
    }

    public function removeManagedAccount(string $id)
    {
        if (auth()->user()->removeManagedAccount($id))
            return back()->with('success', 'You were successfully removed from that account.');

        return back()->with('error', 'An error occurred while removing you from that account.');
    }

    public function switchManagedUser()
    {
        $id = request()->validate(['id' => ['nullable', 'integer']]);
        $id = (isset($id['id']) ? $id['id'] : null);

        $user = auth()->user();
        $name = 'your own';
        $session = request()->session();
        if (($id != null) && ($id != $user->id)) {
            $account = $user->memberOf->find($id);
            if ($account == null)
                throw new CustomBaseException('You are not managing any such account', -1);

            $name = $account->fullName() . '\'s';
            $session->put('managed', $account);
        } else {
            $session->forget('managed');
        }



        return redirect(route('dashboard'))->with('success', 'Switched to ' . $name . ' account');
    }

    public function showDwollaVerificationPage()
    {
        $user = auth()->user();     /** @var User $user */
        $dwollaService = resolve(DwollaService::class); /** @var DwollaService $dwollaService */
        $dwollaCustomer = $dwollaService->retrieveCustomer($user->dwolla_customer_id);

        if (
            !in_array($dwollaCustomer->status, [
                    DwollaService::STATUS_UNVERIFIED,
                    DwollaService::STATUS_RETRY,
                    DwollaService::STATUS_DOCUMENT
            ])
        )
            throw new CustomDwollaException('Your Dwolla account is either already verified or disabled.', -1);

        $dwollaAttempts = $user->dwolla_verification_attempts()
                                ->orderBy('created_at', 'DESC')
                                ->get();

        if (
            ($dwollaAttempts->count() > 0) &&
            ($dwollaAttempts[0]->status == DwollaVerificationAttempt::STATUS_PENDING)
        )
            throw new CustomDwollaException('There is already a verification underway for this account.', -1);

        $remainingVerificationAttempts = max(
            0,
            Setting::dwollaFreeVerificationAttempts() - $dwollaAttempts->count()
        );

        $states = State::where('code', '!=', 'WW')->get();
        if ($states->count() < 1)
            throw new CustomBaseException('Failed to fetch states list. Pleace contact us.');

        return view('user.dwolla_verify', [
            'dwolla' => $dwollaCustomer,
            'remainingAttempts' => $remainingVerificationAttempts,
            'states' => $states,
            'current_page' => 'profile'
        ]);
    }

    public function dwollaVerifyInfo(Request $request)
    {
        $user = auth()->user();     /** @var User $user */
        $data = $request->validate(DwollaVerificationAttempt::INFO_RULES);

        $state = State::where('code', $data['state'])->first();
        if (($state == null) || ($state->code == 'WW'))
            throw new CustomDwollaException('Invalid state code.', -1);

        $dwollaAttempts = $user->dwolla_verification_attempts()
                                ->orderBy('created_at', 'DESC')
                                ->get();
        if (
            ($dwollaAttempts->count() > 0) &&
            ($dwollaAttempts[0]->status == DwollaVerificationAttempt::STATUS_PENDING)
        )
            throw new CustomDwollaException('There is already a verification underway for this account.', -1);

        $remainingVerificationAttempts = max(
            0,
            Setting::dwollaFreeVerificationAttempts() - $dwollaAttempts->count()
        );

        $data = [
            'firstName' => $user->first_name,
            'lastName' => $user->last_name,
            'email' => $user->email,
            'ipAddress' => $request->ip(),
            'type' => 'personal',
            'address1' => $data['addr_1'],
            'address2' => $data['addr_2'],
            'city' => $data['city'],
            'state' => $state->code,
            'postalCode' => $data['zipcode'],
            'dateOfBirth' => $data['date_of_birth'],
            'ssn' => $data['ssn']
        ];

        $dataJson = json_encode($data);
        if ($dataJson === false)
            throw new CustomDwollaException('JSON encoding failed.', -1);

        $dwollaService = resolve(DwollaService::class); /** @var DwollaService $dwollaService */
        $dwollaCustomer = $dwollaService->retrieveCustomer($user->dwolla_customer_id);

        $ssnLength = strlen($data['ssn']);
        switch($dwollaCustomer->status) {
            case DwollaService::STATUS_UNVERIFIED:
                if (($ssnLength != 4) && ($ssnLength != 9))
                    throw new CustomDwollaException('Please provide the last four digits of your SSN, or the full SSN.', -1);
                break;

            case DwollaService::STATUS_RETRY:
                if ($ssnLength != 9)
                    throw new CustomDwollaException('Please provide the full nine digits of your SSN.', -1);
                break;

            default:
                throw new CustomDwollaException('Invalid account state for this action.', -1);
        }

        DB::beginTransaction();
        try {
            $attempt = $user->dwolla_verification_attempts()->create([
                'meta' => $dataJson,
                'resulting_status' => $dwollaCustomer->status,
                'status' => DwollaVerificationAttempt::STATUS_PENDING,
            ]); /** @var DwollaVerificationAttempt $attempt */
            $attempt->save();

            if ($remainingVerificationAttempts < 1) {
                $fee = -Setting::dwollaVerificationFee();
                $attempt->user_balance_transaction()->create([
                    'user_id' => $user->id,
                    'total' => $fee,
                    'description' =>  'Dwolla Verification Attempt Fee',
                    'clears_on' => now(),
                    'type' => UserBalanceTransaction::BALANCE_TRANSACTION_TYPE_DWOLLA_VERIFICATION_FEE,
                    'status' => UserBalanceTransaction::BALANCE_TRANSACTION_STATUS_CLEARED
                ]);
                $user = User::lockForUpdate()->find($user->id);
                $user->cleared_balance += $fee;
                $user->save();
            }

            $customer = $dwollaService->updateCustomer($dwollaCustomer->_links['self']->href, $data);
            DB::commit();
        } catch(\Throwable $e) {
            DB::rollBack();
            throw $e;
        }

        return redirect(route('account.payment.options'))
                ->with('success', 'Verification request sent.');
    }

    public function dwollaUploadDocument(Request $request)
    {
        $user = auth()->user();     /** @var User $user */
        $data = $request->validate(DwollaVerificationAttempt::getVerificationDocumentRules());

        $dwollaAttempts = $user->dwolla_verification_attempts()
                                ->orderBy('created_at', 'DESC')
                                ->get();
        if (
            ($dwollaAttempts->count() > 0) &&
            ($dwollaAttempts[0]->status == DwollaVerificationAttempt::STATUS_PENDING)
        )
            throw new CustomDwollaException('There is already a verification underway for this account.', -1);

        $remainingVerificationAttempts = max(
            0,
            Setting::dwollaFreeVerificationAttempts() - $dwollaAttempts->count()
        );

        $document = $data['document']; /** @var UploadedFile $document */
        $dataJson = json_encode([
            'type' => $data['type'],
            'document' => [
                'filename' => $document->getClientOriginalName(),
                'type' => $document->getMimeType(),
                'size' => $document->getSize(),
            ]
        ]);
        if ($dataJson === false)
            throw new CustomDwollaException('JSON encoding failed.', -1);

        $dwollaService = resolve(DwollaService::class); /** @var DwollaService $dwollaService */
        $dwollaCustomer = $dwollaService->retrieveCustomer($user->dwolla_customer_id);

        if ($dwollaCustomer->status != DwollaService::STATUS_DOCUMENT)
            throw new CustomDwollaException('Invalid account state for this action.', -1);

        $url = $dwollaCustomer->_links['verify-with-document']->href;

        DB::beginTransaction();
        try {
            $attempt = $user->dwolla_verification_attempts()->create([
                'meta' => $dataJson,
                'resulting_status' => $dwollaCustomer->status,
                'status' => DwollaVerificationAttempt::STATUS_PENDING,
            ]); /** @var DwollaVerificationAttempt $attempt */
            $attempt->save();

            if ($remainingVerificationAttempts < 1) {
                $fee = -Setting::dwollaVerificationFee();
                $attempt->user_balance_transaction()->create([
                    'user_id' => $user->id,
                    'total' => $fee,
                    'description' =>  'Dwolla Verification Attempt Fee',
                    'clears_on' => now(),
                    'type' => UserBalanceTransaction::BALANCE_TRANSACTION_TYPE_DWOLLA_VERIFICATION_FEE,
                    'status' => UserBalanceTransaction::BALANCE_TRANSACTION_STATUS_CLEARED
                ]);
                $user = User::lockForUpdate()->find($user->id);
                $user->cleared_balance += $fee;
                $user->save();
            }

            $dwollaService->uploadDocument($url, $document, $data['type']);
            DB::commit();
        } catch(\Throwable $e) {
            DB::rollBack();
            throw $e;
        }

        return redirect(route('account.payment.options'))
                ->with('success', 'Verification request sent.');
    }

    public function impersonate($userId)
    {
        //if user is not admin and session is impersonated_by then return
        if ((!Auth::user()->is_admin) || session('impersonated_by')) {
            return redirect()->back();
        }

        $user = User::find($userId);
        Auth::user()->impersonate($user);

        return redirect()->route('dashboard');
    }

    public function impersonateLeave()
    {
        Auth::user()->leaveImpersonation();

        return redirect()->route('admin.users');
    }
    public function showProfileSite($id)
    {
        // $gym = $this->gyms()->where('id', $id)->first();
        // echo "helo" . $id;
        $gym = new User;
        $activeGyms =  $gym->gyms()
                            ->where('is_archived', false)
                            ->orderBy('name', 'ASC')->get();

        $archivedGyms =  $gym->gyms()
                            ->where('is_archived', true)
                            ->orderBy('name', 'ASC')->get();

        return view('user.minisite.home', [
            'current_page' => 'profile',
            'active_gyms' => $activeGyms,
            'archived_gyms' => $archivedGyms
        ]);
    }
    public function hostDashboard()
    {

        $user = resolve(User::class);
        $data = $user->hostDashboardData();
        $data['current_page'] = 'Host Dashboard';
        // $data['current_gym'] = request()->gym_id !== null ? request()->gym_id : null;
        return view('host_dashboard.index', $data);
    }
    public function hostDashboardFilter()
    {
        $user = resolve(User::class);
        $data = $user->hostDashboardData(request()->gym_id);
        $data['current_page'] = 'Host Dashboard';
        redirect(route('host.dashboard'))->with($data);
        // return view('host_dashboard.index', $data);
    }
    public function hostDashboardExportCSV($gym_id)
    {
        request()->gym_id = $gym_id;
        return Excel::download(new HostDashBoardReport(), 'host_dashboard_summary-'.time().'.csv');
    }
    public function hostDashboardExportExcel($gym_id)
    {
        request()->gym_id = $gym_id;
        return Excel::download(new HostDashBoardReport(), 'host_dashboard_summary-'.time().'.xlsx');
    }
    public function hostDashboardExportPDF($gym_id)
    {
        
        $user = resolve(User::class);
        $data = $user->hostDashboardData();
        $pdf = PDF::loadView('host_dashboard.exports.summary_pdf', $data);
        $pdf->setPaper('A4', 'portrait')
        ->setOption('margin-top', '10mm')
        ->setOption('margin-bottom', '10mm')
        ->setOption('footer-html', view('PDF.host.meet.reports.header_footer.common_footer')->render());
        // return $pdf->download('host_dashboard_summary-'.time().'.pdf');
        return $pdf->stream('host_dashboard_summary-'.time().'.pdf');
                    
    }
}