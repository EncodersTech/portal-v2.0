<?php

namespace App\Services;

use Stripe\Stripe;
use Stripe\Error\Base;
use Stripe\Error\Card;
use Stripe\Error\RateLimit;
use Stripe\Error\InvalidRequest;
use Stripe\Error\Authentication;
use Stripe\Error\ApiConnection;
use App\Models\ErrorCodeCategory;
use App\Exceptions\CustomStripeException;
use Stripe\Charge;
use Stripe\Customer;
use Stripe\Account;
use Stripe\PaymentIntent;
use App\Models\User;

class StripeService {

    private static $cardBrands = [
        'American Express' => '/img/cards/American Express.png',
        'Diners Club' => '/img/cards/Diners Club.png',
        'Discover' => '/img/cards/Discover.png',
        'JCB' => '/img/cards/JCB.png',
        'MasterCard' => '/img/cards/MasterCard.png',
        'UnionPay' => '/img/cards/UnionPay.png',
        'Visa' => '/img/cards/Visa.png',
        'Unknown' => '/img/cards/Unknown.png',
    ];

    public const CARD_TOKEN_RULES = [
        'card_token' => ['required', 'string']
    ];
    public const BANK_TOKEN_RULES = [
        'bank_token' => ['required', 'string'],
        'account_name' => ['required', 'string']
    ];
    public const BANK_VERIFY_RULES = [
        'bank_token' => ['required', 'string'],
        'first_deposit' => ['required', 'string'],
        'second_deposit' => ['required', 'string']
    ];

    public static function init(string $apiKey)
    {
        Stripe::setApiKey($apiKey);
    }

    public static function createCustomer(string $name, string $email, string $description = null, array $metadata = null)
    {
        try {
            return Customer::create(compact(['name', 'email', 'description', 'metadata']));
        } catch (Base $e) {
            self::_handleStripeException($e);
        }
    }
    public static function getFundingSource(string $customer_id,string $fundingSourceId)
    {
        try {
            $banks = null;
            $banks = Customer::retrieveSource($customer_id, $fundingSourceId);
            return $banks;
        } catch (Base $e) {
            self::_handleStripeException($e);
        }
    }
    public static function listBankAccounts($customer_id, int $limit = 100)
    {
        try {
            // $banks = resolve(Account::class);
            // if($customer_id != null)
            //     $banks = Account::allExternalAccounts($customer_id);
            // else
            //     $banks->data = [];
            $banks = Customer::allSources($customer_id, [
                'limit' => $limit,
                'object' => 'bank_account',
            ]);

                // print_r($banks->data); die();
            return $banks->data;
        } catch (Base $e) {
            self::_handleStripeException($e);
        }
    }
    public static function listCards(string $customer_id, int $limit = 100)
    {
        try {
            $cards = Customer::allSources($customer_id, [
                'limit' => $limit,
                'object' => 'card',
            ]);


            return $cards;
        } catch (Base $e) {
            self::_handleStripeException($e);
        }
    }

    public static function getCard(string $customer_id, string $card_id)
    {
        try {
            $card = Customer::retrieveSource($customer_id, $card_id);
            return $card;
        } catch (Base $e) {
            self::_handleStripeException($e);
        }
    }

    public static function getCardBrandImage(string $brand) {
        if (key_exists($brand, self::$cardBrands))
            return self::$cardBrands[$brand];
        else
            return self::$cardBrands['Unknown'];
    }

    public static function storeCard(string $customer_id, string $token) {
        try {
            return Customer::createSource($customer_id, [
                'source' => $token,
            ]);
        } catch (Base $e) {
            self::_handleStripeException($e);
        }
    }
    public static function deleteCard(string $customer_id, string $id) {
        try {
            return Customer::deleteSource($customer_id, $id);
        } catch (Base $e) {
            self::_handleStripeException($e);
        }
    }

    public static function createCharge(string $customer, string $source, float $amount,
        string $currency = 'USD', string $description = null, array $metadata = null,$type=null)
    {
        try {
            if ($amount < 0.50)
                throw new CustomStripeException("Amount too small. Minimum amount is 0.50 USD.", 1);

            $amount = floor($amount * 100);
            
            if($type == 'ach')
            {
                // print_r($customer); die();
                return \Stripe\Charge::create([
                    'amount'   => $amount,
                    'currency' => $currency,
                    "description" => $description,
                    "metadata" => $metadata,
                    'source' => $customer
                  ]);
                // return \Stripe\PaymentIntent::create([
                //     "amount" => $amount,
                //     "currency" => "usd",
                //     "description" => $description,
                //     "metadata" => $metadata,
                //     "payment_method" => $source,
                //     "payment_method_types" => ["us_bank_account"],
                // ], ["stripe_account" => $customer]);
            }
            return Charge::create(array_merge(compact(['customer', 'source', 'amount', 'currency',
                'description', 'metadata'
            ]), ["expand" => array("balance_transaction")]));

            // return Customer::create();
        } catch (Base $e) {
            self::_handleStripeException($e);
        }
    }

