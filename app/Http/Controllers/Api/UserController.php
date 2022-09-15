<?php

namespace App\Http\Controllers\Api;

use App\Mail\MemberInvitationAccepted;
use App\Models\Gym;
use App\Models\MeetRegistration;
use App\Models\Notification;
use App\Models\SanctioningBody;
use Illuminate\Http\Request;
use \App\Models\User;
use Illuminate\Http\Response;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Laravel\Passport\TransientToken;
use Illuminate\Auth\Events\Registered;
use App\Services\StripeService;
use App\Services\DwollaService;
use Illuminate\Support\Facades\Hash;
use App\Exceptions\CustomBaseException;
use App\Exceptions\CustomDwollaException;
use App\Helper;
use App\Mail\User\WithdrawalRequestedMailable;
use App\Models\Setting;
use App\Models\USAGReservation;
use App\Models\USAGSanction;
use App\Models\UserBalanceTransaction;
use DwollaSwagger\models\Transfer;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class UserController extends BaseApiController
{

    /**
     * Returns Authenticated User Details
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        return $this->success(['user' => auth()->user()->makeHidden([
            'handling_fee_override', 'cc_fee_override',
            'paypal_fee_override', 'ach_fee_override', 'check_fee_override',
        ])]);
    }

    /**
     * Handles Registration Request
     *
     * @param  UploadedFile|null  $profilePicture
     * @return string
     */
    public function create(Request $request, UploadedFile $profilePicture = null)
    {
        try {
            $rules = User::getCreateRules();
            $rules['email'] = 'required|string|email|max:255';
            $rules['office_phone'] = ['required', 'regex:/^(\([0-9]{3}\)-|[0-9]{3}-)[0-9]{3}-[0-9]{4}$/'];
            $userAttr = $request->validate($rules);

            if(trim($userAttr['h-captcha-response']) != md5(gmdate("Y-m-d\TH:i\Z")))
                throw new CustomBaseException("Suspicious activity detected. Please contact admin"); 

            $isEmailExist = User::where('email', $userAttr['email'])->exists();
            if ($isEmailExist) {
                return $this->error(['message' => 'An account using this email already exists.']);
            }
            $sender = null;

            DB::beginTransaction();

            /** @var User $user */
            $user = User::create([
                'email' => strtolower($userAttr['email']),
                'password' => Hash::make($userAttr['password']),
                'first_name' => $userAttr['first_name'],
                'last_name' => $userAttr['last_name'],
                'office_phone' => $userAttr['office_phone'],
                'job_title' => $userAttr['job_title'],
                'profile_picture' => config('app.default_profile_picture'),
            ]);

            $user->save();

            if (isset($userAttr['member_invite']))
                $sender = $user->acceptInvite($userAttr['member_invite'], true);

            if ($profilePicture)
                $user->storeProfilePicture($profilePicture);

            $user->stripe_customer_id = StripeService::createCustomer(
                $user->fullName(),
                $user->email,
                config('app.name') . ' | ' . $user->fullName()
            )->id;

            $user->dwolla_customer_id = resolve(DwollaService::class)->createCustomer(
                $user->first_name,
                $user->last_name,
                $user->email
            )->id;

            $user->save();

            DB::commit();

            if (isset($userAttr['member_invite'])) {
                Mail::to($sender->email)->send(new MemberInvitationAccepted(
                    $sender,
                    $user
                ));
            }

            event(new Registered($user));
            return $this->success(['message' => 'A validation link has been sent to your email. Please check your inbox.'], Response::HTTP_CREATED);

        } 
        catch (ValidationException $e ) {
            DB::rollBack();
            return $this->error(['message' => array_values($e->errors())[0][0]]);
        } 
        catch (\Throwable $throwable) {
            DB::rollBack();
            return $this->error(['message' => $throwable->getMessage() .'2']);
        }
    }

    /**
     * Handles Login Request
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function login()
    {
        $attr = request()->validate(User::getLoginRules());

        $credentials = [
            'email' => $attr['email'],
            'password' => $attr['password']
        ];
        if(trim($attr['h-captcha-response']) != md5(gmdate("Y-m-d\TH:i\Z")))
            return $this->error(['message' => 'Unauthenticated'], Response::HTTP_PRECONDITION_FAILED);
        if (!auth()->attempt($credentials))
            return $this->error(['message' => 'Unauthenticated'], Response::HTTP_PRECONDITION_FAILED);

        if (auth()->user()->email_verified_at == null) {
            return $this->error(['message' => 'User must verify email to activate account.'], Response::HTTP_PRECONDITION_FAILED);
        }

        $tokenResult = auth()->user()->createToken(config('auth.pat_name'));
        $token = $tokenResult->token;

        if (isset($attr['remember']))
            $token->expires_at = now()->addDay(60);

        $token->save();

        return $this->success([
            'token' => $tokenResult->accessToken,
            'expires_at' => $token->expires_at,
        ]);
    }

    /**
     * Handles Logout Request
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout(Request $request)
    {

        /*
            At the time of writing, this is an issue with Laravel Passport,
            where a middleware uses a JWT to authenticate web users on the
            api routes. We can't revoke a JWT. See here :

            https://github.com/laravel/passport/issues/909#issuecomment-482659835
        */
        if ($request->user()->token() instanceof TransientToken)
            return $this->error(['message' => 'Can\'t log out a web user from the API' ], Response::HTTP_BAD_REQUEST);

        $request->user()->token()->revoke();
        $request->user()->token()->delete();

        return $this->success();
    }

    public function profile()
    {
        try{
            $user = auth()->user();
            $attr = request()->validate($user->getUpdateRules());

            if ($user->updateProfile($attr))
                return $this->success(['message' => 'Your profile was updated.']);
        }
        catch (ValidationException $e ) {
            return $this->error(['message', array_values($e->errors())[0][0]]);
        } 
        catch (\Throwable $throwable) {
            return $this->error(['message', 'There was an error while updating your profile']);
        }

        // return $this->error(['message', 'There was an error while updating your profile']);
    }

    public function clearProfilePicture()
    {
        if (auth()->user()->clearProfilePicture())
            return $this->success();
        else
            return $this->error(['message' => 'There was an error while removing your profile picture']);
    }

    public function changeProfilePicture()
    {
        $attr = request()->validate([
            'profile_picture' => auth()->user()->getProfilePictureRules()
        ]);

        if (!isset($attr['profile_picture']))
            return $this->error(['message' => 'Please provide a valid picture.']);
        elseif (auth()->user()->storeProfilePicture($attr['profile_picture']))
            return $this->success(['message' => 'Your profile picture was updated.']);

        return $this->error(['message' => 'There was an error while updating your profile picture']);
    }

    public function resetPassword()
    {
        $user = auth()->user();
        $attr = request()->validate(User::PASSWORD_UPDATE_RULES);

        if (!Hash::check($attr['old_password'], $user->password))
            throw new CustomBaseException('Wrong password', -1);

        if ($user->resetPassword($attr['old_password'], $attr['password']))
            return $this->success(['message' => 'Your password was updated.']);

        return $this->error(['message' => 'An error occurred while updating your password.']);
    }

    public function getBalanceTransactions()
    {
        $transactions = auth()->user()->balance_transactions;

        //->where('status', '!=', UserBalanceTransaction::BALANCE_TRANSACTION_STATUS_UNCONFIRMED)
//        $transactions = $transactions->load('related.meet_registration.meet');

        $result = $transactions->map(function ($items,$key){
            $data = [
                'id' => $items->id,
                'user_id' => $items->user_id,
                'processor_id' => $items->processor_id,
                'total' => $items->total,
                'description' => $items->description,
                'clears_on' => $items->clears_on,
                'type' => $items->type,
                'status' => $items->status,
                'created_at' => $items->created_at,
                'updated_at' => $items->updated_at,
                'meet_name' => ($items->type == UserBalanceTransaction::BALANCE_TRANSACTION_TYPE_WITHDRAWAL) ? '' : ((!empty($items->related) && !empty($items->related->meet_registration)) ? $items->related->meet_registration->meet->name : ''),
            ];

            return $data;
        });

        return $this->success([
            'transactions' => $result,
        ]);
    }

    public function getCard(string $cardId)
    {
        $card = auth()->user()->getCard($cardId);
        return $this->success([
            'card' => $card,
        ]);
    }

    public function getCards()
    {
        $cards = auth()->user()->getCards();
        return $this->success([
            'cards' => $cards,
        ]);
    }

    public function getCardsForManaged()
    {
        $cards = request()->_managed_account->getCards();
        foreach ($cards as $i => $card) {
            $cards[$i] = [
                'id' => $card['id'],
                'brand' => $card['brand'],
                'exp_month' => $card['exp_month'],
                'exp_year' => $card['exp_year'],
                'last4' => $card['last4'],
                'image' => $card['image'],
            ];
        }
        return $this->success([
            'cards' => $cards,
        ]);
    }

    public function storeCard()
    {
        $attr = request()->validate(StripeService::CARD_TOKEN_RULES);
        $card = auth()->user()->addCard($attr['card_token']);
        return $this->success([
            'message' => 'Your card was linked.',
            'card' => $card,
        ]);
    }

    public function deleteCard(string $id) {
        $card = auth()->user()->removeCard($id);
        return $this->success([
            'message' => 'Your card was unlinked.',
            'card' => $card
        ]);
    }

    public function getBankAccount(string $id)
    {
        $bankAccount = auth()->user()->getBankAccount($id);
        $bankAccount = [
            'id' => $bankAccount->id,
            'name' => $bankAccount->name,
            'type' => $bankAccount->bank_account_type,
            'status' => $bankAccount->status,
            'bank_name' => $bankAccount->bank_name,
            'created' => $bankAccount->created,
            'removed' => false
        ];

        return $this->success([
            'bank_account' => $bankAccount,
        ]);
    }

    public function getBankAccounts()
    {
        $bankAccounts = auth()->user()->getBankAccounts(); //dwolla
        // $bankAccounts = auth()->user()->getStripeBankAccounts();
        $result = [];

        // if ($bankAccounts !== null) { // stripe
        //     foreach ($bankAccounts as $bankAccount) {
        //         $result[] = [
        //             'id' => $bankAccount->id,
        //             'name' => $bankAccount->account_holder_name,
        //             'type' => $bankAccount->account_holder_type ?? $bankAccount->account_type,
        //             'status' => $bankAccount->status,
        //             'bank_name' => $bankAccount->bank_name ?? $bankAccount->account_holder_name,
        //             'created' => $bankAccount->created,
        //             'removed' => false
        //         ];
        //     }
        // }
        if ($bankAccounts !== null) { // dwolla
            foreach ($bankAccounts as $bankAccount) {
                $result[] = [
                    'id' => $bankAccount->id,
                    'name' => $bankAccount->name,
                    'type' => $bankAccount->bankAccountType ?? $bankAccount->type,
                    'status' => $bankAccount->status,
                    'bank_name' => $bankAccount->bankName ?? $bankAccount->name,
                    'created' => $bankAccount->created,
                    'removed' => false
                ];
            }
        }

        return $this->success([
            'bank_accounts' => $result,
        ]);
    }

    public function deleteBankAccount(string $id) {
        $bankAccounts = auth()->user()->removeBankAccount($id);
        return $this->success([
            'message' => 'Your bank account was unlinked.',
            'bank_account' => $bankAccounts,
        ]);
    }

    public function verifyMicroDeposits(string $id)
    {
        $attr = request()->validate(DwollaService::MICRO_DEPOSITS_RULES);
        $verification = auth()->user()->verifyMicroDeposits($id, $attr['amount1'], $attr['amount2']);
        return $this->success([
            'message' => 'Your bank account was verified.'
        ]);
    }

    public function getIAVToken() {
        $token = resolve(DwollaService::class)->generateIAVToken(auth()->user()->dwolla_customer_id);
        return $this->success([
            'token' => $token
        ]);
    }

    public function withdrawBalance(Request $request) {
        $dwollaService = resolve(DwollaService::class); /** @var DwollaService $dwollaService */
        // $stripeService = resolve(StripeService::class); /** @var DwollaService $dwollaService */
        $transaction = null;
        $balanceTransaction = null;
        $isDwollaVerified = null;
        try {
            DB::beginTransaction();

            $user = User::lockForUpdate()->find(auth()->user()->id); /** @var User $user */
            if ($user == null)
                throw new CustomBaseException('No such user with id `' . auth()->user()->id . '`');

            $bankAccount = $request->input('account');
            if (!isset($bankAccount) || ($bankAccount == ''))
                throw new CustomBaseException('Invalid bank account', -1);

            $amount = $request->input('amount');
            if (Helper::isFloat($amount)) {
                $amount = (float) $amount;
                if ($amount < 0)
                    throw new CustomBaseException('Invalid amount: Amount needs to be a positive value', -1);
                // if ($amount > 5000.0 && !$isDwollaVerified)
                //     throw new CustomBaseException('Dwolla unverified: Amount needs to be less than $5000', -1);

                if ($amount > $user->cleared_balance)
                    throw new CustomBaseException('You do not have enough balance to withdraw that amount.', -1);
            } else {
                throw new CustomBaseException('Invalid amount', -1);
            }

            $total = $request->input('total');
            if (Helper::isFloat($total)) {
                $total = (float) $total;
                if ($total < 0)
                    throw new CustomBaseException('Invalid amount: Total needs to be a positive value', -1);
            } else {
                throw new CustomBaseException('Invalid total', -1);
            }

            $featuredFee = Auth::user()->meetFeaturedWithdrawalFee()['total_net_value'];
            $total = $total - $featuredFee;
            $amount = $amount - $featuredFee;

            $fee = 0;


            $calculatedTotal = $amount + $fee;

            if ($calculatedTotal != $total)
                throw new CustomBaseException('Total calculation mismatch', -1);

            if ($calculatedTotal > $user->cleared_balance)
                throw new CustomBaseException('You do not have enough balance to withdraw that amount.', -1);

            $bankAccount = $user->getBankAccount($bankAccount);
            // if (!Str::endsWith($bankAccount['_links']['customer']['href'], $user->dwolla_customer_id))
            if ( $bankAccount == null )
            throw new CustomBaseException('No such bank account linked to your account.', -1);
            

            $source = $dwollaService->getFundingSource(config('services.dwolla.master')); // trackthis
            $now = now();
            $balanceTransaction = $user->balance_transactions()->create([
                'processor_id' => null,
                'total' => -$total,
                'description' => 'Balance withdrawal $' . number_format($amount, 2),
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

            $user->cleared_balance -= ($calculatedTotal + $featuredFee);
            $user->save();

            // is_withdrawal status update in meet transaction table
            $currentUser = User::with(['gyms.meets.registrations.transactions'])->where('id', Auth::user()->id)->get();
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
                $calculatedTotal,
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

            return $this->success();
        } catch(\Throwable $e) {
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
    
    public function getAllGymSanctions(Request $request)
    {
        $managed = $request->_managed_account; /** @var User $managed */
        $gymsWithPendingSanctions = $managed->gyms()
            ->select(['id', 'user_id', 'name', 'profile_picture'])
            ->where('is_archived', false)
            ->with([
                'usag_sanctions' => function (Relation $q0) {
                    $q0->exclude([
                        'payload', 'notification_stage', 'next_notification_on',
                    ])->where('status', USAGSanction::SANCTION_STATUS_PENDING);
                },
                'usag_sanctions.level_category' => function (Relation $q0) {
                    $q0->exclude(['male', 'female']);
                },
                'usag_sanctions.meet' => function (Relation $q0) {
                    $q0->select(['id', 'profile_picture', 'name']);
                }
            ])->whereHas('usag_sanctions', function (Builder $q0) {
                $q0->where('status', USAGSanction::SANCTION_STATUS_PENDING);
            })->get();
        return $this->success([
            'gyms' => $gymsWithPendingSanctions,
        ]);
    }

    public function getAllGymReservations(Request $request)
    {
        $managed = $request->_managed_account; /** @var User $managed */
        $gymsWithPendingRerservations = $managed->gyms()
            ->select(['id', 'user_id', 'name', 'profile_picture'])
            ->where('is_archived', false)
            ->with([
                'usag_reservations' => function (Relation $q0) {
                    $q0->exclude([
                        'payload', 'notification_stage', 'next_notification_on',
                    ])->where('status', USAGReservation::RESERVATION_STATUS_PENDING);
                },
                'usag_reservations.usag_sanction' => function (Relation $q0) {
                    $q0->exclude([
                        'payload', 'notification_stage', 'next_notification_on',
                    ]);
                },
                'usag_reservations.usag_sanction.level_category' => function (Relation $q0) {
                    $q0->exclude(['male', 'female']);
                },
                'usag_reservations.usag_sanction.meet' => function (Relation $q0) {
                    $q0->select(['id', 'profile_picture', 'name']);
                }
            ])->whereHas('usag_reservations', function (Builder $q0) {
                $q0->where('status', USAGReservation::RESERVATION_STATUS_PENDING)
                    ->whereHas('usag_sanction', function (Builder $q1) {
                        $q1->whereIn('status', [USAGSanction::SANCTION_STATUS_MERGED, USAGSanction::SANCTION_STATUS_UNASSIGNED, USAGReservation::RESERVATION_STATUS_PENDING]);
                    });
            })->get();
        return $this->success([
            'gyms' => $gymsWithPendingRerservations,
        ]);
    }

    public function getJoinedMeets(Request $request)
    {
        $gyms = $request->_managed_account->gyms()->pluck('id')->toArray();
        $filters = null;
        try {
            $browseMeetsRules = [
                'page' => ['sometimes', 'nullable', 'integer', 'min:1'],
                'limit' => ['sometimes', 'nullable', 'integer', 'min:1'],
                'state' => ['sometimes', 'nullable', 'string', 'size:2'],
                'from' => ['sometimes', 'nullable', 'date_format:m/d/Y',],
                'to' => ['sometimes', 'nullable', 'date_format:m/d/Y',],
                'usag' => ['sometimes', 'nullable', 'boolean'],
                'usaigc' => ['sometimes', 'nullable', 'boolean'],
                'aau' => ['sometimes', 'nullable', 'boolean'],
                'nga' => ['sometimes', 'nullable', 'boolean'],
                'name' => ['sometimes', 'nullable', 'string', 'max:255'],
                'status' => ['sometimes', 'nullable', 'integer'],
            ];

            $filters = $request->validate($browseMeetsRules);

            $page = (isset($filters['page']) ? (int) $filters['page'] : 1);
            $limit = (isset($filters['limit']) ? (int) $filters['limit'] : null);
            $status = (isset($filters['status']) ? (int) $filters['status'] : null);

            $query = MeetRegistration::whereIn('gym_id', $gyms)
                ->select([
                    'id', 'gym_id', 'meet_id', 'status'
                ])->whereHas('meet', function (Builder $q) use ($filters) {
                    $state = (isset($filters['state']) ? $filters['state'] : null);
                    $from = (isset($filters['from']) ? new \DateTime($filters['from']) : null);
                    $to = (isset($filters['to']) ? new \DateTime($filters['to']) : null);
                    $name = (isset($filters['name']) ? $filters['name'] : null);
                    $usag = (isset($filters['usag']) && $filters['usag'] ? true : false);
                    $usaigc = (isset($filters['usaigc']) && $filters['usaigc'] ? true : false);
                    $aau = (isset($filters['aau']) && $filters['aau'] ? true : false);
                    $nga = (isset($filters['nga']) && $filters['nga'] ? true : false);

                    if ($from !== null) {
                        $q->where('start_date', '>=', $from);
                    }

                    if ($to !== null) {
                        $q->where('end_date', '<=', $to);
                    }

                    if ($state !== null) {
                        $q->whereHas('venue_state', function (Builder $q2) use ($state) {
                            $q2->where('code', $state);
                        });
                    }

                    if ($name !== null) {
                        $q->where('name', 'ILIKE', '%'.strtolower($name).'%');
                    }

                    if ($usag || $usaigc || $aau || $nga) {
                        $subquery = ' in (
                        SELECT sanctioning_body_id
                        FROM category_meet
                        WHERE meet_id = id
                    )';

                        $q->where(function ($q2) use ($usag, $usaigc, $aau, $nga, $subquery) {
                            if ($usag) {
                                $q2->orWhereRaw(SanctioningBody::USAG.$subquery);
                            }

                            if ($usaigc) {
                                $q2->orWhereRaw(SanctioningBody::USAIGC.$subquery);
                            }

                            if ($aau) {
                                $q2->orWhereRaw(SanctioningBody::AAU.$subquery);
                            }

                            if ($nga) {
                                $q2->orWhereRaw(SanctioningBody::NGA.$subquery);
                            }
                        });
                    }

                    $q->where('is_published', true)
                        ->where('is_archived', false);
                })->where('status', '!=', MeetRegistration::STATUS_CANCELED)
                ->with([
                    'meet' => function ($q) {
                        $q->select([
                            'id', 'gym_id', 'profile_picture', 'name', 'start_date', 'end_date',
                            'venue_city', 'venue_state_id'
                        ]);
                    },
                    'meet.gym' => function ($q) {
                        $q->select([
                            'id', 'name', 'short_name'
                        ]);
                    },
                    'meet.venue_state' => function ($q) {
                        $q->exclude(['created_at', 'updated_at']);
                    },
                    'meet.categories' => function ($q) {
                        $q->select('id');
                    }
                ]);

            if (($status !== null)/* && ($status != MeetRegistration::STATUS_CANCELED)*/) {
                $query->where('status', $status);
            }

            $countQuery = clone $query;
            $countQuery->select(DB::raw('count(id) as registration_count'));
            $count = $countQuery->first()->registration_count;

            if ($limit !== null) {
                $query->limit($limit)->offset(($page - 1) * $limit);
            }
            $registrations = $query->orderBy('created_at', 'DESC')
                //->orderBy('name', 'ASC')
                ->get();

            $registrations = $registrations->map(function ($r) {
                $r->has_pending_transactions = $r->hasPendingTransactions();
                $r->has_repayable_transactions = $r->hasRepayableTransactions();
                return $r;
            });

            return $this->success([
                'total' => $count,
                'page' => $page,
                'limit' => $limit,
                'registrations' => $registrations,
            ]);
        } catch(ValidationException $e) {
            throw $e;
        } catch(CustomBaseException $e) {
            throw $e;
        } catch(\Throwable $e) {
            if (config('app.debug'))
                throw $e;

            Log::warning(self::class . '@joinedMeets : ' . $e->getMessage(), [
                'filters' => $filters
            ]);
            return $this->error([
                'message' => 'Something went wrong while retreiving meets.'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function notifications(Request $request)
    {
        $loginUser = $request->_managed_account;
        $notifications = Notification::where('user_id','=',$loginUser->id)->where('read_at','=',null)->orderBy('created_at','DESC')->get();

        return $this->success([
            'notifications' => $notifications,
        ]);
    }

    public function readNotifications(Request $request)
    {
        $loginUser = $request->_managed_account;
        $notifications = Notification::where('user_id','=',$loginUser->id)->where('read_at','!=',null)->orderBy('created_at','DESC')->get();

        return $this->success([
            'notifications' => $notifications,
        ]);
    }

    public function testDwolla()
    {
        /** @var DwollaService $dwollaService */
        $dwollaService = resolve(DwollaService::class);
        $transaction = null;
        $balanceTransaction = null;
        try {
            Auth::loginUsingId(58);
            DB::beginTransaction();

            $user = User::lockForUpdate()->find(Auth::id()); /** @var User $user */
            if ($user == null)
                throw new CustomBaseException('No such user with id `' . Auth::id() . '`');

            //userA bank
            $sourceBankAccount = resolve(DwollaService::class)->listFundingSources("6b08b0dc-72ab-46e5-adf0-38a918e55b2f")[0];
            $sourceBankAccount = $user->getBankAccount($sourceBankAccount->id);
//            if (!Str::endsWith($sourceBankAccount['_links']['customer']['href'], $user->dwolla_customer_id))
//                throw new CustomBaseException('No such bank account linked to your account.', -1);

//            $bankAccount1 = resolve(DwollaService::class)->listFundingSources("")[0];
//            if (!isset($bankAccount1) || ($bankAccount1 == ''))
//                throw new CustomBaseException('Invalid bank account', -1);

            $amount = "100";
            if (Helper::isFloat($amount)) {
                $amount = (float) $amount;
                if ($amount < 0)
                    throw new CustomBaseException('Invalid amount: Amount needs to be a positive value', -1);

                if ($amount > $user->cleared_balance)
                    throw new CustomBaseException('You do not have enough balance to withdraw that amount.', -1);
            } else {
                throw new CustomBaseException('Invalid amount', -1);
            }

            $fee = 0;

            $user2BankAccount = resolve(DwollaService::class)->listFundingSources("4af1f3f4-893a-4155-a219-f6ececa887a1")[0];
            $bankAccount = $user->getBankAccount($user2BankAccount->id);
//            if (!Str::endsWith($bankAccount['_links']['customer']['href'], $user->dwolla_customer_id))
//                throw new CustomBaseException('No such bank account linked to your account.', -1);


            // user A
//            $source = $dwollaService->getFundingSource("6b08b0dc-72ab-46e5-adf0-38a918e55b2f");
            $now = now();

            $balanceTransaction = $user->balance_transactions()->create([
                'processor_id' => null,
                'total' => -100,
                'description' => 'userA transfer money to user B',
                'clears_on' => $now,
                'type' => UserBalanceTransaction::BALANCE_TRANSACTION_TYPE_WITHDRAWAL,
                'status' => UserBalanceTransaction::BALANCE_TRANSACTION_STATUS_PENDING
            ]); /** @var UserBalanceTransaction $balanceTransaction */


            $transaction = $dwollaService->initiateACHTransfer(
                $sourceBankAccount['_links']['self']['href'],
                $bankAccount['_links']['self']['href'],
                150,
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

//            try {
//                Mail::to($user->email)
//                    ->send(new WithdrawalRequestedMailable($balanceTransaction));
//            } catch (\Throwable $th) {
//            }

            return $this->success();
        } catch(\Throwable $e) {
            if (DB::transactionLevel() > 0)
                DB::rollBack();

            if ($transaction != null) {
                $cancelFailed = true;
                try {
                    // Try and cancel the transaction.
                    if ($transaction instanceof Transfer){
                        $transaction = $transaction['_links']['self']['href'];
                    }

                    $cancelFailed = !$dwollaService->cancelACHTransfer($transaction);
                } catch (\Throwable $e) {
                    Log::debug('Panic TX Cancelation : ' . $e->getMessage());
                }
            }

            throw $e;
        }
    }
}