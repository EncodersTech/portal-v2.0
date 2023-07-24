<?php

namespace App\Models;

use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Lab404\Impersonate\Models\Impersonate;
use Laravel\Passport\HasApiTokens;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;
use App\Services\StripeService;
use Illuminate\Support\Facades\DB;
use App\Services\DwollaService;
use App\Exceptions\CustomStripeException;
use App\Exceptions\CustomDwollaException;
use App\Models\MemberInvitation;
use App\Exceptions\CustomBaseException;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Mail\MemberInvitationMailable;
use App\Mail\MemberInvitationAccepted;
use App\Helper;
use GuzzleHttp\Client as Guzzle;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, Notifiable;
    use Impersonate;
    public const STRIPE_CONNECT_NEVER_ATTEMPTED = 1;
    public const STRIPE_CONNECT_STATUS_PENDING = 2;
    public const STRIPE_CONNECT_STATUS_ACCEPT = 3;
    public const STRIPE_CONNECT_STATUS_FIELD_NEEDED = 4;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'email',
        'password',
        'first_name',
        'last_name',
        'office_phone',
        'job_title',
        'profile_picture',
        'email_verified_at'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token', 'stripe_customer_id', 'dwolla_customer_id'
    ];

    protected $appends = ['full_name'];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    protected static $profilePictureRules = [
        'nullable',
        'image',
        'mimes:jpeg,png,jpg',
        'dimensions:min_width=100,min_height=100',
    ];

    protected static $createRules = [
        'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
        'password' => ['required', 'string', 'min:8', 'confirmed'],
        'first_name' => ['required', 'string', 'max:255'],
        'last_name' => ['required', 'string', 'max:255'],
        'office_phone' => ['required', 'phone:AUTO,US,UK'],
        'job_title' => ['required', 'string', 'max:255'],
        'terms_of_service_and_privacy_policy' => 'accepted',
        'member_invite' => ['nullable', 'string', 'max:255'],
        'h-captcha-response' => ['required', 'string']
    ];

    protected static $updateRules = [
        'first_name' => ['required', 'string', 'max:255'],
        'last_name' => ['required', 'string', 'max:255'],
        'office_phone' => ['required', 'phone:AUTO,US,UK'],
        'job_title' => ['required', 'string', 'max:255'],
    ];

    protected static $loginRules = [
        'email' => ['required', 'string', 'email', 'max:255'],
        'password' => ['required', 'string'],
        'remember' => ['sometimes', 'accepted'],
        'h-captcha-response' => ['required', 'string']
    ];

    public const PASSWORD_UPDATE_RULES = [
        'old_password' => ['required', 'string', 'min:8'],
        'password' => ['required', 'string', 'min:8', 'confirmed'],
    ];

    public const MEMBER_INVITE_RULES = [
        'invite_email' => ['required', 'string', 'email', 'max:255'],
    ];

    public const ADMIN_UPDATE_USER_RULE = [
        'first_name' => ['required', 'string', 'max:255'],
        'last_name' => ['required', 'string', 'max:255'],
        'office_phone' => ['required', 'phone:AUTO,US,UK'],
        'job_title' => ['required', 'string', 'max:255'],
    ];

    public static function getProfilePictureRules()
    {
        return array_merge(self::$profilePictureRules, [
            'max:' . Setting::profilePictureMaxSize()
        ]);
    }

    public static function getCreateRules()
    {
        return self::$createRules;
    }

    public static function getUpdateRules()
    {
        return self::$updateRules;
    }

    public static function getLoginRules()
    {
        return self::$loginRules;
    }

    public function fullName()
    {
        return $this->first_name . ' ' . $this->last_name;
    }

    public function availableFunds()
    {
        return number_format($this->cleared_balance, 2);
    }

    public function pendingFunds()
    {
        return number_format($this->pending_balance, 2);
    }

    private function _user_member(bool $flip = false)
    {
        return $this->belongsToMany(User::class, 'member_user', ($flip ? 'member_id' : 'user_id'), ($flip ? 'user_id' : 'member_id'))
                    ->using(MemberUser::class)
                    ->withPivot(MemberUser::getPivotFieldsOfInterest())
                    ->withTimestamps();
    }

    public function members()
    {
        return $this->_user_member();
    }

    public function memberOf()
    {
        return $this->_user_member(true);
    }

    public function gyms()
    {
        return $this->hasMany(Gym::class);
    }

    public function state()
    {
        return $this->belongsTo(State::class);
    }

    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    public function memberInvitations()
    {
        return $this->hasMany(MemberInvitation::class);
    }

    public function balance_transactions()
    {
        return $this->hasMany(UserBalanceTransaction::class);
    }

    public function dwolla_verification_attempts()
    {
        return $this->hasMany(DwollaVerificationAttempt::class);
    }

    public function getFullNameAttribute()
    {
        return $this->first_name. ' ' .$this->last_name;
    }

    public function isCurrentUser() {
        return $this->id == auth()->user()->id;
    }

    public function isNotCurrentUser() {
        return $this->id != auth()->user()->id;
    }
    public function countGymUsag_rs($gyms)
    {
        $k = 0;
        $data = $gyms->get();
        $usagGym = resolve(Gym::class);
        foreach ($data as $key => $value) {
            $a = USAGReservation::where('gym_id',$value->id)->where('status',1)->count();
            $b = USAGSanction::where('gym_id',$value->id)->where('status',1)->count();
            $k += $a+$b;
        }
        // die();
        return $k;
        // return $gyms->count() .' ';
    }
    public static function generateRandomString($length = 10) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }
    public static function register(array $userAttr, UploadedFile $profilePicture = null) : User
    {
        $sender = null;
        DB::beginTransaction();

        try {
            $domain = explode('@',$userAttr['email'])[1];
            if(in_array($domain, Helper::SPAM_MAIL_DOMAINS))
            {
                throw new CustomBaseException("Suspicious activity detected. Please contact admin");
            }
            if(!isset($userAttr['h-captcha-response']))
                throw new CustomBaseException("Captcha is not validated");
            $path = "https://hcaptcha.com/siteverify";
            $options = [
                'form_params' => [
                    "secret" => env('HCAPTCHA_SECRET_KEY'),
                    "response" => $userAttr['h-captcha-response']
                   ]
               ];
            $client = new Guzzle([
                'timeout'  => config('app.ext_api_timeout', 15)
            ]);
            $response = (string) $client->request('POST',  $path, $options)->getBody();
            $r_body = json_decode($response);
            if(!$r_body->success)
            {
                throw new CustomBaseException("Suspicious activity detected. Please contact admin");
            }

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

            $user->stripe_customer_id = 'fake_stripe_'.User::generateRandomString();
            /* StripeService::createCustomer(
                $user->fullName(),
                $user->email,
                config('app.name') . ' | ' . $user->fullName()
            )->id; */

            $user->dwolla_customer_id = 'fake_dwolla_'.User::generateRandomString();
            /*resolve(DwollaService::class)->createCustomer(
                $user->first_name,
                $user->last_name,
                $user->email
            )->id; */

            $user->save();

            DB::commit();

            if (isset($userAttr['member_invite'])) {
                Mail::to($sender->email)->send(new MemberInvitationAccepted(
                    $sender,
                    $user
                ));
            }

            return $user;
        } catch (\Throwable $throwable) {
            DB::rollBack();
            throw $throwable;
        } catch (CustomBaseException $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function resetPassword(string $old, string $new) {
        $this->password = Hash::make($new);
        return $this->save();
    }

    public function storeProfilePicture(UploadedFile $profilePicture) : bool
    {
        $old = $this->profile_picture;
        $this->profile_picture = Storage::url(Storage::putFile('public/images/user_profile', $profilePicture));
        Helper::removeOldFile($old, config('app.default_profile_picture'));
        return $this->save();
    }

    public function clearProfilePicture() : bool
    {
        $default = config('app.default_profile_picture');
        $old = $this->profile_picture;
        $this->profile_picture = $default;
        Helper::removeOldFile($old, $default);
        return $this->save();
    }
    public function update_connect_account(array $attr)
    {
        if (isset($attr['stripe_connect_id']))
            $this->stripe_connect_id = $attr['stripe_connect_id'];

        if (isset($attr['stripe_connect_status']))
            $this->stripe_connect_status = $attr['stripe_connect_status'];

        return $this->save();
    }
    public function getStripeConnectInfo()
    {
        if($this->stripe_connect_id != null)
        {
            // public const STRIPE_CONNECT_NEVER_ATTEMPTED = 1;
            // public const STRIPE_CONNECT_STATUS_PENDING = 2;
            // public const STRIPE_CONNECT_STATUS_ACCEPT = 3;
            // public const STRIPE_CONNECT_STATUS_FIELD_NEEDED = 4;
            if($this->stripe_connect_status == User::STRIPE_CONNECT_STATUS_FIELD_NEEDED)
            {
                return StripeService::createAccountConnectLink($this->stripe_connect_id);
            }
            else if($this->stripe_connect_status == User::STRIPE_CONNECT_STATUS_PENDING)
            {
                return array("code"=>2);
            }
            else if($this->stripe_connect_status == User::STRIPE_CONNECT_STATUS_ACCEPT)
            {
                return array("code"=>3);
            }
            else
                return array("code" => 1);
        }
        else
            return array("code" => 1);
    }

    public function updateProfile(array $attr)
    {
        if (isset($attr['first_name']))
            $this->first_name = $attr['first_name'];

        if (isset($attr['last_name']))
            $this->last_name = $attr['last_name'];

        if (isset($attr['office_phone']))
            $this->office_phone = $attr['office_phone'];

        if (isset($attr['job_title']))
            $this->job_title = $attr['job_title'];

        if (isset($attr['email']))
            $this->email = $attr['email'];
        return $this->save();
    }

    public function addCard(string $token)
    {
        try {
            $cards = StripeService::storeCard($this->stripe_customer_id, $token);
            AuditEvent::cardLinked($cards);
            return $cards;
        } catch (CustomStripeException $e) {
            throw new CustomStripeException('We couldn\'t link this card to your account.', -1);
        }
    }
    public function stripeAddBank(string $token, string $accountName)
    {
        try {
            $banks = StripeService::storeBank($this->stripe_customer_id, $token, $accountName);
            return $banks;
        } catch (CustomStripeException $e) {
            throw new CustomStripeException('We couldn\'t link this bank account to your account.', -1);
        }
    }
    public function stripeVerifyBank(string $token, string $first_deposit, string $second_deposit)
    {
        try {
            $banks = StripeService::verifyBank($this->stripe_customer_id, $token, $first_deposit, $second_deposit);
            return $banks;
        } catch (CustomStripeException $e) {
            throw new CustomStripeException('We couldn\'t link this bank account to your account.', -1);
        }
    }
    public function removeCard(string $id)
    {
        $cards = null;
        try {
            $cards = StripeService::deleteCard($this->stripe_customer_id, $id);
            AuditEvent::cardUnlinked($cards);
            return $cards;
        } catch (CustomStripeException $e) {
            throw new CustomStripeException('We couldn\'t unlink this card from your account.', -1);
        }
    }
    public function getStripeBankAccounts(bool $throw = true)
    {
        $accounts = null;
        try {
            $accounts = StripeService::listBankAccounts($this->stripe_customer_id);
            $accounts = empty($account) ? array() : $account->data;

            if (count($accounts) == 0) {
                $accounts = null;
            }
            return $accounts;
        } catch (CustomStripeException $e) {
            $result = new CustomStripeException(
                'You cannot make changes to your linked bank accounts for the time being.' .
                ' Please contact us as soon as possible.', $e->getCode(), $e);

            if ($throw)
                throw $result;

            return $result;
        }
    }
    public function getCards(bool $throw = true)
    {
        $cards = null;
        try {
            $card = StripeService::listCards($this->stripe_customer_id);
            $cards = empty($card) ? array() : $card->data;
            if (count($cards) > 0) {
                foreach ($cards as $card) {
                    $card['image'] = StripeService::getCardBrandImage($card['brand']);
                }
            } else {
                $cards = null;
            }

            return $cards;
        } catch (CustomStripeException $e) {
            $result = new CustomStripeException(
                'You cannot make changes to your linked credit cards for the time being.' .
                ' Please contact us as soon as possible.', $e->getCode(), $e);

            if ($throw)
                throw $result;

            return $result;
        }
    }

    public function getCard(string $cardId, bool $throw = true)
    {
        try {
            $card = StripeService::getCard($this->stripe_customer_id, $cardId);
            return $card;
        } catch (CustomStripeException $e) {
            $result = new CustomStripeException(
                'You cannot make changes to your linked credit cards for the time being.' .
                ' Please contact us as soon as possible.', $e->getCode(), $e);

            if ($throw)
                throw $result;

            return $result;
        }
    }

    public function getBankAccount(string $bankAccountId, bool $throw = true, bool $removed = false)
    {
        try {
            $bankAccount = resolve(DwollaService::class)->getFundingSource($bankAccountId);
            return $bankAccount;
        } catch (CustomDwollaException $e) {
            $result = new CustomDwollaException(
                'You cannot make changes to your linked bank accounts for the time being.' .
                ' Please contact us as soon as possible.', $e->getCode(), $e);

            if ($throw)
                throw $result;

            return $result;
        }
    }

    public function getBankAccounts(bool $throw = true, bool $removed = false)
    {
        try {
            $bankAccount = resolve(DwollaService::class)->listFundingSources($this->dwolla_customer_id);
            // echo $this->stripe_customer_id;
            // $bankAccount = resolve(StripeService::class)->listBankAccounts($this->stripe_connect_id);
            // $bankAccount = resolve(StripeService::class)->listBankAccounts($this->stripe_customer_id);
            // print_r($bankAccount);
            if (count($bankAccount) < 1)
                $bankAccount = null;

            // print_r($bankAccount); die();

            return $bankAccount;
        } catch (CustomDwollaException $e) {
            Log::error($e->getMessage());
            $result = new CustomDwollaException(
                'You cannot make changes to your linked bank accounts for the time being.' .
                ' Please contact us as soon as possible.', $e->getCode(), $e);

            if ($throw)
                throw $result;

            return $result;
        }
    }

    public function removeBankAccount(string $id)
    {
        $bank = resolve(DwollaService::class)->removeFundingSource($id);
        return $bank;
    }

    public function verifyMicroDeposits(string $id, string $v1, string $v2)
    {
        $microDeposit = resolve(DwollaService::class)->verifyMicroDeposits($id, $v1, $v2);
        return $microDeposit;
    }

    public function inviteMember(string $email) {

        if ($email == $this->email)
            throw new CustomBaseException('You can\'t invite yourself.', 420);

        $previous = $this->memberInvitations()->where('email', $email)->first();
        if ($previous != null)
            throw new CustomBaseException('You already sent an invite to ' . $email, -1);

        $invited = self::where('email', $email)->first();

        if ($invited != null) {
            $member = $this->members->find($invited->id);
            if ($member != null)
                throw new CustomBaseException('This member is already managing your account', -1);
        }

        $token = (string) Str::orderedUuid();

        DB::beginTransaction();
        try {
            $this->memberInvitations()->create([
                'email' => $email,
                'token' => $token
            ]);

            Mail::to($email)
                ->send(new MemberInvitationMailable(
                    $this,
                    $token,
                    $invited
                )
            );

            DB::commit();
            return true;
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::channel('slack-warning')->warning('inviteMember(' . $email . ') failed : ' . $e->getMessage(), [
                'User' => $this->email,
                'Throwable' => $e
            ]);
            return false;
        }
    }

    public function acceptInvite(string $token, bool $creation = false) {
        DB::beginTransaction();
        try {
            $invitation = MemberInvitation::where('token', $token)->first();
            if ($invitation == null)
                throw new CustomBaseException('No such invitation code.');

            $sender = self::find($invitation->user->id);
            if ($sender == null)
                throw new CustomBaseException(
                    'Something went wrong', ErrorCodeCategory::getCategoryBase('General') + 3
                );

            if ($this->email != $invitation->email)
                throw new CustomBaseException('Wrong email address for this invitation.');

            $member = $sender->members->find($this->id);
            if ($member != null)
                throw new CustomBaseException('You are already managing this member\'s account', -1);

            $sender->members()->attach($this->id);
            if (!$creation) {
                Mail::to($sender->email)->send(new MemberInvitationAccepted(
                    $sender,
                    $this
                ));
            }

            AuditEvent::memberAdded($sender, $this);

            $invitation->delete();

            DB::commit();
            return $sender;
        } catch (\Throwable $throwable) {
            DB::rollBack();
            throw $throwable;
        }
    }

    public function removeInvite(string $id) {
        $invitation = $this->memberInvitations->find($id);
        if ($invitation == null)
            throw new CustomBaseException('No such invite', -1);

        return $invitation->delete();
    }

    public function resendInvite(string $id) {
        $invitation = $this->memberInvitations->find($id);
        if ($invitation == null)
            throw new CustomBaseException('No such invite', -1);

        $invited = self::where('email', $invitation->email)->first();
        if ($invited != null) {
            $member = $this->members->find($invited->id);
            if ($member != null)
                throw new CustomBaseException('This member is already managing your account', -1);
        }

        Mail::to($invitation->email)
            ->send(new MemberInvitationMailable(
                $this,
                $invitation->token,
                $invited
            )
        );

        $invitation->updated_at = now();

        return $invitation->save();
    }

    public function removeMember(string $id) {
        $member = $this->members->find($id);
        if ($member == null)
            throw new CustomBaseException('There is no such member managing your account', -1);

        try {
            $this->members()->detach($member->id);
            AuditEvent::memberRemoved($this, $member);
        } catch(\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
        return true;
    }

    public function changeMemberPermissions(array $attr)
    {
        try {
            $old = [];
            $new = [];
            $member = $this->members->find($attr['member']);
            if ($member == null)
                throw new CustomBaseException('There is no such member managing your account', -1);

            foreach (MemberUser::PIVOT_FIELDS_OF_INTEREST as $field => $description) {
                $old[$field] = $member->pivot->can($field);
                $new[$field] = isset($attr[$field]);
                $member->pivot->set($field, $new[$field]);
            }
            $member->pivot->save();
            $diff = AuditEvent::attributeDiff($old, $new);
            AuditEvent::memberPermissionChanged($this, $member, $diff);
        } catch(\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
        return true;
    }

    public function removeManagedAccount(string $id)
    {
        $account = $this->memberOf->find($id);
        if ($account == null)
            throw new CustomBaseException('You are not managing any such account', -1);

        try {
            $this->memberOf()->detach($account->id);
            AuditEvent::memberRemoved($account, $this, true);
        } catch(\Throwable $e) {
            DB::rollBack();
            throw $e;
        }

        return true;
    }

    public function createGym(array $attr)
    {
        DB::beginTransaction();

        try {
            $country = Country::where('code', $attr['country'])->first();
            /** @var \App\Models\Country $country */
            if ($country == null)
                throw new CustomBaseException('No such country code.', '-1');

            $state = State::where('code', $attr['state'])->first();
            /** @var \App\Models\State $state */
            if ($state == null)
                throw new CustomBaseException('No such state code.', '-1');

            if (!$state->inCountry($country))
                throw new CustomBaseException('Please choose (Outside Of USA) as a state if you\'re located outside the United States', '-1');

            $result = Helper::verifyStateCountryCombo($attr['state'], $attr['country']);
            $state = $result['state'];
            $country = $result['country'];

            $attr["usag_membership"] = (isset($attr["usag_membership"]) ? $attr["usag_membership"] : null);
            $attr["usaigc_membership"] = (isset($attr["usaigc_membership"]) ? $attr["usaigc_membership"] : null);
            $attr["aau_membership"] = (isset($attr["aau_membership"]) ? $attr["aau_membership"] : null);
            $attr["nga_membership"] = (isset($attr["nga_membership"]) ? $attr["nga_membership"] : null);

            foreach (['usag_membership', 'usaigc_membership', 'aau_membership', 'nga_membership'] as $key) {
                $membership = $attr[$key];
                if ($membership !== null) {
                    $existingMemberhsip = Gym::where($key, $membership)
                                                ->where('id', '!=', $this->id)
                                                ->first();
                    if ($existingMemberhsip !== null)
                        throw new CustomBaseException("Membership #" . $membership . ' is already taken. Please contact us.', -1);
                }
            }

            $gym = $this->gyms()->create([
                'name' => Helper::title($attr["name"]),
                'short_name' => Helper::title($attr["short_name"]),
                'profile_picture' => config('app.default_gym_picture'),
                'addr_1' => Helper::title($attr["addr_1"]),
                'addr_2' => Helper::title($attr["addr_2"]),
                'city' => Helper::title($attr["city"]),
                'state_id' => $state->id,
                'zipcode' => $attr["zipcode"],
                'country_id' => $country->id,
                'office_phone' => $attr["office_phone"],
                'mobile_phone' => $attr["mobile_phone"],
                'fax' => $attr["fax"],
                'website' => $attr["website"],
                'usag_membership' => $attr["usag_membership"],
                'usaigc_membership' => $attr["usaigc_membership"],
                'aau_membership' => $attr["aau_membership"],
                'nga_membership' => $attr["nga_membership"],
            ]);

            $gym->save();

            AuditEvent::gymCreated($this, auth()->user(), $gym);

            if ($gym->usag_membership !== null) {
                $sanctions = USAGSanction::where('gym_usag_no', $gym->usag_membership)
                                        ->where('status', USAGSanction::SANCTION_STATUS_UNASSIGNED)
                                        ->update([
                                            'gym_id' => $gym->id,
                                            'status' => USAGSanction::SANCTION_STATUS_PENDING,
                                        ]);

                $reservations = USAGReservation::where('gym_usag_no', $gym->usag_membership)
                                        ->where('status', USAGReservation::RESERVATION_STATUS_UNASSIGNED)
                                        ->update([
                                            'gym_id' => $gym->id,
                                            'status' => USAGReservation::RESERVATION_STATUS_PENDING,
                                        ]);

            }

            DB::commit();

            return $gym;
        } catch(\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function retrieveGym(string $id, bool $archived = false) : Gym
    {
        $gym = $this->gyms()->where('id', $id)->first();
        if ($gym == null)
            throw new CustomBaseException(
                'There is no such gym in ' . ($this->isCurrentUser() ? 'your' : $this->fullName() . '\'s') . ' account' ,
                -1
            );

        if (!$archived && $gym->is_archived)
            throw new CustomBaseException('You cannot edit archived gyms', -1);

        return $gym;
    }

    /**
     * @return array
     *
     * @throws CustomBaseException
     */
    public function meetFeaturedWithdrawalFee(): array
    {
        $featuredMeetFee = [];
        if ((Setting::getSetting(Setting::ENABLED_FEATURED_MEET_FEE)->value == true)) {
            $currentUser = User::with(['gyms.meets.registrations.transactions'])->find(\Auth::user()->id);

            if (!isset($currentUser)) {
                throw new CustomBaseException('User Not Found', -1);
            }
            $data = [];
            foreach ($currentUser->gyms as $gym) {
                foreach($gym->meets as $meet) {
                    $total = [];
                    if ($meet->is_featured == true) {
                        foreach($meet->registrations as $meetRegistration) {
                            $data['total'] = $meetRegistration->transactions->where('is_withdrawal', false)
                                ->pluck('breakdown.host.total')->toArray();
                            $total[] = array_sum($data['total']);
                        }
                    }
                    $netValue = (array_sum($total) * Setting::getSetting(Setting::FEATURED_MEET_FEE)->value / 100);
                    $featuredMeetFee[$meet->name] = [
                        'total' => array_sum($total),
                        'percentage_fee' => Setting::getSetting(Setting::FEATURED_MEET_FEE)->value,
                        'net_value' => $netValue,
                    ];
                }
            }
        }
        $featuredMeetFee['total_net_value'] = array_sum(array_column($featuredMeetFee,'net_value'));

        return $featuredMeetFee;
    }
    public function meetFeaturedWithdrawalFeeWithdraw($user): array
    {
        $featuredMeetFee = [];
        if ((Setting::getSetting(Setting::ENABLED_FEATURED_MEET_FEE)->value == true)) {
            $currentUser = User::with(['gyms.meets.registrations.transactions'])->find($user->id);

            if (!isset($currentUser)) {
                throw new CustomBaseException('User Not Found', -1);
            }
            $data = [];
            foreach ($currentUser->gyms as $gym) {
                foreach($gym->meets as $meet) {
                    $total = [];
                    if ($meet->is_featured == true) {
                        foreach($meet->registrations as $meetRegistration) {
                            $data['total'] = $meetRegistration->transactions->where('is_withdrawal', false)
                                ->pluck('breakdown.host.total')->toArray();
                            $total[] = array_sum($data['total']);
                        }
                    }
                    $netValue = (array_sum($total) * Setting::getSetting(Setting::FEATURED_MEET_FEE)->value / 100);
                    $featuredMeetFee[$meet->name] = [
                        'total' => array_sum($total),
                        'percentage_fee' => Setting::getSetting(Setting::FEATURED_MEET_FEE)->value,
                        'net_value' => $netValue,
                    ];
                }
            }
        }
        $featuredMeetFee['total_net_value'] = array_sum(array_column($featuredMeetFee,'net_value'));

        return $featuredMeetFee;
    }
}