    private static function _handleStripeException(Base $e, bool $throw = true) : array {
        $result = [
            'code' => ErrorCodeCategory::getCategoryBase('Stripe'),
            'message' => 'Something went wrong with our payment processor.',
            'details' => null,
        ];
        
        try{
            $error = $e->getJsonBody();
            $error = isset($error['error']) ? $error['error'] : $error;
            if(!isset($error['code']))
                throw new CustomStripeException("Error Processing Request", 1);

            switch ($error['code']) {
                case 'resource_missing':
                    $result = [
                        'code' => ErrorCodeCategory::getCategoryBase('Stripe') + 1,
                        'message' => 'There\'s a problem with your account. Please contact us as soon as possible.',
                        'details' => $error['param'],
                    ];
            }
    
            if ($e instanceof Card) {
                //dump($e->getJsonBody(), $e->getMessage());
    
            } elseif ($e instanceof RateLimit) {
    
            } elseif ($e instanceof InvalidRequest) {
    
            } elseif ($e instanceof Authentication) {
    
            } elseif ($e instanceof ApiConnection) {
    
            } else {    // Stripe\Error\Base
    
            }
    
            if ($throw)
                throw new CustomStripeException($result['message'], $result['code'], $e);
    
            return $result;
        }catch(CustomStripeException $e)
        {
            return $result;
        }
        
    }
    public static function updateConnectAccountWebhook($payload,$header)
    {
        $authentic = self::verifyWebhookSignature(
            $header->get('stripe-signature'),
            "whsec_FYtjBfB2FBNaL0p3i0F0OSjREcvLmnSK",
            $payload
        );
        if (!$authentic)
            throw new CustomBaseException('Webhook payload authentication failed ', -1);

        $payload = json_decode($payload);
        if ($payload === null)
        throw new CustomBaseException('Server error', ErrorCodeCategory::getCategoryBase('General') + 1);
        switch ($payload->type) {
            case 'account.updated':
                return self::updateConnectAccount(
                    $payload->data->object
                );
            case 'transfer.paid':
                return self::updateRegistrationStatus(
                    $payload->data->object,
                    $payload->user_id
                );
            default:
        }
        return true;
    }
    public static function updateRegistrationStatus($payload, $user_id)
    {
        $host = User::where("stripe_connect_id",$user_id)->first();

        $balance = \Stripe\Balance::retrieve(
            ['stripe_account' => $user_id]
        );
        print_r($balance);
        if($balance->available[0]->amount >= 0)
        {
            $data = DB::select('select mt.processor_id from meet_transactions as mt 
            join meet_registrations as mr 
            on mt.meet_registration_id = mr.id 
            where mr.gym_id = '.$host->id.' and mt.method = 3 and mt.status = 1');

            print_r($data);
            
        }
    }
    public static function updateConnectAccount($payload, $user_id){


        if($payload->verification->disabled_reason == "fields_needed")
        {
            $host->stripe_connect_status = User::STRIPE_CONNECT_STATUS_FIELD_NEEDED;
        }
        else if($payload->details_submitted == true && $payload->charges_enabled == true)
        {
            $host->stripe_connect_status = User::STRIPE_CONNECT_STATUS_ACCEPT;
        }
        else if($payload->verification->disabled_reason == null)
        {
            $host->stripe_connect_status = User::STRIPE_CONNECT_STATUS_PENDING;
        }
        $host->save();
        return true;
    }
    public static function acceptTransaction()
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
    public static function storeBank(string $customer_id, string $token, string $accountName) {
        try {
            Customer::createSource($customer_id, [
                'source' => $token
            ]);
            
            $host = User::where("stripe_customer_id",$customer_id)->first();
            if($host->stripe_connect_id == null)
            {
                $account = \Stripe\Account::create(
                    [
                      'country' => 'US',
                      'type' => 'custom',
                      'capabilities' => [
                        'card_payments' => ['requested' => true],
                        'transfers' => ['requested' => true],
                        'us_bank_account_ach_payments' => ['requested' => true]
                      ],
                      'settings' => [
                        'payouts' => [
                            'debit_negative_balances' => true
                        ]
                      ]
                    ]
                );
                $user = auth()->user();
                $user->update_connect_account(array("stripe_connect_id"=>$account->id, "stripe_connect_status" => User::STRIPE_CONNECT_STATUS_PENDING));
    
                $external_account = \Stripe\Account::createExternalAccount(
                    $account->id,
                    [
                      'external_account' => $token,
                    ]
                );
                // print_r($account); die();
                $links = \Stripe\AccountLink::create(
                    [
                      'account' => $account->id,
                      'refresh_url' => 'http://127.0.0.1:8000/account/payment_options',
                      'return_url' => 'http://127.0.0.1:8000/account/payment_options',
                      'type' => 'account_onboarding',
                      'collect' => 'eventually_due',
                    ]
                );
                header('Location: '.$links->url);
                exit();
            }
            
            return true;
        } catch (Base $e) {
            // print_r($e->getMessage()); die();
            self::_handleStripeException($e);
        }
    }
    public static function createAccountConnectLink($id)
    {
        $links = \Stripe\AccountLink::create(
            [
              'account' => $id,
              'refresh_url' => 'http://127.0.0.1:8000/account/payment_options',
              'return_url' => 'http://127.0.0.1:8000/account/payment_options',
              'type' => 'account_onboarding',
              'collect' => 'eventually_due',
            ]
        );
        return array(
            "code" => 4,
            "url" => $links->url);
    }
    public static function verifyWebhookSignature($sig_header, $endpoint_secret, $payload) {
        try {
            $event = \Stripe\Webhook::constructEvent(
                $payload, $sig_header, $endpoint_secret
            );
            return true;
        } catch(Base $e) {
            self::_handleStripeException($e);
        }
        return false;
    }
    public static function verifyBank(string $customer_id, string $token, string $first_deposit, string $second_deposit) {
        try {
            $bankAccount = Customer::retrieveSource(
                $customer_id,
                $token
            );
            # verify the account
            return $bankAccount->verify(['amounts' => [$first_deposit, $second_deposit]]);
        } catch (Base $e) {
            self::_handleStripeException($e);
        }
    }
}
