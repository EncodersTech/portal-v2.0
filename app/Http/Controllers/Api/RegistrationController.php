<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\CustomBaseException;
use App\Exceptions\CustomDwollaException;
use App\Exceptions\CustomStripeException;
use App\Helper;
use App\Mail\Registrant\RegistrantMeetEntryMailable;
use App\Models\Gym;
use App\Models\Meet;
use App\Models\Deposit;
use App\Models\MeetRegistration;
use App\Models\MeetTransaction;
use App\Models\RegistrationSpecialist;
use Illuminate\Http\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;

class RegistrationController extends BaseApiController
{
    public function register(Request $request, string $meet, string $gym)
    {

        try {
            $meet = Meet::retrieveMeet($meet, true); /** @var \App\Models\Meet $meet */
            $gym = $request->_managed_account->retrieveGym($gym); /** @var \App\Models\Gym $gym */
            return MeetRegistration::register(
                $meet,
                $gym,
                $request->input('levels'),
                $request->input('coaches'),
                $request->input('summary'),
                $request->input('method'),
                (bool) $request->input('use_balance'),
                null,
                $request->input('deposit'),
                $request->input('coupon')
            );
        } catch(CustomBaseException $e) {
            throw $e;
        } catch(\Throwable $e) {
            Log::warning(self::class . '@register : ' . $e->getMessage(), [
                'Meet' => $meet,
                'Gym' => $gym,
                'Throwable' => $e
            ]);
            return $this->error([
                'message' => 'Something went wrong while processing your registration.'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    public function checkCoupon(Request $request)
    {
        try{
            $deposit = Deposit::where('meet_id',$request->meet_id)
            ->where('gym_id',$request->gym_id)
            ->where('token_id',$request->coupon)
            ->where('is_enable',true)
            ->where('is_used',false)
            ->first();
            if($deposit)
            {
                return $this->success([
                    'message' => "Coupon Found Successfully",
                    'value' => $deposit->amount
                ]);
            }
            else{
                return $this->error([
                    'message' => "Coupon not found"
                ]);
            }

        }catch(CustomBaseException $e)
        {
            throw $e;
        }
    }

    public function edit(Request $request, string $gym, string $registration)
    {
        try {
            $gym = $request->_managed_account->retrieveGym($gym); /** @var \App\Models\Gym $gym */

            $registration = $gym->registrations()
                                ->where('status', '!=', MeetRegistration::STATUS_CANCELED)
                                ->find($registration); /** @var MeetRegistration $registration */
            if ($registration == null)
                throw new CustomBaseException("No such registration", -1);

            $meet = $registration->meet; /** @var \App\Models\Meet $meet */

            $previous_deposit_remaining_total = array();
            
            foreach ($registration->transactions as $key => $value) {
                if($value['is_deposit'] == true && $value['is_deposit_sattle'] == false && $value['status'] == MeetTransaction::STATUS_COMPLETED)
                {
                    $previous_deposit_remaining = $value['breakdown'];
                    $previous_deposit_remaining_total = array(
                        'registration_id' => $value["id"],
                        'total' => $previous_deposit_remaining['gym']['subtotal'] - $previous_deposit_remaining['gym']['deposit_subtotal']
                    );
                    break;
                }
            }
            return $registration->edit(
                $meet,
                $gym,
                $request->input('bodies'),
                $request->input('coaches'),
                $request->input('summary'),
                $request->input('method'),
                (bool) $request->input('use_balance'),
                $previous_deposit_remaining_total,
                $request->input('coupon')
            );
        } catch(CustomBaseException $e) {
            throw $e;
        } catch(\Throwable $e) {
            Log::warning(self::class . '@edit : ' . $e->getMessage(), [
                'Registration' => $registration,
                'Gym' => $gym,
                'Throwable' => $e
            ]);
            return $this->error([
                'message' => 'Something went wrong while processing your changes.'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function pay(Request $request, string $gym, string $registration,
        string $transaction)
    {
        try {
            $gym = $request->_managed_account->retrieveGym($gym); /** @var \App\Models\Gym $gym */

            $registration = $gym->registrations()
                                ->where('status', '!=', MeetRegistration::STATUS_CANCELED)
                                ->find($registration); /** @var MeetRegistration $registration */
            if ($registration == null)
                throw new CustomBaseException("No such registration", -1);

            $transaction = $registration->transactions()
                                        ->whereIn('status', [
                                            MeetTransaction::STATUS_WAITLIST_CONFIRMED,
                                            MeetTransaction::STATUS_FAILED,
                                            MeetTransaction::STATUS_CANCELED,
                                        ])->find($transaction);
            if ($transaction == null)
                throw new CustomBaseException("No such transaction", -1);

            return $registration->pay(
                $gym, $transaction,
                $request->input('summary'),
                $request->input('method'),
                (bool) $request->input('use_balance')
            );
        } catch(CustomBaseException $e) {
            throw $e;
        } catch(\Throwable $e) {
            Log::warning(self::class . '@pay : ' . $e->getMessage(), [
                'Gym' => $gym,
                'Registration' => $registration,
                'Transaction' => $transaction,
                'Throwable' => $e
            ]);
            return $this->error([
                'message' => 'Something went wrong while processing your payment.'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function paymentOptions(Request $request, string $meet, string $gym)
    {
        try {
            $meet = Meet::retrieveMeet($meet, true); /** @var \App\Models\Meet $meet */
            $gym = $request->_managed_account->retrieveGym($gym); /** @var \App\Models\Gym $gym */
            $discount = 0;
            $now = new \DateTime();
            if($meet->registration_first_discount_is_enable)
            {
                $first = new \DateTime($meet->registration_first_discount_end_date);
                if($first >= $now)
                {
                    $discount = $meet->registration_first_discount_amount;
                }
                else if($meet->registration_second_discount_is_enable)
                {
                    $second = new \DateTime($meet->registration_second_discount_end_date);
                    if($second >= $now)
                    {
                        $discount = $meet->registration_second_discount_amount;
                    }
                    else if($meet->registration_third_discount_is_enable)
                    {
                        $third = new \DateTime($meet->registration_third_discount_end_date);
                        if($third >= $now)
                        {
                            $discount = $meet->registration_third_discount_amount;
                        }
                    }
                }
            }
            $available_payment_options = [
                'is_own' => ($meet->gym->user->id == $gym->user->id),
                'discount' => $discount,
                'defer' => [
                    'handling' => $meet->defer_handling_fees,
                    'processor' => $meet->defer_processor_fees,
                ],
                'handling' => [
                    'mode' => MeetRegistration::HANDLING_FEE_MODE,
                    'fee' => Helper::getHandlingFee($meet)
                ],
                'methods' => [
                    MeetRegistration::PAYMENT_OPTION_BALANCE => [
                        'mode' => MeetRegistration::PAYMENT_OPTION_FEE_MODE[MeetRegistration::PAYMENT_OPTION_BALANCE],
                        'fee' => $meet->balance_fee(),
                        'current' => $request->_managed_account->cleared_balance
                    ],
                    MeetRegistration::PAYMENT_OPTION_CARD => [
                        'mode' => MeetRegistration::PAYMENT_OPTION_FEE_MODE[MeetRegistration::PAYMENT_OPTION_CARD],
                        'fee' => $meet->cc_fee(),
                        'cards' => []
                    ],
                ]
            ];

            $cards = $request->_managed_account->getCards(false);
            if(($cards instanceof CustomStripeException) || ($cards === null))
                $cards = [];

            foreach ($cards as $card) {
                $available_payment_options['methods'][MeetRegistration::PAYMENT_OPTION_CARD]['cards'][] = [
                    'id' => $card->id,
                    'brand' => $card->brand,
                    'expires' => [
                        'month' => $card->exp_month,
                        'year' => $card->exp_year,
                    ],
                    'last4' => $card->last4,
                    'image' => $card->image
                ];
            }

//            if ($meet->accept_paypal) {
//                $available_payment_options['methods'][MeetRegistration::PAYMENT_OPTION_PAYPAL] = [
//                    'mode' => MeetRegistration::PAYMENT_OPTION_FEE_MODE[MeetRegistration::PAYMENT_OPTION_PAYPAL],
//                    'fee' => $meet->paypal_fee()
//                ];
//            }

            if ($meet->accept_mailed_check) {
                $hostCards = $meet->gym->user->getCards(false);
                if (($hostCards !== null) && !($hostCards instanceof CustomStripeException) && (count($hostCards) > 0)) {
                    $available_payment_options['methods'][MeetRegistration::PAYMENT_OPTION_CHECK] = [
                        'mode' => MeetRegistration::PAYMENT_OPTION_FEE_MODE[MeetRegistration::PAYMENT_OPTION_CHECK],
                        'fee' => $meet->check_fee()
                    ];
                }
            }

            if ($meet->accept_ach /*&& $meet->gym->canUseACH() && $gym->canUseACH()*/) {

                // $bankAccounts = $request->_managed_account->getBankAccounts(false);
                $bankAccounts = $request->_managed_account->getStripeBankAccounts(false);
                if (($bankAccounts instanceof CustomStripeException) || ($bankAccounts === null))
                    $bankAccounts = [];
                // if (($bankAccounts instanceof CustomDwollaException) || ($bankAccounts === null))
                //     $bankAccounts = [];

                $available_payment_options['methods'][MeetRegistration::PAYMENT_OPTION_ACH] = [
                    'mode' => MeetRegistration::PAYMENT_OPTION_FEE_MODE[MeetRegistration::PAYMENT_OPTION_ACH],
                    'fee' => $meet->ach_fee(),
                    'accounts' => []
                ];

                foreach ($bankAccounts as $ba) {
                    // print_r($ba);
                    // if (($ba->status != 'verified'))
                    //     continue;
                    // if (($ba->status != 'verified') || !in_array('ach', $ba->channels))
                    //     continue;

                    $available_payment_options['methods'][MeetRegistration::PAYMENT_OPTION_ACH]['accounts'][] = [
                        'id' => $ba->id,
                        'type' => $ba->account_type,
                        'name' => $ba->account_holder_name,
                        'bankName' => $ba->bank_name
                    ];
                    // $available_payment_options['methods'][MeetRegistration::PAYMENT_OPTION_ACH]['accounts'][] = [
                    //     'id' => $ba->id,
                    //     'type' => $ba->bankAccountType,
                    //     'name' => $ba->name,
                    //     'bankName' => $ba->bankName
                    // ];
                }
            }

            return $available_payment_options;

        } catch(CustomBaseException $e) {
            throw $e;
        } catch(\Throwable $e) {
            Log::warning(self::class . '@paymentOptions : ' . $e->getMessage(), [
                'Meet' => $meet,
                'Gym' => $gym,
                'Throwable' => $e
            ]);
            return $this->error([
                'message' => 'Something went wrong while fetching payment options.'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function registrationDetails(Request $request, string $registration)
    {
        try {
            $registration = MeetRegistration::find($registration); /** @var MeetRegistration $registration */
            if ($registration == null)
                throw new CustomBaseException("No such registration", -1);

            $meet = Meet::find($registration->meet_id); /** @var Meet $meet */
            if ($meet == null)
                throw new CustomBaseException("No such meet", -1);

            $gym = Gym::find($registration->gym_id);; /** @var Gym $gym */
            if ($gym == null)
                throw new CustomBaseException("No such gym", -1);

            $hiddenParty = 'host';
            if ($meet->gym->user->id == $request->_managed_account->id)
                $hiddenParty = '$gym';
            else if ($gym->user->id != $request->_managed_account->id)
                throw new CustomBaseException("You have no such registration.", -1);

            $levels = $registration->levels()/*->where('is_disabled',false)*/
            ->with([
                'sanctioning_body' => function ($q) {
                    $q->exclude(['created_at', 'updated_at']);
                },
                'level_category' => function ($q) {
                    $q->exclude(['created_at', 'updated_at']);
                },
            ])->get()
            ->makeHidden([
                'sanctioning_body_id', 'level_category_id', 'is_disabled',
                'created_at', 'updated_at'
            ]);

            $athletes = $registration->athletes()->with([
                'tshirt' => function ($q) {
                    $q->exclude(['is_disabled'])
                        /*->where('is_disabled',false)*/;
                },
                'leo' => function ($q) {
                    $q->exclude(['is_disabled'])
                        /*->where('is_disabled',false)*/;
                },
                'registration_level' => function ($q) {
                    $q->with([
                            'level' => function ($q) {
                                $q->select(['id', 'sanctioning_body_id', 'level_category_id']);
                            },
                        ]);
                },
            ])->orderBy('first_name', 'ASC')
            ->orderBy('last_name', 'ASC')
            ->get();

            $specialists = $registration->specialists()->with([
                'tshirt' => function ($q) {
                    $q->exclude(['is_disabled']);
                },
                'leo' => function ($q) {
                    $q->exclude(['is_disabled']);
                },
                'registration_level' => function ($q) {
                    $q
                        ->with([
                            'level' => function ($q) {
                                $q->select(['id', 'sanctioning_body_id', 'level_category_id']);
                            },
                        ]);
                },
                'events' => function ($q) {
                },
            ])->orderBy('first_name', 'ASC')
            ->orderBy('last_name', 'ASC')
            ->get()/*
            ->makeHidden([
                'tshirt_size_id', 'leo_size_id', 'meet_registration_id'
            ])*/;

            foreach ($specialists as $i => $specialist) { /** @var RegistrationSpecialist $specialist */
                $specialists[$i]->status = $specialist->status();
                $specialists[$i]->has_pending_events = $specialist->hasPendingEvents();
            }

            $coaches = $registration->coaches()->with([
                'tshirt' => function ($q) {
                    $q->exclude(['is_disabled'])
                        /*->where('is_disabled',false)*/;
                }
            ])->orderBy('first_name', 'ASC')
            ->orderBy('last_name', 'ASC')
            ->get()/*
            ->makeHidden([
                'tshirt_size_id', 'meet_registration_id'
            ])*/;

            $transactions = $registration->transactions()
            ->orderBy('created_at', 'DESC')
            ->get()
            ->makeHidden([
                'meet_registration_id', 'handling_rate', 'processor_rate', 'total'
            ])->toArray();

            foreach ($transactions as $i => $tx)
                unset($transactions[$i]['breakdown'][$hiddenParty]);

            $result  = [
                'was_late' => $registration->was_late,
                'late_fee' => $registration->late_fee,
                'late_refund' => $registration->late_refund,
                'levels' => $levels,
                'athletes' => $athletes,
                'specialists' => $specialists,
                'coaches' => $coaches,
                'transactions' => $transactions,
                'editing_abilities' => $registration->editingAbilities(),
            ];

            return $result;
        } catch(CustomBaseException $e) {
            throw $e;
        } catch(\Throwable $e) {
            Log::warning(self::class . '@registrationDetails : ' . $e->getMessage(), [
                'Registration' => $registration,
                'Throwable' => $e
            ]);
            return $this->error([
                'message' => 'Something went wrong while fetching registration details.'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}