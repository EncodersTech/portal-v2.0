<?php

namespace App\Models;

use App\Exceptions\CustomBaseException;
use App\Helper;
use App\Mail\Host\HostReceiveMeetRegistrationMailable;
use App\Mail\Host\RegistrantsConfirmationMailable;
use App\Mail\Registrant\GymRegisteredMailable;
use App\Mail\Registrant\GymRegistrationUpdatedMailable;
use App\Mail\Registrant\TransactionExecutedMailable;
use App\Services\DwollaService;
use App\Services\StripeService;
use Illuminate\Database\Eloquent\Model;
use App\Traits\Excludable;
use DwollaSwagger\models\FundingSource;
use DwollaSwagger\models\Transfer;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class MeetRegistration extends Model
{
    use Excludable;

    public const STATUS_REGISTERED = 1;
    //public const STATUS_WAITLIST_CONFIRMED = 3;
    public const STATUS_CANCELED = 4;

    public const PAYMENT_OPTION_CARD = 'card';
    public const PAYMENT_OPTION_PAYPAL = 'paypal';
    public const PAYMENT_OPTION_ACH = 'ach';
    public const PAYMENT_OPTION_CHECK = 'check';
    public const PAYMENT_OPTION_BALANCE = 'balance';

    public const FEE_MODE_PERCENTAGE = 'percent';
    public const FEE_MODE_FLAT = 'flat';

    public const HANDLING_FEE_MODE = self::FEE_MODE_PERCENTAGE;

    public const PAYMENT_OPTION_FEE_MODE = [
        self::PAYMENT_OPTION_CARD => self::FEE_MODE_PERCENTAGE,
        self::PAYMENT_OPTION_PAYPAL => self::FEE_MODE_PERCENTAGE,
        self::PAYMENT_OPTION_ACH => self::FEE_MODE_FLAT,
        self::PAYMENT_OPTION_CHECK => self::FEE_MODE_FLAT,
        self::PAYMENT_OPTION_BALANCE => self::FEE_MODE_FLAT
    ];

    protected $guarded = ['id'];

    protected $appends = ['teams_count','total_fee','payment_status'];

    public function gym()
    {
        return $this->belongsTo(Gym::class);
    }

    public function meet()
    {
        return $this->belongsTo(Meet::class);
    }

    public function transactions()
    {
        return $this->hasMany(MeetTransaction::class, 'meet_registration_id');
    }

    public function levels()
    {
        return $this->belongsToMany(AthleteLevel::class, 'level_registration', 'meet_registration_id', 'level_id')
            ->using(LevelRegistration::class)
            ->withPivot(LevelRegistration::PIVOT_FIELDS)
            ->withTimestamps();
    }

    public function activeLevels()
    {
        return $this->belongsToMany(AthleteLevel::class, 'level_registration', 'meet_registration_id', 'level_id')
            ->using(LevelRegistration::class)
            ->withPivot(LevelRegistration::PIVOT_FIELDS)
            ->wherePivot('disabled', false)
            ->withTimestamps();
    }

    public function athletes()
    {
        return $this->hasMany(RegistrationAthlete::class, 'meet_registration_id');
    }

    public function specialists()
    {
        return $this->hasMany(RegistrationSpecialist::class, 'meet_registration_id');
    }

    public function coaches()
    {
        return $this->hasMany(RegistrationCoach::class, 'meet_registration_id');
    }

    public function athlete_verifications()
    {
        return $this->hasMany(RegistrationAthleteVerification::class, 'meet_registration_id');
    }

    public function coach_verifications()
    {
        return $this->hasMany(RegistrationCoachVerification::class, 'meet_registration_id');
    }

    public function user_balance_transaction()
    {
        return $this->morphMany(UserBalanceTransaction::class, 'related');
    }

    public function latePaidFor() : bool
    {
        return ($this->late_fee - $this->late_refund) > 0;
    }

    public function editingAbilities()
    {
        return $this->meet->editingAbilities();
    }

    public function hasPendingTransactions(bool $forceFresh = false) : bool
    {
        return ($this->transactions()
                    ->where('status', MeetTransaction::STATUS_PENDING)
                    ->count()
                ) > 0;
    }

    public function getTeamsCountAttribute()
    {
        return LevelRegistration::where('meet_registration_id',$this->id)->where('has_team',true)->count();
    }

    public function getTotalFeeAttribute()
    {
        return $this->transactions->sum('total');
    }

    public function getPaymentStatusAttribute()
    {
        $payment_status = true;
        foreach ($this->gym->user->balance_transactions as $transaction) {
            if($transaction->status === 1){
                $payment_status = false;
                break;
            }
        }
        return $payment_status;
    }

    public function hasRepayableTransactions() : bool
    {
        return ($this->transactions()
                    ->whereIn('status', [MeetTransaction::STATUS_CANCELED, MeetTransaction::STATUS_FAILED])
                    ->where('was_replaced', false)
                    ->count()
                ) > 0;
    }

    public function canBeEdited(bool $forceFresh = false) : bool
    {
        $meet = $this->meet; /** @var Meet $meet */
        $registrationStatus = $meet->registrationStatus();
        $regularPeriod = ($registrationStatus == Meet::REGISTRATION_STATUS_OPEN);
        $latePeriod = ($registrationStatus == Meet::REGISTRATION_STATUS_LATE);
        $canScratch = $meet->canScratch();

        return $regularPeriod || $latePeriod || $canScratch;
    }

    public static function register(Meet $meet, Gym $gym, $inputLevels, $inputCoaches, $summary,
        $method, bool $useBalance, $attachment = null/*, bool $clientWaitlist*/) {

        $chosenMethod = [
            'type' => $method['type'],
            'id' => '',
            'fee' => '',
            'mode' => ''
        ];

        try {
            if ($meet->registrationStatus() == Meet::REGISTRATION_STATUS_OPENING_SOON)
                throw new CustomBaseException("This meet is not open for registrations yet", -1);

            $today = now()->setTime(0, 0);
            if ($today >= $meet->start_date)
                throw new CustomBaseException("This meet is not open for registrations", -1);

            $registration = $meet->registrations()
                                ->where('gym_id', $gym->id)
                                ->where('status', '!=', self::STATUS_CANCELED)
                                ->get();
            if (count($registration) > 0)
                throw new CustomBaseException('This gym has already registered for this meet.', -1);
            $registration = null;

            if (!(isset($inputLevels) && is_array($inputLevels) && (count($inputLevels) > 0)))
                throw new CustomBaseException('You need to select at least one athlete to compete.', -1);

            if (!(isset($inputCoaches) && is_array($inputCoaches) && (count($inputCoaches) > 0)))
                throw new CustomBaseException('Please select at least one coach to attend competition.', -1);

            $is_own = ($meet->gym->user->id == $gym->user->id);
            $tshirtRequired = $meet->tshirt_size_chart_id != null;
            $leoRequired = $meet->leo_size_chart_id != null;
            $slots = $meet->getUsedSlots();
            $late = $meet->isLate();
            $subtotal = 0;

            $meetInWaitlist = $meet->isWaitList();

            $registrationStatus = self::STATUS_REGISTERED;
            $athleteStatus = RegistrationAthlete::STATUS_PENDING_NON_RESERVED;
            $specialistStatus = RegistrationSpecialistEvent::STATUS_SPECIALIST_PENDING;
            $coachStatus = RegistrationCoach::STATUS_PENDING_NON_RESERVED;

            switch($method['type']) {
                case self::PAYMENT_OPTION_CARD:
                    $chosenMethod = [
                        'type' => $method['type'],
                        'id' => $method['id'],
                        'fee' => $meet->cc_fee(),
                        'mode' => self::PAYMENT_OPTION_FEE_MODE[$method['type']]
                    ];
                    $athleteStatus = RegistrationAthlete::STATUS_REGISTERED;
                    $specialistStatus = RegistrationSpecialistEvent::STATUS_SPECIALIST_REGISTERED;
                    $coachStatus = RegistrationCoach::STATUS_REGISTERED;
                    break;

                case self::PAYMENT_OPTION_ACH:
                    $chosenMethod = [
                        'type' => self::PAYMENT_OPTION_ACH,
                        'id' => $method['id'],
                        'fee' => $meet->ach_fee(),
                        'mode' => self::PAYMENT_OPTION_FEE_MODE[$method['type']]
                    ];
                    $athleteStatus = RegistrationAthlete::STATUS_PENDING_RESERVED;
                    $specialistStatus = RegistrationSpecialistEvent::STATUS_SPECIALIST_PENDING;
                    $coachStatus = RegistrationCoach::STATUS_PENDING_RESERVED;
                    break;

//                case self::PAYMENT_OPTION_PAYPAL:
//                    $chosenMethod = [
//                        'type' => self::PAYMENT_OPTION_PAYPAL,
//                        'fee' => $meet->paypal_fee(),
//                        'mode' => self::PAYMENT_OPTION_FEE_MODE[$method['type']]
//                    ];
//                    $athleteStatus = RegistrationAthlete::STATUS_REGISTERED;
//                    $specialistStatus = RegistrationSpecialistEvent::STATUS_SPECIALIST_REGISTERED;
//                    $coachStatus = RegistrationCoach::STATUS_REGISTERED;
//                    break;

                case self::PAYMENT_OPTION_CHECK:
                    $chosenMethod = [
                        'id' => $method['id'],                  // Check number
                        'type' => self::PAYMENT_OPTION_CHECK,
                        'fee' => $meet->check_fee(),
                        'mode' => self::PAYMENT_OPTION_FEE_MODE[$method['type']]
                    ];

                    if ($useBalance)
                        throw new CustomBaseException('Allgymnastics.com balance cannot be used with mailed checks.', -1);

                    $athleteStatus = RegistrationAthlete::STATUS_PENDING_NON_RESERVED;
                    $specialistStatus = RegistrationSpecialistEvent::STATUS_SPECIALIST_PENDING;
                    $coachStatus = RegistrationCoach::STATUS_PENDING_NON_RESERVED;
                    break;

                default:
                    throw new CustomBaseException('Invalid payment method.', -1);
            }

            $athleteSanctions = [
                SanctioningBody::USAG => [],
                SanctioningBody::USAIGC => [],
                SanctioningBody::AAU => [],
                SanctioningBody::NGA => [],
            ];

            $specialistSanctions = [
                SanctioningBody::USAG => [],
                SanctioningBody::USAIGC => [],
                SanctioningBody::AAU => [],
                SanctioningBody::NGA => [],
            ];

            $specialistEvents = [];
            foreach (AthleteSpecialistEvents::all() as $evt)
                $specialistEvents[$evt->id] = $evt;

            DB::beginTransaction();
            $transaction = null;
            $waitlistTransaction = null;
            $needRegularTransaction = false;
            $needWaitlistTransaction = false;
            $shouldGoIntoWaitlist = [];
            $waitlistAccountedFor = 0;
            try {
                $host = User::lockForUpdate()->find($meet->gym->user->id); /** @var User $host */
                if ($host == null)
                    throw new CustomBaseException('No such host');

                $registrant = User::lockForUpdate()->find($gym->user->id); /** @var User $registrant */
                if ($registrant == null)
                    throw new CustomBaseException('No such registrant');

                $registration = $meet->registrations()->create([
                    'gym_id' => $gym->id,
                    'was_late' => false,
                    'late_fee' => 0,
                    'late_refund' => 0,
                    'handling_fee_override' => Helper::getHandlingFee($meet),
                    'cc_fee_override' => $meet->cc_fee(),
                    'paypal_fee_override' => $meet->paypal_fee(),
                    'ach_fee_override' => $meet->ach_fee(),
                    'check_fee_override' => $meet->check_fee(),
                    'status' => $registrationStatus,
                ]); /** @var \App\Models\MeetRegistration $registration */
                $registration->save();

                $registrationAthleteCount = 0;
                $registrationWaitlistCount = 0;

                $snapshot = [
                    'registration' => [
                        'old' => [
                            'was_late' => $registration->was_late,
                            'late_fee' => $registration->late_fee,
                            'late_refund' => $registration->late_refund,
                        ],
                        'new' => [],
                    ],
                    'levels' => [],
                ];

                $registration->was_late = $late;
                $registration->save();

                $gym_membership_checked = [];
                foreach ($inputLevels as $l) {
                    if (!isset($l['id'], $l['team'], $l['athletes']))
                        throw new CustomBaseException('Invalid level format.', -1);

                    if (!(is_array($l['athletes']) && (count($l['athletes']) > 0)))
                        throw new CustomBaseException('You need to select at least one athlete per each submitted level.', -1);

                    $level = $meet->activeLevels()
                                    ->wherePivot('allow_men', $l['male'])
                                    ->wherePivot('allow_women', $l['female'])
                                    ->find($l['id']); /** @var \App\Models\AthleteLevel $level */
                    if ($level == null)
                        throw new CustomBaseException('No such level', -1);

                    if (!in_array($level->sanctioning_body_id, $gym_membership_checked)) {
                        $gym_membership = null;
                        $gym_membership_body = '';
                        switch($level->sanctioning_body_id) {
                            case SanctioningBody::USAG:
                                $gym_membership = ($gym->usag_membership ? $gym->usag_membership : null);
                                $gym_membership_body = 'USAG';
                                break;

                            case SanctioningBody::USAIGC:
                                $gym_membership = ($gym->usaigc_membership ? $gym->usaigc_membership : null);
                                $gym_membership_body = 'USAIGC';
                                break;

                            case SanctioningBody::AAU:
                                $gym_membership = ($gym->aau_membership ? $gym->aau_membership : null);
                                $gym_membership_body = 'AAU';
                                break;

                            case SanctioningBody::NGA:
                                $gym_membership = ($gym->nga_membership ? $gym->nga_membership : null);
                                $gym_membership_body = 'NGA';
                                break;
                        }
                        if ($gym_membership === null)
                            throw new CustomBaseException('This gym does not have a valid ' . $gym_membership_body . ' membership number.', -1);
                        $gym_membership_checked[] = $level->sanctioning_body_id;
                    }

                    $category = $meet->categories()
                                    ->where('sanctioning_body_id', $level->sanctioning_body_id)
                                    ->where('level_category_id', $level->level_category_id)
                                    ->first(); /** @var LevelCategory $category */

                    $categoryMeet = $category->pivot; /** @var CategoryMeet $categoryMeet */

                    if ($categoryMeet->requiresSanction())
                        throw new CustomBaseException('Athletes cannot be registered in ' . $category->name . ' manually.', -1);

                    $team = $level->pivot->allow_teams && $l['team'];

                    $registrationLevel = $registration->levels()->attach($level->id, [
                        'allow_men' => $level->pivot->allow_men,
                        'allow_women' => $level->pivot->allow_women,
                        'registration_fee' => $level->pivot->registration_fee,
                        'late_registration_fee' => $level->pivot->late_registration_fee,
                        'allow_specialist' => $level->pivot->allow_specialist,
                        'specialist_registration_fee' => $level->pivot->specialist_registration_fee,
                        'specialist_late_registration_fee' => $level->pivot->specialist_late_registration_fee,
                        'allow_teams' => $level->pivot->allow_teams,
                        'team_registration_fee' => $level->pivot->team_registration_fee,
                        'team_late_registration_fee' => $level->pivot->team_late_registration_fee,
                        'enable_athlete_limit' => $level->pivot->enable_athlete_limit,
                        'athlete_limit' => $level->pivot->athlete_limit,
                        'has_team' => false,
                        'was_late' => false,
                        'team_fee' => 0,
                        'team_late_fee' => 0,
                        'team_refund' => 0,
                        'team_late_refund' => 0
                    ]);

                    $registrationLevel = LevelRegistration::where('meet_registration_id', $registration->id)
                                                            ->where('level_id', $level->id)
                                                            ->where('allow_men', $level->pivot->allow_men)
                                                            ->where('allow_women', $level->pivot->allow_women)
                                                            ->first();
                    /** @var LevelRegistration $registrationLevel */

                    $snapshot['levels'][$registrationLevel->id] = [
                        'old' => [
                            'has_team' => $registrationLevel->has_team,
                            'was_late' => $registrationLevel->was_late,
                            'team_fee' => $registrationLevel->team_fee,
                            'team_late_fee' => $registrationLevel->team_late_fee,
                            'team_refund' => $registrationLevel->team_refund,
                            'team_late_refund' => $registrationLevel->team_late_refund,
                        ],
                        'new' => [],
                        'athletes' => [],
                        'specialists' => [],
                    ];

                    $registrationLevel->has_team = $team;
                    $registrationLevel->was_late = $late;
                    $registrationLevel->save();

                    $levelHasSpecialistsOnly = true;
                    $levelAthleteCount = 0;
                    $levelWaitlistCount = 0;
                    foreach ($l['athletes'] as $a) {
                        if (!isset($a['id']))
                            throw new CustomBaseException('Invalid athlete format.', -1);

                        $athlete = $gym->athletes()->find($a['id']); /** @var \App\Models\Athlete $athlete */
                        if ($athlete == null)
                            throw new CustomBaseException('No such athlete', -1);

                        $athlete_sanction = null;
                        switch($level->sanctioning_body_id) {
                            case SanctioningBody::USAG:
                                $athlete_sanction = ($athlete->usag_active ? $athlete->usag_no : null);
                                break;

                            case SanctioningBody::USAIGC:
                                $athlete_sanction = ($athlete->usaigc_active ? $athlete->usaigc_no : null);
                                break;

                            case SanctioningBody::AAU:
                                $athlete_sanction = ($athlete->aau_active ? $athlete->aau_no : null);
                                break;

                            case SanctioningBody::NGA:
                                $athlete_sanction = ($athlete->nga_active ? $athlete->nga_no : null);
                                break;
                        }
                        if ($athlete_sanction === null)
                            throw new CustomBaseException('Competing athlete need to have an active membership and a valid number in the organization they are competing within.', -1);

                        $newAthlete = [
                            'level_registration_id' => $registrationLevel->id,
                            'first_name' => $athlete->first_name,
                            'last_name' => $athlete->last_name,
                            'gender' => $athlete->gender,
                            'dob' => $athlete->dob,
                            'is_us_citizen' => $athlete->is_us_citizen,
                            'tshirt_size_id' => null,
                            'leo_size_id' => null,
                            'usag_no' => $athlete->usag_no,
                            'usag_active' => $athlete->usag_active,
                            'usaigc_no' => $athlete->usaigc_no,
                            'usaigc_active' => $athlete->usaigc_active,
                            'aau_no' => $athlete->aau_no,
                            'aau_active' => $athlete->aau_active,
                            'nga_no' => $athlete->nga_no,
                            'nga_active' => $athlete->nga_active,
                            'was_late' => $late,
                            'fee' => 0,
                            'late_fee' => 0,
                            'refund' => 0,
                            'late_refund' => 0,
                            'status' => $athleteStatus,
                        ];

                        $athlete->male = ($athlete->gender == 'male');
                        $athlete->female = !$athlete->male;

                        if (!(
                            ($athlete->male && $level->pivot->allow_men) ||
                            ($athlete->female && $level->pivot->allow_women)
                        ))
                            throw new CustomBaseException('Athlete \'' . $athlete->fullName() . '\' gender mismatch with event.', -1);

                        if ($tshirtRequired) {
                            if (!isset($a['tshirt']) || ($a['tshirt'] == -1))
                                throw new CustomBaseException('T-Shirt sizes are required for this meet.', -1);

                            $tshirtSize = $meet->tshirt_chart->sizes()->where('id', $a['tshirt'])->first();
                            if ($tshirtSize == null)
                                throw new CustomBaseException('Invalid T-Shirt size for this meet.', -1);

                            $newAthlete['tshirt_size_id'] = $tshirtSize->id;
                        }

                        if ($leoRequired && $athlete->female) {
                            if (!isset($a['leo']) || ($a['leo'] == -1))
                                throw new CustomBaseException('Leo sizes are required for this meet.', -1);

                            $leoSize = $meet->leo_chart->sizes()->where('id', $a['leo'])->first();
                            if ($leoSize == null)
                                throw new CustomBaseException('Invalid Leo size for this meet.', -1);

                            $newAthlete['leo_size_id'] = $leoSize->id;
                        }

                        $snapshotData = null;
                        if (($level->pivot->allow_specialist) && isset($a['specialist']) &&
                            is_array($a['specialist']) && (count($a['specialist']) > 0)) {

                            if (array_key_exists(
                                    $athlete_sanction,
                                    $specialistSanctions[$level->sanctioning_body_id]
                                )
                            )
                                throw new CustomBaseException('Athlete ' . $athlete->fullName() . ' can only compete in specialist events in one level per organization.', -1);

                            $specialistSanctions[$level->sanctioning_body_id][$athlete_sanction] = $registrationLevel->id;

                            if (array_key_exists(
                                    $athlete_sanction,
                                    $athleteSanctions[$level->sanctioning_body_id]
                                )
                            ) {
                                $specialistLevelToCheck = $athleteSanctions[$level->sanctioning_body_id][$athlete_sanction];
                                if ($specialistLevelToCheck == $registrationLevel->id)
                                    throw new CustomBaseException('Athlete ' . $athlete->fullName() . ' cannot compete in all around and specialist events in the same level.', -1);
                            }

                            if ($meetInWaitlist && !$a['waitlist'])
                                throw new CustomBaseException('Specialists need to enter the waitlist.', -1);

                            unset(
                                $newAthlete['was_late'],
                                $newAthlete['fee'],
                                $newAthlete['late_fee'],
                                $newAthlete['refund'],
                                $newAthlete['late_refund'],
                                $newAthlete['status']
                            );

                            $specialist = $registration->specialists()->create($newAthlete); /** @var RegistrationSpecialist $specialist */

                            if ($a['waitlist']) {
                                $needWaitlistTransaction = true;
                            } else {
                                $needRegularTransaction = !$meetInWaitlist;
                                $snapshotData = [];
                            }

                            $needRegularTransaction = !$meetInWaitlist;
                            $needWaitlistTransaction = $needWaitlistTransaction || $a['waitlist'];
                            $specialist->save();

                            $existingEvents = [];
                            foreach ($a['specialist'] as $evtId) {

                                if (!$a['waitlist']) {
                                    $snapshotDataEvent = [
                                        'old' => [
                                            'was_late' => false,
                                            'fee' => 0,
                                            'late_fee' => 0,
                                            'refund' => 0,
                                            'late_refund' => 0,
                                        ],
                                        'new' => [],
                                    ];
                                }

                                if (!array_key_exists($evtId, $specialistEvents))
                                    throw new CustomBaseException('Invalid specialist event.', -1);

                                $evt = $specialistEvents[$evtId]; /** @var \App\Models\AthleteSpecialistEvents $evt */
                                if ($evt->sanctioning_body->id != $level->sanctioning_body->id)
                                    throw new CustomBaseException('Specialist event sanctioning body mismatch with level sanctioning body.', -1);

                                if (in_array($evtId, $existingEvents))
                                    throw new CustomBaseException('Athlete ' . $athlete->fullName() . ' can only compete in ' . $evt->name . ' once.', -1);

                                if (!(
                                    ($athlete->male && $evt->male) ||
                                    ($athlete->female && $evt->female)
                                ))
                                    throw new CustomBaseException('Specialist event gender mismatch with athlete gender.', -1);

                                $existingEvents[] = $evt->id;
                                $event = $specialist->events()->create([
                                    'event_id' => $evt->id,
                                    'transaction_id' => null,
                                    'was_late' => $late,
                                    'fee' => $level->pivot->specialist_registration_fee,
                                    'late_fee' => ($late ? $level->pivot->specialist_late_registration_fee : 0),
                                    'refund' => 0,
                                    'late_refund' => 0,
                                    'status' => $specialistStatus,
                                    'in_waitlist' => $a['waitlist']
                                ]); /** @var \App\Models\RegistrationSpecialistEvent $evt */

                                $specialist->save();

                                if (!$a['waitlist']) {
                                    $snapshotDataEvent['new'] = [
                                        'was_late' => $event->was_late,
                                        'fee' => $event->fee,
                                        'late_fee' => $event->late_fee,
                                        'refund' => $event->refund,
                                        'late_refund' => $event->late_refund,
                                    ];

                                    $snapshotData[] = $snapshotDataEvent;
                                }
                            }
                        } else {
                            if (!$a['waitlist']) {
                                $snapshotData = [
                                    'old' => [
                                        'was_late' => false,
                                        'fee' => 0,
                                        'late_fee' => 0,
                                        'refund' => 0,
                                        'late_refund' => 0,
                                    ],
                                    'new' => [],
                                ];
                            }

                            if (array_key_exists(
                                    $athlete_sanction,
                                    $athleteSanctions[$level->sanctioning_body_id]
                                )
                            )
                                throw new CustomBaseException('Athlete ' . $athlete->fullName() . ' can only compete in one level per organization.', -1);

                            $athleteSanctions[$level->sanctioning_body_id][$athlete_sanction] = $registrationLevel->id;

                            if (array_key_exists(
                                    $athlete_sanction,
                                    $specialistSanctions[$level->sanctioning_body_id]
                                )
                            ) {
                                $aaLevelToCheck = $specialistSanctions[$level->sanctioning_body_id][$athlete_sanction];
                                if ($aaLevelToCheck == $registrationLevel->id)
                                    throw new CustomBaseException('Athlete ' . $athlete->fullName() . ' cannot compete in all around and specialist events in the same level.', -1);
                            }
                            $athlete = $registration->athletes()->create($newAthlete);

                            $athlete->fee = $level->pivot->registration_fee;
                            if ($late)
                                $athlete->late_fee = $level->pivot->late_registration_fee;

                            if ($meetInWaitlist && !$a['waitlist'])
                                throw new CustomBaseException('Athletes need to enter the waitlist.', -1);

                            if ($a['waitlist']) {
                                $athlete->in_waitlist = true;
                                $shouldGoIntoWaitlist[] = $athlete->id;
                                $needWaitlistTransaction = true;
                                $levelWaitlistCount++;
                            } else {
                                $levelHasSpecialistsOnly = false;
                                $needRegularTransaction = !$meetInWaitlist;
                                $levelAthleteCount++;

                                $snapshotData['new'] = [
                                    'was_late' => $athlete->was_late,
                                    'fee' => $athlete->fee,
                                    'late_fee' => $athlete->late_fee,
                                    'refund' => $athlete->refund,
                                    'late_refund' => $athlete->late_refund,
                                ];
                            }
                            $athlete->save();
                        }

                        if ($snapshotData !== null) {
                            $type = ($athlete instanceof RegistrationAthlete ? 'athletes' : 'specialists');
                            $snapshot['levels'][$registrationLevel->id][$type][] = $snapshotData;
                        }
                    }

                    if (!$levelHasSpecialistsOnly && $level->pivot->enable_athlete_limit) {
                        $levelGender = (
                            ($level->pivot->allow_men && $level->pivot->allow_women) ?
                            'both' :
                            ($level->pivot->allow_men ? 'male' : 'female')
                        );

                        $levelAvailableSlots = $level->pivot->athlete_limit - $slots[$level->id][$levelGender]['count'];

                        if ($levelAthleteCount > $levelAvailableSlots) {
                            throw new CustomBaseException(
                                "You cannot have more than " . $levelAvailableSlots . " athletes in " .
                                $level->name . ". Extra athletes should go into the waitlist.", -1);
                        } elseif ($levelAthleteCount == $levelAvailableSlots) {
                            $waitlistAccountedFor += $levelWaitlistCount;
                        } else {
                            $registrationWaitlistCount += $levelWaitlistCount;

                        }
                    }

                    $registrationLevel->save();

                    $registrationAthleteCount += $levelAthleteCount;
                }

                if ($meet->athlete_limit !== null) {
                    $registrationAvailableSlots = $meet->athlete_limit - $slots['total'];

                    if ($registrationAthleteCount > $registrationAvailableSlots) {
                        throw new CustomBaseException(
                            "You cannot have more than " . $registrationAvailableSlots . " athletes in " .
                            "this meet. Extra athletes should go into the waitlist.", -1);
                    } else {
                        if ($registrationAthleteCount == $registrationAvailableSlots)
                            $waitlistAccountedFor += $registrationWaitlistCount;

                        $remainingslots = $registrationAvailableSlots - $registrationAthleteCount;
                        $remainingWaitlist = count($shouldGoIntoWaitlist) - $waitlistAccountedFor;
                        if (($remainingWaitlist > 0) && ($remainingWaitlist <= $remainingslots)) {
                            throw new CustomBaseException(
                                'You cannot have athletes in the waitlist if there are still ' .
                                'slots available.', -1);
                        }
                    }
                }

                $neededSanctions = [];
                foreach ($meet->categories as $category) { /** @var LevelCategory $category */
                    switch ($category->pivot->sanctioning_body_id) {
                        case SanctioningBody::USAG:
                            //$neededSanctions['usag'] = true;
                            break;

                        case SanctioningBody::USAIGC:
                            //$neededSanctions['usaigc'] = true;
                            break;

                        case SanctioningBody::AAU:
                            //$neededSanctions['aau'] = true;
                        break;

                        case SanctioningBody::NGA:
                            //$neededSanctions['nga'] = true;
                            break;
                    }
                }

                $neededSanctionsCount = count($neededSanctions);

                foreach ($inputCoaches as $c) {
                    $coach = $gym->coaches()->find($c['id']); /** @var \App\Models\Coach $coach */
                    if ($coach == null)
                        throw new CustomBaseException('No such coach', -1);

                    if ($neededSanctionsCount > 0) {
                        $flag = false;
                        foreach ($neededSanctions as $body => $value) {
                            $flag = $flag || ($coach->{$body . '_no'} !== null);
                        }
                        if (!$flag) {
                            throw new CustomBaseException(
                                'No sanction number was provided for coach ' . $coach->fullName() .
                                '. Please update your coach details in your roster.',
                                -1
                            );
                        }
                    }

                    $tshirtSize = null;
                    if ($tshirtRequired) {
                        if (isset($c['tshirt']) && ($c['tshirt'] != -1)) {
                            $tshirtSize = $meet->tshirt_chart->sizes()->find($c['tshirt'])->first();
                            if ($tshirtSize == null)
                                throw new CustomBaseException('Invalid T-Shirt size for this meet.', -1);

                            $tshirtSize = $tshirtSize->id;
                        }
                    }

                    $coach = $registration->coaches()->create([
                        'first_name' => $coach->first_name,
                        'last_name' => $coach->last_name,
                        'gender' => $coach->gender,
                        'dob' => $coach->dob,
                        'tshirt_size_id' => $tshirtSize,
                        'usag_no' => $coach->usag_no,
                        'usag_active' => $coach->usag_active,
                        'usag_expiry' => $coach->usag_expiry,
                        'usag_safety_expiry' => $coach->usag_safety_expiry,
                        'usag_safesport_expiry' => $coach->usag_safesport_expiry,
                        'usag_background_expiry' => $coach->usag_background_expiry,
                        'usag_u100_certification' => $coach->usag_u100_certification,
                        'usaigc_no' => $coach->usaigc_no,
                        'usaigc_active' => $coach->usaigc_active,
                        'usaigc_background_check' => $coach->usaigc_background_check,
                        'aau_no' => $coach->aau_no,
                        'nga_no' => $coach->nga_no,
                        'was_late' => $late,
                        'status' => $coachStatus
                    ]);
                }


                $registration->fresh();
                $hasAthletes = false;
                foreach ($registration->levels as $al) { /** @var AthleteLevel $al */
                    $rl = $al->pivot; /** @var LevelRegistration $rl */

                    $athleteCount = $rl->athletes()
                        ->where('in_waitlist', false)
                        ->where('status', '!=', RegistrationAthlete::STATUS_SCRATCHED)
                        ->count();

                    $specialistCount = $rl->specialists()
                        ->whereHas('events', function (Builder $q0) {
                            $q0->where('in_waitlist', false)
                                ->where('status', '!=', RegistrationSpecialistEvent::STATUS_SPECIALIST_SCRATCHED);
                        })->count();

                    $levelHasAthletes = ($athleteCount + $specialistCount) > 0;
                    $hasAthletes = $hasAthletes || $levelHasAthletes;
                    if ($levelHasAthletes) {
                        if ($rl->has_team) {
                            if (($rl->team_fee - $rl->team_refund) != $rl->team_registration_fee)
                                $rl->team_fee += $rl->team_registration_fee - ($rl->team_fee - $rl->team_refund);

                            if ($rl->was_late) {
                                if (($rl->team_late_fee - $rl->team_late_refund) != $rl->team_late_registration_fee)
                                    $rl->team_late_fee += $rl->team_late_registration_fee - ($rl->team_late_fee - $rl->team_late_refund);
                            } else {
                                // clear late fees
                                if (($rl->team_late_fee - $rl->team_late_refund) != 0)
                                    $rl->team_late_refund = $rl->team_late_fee;
                            }
                        } else {
                            // clear fees
                            if (($rl->team_fee - $rl->team_refund) != 0)
                                $rl->team_refund = $rl->team_fee;

                            $rl->was_late = false;
                            if (($rl->team_late_fee - $rl->team_late_refund) != 0)
                                $rl->team_late_refund = $rl->team_late_fee;
                        }
                    } else {
                        // clear fees
                        $rl->has_team = false;
                        if (($rl->team_fee - $rl->team_refund) != 0)
                            $rl->team_refund = $rl->team_fee;

                        $rl->was_late = false;
                        if (($rl->team_late_fee - $rl->team_late_refund) != 0)
                            $rl->team_late_refund = $rl->team_late_fee;
                    }

                    $rl->save();

                    if (!isset($snapshot['levels'][$rl->id])) {
                        $snapshot['levels'][$rl->id] = [
                            'old' => [
                                'has_team' => $rl->has_team,
                                'was_late' => $rl->was_late,
                                'team_fee' => $rl->team_fee,
                                'team_late_fee' => $rl->team_late_fee,
                                'team_refund' => $rl->team_refund,
                                'team_late_refund' => $rl->team_late_refund,
                            ],
                            'new' => [],
                            'athletes' => [],
                            'specialists' => [],
                        ];
                    }

                    $snapshot['levels'][$rl->id]['new'] = [
                        'has_team' => $rl->has_team,
                        'was_late' => $rl->was_late,
                        'team_fee' => $rl->team_fee,
                        'team_late_fee' => $rl->team_late_fee,
                        'team_refund' => $rl->team_refund,
                        'team_late_refund' => $rl->team_late_refund,
                    ];
                }

                if ($hasAthletes) {
                    if ($registration->was_late) {
                        if (($registration->late_fee - $registration->late_refund) != $meet->late_registration_fee)
                            $registration->late_fee += $meet->late_registration_fee - ($registration->late_fee - $registration->late_refund);
                    } else {
                        // clear fees
                        if (($registration->late_fee - $registration->late_refund) != 0)
                            $registration->late_refund = $registration->late_fee;
                    }
                } else {
                    // clear the fees
                    $registration->was_late = false;
                    if (($registration->late_fee - $registration->late_refund) != 0)
                        $registration->late_refund = $registration->late_fee;
                }
                $registration->save();

                $snapshot['registration']['new'] = [
                    'was_late' => $registration->was_late,
                    'late_fee' => $registration->late_fee,
                    'late_refund' => $registration->late_refund,
                ];

                $incurredFees = $registration->calculateRegistrationTotal($snapshot);

                $subtotal = $incurredFees['subtotal'];
                if ($subtotal != $summary['subtotal'])
                    throw new CustomBaseException('Subtotal calculation mismatch.', -1);

                $calculatedFees = self::calculateFees($subtotal, $meet, $is_own, $chosenMethod,
                    $useBalance, $registrant->cleared_balance);

                $calculatedFees += $incurredFees;

                $gymSummary = $calculatedFees['gym'];

                if ($gymSummary['handling'] != $summary['handling'])
                    throw new CustomBaseException('Handling fee calculation mismatch.', -1);

                if ($gymSummary['used_balance'] != $summary['used_balance'])
                    throw new CustomBaseException('Used balance calculation mismatch.', -1);

                if ($gymSummary['processor'] != $summary['processor'])
                    throw new CustomBaseException('Processor fee calculation mismatch.', -1);

                if ($gymSummary['total'] != $summary['total'])
                    throw new CustomBaseException('Total sum calculation mismatch.', -1);

                $paymentMethodString = 'Unknown';

                $result = [
                    'waitlist' => $waitlistTransaction !== null,
                    'message' => 'You have successfully entered this meet\'s wait-list.'
                ];

                if ($needWaitlistTransaction) {
                    $waitlistTransaction = $registration->transactions()->create([
                        'processor_id' => 'AG-WAITLIST-' . Helper::uniqueId(),
                        'handling_rate' => 0,
                        'processor_rate' => 0,
                        'total' => 0,
                        'breakdown' => [],
                        'method' => MeetTransaction::PAYMENT_METHOD_BALANCE,
                        'status' => MeetTransaction::STATUS_WAITLIST_PENDING
                    ]); /** @var Meettransaction $transaction */
                }

                if ($needRegularTransaction) {
                    if ($useBalance && ($gymSummary['used_balance'] > 0) && ($gymSummary['total'] == 0)) {
                        $chosenMethod = [
                            'type' => self::PAYMENT_OPTION_BALANCE,
                            'id' => null,
                            'fee' => $meet->balance_fee(),
                            'mode' => self::PAYMENT_OPTION_FEE_MODE[self::PAYMENT_OPTION_BALANCE]
                        ];

                        $athleteStatus = RegistrationAthlete::STATUS_REGISTERED;
                        $specialistStatus = RegistrationSpecialistEvent::STATUS_SPECIALIST_REGISTERED;
                        $coachStatus = RegistrationCoach::STATUS_REGISTERED;
                    }

                    $executedTransactionResult = self::executePayment(
                        $calculatedFees,
                        $chosenMethod,
                        $registration,
                        $host,
                        $registrant
                    );

                    $transaction = $executedTransactionResult['transaction']; /** @var MeetTransaction $transaction */
                    $athleteStatus = $executedTransactionResult['athlete_status'];
                    $specialistStatus = $executedTransactionResult['specialist_status'];
                    $coachStatus = $executedTransactionResult['coach_status'];
                    $calculatedFees = $executedTransactionResult['calculated_fees'];
                    $paymentMethodString = $executedTransactionResult['payment_method_string'];
                    $result['message'] = $executedTransactionResult['message'];
                }

                $auditEvent = [
                    'registration' => [],
                    'athletes' => [],
                    'specialists' => [],
                    'coaches' => [],
                ];



                foreach ($registration->athletes as $ra) { /** @var RegistrationAthlete $ra */
                    if (in_array($ra->id, $shouldGoIntoWaitlist)) {
                        $ra->in_waitlist = true;
                        $ra->transaction()->associate($waitlistTransaction);
                        $ra->status = RegistrationAthlete::STATUS_PENDING_NON_RESERVED;
                    } else {
                        $ra->transaction()->associate($transaction);
                        $ra->status = $athleteStatus;
                    }
                    $ra->save();
                    $a = $ra->toArray();
                    unset($a['transaction']);
                    $auditEvent['athletes'][] = $a;
                }

                foreach ($registration->specialists as $rs) { /** @var RegistrationSpecialist $rs */
                    $s = $rs->toArray();
                    $s['events'] = [];
                    foreach ($rs->events as $se) { /** @var RegistrationSpecialistEvent $se */
                        if ($transaction !== null) {
                            $se->transaction()->associate($transaction);
                            $se->status = $specialistStatus;
                        } else {
                            $se->transaction()->associate($waitlistTransaction);
                            $se->in_waitlist = true;
                            $se->status = RegistrationSpecialistEvent::STATUS_SPECIALIST_PENDING;
                        }
                        $se->save();
                        $e = $se->toArray();
                        unset($e['transaction']);
                        $s['events'][] = $e;
                    }
                    $auditEvent['specialists'][] = $s;
                }

                foreach ($registration->coaches as $rc) { /** @var RegistrationCoach $rc */
                    if ($transaction !== null) {
                        $rc->transaction()->associate($transaction);
                        $rc->status = $coachStatus;
                    } else {
                        $rc->transaction()->associate($waitlistTransaction);
                        $rc->in_waitlist = true;
                        $rc->status = RegistrationCoach::STATUS_PENDING_NON_RESERVED;
                    }
                    $rc->save();
                    $c = $rc->toArray();
                    unset($c['transaction']);
                    $auditEvent['coaches'][] = $c;
                }

                $registration->status = $registrationStatus;
                $registration->save();

                $registrationArray = $registration->toArray();
                unset($registrationArray['athletes']);
                unset($registrationArray['specialists']);
                unset($registrationArray['coaches']);
                $auditEvent['registration'] = $registrationArray;

                AuditEvent::registrationCreated(
                    request()->_managed_account, auth()->user(), $registration, $auditEvent
                );
                //if meet is featured then create featured meet record
//                if (($meet->is_featured == true) && (Setting::getSetting(Setting::ENABLED_FEATURED_MEET_FEE)->value == true)) {
//                    $featuredMeetFees = FeaturedMeetsFees::create([
//                        'meet_registration_id' => $registration->id,
//                        'fees' => 0,
//                        'fess_in_percentage' => Setting::getSetting(Setting::FEATURED_MEET_FEE)->value,
//                    ]);
//                }

                $result['registration'] = $registration->id;
                $meetEntryReport = $meet->registrantMeetEntryAndStoreReport($meet->id, $gym);

                // TODO : Mail to host

                DB::commit();

                Log::debug('GymRegisteredMailable');
                Mail::to($gym->user->email)->send(new GymRegisteredMailable(
                    $meet,
                    $gym,
                    $registration,
                    $gymSummary,
                    $paymentMethodString,
                    $transaction !== null,
                    $waitlistTransaction !== null,
                    null,
                    $meetEntryReport
                ));

                //when registration complete then host receive mail.
                Log::debug('when registration complete then host receive mail.');
                Mail::to($meet->gym->user->email)->send(new HostReceiveMeetRegistrationMailable($meet,$gym));

                //Send an email to the registrant with a confirmation.
                Log::debug('Send an email to the registrant with a confirmation.');
                $totalAth = $registration->athletes->count() + $registration->specialists->count();
                $totalFees = $summary['total'];
                Log::debug('RegistrantsConfirmationMailable');
                Mail::to($gym->user->email)->send(new RegistrantsConfirmationMailable($meet, $gym, $totalAth, $totalFees));

                Log::debug('Emails Done');
            } catch(\Throwable $e) {
                DB::rollBack();
                Log::debug($e->getMessage());
                self::panicCancelTransaction($e, $transaction, $chosenMethod['type']);
                throw $e;
            }

            return $result;
        } catch(\Throwable $e) {
            throw $e;
        }
    }

    public function pay(Gym $gym, MeetTransaction $oldTx, array $summary,
        array $method, bool $useBalance) {

        DB::beginTransaction();
        $transaction = null;

        $chosenMethod = [
            'type' => $method['type'],
            'id' => '',
            'fee' => '',
            'mode' => ''
        ];

        try {
            $meet = $this->meet; /** @var Meet $meet */
            $paymentMethodString = 'Unknown';
            $is_own = ($meet->gym->user->id == $gym->user->id);

            $athleteStatus = RegistrationAthlete::STATUS_PENDING_NON_RESERVED;
            $specialistStatus = RegistrationSpecialistEvent::STATUS_SPECIALIST_PENDING;
            $coachStatus = RegistrationCoach::STATUS_PENDING_NON_RESERVED;

            switch($method['type']) {
                case self::PAYMENT_OPTION_CARD:
                    $chosenMethod = [
                        'type' => $method['type'],
                        'id' => $method['id'],
                        'fee' => $meet->cc_fee(),
                        'mode' => self::PAYMENT_OPTION_FEE_MODE[$method['type']]
                    ];
                    break;

                case self::PAYMENT_OPTION_ACH:
                    $chosenMethod = [
                        'type' => self::PAYMENT_OPTION_ACH,
                        'id' => $method['id'],
                        'fee' => $meet->ach_fee(),
                        'mode' => self::PAYMENT_OPTION_FEE_MODE[$method['type']]
                    ];
                    break;

//                case self::PAYMENT_OPTION_PAYPAL:
//                    $chosenMethod = [
//                        'type' => self::PAYMENT_OPTION_PAYPAL,
//                        'fee' => $meet->paypal_fee(),
//                        'mode' => self::PAYMENT_OPTION_FEE_MODE[$method['type']]
//                    ];
//                    break;

                case self::PAYMENT_OPTION_CHECK:
                    $chosenMethod = [
                        'id' => $method['id'],                  // Check number
                        'type' => self::PAYMENT_OPTION_CHECK,
                        'fee' => $meet->check_fee(),
                        'mode' => self::PAYMENT_OPTION_FEE_MODE[$method['type']]
                    ];
                    if ($useBalance)
                        throw new CustomBaseException('Allgymnastics.com balance cannot be used with mailed checks.', -1);
                    break;

                default:
                    throw new CustomBaseException('Invalid payment method.', -1);
            }

            $waitlistPayment = ($oldTx->status == MeetTransaction::STATUS_WAITLIST_CONFIRMED);

            $subtotal = 0;
            if (!$waitlistPayment) {
                if ($oldTx->was_replaced || !in_array(
                    $oldTx->status, [
                        MeetTransaction::STATUS_FAILED, MeetTransaction::STATUS_CANCELED
                    ])
                )
                    throw new CustomBaseException("Invalid transaction status.", -1);
            }

            $snapshot = $oldTx->reapplyFees();
            $calculatedTotal = $oldTx->calculatedTotal($snapshot);
            $subtotal = $calculatedTotal['subtotal'];
            unset($calculatedTotal['subtotal']);

            $host = User::lockForUpdate()->find($meet->gym->user->id); /** @var User $host */
            if ($host == null)
                throw new CustomBaseException('No such host');

            $registrant = User::lockForUpdate()->find($gym->user->id); /** @var User $registrant */
            if ($registrant == null)
                throw new CustomBaseException('No such registrant');

            if ($subtotal != $summary['subtotal'])
                throw new CustomBaseException('Subtotal calculation mismatch.', -1);

            $calculatedFees = self::calculateFees($subtotal, $meet, $is_own, $chosenMethod,
                $useBalance, $registrant->cleared_balance) + $calculatedTotal;

            $gymSummary = $calculatedFees['gym'];

            if ($gymSummary['handling'] != $summary['handling'])
                throw new CustomBaseException('Handling fee calculation mismatch.', -1);

            if ($gymSummary['used_balance'] != $summary['used_balance'])
                throw new CustomBaseException('Used balance calculation mismatch.', -1);

            if ($gymSummary['processor'] != $summary['processor'])
                throw new CustomBaseException('Processor fee calculation mismatch.', -1);

            if ($gymSummary['total'] != $summary['total'])
                throw new CustomBaseException('Total sum calculation mismatch.', -1);

            if ($useBalance && ($gymSummary['used_balance'] > 0) && ($gymSummary['total'] == 0)) {
                $chosenMethod = [
                    'type' => self::PAYMENT_OPTION_BALANCE,
                    'id' => null,
                    'fee' => $meet->balance_fee(),
                    'mode' => self::PAYMENT_OPTION_FEE_MODE[self::PAYMENT_OPTION_BALANCE]
                ];
            }

            $result = self::executePayment($calculatedFees, $chosenMethod, $this, $host, $registrant);
            $transaction = $result['transaction']; /** @var MeetTransaction $transaction */
            $athleteStatus = $result['athlete_status'];
            $specialistStatus = $result['specialist_status'];
            $coachStatus = $result['coach_status'];
            $calculatedFees = $result['calculated_fees'];
            $paymentMethodString = $result['payment_method_string'];

            Mail::to($gym->user->email)->send(new TransactionExecutedMailable(
                $transaction,
                $paymentMethodString
            ));

            // TODO : Mail to host

            $result = [
                'waitlist' => false,
                'message' => $result['message'],
                'registration' => $this->id
            ];

            if ($gymSummary['used_balance'] != 0) {
                $description = 'Balance used in ' . $gym->name .
                            '\'s registration in ' . $meet->name;

                $btxStatus  = (
                    $chosenMethod['type'] == self::PAYMENT_OPTION_ACH ?
                    UserBalanceTransaction::BALANCE_TRANSACTION_STATUS_UNCONFIRMED :
                    UserBalanceTransaction::BALANCE_TRANSACTION_STATUS_CLEARED
                );
                $balanceTransaction = $this->user_balance_transaction()->create([
                    'user_id' => $gym->user->id,
                    'processor_id' => null,
                    'total' => -$gymSummary['used_balance'],
                    'description' => $description,
                    'clears_on' => now()->addDays(Setting::userBalanceHoldDuration()),
                    'type' => UserBalanceTransaction::BALANCE_TRANSACTION_TYPE_REGISTRATION_PAYMENT,
                    'status' => $btxStatus
                ]);
                $balanceTransaction->save();

                if ($transaction !== null) {
                    $breakdown = $transaction->breakdown;
                    $breakdown['gym']['used_balance_tx_id'] = $balanceTransaction->id;
                    $transaction->breakdown = $breakdown;
                    $transaction->save();
                }

                Log::info('pay : Updating Balance of user :'. $registrant->id);
                Log::info('Previous Balance :'. $registrant->cleared_balance);
                $registrant->cleared_balance -= $gymSummary['used_balance'];
                $registrant->save();
                Log::info('Updated Balance :'. $registrant->cleared_balance);
            }


            foreach ($oldTx->athletes as $a) {  /** @var RegistrationAthlete $a */
                $a->status = $athleteStatus;
                if ($waitlistPayment)
                    $a->in_waitlist = false;
                $a->transaction()->associate($transaction);
                $a->save();
            }

            foreach ($oldTx->specialist_events as $e) { /** @var RegistrationSpecialistEvent $e */
                $e->status = $specialistStatus;
                if ($waitlistPayment)
                    $e->in_waitlist = false;
                $e->transaction()->associate($transaction);
                $e->save();
            }

            foreach ($oldTx->coaches as $c) { /** @var RegistrationCoach $c */
                $c->status = $coachStatus;
                if ($waitlistPayment)
                    $c->in_waitlist = false;
                $c->transaction()->associate($transaction);
                $c->save();
            }

            $levelTeamFees = $calculatedTotal['level_team_fees'];
            foreach ($this->levels as $l) {
                if (array_key_exists($l->pivot->id, $levelTeamFees)) {
                    $l->pivot->team_fee += $levelTeamFees[$l->pivot->id]['fee'];
                    $l->pivot->team_late_fee += $levelTeamFees[$l->pivot->id]['late'];
                    if ($l->pivot->team_late_fee > 0)
                        $l->pivot->was_late = true;
                    $l->pivot->save();
                }
            }
            $this->late_fee += $calculatedTotal['registration_late_fee'];
            if ($this->late_fee > 0)
                $this->was_late = true;
            $this->save();

            if ($waitlistPayment) {
                $oldTx->delete();
            } else {
                $oldTx->was_replaced = true;
                $oldTx->save();
            }

            DB::commit();
            return $result;
        } catch(\Throwable $e) {
            DB::rollBack();
            self::panicCancelTransaction($e, $transaction, $chosenMethod['type']);
            throw $e;
        }
    }

    public function calculateRegistrationTotal(array $snapshot)
    {
        $meet = $this->meet; /** @var Meet $m */

        try {
            $subtotal = 0;
            $lTeamFee = [];
            $mLateFee = 0;

            $old = null;
            $new = null;
            $old_late = null;
            $new_late = null;
            $fee = null;
            $fee_late = null;
            $r = $snapshot['registration'];


            // late registration fee
            $old = $r['old']['late_fee'] - $r['old']['late_refund'];
            $new = $r['new']['late_fee'] - $r['new']['late_refund'];
            $fee = $meet->late_registration_fee;

            if ($r['new']['was_late']) { // check that the fee and flag match
                if ($new != $fee)
                    throw new CustomBaseException('Calculation mismatch (late registration fee)', -1);

                if ($new != $old) { // we should charge the fee
                    $mLateFee = $fee;
                    $subtotal += $fee;
                }
            } else { // check if the fee should have been refunded
                if ($new != 0)
                    throw new CustomBaseException('Calculation mismatch (late registration fee refund)', -1);
            }


            $levels = $snapshot['levels'];
            foreach ($levels as $level_id => $l) {

                $level = LevelRegistration::find($level_id); /** @var LevelRegistration $level */
                if ($level === null)
                    throw new CustomBaseException('Calculation error: can\'t find level ' . $level_id, -1);

                // team registration fee
                $old = $l['old']['team_fee'] - $l['old']['team_refund'];
                $new = $l['new']['team_fee'] - $l['new']['team_refund'];
                $old_late = $l['old']['team_late_fee'] - $l['old']['team_late_refund'];
                $new_late = $l['new']['team_late_fee'] - $l['new']['team_late_refund'];

                $fee = $level->team_registration_fee;
                $fee_late = $level->team_late_registration_fee;

                $lTeamFee[$level->id] = [   // nothing incurred yet
                    'fee' => 0,
                    'late' => 0
                ];

                if ($l['new']['has_team']) { // check the flag and fee match
                    if ($new != $fee)
                        throw new CustomBaseException('Calculation mismatch (team registration fee)', -1);

                    if ($new != $old) { // we should charge the team fee
                        $lTeamFee[$level->id]['fee'] = $fee;
                        $subtotal += $fee;
                    }
                } else { // check if the fee should have been refunded
                    if ($new != 0)
                        throw new CustomBaseException('Calculation mismatch (team registration fee refund)', -1);
                }

                if ($l['new']['was_late']) { // check the flag and fee match
                    if ($new_late != $fee_late)
                        throw new CustomBaseException('Calculation mismatch (late team registration fee)', -1);

                    if ($new_late != $old_late) { // we should charge the late team fee
                        $lTeamFee[$level->id]['late'] = $fee_late;
                        $subtotal += $fee_late;
                    }
                } else { // check if the fee should have been refunded
                    if ($new_late != 0)
                        throw new CustomBaseException('Calculation mismatch (late team registration fee refund)', -1);
                }

                $athletes = $l['athletes'];
                foreach ($athletes as $a) {
                    $subtotal += $a['new']['fee'] + $a['new']['late_fee'] - ($a['new']['refund'] + $a['new']['late_refund']);
                }

                $specialists = $l['specialists'];
                foreach ($specialists as $specialist_events) {
                    foreach ($specialist_events as $se) {
                        $subtotal += $se['new']['fee'] + $se['new']['late_fee'] - ($se['new']['refund'] + $se['new']['late_refund']);
                    }
                }
            }

            $result = [
                'level_team_fees' => $lTeamFee,
                'registration_late_fee' => $mLateFee,
                'subtotal' => $subtotal,
            ];
            return $result;
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public static function calculateFees(float $subtotal, Meet $meet, bool $is_own,
        array $chosenMethod, bool $useBalance, float $balance = 0) {

        $defer_handling = $meet->defer_handling_fees || $is_own;
        $defer_processor = $meet->defer_processor_fees || $is_own;

        $own_meet_refund = $is_own ? $subtotal : 0;
        $gymBalanceUsed = 0;

        $handlingFee = Helper::applyFeeMode($subtotal, Helper::getHandlingFee($meet), self::HANDLING_FEE_MODE);
        $gymHandling = $defer_handling ? $handlingFee : 0;
        $hostHandling = $defer_handling ? 0 : $handlingFee;

        $gymTotal = $subtotal - $own_meet_refund + $gymHandling;
        $hostTotal = $subtotal - $own_meet_refund - $hostHandling;

        if ($chosenMethod['type'] != self::PAYMENT_OPTION_CHECK) {
            if ($balance < 0)
                $gymBalanceUsed = $balance;
            else if ($useBalance)
                $gymBalanceUsed = ($balance >= $gymTotal ? $gymTotal : $balance);
        }

        $gymTotal -= $gymBalanceUsed;
        $hostProcessor = $defer_processor ?
            0 : Helper::applyFeeMode($gymTotal, $chosenMethod['fee'], $chosenMethod['mode']);

        $gymProcessor = 0;

        if ($gymTotal > 0) {
            if ($defer_processor) {
                $gymProcessor = Helper::applyFeeMode($gymTotal, $chosenMethod['fee'], $chosenMethod['mode']);
            } else if ($gymBalanceUsed < 0) {
                /* Need to pay processor fee for negative balance even if not deferred as host is only
                    charged for processor fee outside of gym's negative balance settelement. */
                $gymProcessor = Helper::applyFeeMode(-$gymBalanceUsed, $chosenMethod['fee'], $chosenMethod['mode']);
            }
        }

        $gymTotal = $gymTotal + $gymProcessor ;
        $hostTotal = $hostTotal - $hostProcessor;

        $hostTotal = $is_own ? 0 : $hostTotal;

        return [
            'gym' => [
                'subtotal' => $subtotal,
                'own_meet_refund' => $own_meet_refund,
                'handling' => $gymHandling,
                'used_balance' => $gymBalanceUsed,
                'processor' => $gymProcessor,
                'total' => $gymTotal,
            ],
            'host' => [
                'subtotal' => $subtotal,
                'own_meet_refund' => $own_meet_refund,
                'handling' => $hostHandling,
                'processor' => $hostProcessor,
                'total' => $hostTotal,
            ],
            'defer' => [
                'handling' => $defer_handling,
                'processor' => $defer_processor,
            ]
        ];
    }

    public static function executePayment(array $calculatedFees, array $chosenMethod,
        MeetRegistration $registration, User $host, User $registrant)
    {
        $dwollaService = resolve(DwollaService::class); /** @var DwollaService $dwollaService */

        $meet = $registration->meet;
        $gym = $registration->gym;

        $athleteStatus = null;
        $specialistStatus = null;
        $coachStatus = null;

        $gymSummary = $calculatedFees['gym'];

        $transaction = null;
        $result = [
            'message' => 'Payment executed.',
            'payment_method_string' => ''
        ];

        $handlingFee = 0;
        $processorFee = 0;

        //Count handling fee for per transaction
        if($calculatedFees['gym']['handling']){
            $handlingFee = $calculatedFees['gym']['handling'];
        }

        //Count processor fee for per transaction
        if($calculatedFees['gym']['processor']){
            $processorFee = $calculatedFees['gym']['processor'];
        }

        switch($chosenMethod['type']) {
            case self::PAYMENT_OPTION_CARD:
                if (!isset($chosenMethod['id']))
                    throw new CustomBaseException('Invalid payment method format.', -1);

                $transaction = StripeService::createCharge(
                    $gym->user->stripe_customer_id,
                    $chosenMethod['id'],
                    $gymSummary['total'],
                    'USD',
                    '',
                    [
                        'registration' => $registration->id,
                        'meet' => $gym->name,
                        'gym' => $meet->name,
                    ]
                );

                $result['payment_method_string'] = 'Card ending with ' .
                                        $transaction->payment_method_details->card->last4;

                $calculatedFees['gym']['last4'] = $transaction->payment_method_details->card->last4;

                //Count Stripe fee
                $stripeFee = 0;
                if($transaction->balance_transaction['fee'] > 0){
                    $stripeFee = ($transaction->balance_transaction['fee'] / 100);
                }

                $transaction = $registration->transactions()->create([
                    'processor_id' => $transaction->id,
                    'handling_rate' => Helper::getHandlingFee($meet),
                    'processor_rate' => $meet->cc_fee(),
                    'total' => $gymSummary['total'],
                    'breakdown' => $calculatedFees,
                    'method' => MeetTransaction::PAYMENT_METHOD_CC,
                    'status' => MeetTransaction::STATUS_COMPLETED,
                    'handling_fee' => $handlingFee,
                    'processor_fee' => $processorFee,
                    'processor_charge_fee' => $stripeFee
                ]); /** @var Meettransaction $transaction */

                if ($calculatedFees['host']['total'] != 0) {
                    $description = 'Revenue from ' . $gym->name .
                                    '\'s registration in ' . $meet->name;
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

                $athleteStatus = RegistrationAthlete::STATUS_REGISTERED;
                $specialistStatus = RegistrationSpecialistEvent::STATUS_SPECIALIST_REGISTERED;
                $coachStatus = RegistrationCoach::STATUS_REGISTERED;

                $result['message'] = 'Your payment has been successfully processed.';
                break;

            case self::PAYMENT_OPTION_ACH:
                if (!isset($chosenMethod['id']))
                    throw new CustomBaseException('Invalid payment method format.', -1);

                $fundingSource = $dwollaService->getFundingSource($chosenMethod['id']); /** @var FundingSource $fundingSource */

                $transaction = self::payWithACH(
                    $gym->user->dwolla_customer_id,
                    $fundingSource,
                    $gymSummary['total'],
                    [
                        'type' => 'registration',
                        'registration' => $registration->id,
                        'meet' => $gym->name,
                        'gym' => $meet->name,
                    ]
                );

                $transaction = $dwollaService->getACHTransfer($transaction);

                $result['payment_method_string'] = '(Pending) ' . ucfirst($fundingSource->bank_account_type) .
                                        ' Bank Account' . $fundingSource->name . '"';

                $transaction = $registration->transactions()->create([
                    'processor_id' => $transaction->id,
                    'handling_rate' => Helper::getHandlingFee($meet),
                    'processor_rate' => $meet->ach_fee(),
                    'total' => $gymSummary['total'],
                    'breakdown' => $calculatedFees,
                    'method' => MeetTransaction::PAYMENT_METHOD_ACH,
                    'status' => MeetTransaction::STATUS_PENDING,
                    'handling_fee' => $handlingFee,
                    'processor_fee' => $processorFee,
                ]); /** @var Meettransaction $transaction */

                $athleteStatus = RegistrationAthlete::STATUS_PENDING_RESERVED;
                $specialistStatus = RegistrationSpecialistEvent::STATUS_SPECIALIST_PENDING;
                $coachStatus = RegistrationCoach::STATUS_PENDING_RESERVED;

                $result['message'] = 'Your ACH payment is currently being processed. ' .
                                    'You will receive a status update within the next ' .
                                    '7 days.';
                break;

//            case self::PAYMENT_OPTION_PAYPAL:
//                throw new CustomBaseException('Paypal Payments are coming soon.', -1);
//                $athleteStatus = RegistrationAthlete::STATUS_REGISTERED;
//                $specialistStatus = RegistrationSpecialistEvent::STATUS_SPECIALIST_REGISTERED;
//                $coachStatus = RegistrationCoach::STATUS_REGISTERED;
//                break;

            case self::PAYMENT_OPTION_CHECK:
                if (!isset($chosenMethod['id']))
                    throw new CustomBaseException('Please provide a check number.', -1);

                $calculatedFees['gym']['check_no'] = $chosenMethod['id'];

                $transaction = $registration->transactions()->create([
                    'processor_id' => 'AG-CHECK-' . Helper::uniqueId(),
                    'handling_rate' => Helper::getHandlingFee($meet),
                    'processor_rate' => $meet->check_fee(),
                    'total' => $gymSummary['total'],
                    'breakdown' => $calculatedFees,
                    'method' => MeetTransaction::PAYMENT_METHOD_CHECK,
                    'status' => MeetTransaction::STATUS_PENDING,
                    'handling_fee' => $handlingFee,
                    'processor_fee' => $processorFee,
                ]); /** @var Meettransaction $transaction */


                if ($calculatedFees['defer']['handling'] != false && $meet->defer_handling_fees != false) {
                    $hostBalance = $gym->user->cleared_balance;
                    $minusCCAmount = 0;

                    if (($hostBalance != 0) && ($hostBalance >= $gymSummary['handling'])) {
                        //minus $hostBalance in meet host balance
                       self::minusHandlingFeeFromMeetHost($gymSummary['handling'], $gym, $meet, $transaction, $gym->user_id);
                    }

                    if(($hostBalance != 0) && ($gymSummary['handling'] > $hostBalance)){
                        self::minusHandlingFeeFromMeetHost($hostBalance, $gym, $meet, $transaction, $gym->user_id);
                    }

                    // if charge amount is grater then meet host balance, then create cc charge for remaining amount.
                    //The meet host will receive a check so AllGym will have to collect the CC fee from the meet host.
                    $meetHostCards = $meet->gym->user->getCards();
                    $minusCCAmount = $gymSummary['handling'] - $hostBalance;
                    if ((count($meetHostCards) > 0) && ($minusCCAmount > 0)) {
                        $han_fee = $minusCCAmount;
                        $charge = ($minusCCAmount * $meet->cc_fee()) / 100;
                        $chargeAmount = $minusCCAmount + $charge;
                        $meetHostCCFee = self::meetHostCCFeeTransaction($gym, $chargeAmount, $registration, $meet, $meetHostCards);
                    }
                }

                //if meet host defer handling fee false then handling fee minus from meet host and here minus handling fee from meet host.
                if($calculatedFees['defer']['handling'] == false && $meet->defer_handling_fees == false){
                    $tra_handling_fee = $calculatedFees['host']['handling'];
                    $minusHandlingFee = self::minusHandlingFeeFromMeetHost($tra_handling_fee, $gym, $meet, $transaction, $host->id);
                }

                $athleteStatus = RegistrationAthlete::STATUS_PENDING_NON_RESERVED;
                $specialistStatus = RegistrationSpecialistEvent::STATUS_SPECIALIST_PENDING;
                $coachStatus = RegistrationCoach::STATUS_PENDING_NON_RESERVED;
                $result['payment_method_string'] = 'Mailed Check #' . $chosenMethod['id'];
                $result['message'] = 'Your payment has been successfully processed.';
                break;

            case self::PAYMENT_OPTION_BALANCE:
                $result['payment_method_string'] = 'Allgymnastics.com Balance';
                $transaction = $registration->transactions()->create([
                    'processor_id' => 'AG-BALANCE-' . Helper::uniqueId(),
                    'handling_rate' => Helper::getHandlingFee($meet),
                    'processor_rate' => $meet->balance_fee(),
                    'total' => $gymSummary['used_balance'],
                    'breakdown' => $calculatedFees,
                    'method' => MeetTransaction::PAYMENT_METHOD_BALANCE,
                    'status' => MeetTransaction::STATUS_COMPLETED,
                    'handling_fee' => $handlingFee,
                    'processor_fee' => $processorFee,
                ]); /** @var Meettransaction $transaction */

                $athleteStatus = RegistrationAthlete::STATUS_REGISTERED;
                $specialistStatus = RegistrationSpecialistEvent::STATUS_SPECIALIST_REGISTERED;
                $coachStatus = RegistrationCoach::STATUS_REGISTERED;

                $result['message'] = 'Your payment has been successfully processed.';
                break;
            default:
                throw new CustomBaseException('Invalid payment method.', -1);
        }
/*
//        if meet host defer handling fee false then handling fee minus from meet host
        if($calculatedFees['defer']['handling'] == false && $meet->defer_handling_fees == false && $chosenMethod['type'] != self::PAYMENT_OPTION_CHECK){
            $tra_handling_fee = $calculatedFees['host']['handling'];
            if($tra_handling_fee != 0){
                $han_fee_desc = $gym->name.' gym registered in '. $meet->name. ' and its handling fee was charged.';
                $transaction->host_balance_transaction()->create([
                    'user_id' => $host->id,
                    'total' => -$transaction->breakdown['host']['handling'],
                    'description' =>  $han_fee_desc,
                    'clears_on' => now()->addDays(Setting::userBalanceHoldDuration()),
//            Do not change the type as this type is used in  ClearPendingBalance.php, only if this is the type the fee will be deducted from the clear balance.
                    'type' => UserBalanceTransaction::BALANCE_TRANSACTION_TYPE_REGISTRATION_REVENUE,
                    'status' => UserBalanceTransaction::BALANCE_TRANSACTION_STATUS_PENDING
                ]);
            }
        }
*/

        if ($gymSummary['used_balance'] != 0) {
            $description = 'Balance used in ' . $gym->name .
                        '\'s registration in ' . $meet->name;

            $btxStatus  = (
                $chosenMethod['type'] == self::PAYMENT_OPTION_ACH ?
                UserBalanceTransaction::BALANCE_TRANSACTION_STATUS_UNCONFIRMED :
                UserBalanceTransaction::BALANCE_TRANSACTION_STATUS_CLEARED
            );
            $balanceTransaction = $registration->user_balance_transaction()->create([
                'user_id' => $gym->user->id,
                'processor_id' => null,
                'total' => -$gymSummary['used_balance'],
                'description' => $description,
                'clears_on' => now()->addDays(Setting::userBalanceHoldDuration()),
                'type' => UserBalanceTransaction::BALANCE_TRANSACTION_TYPE_REGISTRATION_PAYMENT,
                'status' => $btxStatus
            ]);
            $balanceTransaction->save();

            if ($transaction !== null) {
                $breakdown = $transaction->breakdown;
                $breakdown['gym']['used_balance_tx_id'] = $balanceTransaction->id;
                $transaction->breakdown = $breakdown;
                $transaction->save();
            }

            Log::info('executePayment : Updating Balance of user :'. $registrant->id);
            Log::info('Previous Balance :'. $registrant->cleared_balance);
            $registrant->cleared_balance -= $gymSummary['used_balance'];
            $registrant->save();
            Log::info('Updated Balance :'. $registrant->cleared_balance);
        }
        AuditEvent::registrationTransactionPaid(
            request()->_managed_account, auth()->user(), $transaction
        );
        $result += [
            'athlete_status' => $athleteStatus,
            'specialist_status' => $specialistStatus,
            'coach_status' => $coachStatus,
            'calculated_fees' => $calculatedFees,
            'transaction' => $transaction
        ];
        return $result;
    }

    public static function minusHandlingFeeFromMeetHost($tra_handling_fee, $gym, $meet, $transaction, $userId) {
        if($tra_handling_fee != 0){
            $han_fee_desc = $gym->name.' gym registered in '. $meet->name. ' and its handling fee was charged.';
            $transaction->host_balance_transaction()->create([
                'user_id' => $userId,
                'total' => -$tra_handling_fee,
                'description' =>  $han_fee_desc,
                'clears_on' => now()->addDays(Setting::userBalanceHoldDuration()),
                'type' => UserBalanceTransaction::BALANCE_TRANSACTION_TYPE_REGISTRATION_REVENUE,
                'status' => UserBalanceTransaction::BALANCE_TRANSACTION_STATUS_PENDING
            ]);
        }

        return true;
    }

    public static function meetHostCCFeeTransaction($gym, $minusCCAmount, $registration, $meet, $meetHostCards)
    {
        $chosenMethod = [
            'type' => 'card',
            'id' => $meetHostCards[0]->id,
            'fee' => $meet->cc_fee(),
            'mode' => self::PAYMENT_OPTION_FEE_MODE['card'],
        ];

        if (!isset($chosenMethod['id']))
            throw new CustomBaseException('Invalid payment method format.', -1);

        $transaction = StripeService::createCharge(
            $meet->gym->user->stripe_customer_id,
            $chosenMethod['id'],
            $minusCCAmount,
            'USD',
            '',
            [
                'registration' => $registration->id,
                'meet' => $gym->name,
                'gym' => $meet->name,
            ]
        );

        //Count Stripe fee
        $stripeFee = 0;
        if ($transaction->balance_transaction['fee'] > 0) {
            $stripeFee = ($transaction->balance_transaction['fee'] / 100);
        }

        $calculatedFees = [
            'gym' => [
                'subtotal' => $minusCCAmount,
                'own_meet_refund' => 0,
                'handling' => 0,
                'used_balance' => 0,
                'processor' => 0,
                'total' => $minusCCAmount,
                'last4' => $transaction->payment_method_details->card->last4,
            ],
            'host' => [
                'subtotal' => $minusCCAmount,
                'handling' => 0,
                'processor' => 0,
                'total' => $minusCCAmount,
            ],
        ];

        $transaction = $registration->transactions()->create([
            'processor_id' => $transaction->id,
            'handling_rate' => Helper::getHandlingFee($meet),
            'processor_rate' => $meet->cc_fee(),
            'total' => $calculatedFees['gym']['total'],
            'breakdown' => $calculatedFees,
            'method' => MeetTransaction::PAYMENT_METHOD_CC,
            'status' => MeetTransaction::STATUS_COMPLETED,
            'handling_fee' => 0,
            'processor_fee' => $minusCCAmount,
            'processor_charge_fee' => $stripeFee
        ]);
        /** @var Meettransaction $transaction */

        return true;
    }

    public static function payWithACH(string $customer, FundingSource $source, float $amount, array $meta = null) {
        $dwollaService = resolve(DwollaService::class); /** @var DwollaService $dwollaService */
        if (!Str::endsWith($source['_links']['customer']['href'], $customer))
            throw new CustomBaseException('No such bank account for this customer.', -1);

        $destination = $dwollaService->getFundingSource(config('services.dwolla.master'));

        return $dwollaService->initiateACHTransfer(
            $source['_links']['self']['href'],
            $destination['_links']['self']['href'],
            $amount,
            $meta
        );
    }

    public static function panicCancelTransaction(\Throwable $e, ?MeetTransaction $transaction,
        string $methodType) {

        $dwollaService = resolve(DwollaService::class); /** @var DwollaService $dwollaService */
        $msg = $e->getMessage();
        if ($transaction != null) {
            $cancelFailed = true;
            try {
                // Try and cancel the transaction.
                switch($methodType) {
                    case self::PAYMENT_OPTION_CARD:
                        // No way to cancel. Can use Authorize + Capture but to limited effect.
                        break;

                    case self::PAYMENT_OPTION_ACH:
                        if ($transaction instanceof Transfer){
                            $transaction = $transaction['_links']['self']['href'];
                        } elseif ($transaction instanceof MeetTransaction) {
                            $transaction = $transaction->processor_id;
                        };

                        $cancelFailed = !$dwollaService->cancelACHTransfer($transaction);
                        break;

//                    case self::PAYMENT_OPTION_PAYPAL:
//                        break;
                }
            } catch (\Throwable $e) {
                Log::debug('Panic TX Cancelation : ' . $e->getMessage());
            }

            $msg .= '. Payment failed. ';
            if ($cancelFailed) {
                $msg .= 'A charge might have been placed on your payment method, we tried to cancel it.
                If the charge appears on your payment method, please contact us.';
            } else {
                $msg .= 'Please try again later.';
            }
            throw new CustomBaseException($msg, -1, $e);
        }
    }

    public function edit(Meet $meet, Gym $gym,
        $inputBodies, $inputCoaches, $summary, $method, bool $useBalance) {

        $chosenMethod = [
            'type' => $method['type'],
            'id' => '',
            'fee' => '',
            'mode' => ''
        ];

        try {
            if (!$this->canBeEdited())
                throw new CustomBaseException("You cannot edit this registration", -1);

            $is_own = ($meet->gym->user->id == $gym->user->id);
            $tshirtRequired = $meet->tshirt_size_chart_id != null;
            $leoRequired = $meet->leo_size_chart_id != null;
            $preSlots = $meet->getUsedSlots();
            $late = $meet->isLate();
            $subtotal = 0;
            $abilities = $this->editingAbilities();

            $meetInWaitlist = $meet->isWaitList();
            $athleteSanctions = [
                SanctioningBody::USAG => [],
                SanctioningBody::USAIGC => [],
                SanctioningBody::AAU => [],
                SanctioningBody::NGA => [],
            ];

            $specialistSanctions = [
                SanctioningBody::USAG => [],
                SanctioningBody::USAIGC => [],
                SanctioningBody::AAU => [],
                SanctioningBody::NGA => [],
            ];

            switch($method['type']) {
                case self::PAYMENT_OPTION_CARD:
                    $chosenMethod = [
                        'type' => $method['type'],
                        'id' => $method['id'],
                        'fee' => $meet->cc_fee(),
                        'mode' => self::PAYMENT_OPTION_FEE_MODE[$method['type']]
                    ];
                    break;

                case self::PAYMENT_OPTION_ACH:
                    $chosenMethod = [
                        'type' => self::PAYMENT_OPTION_ACH,
                        'id' => $method['id'],
                        'fee' => $meet->ach_fee(),
                        'mode' => self::PAYMENT_OPTION_FEE_MODE[$method['type']]
                    ];
                    break;

//                case self::PAYMENT_OPTION_PAYPAL:
//                    $chosenMethod = [
//                        'type' => self::PAYMENT_OPTION_PAYPAL,
//                        'fee' => $meet->paypal_fee(),
//                        'mode' => self::PAYMENT_OPTION_FEE_MODE[$method['type']]
//                    ];
//                    break;

                case self::PAYMENT_OPTION_CHECK:
                    $chosenMethod = [
                        'id' => $method['id'],                  // Check number
                        'type' => self::PAYMENT_OPTION_CHECK,
                        'fee' => $meet->check_fee(),
                        'mode' => self::PAYMENT_OPTION_FEE_MODE[$method['type']]
                    ];

                    if ($useBalance)
                        throw new CustomBaseException('Allgymnastics.com balance cannot be used with mailed checks.', -1);
                    break;

                default:
                    throw new CustomBaseException('Invalid payment method.', -1);
            }

            $meetLevelToRegistrationLevelMatrix = [];
            foreach ($this->levels as $l) /** @var AthleteLevel $l */ {
                $rl = $l->pivot; /** @var LevelRegistration $rl */
                $meetLevelToRegistrationLevelMatrix[$rl->_uid()] = $rl;
            }

            $specialistEvents = [];
            foreach (AthleteSpecialistEvents::all() as $evt)
                $specialistEvents[$evt->id] = $evt;

            DB::beginTransaction();

            $transaction = null;
            $waitlistTransaction = null;
            $needRegularTransaction = false;
            $needWaitlistTransaction = false;

            try {
                $host = User::lockForUpdate()->find($meet->gym->user->id); /** @var User $host */
                if ($host == null)
                    throw new CustomBaseException('No such host');

                $registrant = User::lockForUpdate()->find($gym->user->id); /** @var User $registrant */
                if ($registrant == null)
                    throw new CustomBaseException('No such registrant');

                $incurredFees = [
                    'registration_late_fee' => 0,
                    'level_team_fees' => [],
                ];

                $count = [];
                $txAthletes = [
                    'waitlist' => [],
                    'added' => []
                ];
                $txSpecialists = [
                    'waitlist' => [],
                    'added' => []
                ];
                $txCoaches = [
                    'waitlist' => [],
                    'added' => []
                ];

                $snapshot = $this->snapshotBegin();
                $newIds = [
                    'athletes' => [],
                    'specialist_events' => []
                ];

                $gym_membership_checked = [];
                $athleteAvailable = true;
                foreach ($inputBodies as $b) {
                    foreach ($b['categories'] as $c) {
                        $category = $meet->categories()
                                    ->where('sanctioning_body_id', $b['id'])
                                    ->where('level_category_id', $c['id'])
                                    ->first(); /** @var LevelCategory $category */
                        $categoryMeet = $category->pivot; /** @var CategoryMeet $categoryMeet */
                        $categoryLocked = $categoryMeet->officially_sanctioned || $categoryMeet->frozen;

                        foreach ($c['levels'] as $l) {
                            $levelTotal = 0;
                            $levelTeamFee = 0;
                            $registrationLevel = null;

                            if (!isset($l['id'], $l['team'], $l['athletes'], $l['changes']) || !is_array($l['athletes']))
                                throw new CustomBaseException('Invalid level format.', -1);

                            if (!$abilities['scratch'] && $l['changes']['team'])
                                throw new CustomBaseException('You are not allowed to modify teams.', -1);

                            if (!$l['changes']['team'] && (count($l['athletes']) < 1))
                                throw new CustomBaseException('You need to select at least one athlete per each submitted level.', -1);

                            $level = $meet->levels()
                                            ->wherePivot('allow_men', $l['male'])
                                            ->wherePivot('allow_women', $l['female'])
                                            ->find($l['id']); /** @var \App\Models\AthleteLevel $level */
                            if ($level == null)
                                throw new CustomBaseException('No such level', -1);

                            if (!in_array($level->sanctioning_body_id, $gym_membership_checked)) {
                                $gym_membership = null;
                                $gym_membership_body = '';
                                switch($level->sanctioning_body_id) {
                                    case SanctioningBody::USAG:
                                        $gym_membership = ($gym->usag_membership ? $gym->usag_membership : null);
                                        $gym_membership_body = 'USAG';
                                        break;

                                    case SanctioningBody::USAIGC:
                                        $gym_membership = ($gym->usaigc_membership ? $gym->usaigc_membership : null);
                                        $gym_membership_body = 'USAIGC';
                                        break;

                                    case SanctioningBody::AAU:
                                        $gym_membership = ($gym->usag_membership ? $gym->aau_membership : null);
                                        $gym_membership_body = 'AAU';
                                        break;

                                    case SanctioningBody::NGA:
                                        $gym_membership = ($gym->nga_membership ? $gym->nga_membership : null);
                                        $gym_membership_body = 'NGA';
                                        break;
                                }
                                if ($gym_membership === null)
                                    throw new CustomBaseException('This gym does not have a valid ' . $gym_membership_body . ' membership number.', -1);
                                $gym_membership_checked[] = $level->sanctioning_body_id;
                            }

                            if (array_key_exists($l['uid'], $meetLevelToRegistrationLevelMatrix)) {
                                $registrationLevel = $meetLevelToRegistrationLevelMatrix[$l['uid']];
                            } else {
                                if ($level->pivot->disabled)
                                    throw new CustomBaseException('Level ' . $level->name . ' was disabled by the meet host.', -1);

                                if ($categoryLocked)
                                    throw new CustomBaseException('Level ' . $level->name . ' cannot be added manually.', -1);

                                $registrationLevel = $this->levels()->attach($level->id, [
                                    'allow_men' => $level->pivot->allow_men,
                                    'allow_women' => $level->pivot->allow_women,
                                    'registration_fee' => $level->pivot->registration_fee,
                                    'late_registration_fee' => $level->pivot->late_registration_fee,
                                    'allow_specialist' => $level->pivot->allow_specialist,
                                    'specialist_registration_fee' => $level->pivot->specialist_registration_fee,
                                    'specialist_late_registration_fee' => $level->pivot->specialist_late_registration_fee,
                                    'allow_teams' => $level->pivot->allow_teams,
                                    'team_registration_fee' => $level->pivot->team_registration_fee,
                                    'team_late_registration_fee' => $level->pivot->team_late_registration_fee,
                                    'enable_athlete_limit' => $level->pivot->enable_athlete_limit,
                                    'athlete_limit' => $level->pivot->athlete_limit,
                                    'has_team' => false,
                                    'was_late' => false,
                                    'team_fee' => 0,
                                    'team_late_fee' => 0,
                                    'team_refund' => 0,
                                    'team_late_refund' => 0
                                ]);

                                $registrationLevel = LevelRegistration::where('meet_registration_id', $this->id)
                                                                        ->where('level_id', $level->id)
                                                                        ->where('allow_men', $level->pivot->allow_men)
                                                                        ->where('allow_women', $level->pivot->allow_women)
                                                                        ->first();
                            } /** @var LevelRegistration $registrationLevel */

                            if (!$categoryLocked) {
                                if ($late)
                                    $registrationLevel->was_late = true;

                                $team = $registrationLevel->allow_teams && $l['team'];
                                if ($l['changes']['team']) {
                                    $registrationLevel->has_team = $team;
                                    $registrationLevel->save();
                                }
                            }

                            if (!key_exists($registrationLevel->id, $count)) {
                                $count[$registrationLevel->id] = [
                                    'added' => 0,
                                    'removed' => 0,
                                    'waitlist' => 0,
                                ];
                            }

                            $bodyId = $level->sanctioning_body_id;
                            $sanctionField = '';
                            switch ($bodyId) {
                                case SanctioningBody::USAG:
                                    $sanctionField = 'usag_no';
                                    break;

                                case SanctioningBody::USAIGC:
                                    $sanctionField = 'usaigc_no';
                                    break;

                                case SanctioningBody::AAU:
                                    $sanctionField = 'aau_no';
                                    break;

                                case SanctioningBody::NGA:
                                    $sanctionField = 'nga_no';
                                    break;
                            }

                            foreach ($l['athletes'] as $a) {
                                $athleteTotal = 0;
                                $athleteAvailable = false;

                                if (!isset($a['id']))
                                    throw new CustomBaseException('Invalid athlete format.', -1);

                                $athlete = null;
                                $athlete_sanction = null;

                                if ($a['is_specialist'] && !$level->pivot->allow_specialist)
                                    throw new CustomBaseException('Specialist events are not allowed in this level', -1);

                                $newAthleteFees = [
                                    'fee' => 0,
                                    'refund' => 0,
                                    'late_fee' => 0,
                                    'late_refund' => 0,
                                ];

                                if ($a['is_new']) {
                                    if ($level->pivot->disabled)
                                        throw new CustomBaseException('Cannot add athlete in ' . $level->name . ' because the level was disabled by the meet host.', -1);

                                    if ($categoryLocked)
                                        throw new CustomBaseException('Caanot manually add athletes in  ' . $level->name . '.', -1);

                                    $athlete = $gym->athletes()->find($a['id']); /** @var Athlete $athlete */
                                    if ($athlete == null)
                                        throw new CustomBaseException('No such athlete', -1);
                                } else {
                                    if ($a['is_specialist']) {
                                        $athlete = $this->specialists()->find($a['id']); /** @var RegistrationSpecialist $athlete */
                                        if ($athlete == null)
                                            throw new CustomBaseException('No such specialist', -1);

                                        $allRegistered = true;
                                        foreach ($athlete->events as $event) /** @var RegistrationSpecialistEvent $event */ {
                                            $allRegistered = $allRegistered && !$event->in_waitlist && ($event->status == RegistrationSpecialistEvent::STATUS_SPECIALIST_REGISTERED);

                                            if (isset($a['total']) && !empty($a['total']) && $a['total'] > 0) {
                                                $snapshot['levels'][$registrationLevel->id]['specialists'][$athlete->id][$event->id]['new'] = [
                                                        'was_late' => false,
                                                        'fee' => $a['total'],
                                                        'late_fee' => 0,
                                                        'refund' => 0,
                                                        'late_refund' => 0,
                                                        'total' => $a['total'],
                                                ];
                                            }
                                        }
                                        if (!$allRegistered)
                                            throw new CustomBaseException('You can only make changes to specialists with only registered event', -1);
                                    } else {
                                        $athlete = $this->athletes()->find($a['id']); /** @var RegistrationAthlete $athlete */
                                        if ($athlete == null)
                                            throw new CustomBaseException('No such athlete', -1);
                                        if ($athlete->status != RegistrationAthlete::STATUS_REGISTERED)
                                            throw new CustomBaseException('You can only make changes to registered athletes', -1);

                                        $newAthleteFees = [
                                            'fee' => $athlete->fee,
                                            'refund' => $athlete->refund,
                                            'late_fee' => $athlete->late_fee,
                                            'late_refund' => $athlete->late_refund,
                                        ];

                                        if (isset($a['new_fee']) && !empty($a['new_fee'])) {
                                            $snapshot['levels'][$registrationLevel->id]['athletes'][$athlete->id]['new'] = [
                                                'was_late' => 0,
                                                'fee' => $a['new_fee'],
                                                'new_fee' => $a['new_fee'],
                                                'late_fee' => 0,
                                                'refund' => 0,
                                                'late_refund' => 0,
                                            ];

                                            $snapshot['levels'][$registrationLevel->id]['old'] = [
                                                'has_team' => $registrationLevel->has_team,
                                                'was_late' => $registrationLevel->was_late,
                                                'team_fee' => $registrationLevel->team_fee,
                                                'team_late_fee' => $registrationLevel->team_late_fee,
                                                'team_refund' => $registrationLevel->team_refund,
                                                'team_late_refund' => $registrationLevel->team_late_refund,
                                            ];

                                            $snapshot['levels'][$registrationLevel->id]['specialists'] = [];
                                        }
                                    }
                                }

                                if ($categoryLocked) {
                                    // $athlete will always be RegistrationAthlete or RegistrationSpecialist

                                    if ($a['changes']['first_name']) {
                                        if ($abilities['change_details']) {
                                            try {
                                                $vv = Validator::make($a, [
                                                    'first_name' => ['required', 'string', 'max:255']
                                                ])->validate();
                                            } catch (ValidationException $ve) {
                                                throw new CustomBaseException('Invalid athlete name "' . $a['first_name'] . '"');
                                            }

                                            $athlete->first_name = $vv['first_name'];
                                        } else {
                                            throw new CustomBaseException('You are not allowed to edit names.');
                                        }
                                    }

                                    if ($tshirtRequired) {
                                        if ($a['changes']['tshirt']) {
                                            if ($abilities['change_details']) {
                                                if (!isset($a['tshirt_size_id']) || ($a['tshirt_size_id'] == -1))
                                                    throw new CustomBaseException('T-Shirt sizes are required for this meet.', -1);

                                                $tshirtSize = $meet->tshirt_chart->sizes()->find($a['tshirt_size_id']);
                                                if ($tshirtSize == null)
                                                    throw new CustomBaseException('Invalid T-Shirt size for this meet.', -1);

                                                $athlete->tshirt_size_id = $tshirtSize->id;
                                            } else {
                                                throw new CustomBaseException('You are not allowed to edit T-shirt sizes.');
                                            }
                                        }
                                    }

                                    if ($leoRequired && ($a['gender'] == 'female')) {
                                        if ($a['changes']['leo']) {
                                            if ($abilities['change_details']) {
                                                if (!isset($a['leo_size_id']) || ($a['leo_size_id'] == -1))
                                                    throw new CustomBaseException('Leo sizes are required for this meet.', -1);

                                                $leoSize = $meet->leo_chart->sizes()->find($a['leo_size_id']);
                                                if ($leoSize == null)
                                                    throw new CustomBaseException('Invalid Leo size for this meet.', -1);

                                                $athlete->leo_size_id = $leoSize->id;
                                            } else {
                                                throw new CustomBaseException('You are not allowed to edit T-shirt sizes.');
                                            }
                                        }
                                    }
                                    $athlete->save();
                                    continue;
                                }

                                $athleteMale = ($athlete->gender == 'male');
                                $athleteFemale = !$athleteMale;

                                if (!(
                                    ($athleteMale && $level->pivot->allow_men) ||
                                    ($athleteFemale && $level->pivot->allow_women)
                                ))
                                    throw new CustomBaseException('Athlete \'' . $athlete->fullName() . '\' gender mismatch with event.', -1);

                                $newAthlete = [
                                    'first_name' => $athlete->first_name,
                                    'last_name' => $athlete->last_name,
                                    'gender' => $athlete->gender,
                                    'dob' => $athlete->dob,
                                    'tshirt_size_id' => $athlete->tshirt_size_id,
                                    'leo_size_id' => $athlete->leo_size_id,
                                    'is_us_citizen' => $athlete->is_us_citizen,
                                    'usag_no' => $athlete->usag_no,
                                    'usag_active' => $athlete->usag_active,
                                    'usaigc_no' => $athlete->usaigc_no,
                                    'usaigc_active' => $athlete->usaigc_active,
                                    'aau_no' => $athlete->aau_no,
                                    'aau_active' => $athlete->aau_active,
                                    'nga_no' => $athlete->nga_no,
                                    'nga_active' => $athlete->nga_active,
                                ];

                                if ($a['is_new']) {
                                    $newAthlete['level_registration_id'] = $registrationLevel->id;
                                }

                                if (!$a['is_specialist']) {
                                    $newAthlete += $newAthleteFees;
                                }


                                if ($a['is_specialist'] && !isset($newAthlete['events']))
                                    $newAthlete['events'] = [];

                                foreach (['first_name', 'last_name'] as $field) {
                                    if ($a['changes'][$field]) {
                                        if ($abilities['change_details']) {
                                            try {
                                                $vv = Validator::make($a, [
                                                    $field => ['required', 'string', 'max:255']
                                                ])->validate();
                                            } catch (ValidationException $ve) {
                                                throw new CustomBaseException('Invalid athlete name "' . $a[$field] . '"');
                                            }
                                            $newAthlete[$field] = $vv[$field];
                                        } else {
                                            throw new CustomBaseException('You are not allowed to edit names.');
                                        }
                                    }
                                }

                                if ($a['changes']['dob']) {
                                    if ($abilities['change_details']) {
                                        try {
                                            $vv = Validator::make($a, [
                                                'dob' => ['required', 'date_format:m/d/Y', 'before:today']
                                            ])->validate();
                                        } catch (ValidationException $ve) {
                                            throw new CustomBaseException('Invalid athlete date of birth "' . $a['dob'] . '"');
                                        }
                                        $newAthlete['dob'] = new \DateTime($vv['dob']);
                                    } else {
                                        throw new CustomBaseException('You are not allowed to edit birth dates.');
                                    }
                                }

                                if ($tshirtRequired) {
                                    if ($a['changes']['tshirt']) {
                                        if ($abilities['change_details']) {
                                            if (!isset($a['tshirt_size_id']) || ($a['tshirt_size_id'] == -1))
                                                throw new CustomBaseException('T-Shirt sizes are required for this meet.', -1);

                                            $tshirtSize = $meet->tshirt_chart->sizes()->find($a['tshirt_size_id']);
                                            if ($tshirtSize == null)
                                                throw new CustomBaseException('Invalid T-Shirt size for this meet.', -1);

                                            $newAthlete['tshirt_size_id'] = $tshirtSize->id;
                                        } else {
                                            throw new CustomBaseException('You are not allowed to edit T-shirt sizes.');
                                        }
                                    }
                                }

                                if ($leoRequired && ($a['gender'] == 'female')) {
                                    if ($a['changes']['leo']) {
                                        if ($abilities['change_details']) {
                                            if (!isset($a['leo_size_id']) || ($a['leo_size_id'] == -1))
                                                throw new CustomBaseException('Leo sizes are required for this meet.', -1);

                                            $leoSize = $meet->leo_chart->sizes()->find($a['leo_size_id']);
                                            if ($leoSize == null)
                                                throw new CustomBaseException('Invalid Leo size for this meet.', -1);

                                            $newAthlete['leo_size_id'] = $leoSize->id;
                                        } else {
                                            throw new CustomBaseException('You are not allowed to edit T-shirt sizes.');
                                        }
                                    }
                                }

                                if ($a['changes']['sanction_no']) {
                                    if ($abilities['change_number']) {
                                        try {
                                            $vv = Validator::make($a, [
                                                $sanctionField => Athlete::CREATE_RULES[$sanctionField]
                                            ])->validate();
                                        } catch (ValidationException $ve) {
                                            throw new CustomBaseException('Invalid athlete sanction "' . $a[$sanctionField] . '"');
                                        }
                                        $newAthlete[$sanctionField] = $vv[$sanctionField];
                                    } else {
                                        throw new CustomBaseException('You are not allowed to change athlete numbers.');
                                    }
                                }

                                if ($a['is_new']) {
                                    if ($a['is_specialist']) {
                                        foreach ($a['events'] as $e) {
                                            if ($e['is_new']) {
                                                if (!isset($specialistEvents[$e['event_id']]))
                                                    throw new CustomBaseException("No such event", 1);

                                                $event = $specialistEvents[$e['event_id']];

                                                /*if (!isset($newAthlete['events']))
                                                    $newAthlete['events'] = [];*/

                                                $newEvent = [
                                                    'event_id' => $event->id,
                                                    'transaction_id' => null,
                                                    'was_late' => $late,
                                                    'in_waitlist' => $e['to_waitlist'],
                                                    'fee' => $level->pivot->specialist_registration_fee,
                                                    'late_fee' => ($late ? $level->pivot->specialist_late_registration_fee : 0),
                                                    'refund' => 0,
                                                    'late_refund' => 0,
                                                    'status' => RegistrationSpecialistEvent::STATUS_SPECIALIST_PENDING
                                                ];

                                                $athleteTotal += $level->pivot->specialist_registration_fee + ($late ? $level->pivot->specialist_late_registration_fee : 0);

                                                if ($newEvent['in_waitlist'])
                                                    $athleteTotal = 0;

                                                $newAthlete['events'][] = $newEvent;
                                            } else {
                                                throw new CustomBaseException('New athletes cannot have existing events');
                                            }
                                        }
                                    } else {
                                        $newAthlete['status'] = RegistrationAthlete::STATUS_PENDING_NON_RESERVED;
                                        $newAthlete['refund'] = 0;
                                        $newAthlete['late_refund'] = 0;

                                        $newAthlete['fee'] = $level->pivot->registration_fee;
                                        $newAthlete['late_fee'] = ($late ? $level->pivot->late_registration_fee : 0);

                                        $athleteTotal = $newAthlete['fee'] + $newAthlete['late_fee'];

                                        if ($a['to_waitlist']) {
                                            $newAthlete['in_waitlist'] = true;
                                            $athleteTotal = 0;
                                            $count[$registrationLevel->id]['waitlist']++;
                                        } else {
                                            $count[$registrationLevel->id]['added']++;
                                        }
                                    }
                                } else {
                                    if ($a['changes']['moved_to']) {
                                        if ($abilities['change_level']) {
                                            if (!$a['changes']['scratch']) {
                                                if ($a['is_specialist']) {
                                                    $newAthlete['level_registration_id'] = $registrationLevel->id;

                                                    if ($a['to_waitlist']) {
                                                        $count[$registrationLevel->id]['waitlist']++;
                                                    }

                                                    foreach ($athlete->events as $event) { /** @var RegistrationSpecialistEvent $event */
                                                        $refundSpecialistEvent = null;
                                                        if ($a['changes']['moved_to'] && $abilities['change_level']) {
                                                            if($l['specialist_registration_fee'] <= $event->specialist->registration_level->specialist_registration_fee) {
                                                                $refundSpecialistEvent = $event->specialist->registration_level->specialist_registration_fee - $l['specialist_registration_fee'];
                                                            }else{
                                                                $refundSpecialistEvent = 0;
                                                            }
                                                                $event->refund = isset($refundSpecialistEvent) ? $refundSpecialistEvent : $event->fee;
                                                                $event->late_refund = $event->late_fee;
                                                        }

                                                        $event->fee += $level->pivot->specialist_registration_fee;
                                                        $event->late_fee += ($late ? $level->pivot->specialist_late_registration_fee : 0);

                                                        $athleteTotal += $event->fee + $event->late_fee - $event->refund - $event->late_refund;
                                                    }
                                                } else {
                                                    $newAthlete['level_registration_id'] = $registrationLevel->id;

                                                    if (!key_exists($athlete->level_registration_id, $count)) {
                                                        $count[$athlete->level_registration_id] = [
                                                            'added' => 0,
                                                            'removed' => 0,
                                                            'waitlist' => 0,
                                                        ];
                                                    }

                                                    $count[$athlete->level_registration_id]['removed']++;
                                                    $count[$registrationLevel->id]['added']++;

                                                    $refundAmount = null;
                                                    if ($a['changes']['moved_to'] && $abilities['change_level']) {
                                                        if ($l['registration_fee'] <= $athlete->registration_level->registration_fee) {
                                                            $refundAmount = $athlete->registration_level->registration_fee - $l['registration_fee'];
                                                        }else{
                                                            $refundAmount = 0;
                                                        }
                                                    }

                                                    $newAthlete['refund'] = isset($refundAmount) ? $refundAmount : $athlete->fee;
                                                    $newAthlete['late_refund'] = $athlete->late_fee;

                                                    $newAthlete['fee'] += $level->pivot->registration_fee;
                                                    $newAthlete['late_fee'] += ($late ? $level->pivot->late_registration_fee : 0);

                                                    $athleteTotal += $newAthlete['fee'] + $newAthlete['late_fee'] - $newAthlete['refund'] - $newAthlete['late_refund'];
                                                }
                                            }
                                        } else {
                                            throw new CustomBaseException('You are not allowed to change athlete levels.');
                                        }
                                    }

                                    if ($a['is_specialist']) {
                                        foreach ($a['events'] as $e) {
                                            if ($e['is_new']) {
                                                if ($level->pivot->disabled)
                                                    throw new CustomBaseException('Cannot add specialist events in ' . $level->name . ' because the level was disabled by the meet host.', -1);

                                                if (!isset($specialistEvents[$e['event_id']]))
                                                    throw new CustomBaseException("No such event", 1);

                                                $event = $specialistEvents[$e['event_id']];

                                                if ($event->sanctioning_body->id != $level->sanctioning_body->id)
                                                    throw new CustomBaseException('Specialist event sanctioning body mismatch with level sanctioning body.', -1);

                                                if (!(
                                                    ($athleteMale && $event->male) ||
                                                    ($athleteFemale && $event->female)
                                                ))
                                                    throw new CustomBaseException('Specialist event gender mismatch with athlete gender.', -1);

                                                $newEvent = [
                                                    'event_id' => $event->id,
                                                    'transaction_id' => null,
                                                    'was_late' => $late,
                                                    'in_waitlist' => $e['to_waitlist'],
                                                    'fee' => $level->pivot->specialist_registration_fee,
                                                    'late_fee' => ($late ? $level->pivot->specialist_late_registration_fee : 0),
                                                    'refund' => 0,
                                                    'late_refund' => 0,
                                                    'status' => RegistrationSpecialistEvent::STATUS_SPECIALIST_PENDING
                                                ];

                                                $athleteTotal += $level->pivot->specialist_registration_fee + ($late ? $level->pivot->specialist_late_registration_fee : 0);

                                                if ($newEvent['in_waitlist'])
                                                    $athleteTotal = 0;

                                                $newAthlete['events'][] = $newEvent;

                                            } else if ($e['changes']['scratch']) {
                                                if ($abilities['scratch']) {
                                                    $event = $athlete->events->where('id', $e['id'])->first(); /** @var RegistrationSpecialistEvent $event */

                                                    if ($e == null)
                                                        throw new CustomBaseException("No such event", 1);

                                                    $event->refund = $event->fee;
                                                    $event->late_refund = $event->late_fee;
                                                    $event->status = RegistrationSpecialistEvent::STATUS_SPECIALIST_SCRATCHED;
                                                } else {
                                                    throw new CustomBaseException('You are not allowed to scratch events.');
                                                }
                                            }
                                        }
                                    }

                                    if ($a['changes']['scratch']) {
                                        if ($abilities['scratch']) {
                                            if ($a['is_specialist']) {
                                                foreach ($athlete->events as $event) { /** @var RegistrationSpecialistEvent $event */
                                                    $event->refund = $event->specialist->registration_level->specialist_registration_fee;
                                                    $event->late_refund = $event->late_fee;
                                                    $event->status = RegistrationSpecialistEvent::STATUS_SPECIALIST_SCRATCHED;
                                                }
                                            } else {
                                                $newAthlete['refund'] = $athlete->registration_level->registration_fee;
                                                $newAthlete['late_refund'] = $athlete->late_fee;
                                                $newAthlete['status'] = RegistrationAthlete::STATUS_SCRATCHED;
                                            }

                                            $athleteTotal = 0;
                                        } else {
                                            throw new CustomBaseException('You are not allowed to scratch athletes.');
                                        }
                                    }
                                }

                                if ($a['is_new']) {
                                    if ($a['is_specialist']) {
                                        $newAthleteEvents = $newAthlete['events'];
                                        unset($newAthlete['events']);

                                        $athlete = $this->specialists()->create($newAthlete); /** @var RegistrationSpecialist $athlete */

                                        foreach ($newAthleteEvents as $e) {
                                            $evt = $athlete->events()->create($e);
                                            if ($evt->in_waitlist) {
                                                $needWaitlistTransaction = true;
                                                $txSpecialists['waitlist'][] = $evt;
                                            } else {
                                                $needRegularTransaction = true;
                                                $txSpecialists['added'][] = $evt;
                                                $newIds['specialist_events'][] = $evt->id;
                                            }
                                        }
                                    } else {
                                        $athlete = $this->athletes()->create($newAthlete); /** @var RegistrationAthlete $athlete */

                                        if ($athlete->in_waitlist) {
                                            $needWaitlistTransaction = true;
                                            $txAthletes['waitlist'][] = $athlete;
                                        } else {
                                            $needRegularTransaction = true;
                                            $txAthletes['added'][] = $athlete;
                                            $newIds['athletes'][] = $athlete->id;
                                        }
                                    }
                                } else {
                                    if ($a['is_specialist']) {
                                        $newAthleteEvents = $newAthlete['events'];
                                        unset($newAthlete['events']);

                                        foreach ($athlete->events as $e) {
                                            $e->save();
                                        }

                                        foreach ($newAthleteEvents as $e) {
                                            $evt = $athlete->events()->create($event);
                                            if ($evt->in_waitlist) {
                                                $needWaitlistTransaction = true;
                                                $txSpecialists['waitlist'][] = $evt;
                                            } else {
                                                $needRegularTransaction = true;
                                                $txSpecialists['added'][] = $evt;
                                                $newIds['specialist_events'][] = $evt->id;
                                            }
                                        }
                                    } else {
                                        if (key_exists('level_registration_id', $newAthlete)) {
                                            if ($athlete->in_waitlist) {
                                                $needWaitlistTransaction = true;
                                                $txAthletes['waitlist'][] = $athlete;
                                            } else {
                                                $needRegularTransaction = true;
                                                $txAthletes['added'][] = $athlete;
                                            }
                                        }
                                    }
                                    $athlete->update($newAthlete);
                                }

                                $athlete->save();

                                $levelTotal += $athleteTotal;
                            }

                            $registrationLevel->save();
                            $subtotal += $levelTotal;
                        }
                    }
                }

                if ($this->athletes()->count() < 1 && $athleteAvailable)
                    throw new CustomBaseException('You need to select at least one athlete per each submitted level.', -1);

                $_totalAdded = 0;
                $_totalRemoved = 0;
                $_totalWaitlist = 0;
                foreach ($count as $rlid => $v) {
                    $_totalAdded += $v['added'];
                    $_totalRemoved += $v['removed'];
                    $_totalWaitlist += $v['waitlist'];
                }
                $_totalNet = $_totalAdded + $_totalWaitlist - $_totalRemoved;
                $_oldTotal = $preSlots['total'];
                $_newTotal = $_oldTotal + $_totalNet;
                $globalWaitlistCount = 0;

                if ($meetInWaitlist) {
                    if ($_totalAdded > 0)
                        throw new CustomBaseException('Meet is in waitlist mode. All new entries need to go into the waitlist.');
                } else {
                    if ($meet->athlete_limit !== null) {
                        if ($_newTotal > $meet->athlete_limit) { // if the current total is above the limit
                            if ($_oldTotal > $meet->athlete_limit) { // if we started with a total above the limit
                                $_dip = ($_oldTotal - $_totalRemoved);
                                if ($_dip < $meet->athlete_limit) { // if we dipped under the limit
                                    $_freedSpots = $meet->athlete_limit - $_dip;
                                    if ($_totalAdded > $_freedSpots) // if adding more than freed spots, throw error
                                        throw new CustomBaseException('Trying to reserve more spots than available.');
                                    else // account for the difference
                                        $globalWaitlistCount = max(0, $_totalWaitlist + $_totalAdded - $_freedSpots);
                                } else { // otherwise everyone goes into waitlist
                                    if ($_totalAdded > 0)
                                        throw new CustomBaseException('Meet limit already reached. All new entries need to go into the waitlist.');

                                    $globalWaitlistCount = $_totalWaitlist; // global acocunts for all waitlist entries by default
                                }
                            } else { // if we started with a total below the limit
                                $globalWaitlistCount = $_newTotal - $meet->athlete_limit; // Global can account for the overflow
                            }
                        }
                    }
                }

                $levels = $this->levels()->get();
                foreach ($levels as $rl) {
                    $registrationLevel = LevelRegistration::find($rl->pivot->id); /** @var LevelRegistration $registrationLevel */

                    $bodyId = $registrationLevel->level->sanctioning_body_id;
                    $sanctionField = '';
                    switch ($bodyId) {
                        case SanctioningBody::USAG:
                            $sanctionField = 'usag_no';
                            break;

                        case SanctioningBody::USAIGC:
                            $sanctionField = 'usaigc_no';
                            break;

                        case SanctioningBody::AAU:
                            $sanctionField = 'aau_no';
                            break;

                        case SanctioningBody::NGA:
                            $sanctionField = 'nga_no';
                            break;
                    }

                    foreach ($registrationLevel->athletes as $a) { /** @var RegistrationAthlete $a */
                        if ($a->status == RegistrationAthlete::STATUS_SCRATCHED)
                            continue;

                        $athlete_sanction = $a->$sanctionField;
                        if ($athlete_sanction === null)
                            throw new CustomBaseException('Competing athlete need to have an active membership and a valid number in the organization they are competing within.', -1);

                        if (array_key_exists($athlete_sanction, $athleteSanctions[$bodyId]))
                            throw new CustomBaseException('Athlete ' . $a->fullName() . ' can only compete in specialist events in one level per organization.', -1);

                        $athleteSanctions[$bodyId][$athlete_sanction] = $registrationLevel->id;
                    }

                    foreach ($registrationLevel->specialists as $s) { /** @var RegistrationSpecialist $s */
                        if ($s->status() == RegistrationSpecialistEvent::STATUS_SPECIALIST_SCRATCHED)
                            continue;

                        $specialist_sanction = $s->$sanctionField;
                        if ($specialist_sanction === null)
                            throw new CustomBaseException('Competing specialist need to have an active membership and a valid number in the organization they are competing within.', -1);

                        if (array_key_exists($specialist_sanction, $specialistSanctions[$bodyId]))
                            throw new CustomBaseException('Specialist ' . $s->fullName() . ' can only compete in specialist events in one level per organization.', -1);

                        if (array_key_exists($specialist_sanction, $athleteSanctions[$bodyId])) {
                            $athleteLevel = $athleteSanctions[$bodyId][$specialist_sanction];
                            if ($s->level_registration_id == $athleteLevel)
                                throw new CustomBaseException('Athlete ' . $s->fullName() . ' cannot compete in all around and specialist events in the same level', -1);
                        }

                        $specialistMale = ($s->gender == 'male');
                        $specialistFemale = !$specialistMale;

                        foreach ($s->events as $evt) { /** @var RegistrationSpecialistEvent $evt*/
                            if ($evt->status == RegistrationSpecialistEvent::STATUS_SPECIALIST_SCRATCHED)
                                continue;

                            if (!(
                                ($specialistMale && $evt->specialist_event->male) ||
                                ($specialistFemale && $evt->specialist_event->female)
                            ))
                                throw new CustomBaseException('Specialist \'' . $athlete->fullName() . '\' gender mismatch with event.', -1);
                        }

                        $specialistSanctions[$bodyId][$specialist_sanction] = $registrationLevel->id;
                    }

                    if (!key_exists($registrationLevel->id, $count))
                        continue;

                    $_added = $count[$registrationLevel->id]['added'];
                    $_removed = $count[$registrationLevel->id]['removed'];
                    $_waitlist = $count[$registrationLevel->id]['waitlist'];

                    if ($registrationLevel->enable_athlete_limit) {
                        if (($_added > 0) || ($_removed > 0) || ($_waitlist > 0)) { // if there has been changes
                            $levelGender = (
                                ($registrationLevel->allow_men && $registrationLevel->allow_women) ?
                                'both' :
                                ($registrationLevel->allow_men ? 'male' : 'female')
                            );

                            $oldCount = $preSlots[$registrationLevel->level_id][$levelGender]['count'];
                            $limit = $preSlots[$registrationLevel->level_id][$levelGender]['limit'];
                            $difference = $limit - ($oldCount + $_added - $_removed);

                            if ($difference < 0) { // difference should go into waitlist
                                if (abs($difference) != $_waitlist)
                                    throw new CustomBaseException('Waitlist / Reserved spots calculation mismatch (Not enough)');
                            } else { // Nobody should have went into waitlist
                                if ($_waitlist > 0) {
                                    if ($globalWaitlistCount > 0)
                                        $globalWaitlistCount--;
                                    else
                                        throw new CustomBaseException('Waitlist / Reserved spots calculation mismatch (Too many)');
                                }
                            }
                        } // else no changes, so skip
                    }
                }

                $neededSanctions = [];
                foreach ($meet->categories as $category) { /** @var LevelCategory $category */
                    switch ($category->pivot->sanctioning_body_id) {
                        case SanctioningBody::USAG:
                            //$neededSanctions['usag'] = true;
                            break;

                        case SanctioningBody::USAIGC:
                            //$neededSanctions['usaigc'] = true;
                            break;

                        case SanctioningBody::AAU:
                            //$neededSanctions['aau'] = true;
                        break;

                        case SanctioningBody::NGA:
                            //$neededSanctions['nga'] = true;
                            break;
                    }
                }

                $neededSanctionsCount = count($neededSanctions);

                foreach ($inputCoaches as $c) {
                    if (!isset($c['id']))
                        throw new CustomBaseException('Invalid coach format.', -1);

                    $coach = null;

                    if ($c['is_new']) {
                        $coach = $gym->coaches()->find($c['id']); /** @var Coach $coach */
                        if ($coach == null)
                            throw new CustomBaseException('No such coach', -1);
                    } else {
                        $coach = $this->coaches()->find($c['id']); /** @var RegistrationCoach $coach */
                        if ($coach == null)
                            throw new CustomBaseException('No such coach', -1);
                        if ($coach->status != RegistrationCoach::STATUS_REGISTERED)
                            throw new CustomBaseException('You can only make changes to registered coaches', -1);
                    }

                    $newCoach = [
                        'first_name' => $coach->first_name,
                        'last_name' => $coach->last_name,
                        'gender' => $coach->gender,
                        'dob' => $coach->dob,
                        'tshirt_size_id' => $coach->tshirt_size_id,
                        'usag_no' => $coach->usag_no,
                        'usag_active' => $coach->usag_active,
                        'usag_expiry' => $coach->usag_expiry,
                        'usag_safety_expiry' => $coach->usag_safety_expiry,
                        'usag_safesport_expiry' => $coach->usag_safesport_expiry,
                        'usag_background_expiry' => $coach->usag_background_expiry,
                        'usag_u100_certification' => $coach->usag_u100_certification,
                        'usaigc_no' => $coach->usaigc_no,
                        'usaigc_background_check' => $coach->usaigc_background_check,
                        'aau_no' => $coach->aau_no,
                        'nga_no' => $coach->nga_no,
                        'was_late' => $late,
                    ];

                    if (!$c['is_new'] && $coach->from_usag) {
                        // $athlete will always be RegistrationCoach
                        if ($c['changes']['first_name']) {
                            if ($abilities['change_details']) {
                                try {
                                    $vv = Validator::make($c, [
                                        'first_name' => ['required', 'string', 'max:255']
                                    ])->validate();
                                } catch (ValidationException $ve) {
                                    throw new CustomBaseException('Invalid coach name "' . $c['first_name'] . '"');
                                }
                                $coach->first_name = $vv['first_name'];
                            } else {
                                throw new CustomBaseException('You are not allowed to edit names.');
                            }
                        }

                        if ($tshirtRequired) {
                            if ($c['changes']['tshirt']) {
                                if ($abilities['change_details']) {
                                    $tshirtSize = $meet->tshirt_chart->sizes()->where('id', $c['tshirt_size_id'])->first();
                                    if ($tshirtSize == null)
                                        throw new CustomBaseException('Invalid T-Shirt size for this meet.', -1);

                                    $coach->tshirt_size_id = $tshirtSize->id;
                                } else {
                                    throw new CustomBaseException('You are not allowed to edit T-shirt sizes.');
                                }
                            }
                        }

                        $coach->save();
                        continue;
                    }

                    foreach (['first_name', 'last_name'] as $field) {
                        if ($c['changes'][$field]) {
                            if ($abilities['change_details']) {
                                try {
                                    $vv = Validator::make($c, [
                                        $field => ['required', 'string', 'max:255']
                                    ])->validate();
                                } catch (ValidationException $ve) {
                                    throw new CustomBaseException('Invalid coach name "' . $c[$field] . '"');
                                }
                                $newCoach[$field] = $vv[$field];
                            } else {
                                throw new CustomBaseException('You are not allowed to edit names.');
                            }
                        }
                    }

                    if ($c['changes']['dob']) {
                        if ($abilities['change_details']) {
                            try {
                                $vv = Validator::make($c, [
                                    'dob' => ['required', 'date_format:m/d/Y', 'before:today']
                                ])->validate();
                            } catch (ValidationException $ve) {
                                throw new CustomBaseException('Invalid coach date of birth "' . $c['dob'] . '"');
                            }
                            $newCoach['dob'] = new \DateTime($vv['dob']);
                        } else {
                            throw new CustomBaseException('You are not allowed to edit birth dates.');
                        }
                    }

                    if ($tshirtRequired) {
                        if ($c['changes']['tshirt']) {
                            if ($abilities['change_details']) {
                                $tshirtSize = $meet->tshirt_chart->sizes()->where('id', $c['tshirt_size_id'])->first();
                                if ($tshirtSize == null)
                                    throw new CustomBaseException('Invalid T-Shirt size for this meet.', -1);

                                $newCoach['tshirt_size_id'] = $tshirtSize->id;
                            } else {
                                throw new CustomBaseException('You are not allowed to edit T-shirt sizes.');
                            }
                        }
                    }

                    foreach (['usag_no', 'usaigc_no', 'aau_no', 'nga_no'] as $sanctionField) {
                        if ($c['changes'][$sanctionField]) {
                            if ($abilities['change_number']) {
                                try {
                                    $vv = Validator::make($c, [
                                        $sanctionField => Coach::CREATE_RULES[$sanctionField]
                                    ])->validate();
                                } catch (ValidationException $ve) {
                                    throw new CustomBaseException('Invalid coach sanction number "' . $c[$sanctionField] . '"');
                                }
                                $newCoach[$sanctionField] = $vv[$sanctionField];
                            } else {
                                throw new CustomBaseException('You are not allowed to change coach numbers.');
                            }
                        }
                    }

                    if ($c['is_new']) {
                        $newCoach['status'] = RegistrationCoach::STATUS_PENDING_NON_RESERVED;

                        $newCoach['in_waitlist'] = $c['to_waitlist'];

                    } else {
                        if ($c['changes']['scratch']) {
                            if ($abilities['scratch']) {
                                $newCoach['status'] = RegistrationCoach::STATUS_SCRATCHED;
                            } else {
                                throw new CustomBaseException('You are not allowed to scratch coaches.');
                            }
                        }
                    }

                    if ($c['is_new']) {
                        $coach = $this->coaches()->create($newCoach); /** @var RegistrationCoach $coach */

                        if ($coach->in_waitlist != $meetInWaitlist)
                            throw new CustomBaseException('New coaches waitlist, meet waitlist mode mismatch.');

                        if ($coach->in_waitlist) {
                            $needWaitlistTransaction = true;
                            $txCoaches['waitlist'][] = $coach;
                        } else {
                            $needRegularTransaction = true;
                            $txCoaches['added'][] = $coach;
                        }
                    } else {
                        $coach->update($newCoach);
                    }

                    if ($neededSanctionsCount > 0) {
                        $flag = false;
                        foreach ($neededSanctions as $body => $value) {
                            $flag = $flag || ($coach->{$body . '_no'} !== null);
                        }

                        if (!$flag) {
                            throw new CustomBaseException(
                                'No sanction number was provided for coach ' . $coach->fullName() .
                                '. Please update your coach details in your roster.',
                                -1
                            );
                        }
                    }

                    $coach->save();
                }

                #region FIX <==========================
                $this->refresh();
                $hasAthletes = false;
                foreach ($this->levels as $al) { /** @var AthleteLevel $al */
                    $rl = $al->pivot; /** @var LevelRegistration $rl */

                    $athleteCount = $rl->athletes()
                        ->where('in_waitlist', false)
                        ->where('status', '!=', RegistrationAthlete::STATUS_SCRATCHED)
                        ->count();

                    $specialistCount = $rl->specialists()
                        ->whereHas('events', function (Builder $q0) {
                            $q0->where('in_waitlist', false)
                                ->where('status', '!=', RegistrationSpecialistEvent::STATUS_SPECIALIST_SCRATCHED);
                        })->count();

                    $levelHasAthletes = ($athleteCount + $specialistCount) > 0;
                    $hasAthletes = $hasAthletes || $levelHasAthletes;
                    if ($levelHasAthletes) {
                        if ($rl->has_team) {
                            if (($rl->team_fee - $rl->team_refund) != $rl->team_registration_fee)
                                $rl->team_fee += $rl->team_registration_fee - ($rl->team_fee - $rl->team_refund);

                            if ($rl->was_late) {
                                if (($rl->team_late_fee - $rl->team_late_refund) != $rl->team_late_registration_fee)
                                    $rl->team_late_fee += $rl->team_late_registration_fee - ($rl->team_late_fee - $rl->team_late_refund);
                            } else {
                                // clear late fees
                                if (($rl->team_late_fee - $rl->team_late_refund) != 0)
                                    $rl->team_late_refund = $rl->team_late_fee;
                            }
                        } else {
                            // clear fees
                            if (($rl->team_fee - $rl->team_refund) != 0)
                                $rl->team_refund = $rl->team_fee;

                            $rl->was_late = false;
                            if (($rl->team_late_fee - $rl->team_late_refund) != 0)
                                $rl->team_late_refund = $rl->team_late_fee;
                        }
                    } else {
                        // clear fees
                        $rl->has_team = false;
                        if (($rl->team_fee - $rl->team_refund) != 0)
                            $rl->team_refund = $rl->team_fee;

                        $rl->was_late = false;
                        if (($rl->team_late_fee - $rl->team_late_refund) != 0)
                            $rl->team_late_refund = $rl->team_late_fee;
                    }

                    $rl->save();
                }

                if ($hasAthletes) {
                    if ($this->was_late) {
                        if (($this->late_fee - $this->late_refund) != $meet->late_registration_fee)
                            $this->late_fee += $meet->late_registration_fee - ($this->late_fee - $this->late_refund);
                    } else {
                        // clear fees
                        if (($this->late_fee - $this->late_refund) != 0)
                            $this->late_refund = $this->late_fee;
                    }
                } else {
                    // clear the fees
                    $this->was_late = false;
                    if (($this->late_fee - $this->late_refund) != 0)
                        $this->late_refund = $this->late_fee;
                }
                $this->save();
                #endregion

                $snapshot = $this->snapshotEnd($snapshot, $newIds);
                $incurredFees = $this->calculateRegistrationTotal($snapshot);
                $subtotal = $incurredFees['subtotal'];

                /*
                dump([
                    'snapshot' => $snapshot,
                    'back' => $subtotal,
                    'front' => $summary['subtotal']
                ]);
                */

                if ($subtotal != $summary['subtotal'])
                    throw new CustomBaseException('Subtotal calculation mismatch.', -1);

                $calculatedFees = self::calculateFees($subtotal, $meet, $is_own, $chosenMethod,
                    $useBalance, $registrant->cleared_balance);

                $calculatedFees += $incurredFees;

                $gymSummary = $calculatedFees['gym'];

                if ($gymSummary['handling'] != $summary['handling'])
                    throw new CustomBaseException('Handling fee calculation mismatch.', -1);

                if ($gymSummary['used_balance'] != $summary['used_balance'])
                    throw new CustomBaseException('Used balance calculation mismatch.', -1);

                if ($gymSummary['processor'] != $summary['processor'])
                    throw new CustomBaseException('Processor fee calculation mismatch.', -1);

                if ($gymSummary['total'] != $summary['total'])
                    throw new CustomBaseException('Total sum calculation mismatch.', -1);

                $paymentMethodString = 'Unknown';

                $result = [
                    'waitlist' => $waitlistTransaction !== null,
                    'message' => ($waitlistTransaction !== null) ? 'You have successfully entered this meet\'s wait-list.' : 'Thank you. You have successfully entered the meet.'
                ];

                if ($needWaitlistTransaction) {
                    $waitlistTransaction = $this->transactions()->create([
                        'processor_id' => 'AG-WAITLIST-' . Helper::uniqueId(),
                        'handling_rate' => 0,
                        'processor_rate' => 0,
                        'total' => 0,
                        'breakdown' => [],
                        'method' => MeetTransaction::PAYMENT_METHOD_BALANCE,
                        'status' => MeetTransaction::STATUS_WAITLIST_PENDING
                    ]); /** @var Meettransaction $transaction */
                }

                if($gymSummary['total'] > 0) {
                    $needRegularTransaction = true;
                }
                if ($needRegularTransaction && $gymSummary['total'] > 0) {
                    if ($useBalance && ($gymSummary['used_balance'] > 0) && ($gymSummary['total'] == 0)) {
                        $chosenMethod = [
                            'type' => self::PAYMENT_OPTION_BALANCE,
                            'id' => null,
                            'fee' => $meet->balance_fee(),
                            'mode' => self::PAYMENT_OPTION_FEE_MODE[self::PAYMENT_OPTION_BALANCE]
                        ];

                        $athleteStatus = RegistrationAthlete::STATUS_REGISTERED;
                        $specialistStatus = RegistrationSpecialistEvent::STATUS_SPECIALIST_REGISTERED;
                        $coachStatus = RegistrationCoach::STATUS_REGISTERED;
                    }

                    $executedTransactionResult = self::executePayment(
                        $calculatedFees,
                        $chosenMethod,
                        $this,
                        $host,
                        $registrant
                    );
                    $transaction = $executedTransactionResult['transaction']; /** @var MeetTransaction $transaction */
                    $athleteStatus = $executedTransactionResult['athlete_status'];
                    $specialistStatus = $executedTransactionResult['specialist_status'];
                    $coachStatus = $executedTransactionResult['coach_status'];
                    $calculatedFees = $executedTransactionResult['calculated_fees'];
                    $paymentMethodString = $executedTransactionResult['payment_method_string'];
                    $result['message'] = $executedTransactionResult['message'];
                }

                $auditEvent = [
                    'registration' => [],
                    'athletes' => [],
                    'specialists' => [],
                    'coaches' => [],
                ];

                foreach ($txAthletes['waitlist'] as $ra) { /** @var RegistrationAthlete $ra */
                    $ra->in_waitlist = true;
                    $ra->transaction()->associate($waitlistTransaction);
                    $ra->status = RegistrationAthlete::STATUS_PENDING_NON_RESERVED;
                    $ra->save();
                    $a = $ra->toArray();
                    unset($a['transaction']);
                    $auditEvent['athletes'][] = $a;
                }
                foreach ($txAthletes['added'] as $ra) { /** @var RegistrationAthlete $ra */
                    $ra->transaction()->associate($transaction);
                    $ra->status = isset($athleteStatus) ? $athleteStatus : RegistrationAthlete::STATUS_REGISTERED;;
                    $ra->save();
                    $a = $ra->toArray();
                    unset($a['transaction']);
                    $auditEvent['athletes'][] = $a;
                }

                $auditEventSpecialists = [];
                foreach ($txSpecialists['added'] as $rse) { /** @var RegistrationSpecialistEvent $rse */
                    if (!key_exists($rse->specialist->id, $auditEventSpecialists)) {
                        $auditEventSpecialists[$rse->specialist->id] = $rse->specialist->toArray();
                    }

                    $s = $auditEventSpecialists[$rse->specialist->id];
                    if (!key_exists('events', $s))
                        $s['events'] = [];

                    $rse->transaction()->associate($transaction);
                    $rse->status = $specialistStatus;
                    $rse->save();
                    $e = $rse->toArray();
                    unset($e['specialist'], $e['transaction']);
                    $s['events'][] = $e;
                }
                foreach ($txSpecialists['waitlist'] as $rse) { /** @var RegistrationSpecialistEvent $rse */
                    if (!key_exists($rse->specialist->id, $auditEventSpecialists)) {
                        $auditEventSpecialists[$rse->specialist->id] = $rse->specialist->toArray();
                    }

                    $s = $auditEventSpecialists[$rse->specialist->id];
                    if (!key_exists('events', $s))
                        $s['events'] = [];

                    $rse->transaction()->associate($waitlistTransaction);
                    $rse->in_waitlist = true;
                    $rse->status = RegistrationSpecialistEvent::STATUS_SPECIALIST_PENDING;
                    $rse->save();
                    $e = $rse->toArray();
                    unset($e['specialist'], $e['transaction']);
                    $s['events'][] = $e;
                }
                foreach($auditEventSpecialists as $k => $v)
                    $auditEvent['specialists'][] = $v;

                foreach ($txCoaches['added'] as $rc) { /** @var RegistrationCoach $rc */
                    $rc->transaction()->associate($transaction);
                    $rc->status = $coachStatus;
                    $rc->save();
                    $c = $rc->toArray();
                    unset($c['transaction']);
                    $auditEvent['coaches'][] = $c;
                }
                foreach ($txCoaches['waitlist'] as $rc) { /** @var RegistrationCoach $rc */
                    $rc->transaction()->associate($waitlistTransaction);
                    $rc->in_waitlist = true;
                    $rc->status = RegistrationCoach::STATUS_PENDING_NON_RESERVED;
                    $rc->save();
                    $c = $rc->toArray();
                    unset($c['transaction']);
                    $auditEvent['coaches'][] = $c;
                }

                $this->save();

                $registrationArray = $this->toArray();
                unset($registrationArray['athletes']);
                unset($registrationArray['specialists']);
                unset($registrationArray['coaches']);
                $auditEvent['registration'] = $registrationArray;

                AuditEvent::registrationUpdated(
                    request()->_managed_account, auth()->user(), $this, $auditEvent
                );

                $result['registration'] = $this->id;
                // This commented section userd for Meet Entry pdf send for registaring host and gym
                // $pdf = $meet->generateMeetEntryReport($gym);
                // Mail::to($gym->user->email)->send(new GymRegistrationUpdatedMailable(
                //     $meet,
                //     $gym,
                //     $this,
                //     $gymSummary,
                //     $paymentMethodString,
                //     $transaction !== null,
                //     $waitlistTransaction !== null,
                //     null,
                //     $pdf
                // ));

                Mail::to($gym->user->email)->send(new GymRegistrationUpdatedMailable(
                    $meet,
                    $gym,
                    $this,
                    $gymSummary,
                    $paymentMethodString,
                    $transaction !== null,
                    $waitlistTransaction !== null
                ));

                // TODO : Mail to host

                DB::commit();
            } catch(\Throwable $e) {
                DB::rollBack();
                self::panicCancelTransaction($e, $transaction, $chosenMethod['type']);
                throw $e;
            }

            return $result;
        } catch(\Throwable $e) {
            throw $e;
        }
    }

    private function snapshotBegin() {
        $snapshot = [
            'registration' => [
                'old' => [
                    'was_late' => $this->was_late,
                    'late_fee' => $this->late_fee,
                    'late_refund' => $this->late_refund,
                ],
                'new' => [],
            ],
            'levels' => [],
        ];

        foreach ($this->levels as $al) { /** @var AthleteLevel $al */
            $l = $al->pivot; /** @var LevelRegistration $l */
            $snapshot['levels'][$l->id] = [
                'old' => [
                    'name' => $al->sanctioning_body->initialism . ' - ' . $al->name,
                    'has_team' => $l->has_team,
                    'was_late' => $l->was_late,
                    'team_fee' => $l->team_fee,
                    'team_late_fee' => $l->team_late_fee,
                    'team_refund' => $l->team_refund,
                    'team_late_refund' => $l->team_late_refund,
                ],
                'new' => [],
                'athletes' => [],
                'specialists' => [],
            ];

            $athletes = $l->athletes()
                        ->where('in_waitlist', false)
                        ->where('status', '!=', RegistrationAthlete::STATUS_SCRATCHED)
                        ->get();

            foreach ($athletes as $a) { /** @var RegistrationAthlete $a */
                $snapshot['levels'][$l->id]['athletes'][$a->id] = [
                    'old' => [
                        'was_late' => $a->was_late,
                        'fee' => $a->fee,
                        'late_fee' => $a->late_fee,
                        'refund' => $a->refund,
                        'late_refund' => $a->late_refund,
                    ],
                    'new' => [],
                ];
            }

            $specialists = $l->specialists()
                            ->whereHas('events', function (Builder $q0) {
                                $q0->where('in_waitlist', false)
                                    ->where('status', '!=', RegistrationSpecialistEvent::STATUS_SPECIALIST_SCRATCHED);
                            })
                            ->get();
            foreach ($specialists as $s) { /** @var RegistrationSpecialist $s */
                $snapshot['levels'][$l->id]['specialists'][$s->id] = [];

                $events = $s->events()
                        ->where('in_waitlist', false)
                        ->where('status', '!=', RegistrationAthlete::STATUS_SCRATCHED)
                        ->get();

                foreach ($events as $se) { /** @var RegistrationSpecialistEvent $se */
                    $snapshot['levels'][$l->id]['specialists'][$s->id][$se->id] = [
                        'old' => [
                            'was_late' => $se->was_late,
                            'fee' => $se->fee,
                            'late_fee' => $se->late_fee,
                            'refund' => $se->refund,
                            'late_refund' => $se->late_refund,
                        ],
                        'new' => [],
                    ];
                }
            }
        }

        return $snapshot;
    }

    private function snapshotEnd(array &$snapshot, array $new) {
        $fresh = $this->fresh([
            'levels',
            'athletes',
            'specialists',
            'coaches',
        ]);

        foreach ($fresh->levels as $al) { /** @var AthleteLevel $al */
            $l = $al->pivot; /** @var LevelRegistration $l */

            $snapshot['registration']['new'] = [
                'was_late' => $this->was_late,
                'late_fee' => $this->late_fee,
                'late_refund' => $this->late_refund,
            ];

            if (!isset($snapshot['levels'][$l->id])) {
                $snapshot['levels'][$l->id] = [
                    'old' => [
                        'has_team' => false,
                        'was_late' => false,
                        'team_fee' => 0,
                        'team_late_fee' => 0,
                        'team_refund' => 0,
                        'team_late_refund' => 0,
                    ],
                    'new' => [],
                    'athletes' => [],
                    'specialists' => [],
                ];
            }

            $snapshot['levels'][$l->id]['new'] = [
                'has_team' => $l->has_team,
                'was_late' => $l->was_late,
                'team_fee' => ($l->has_team && $l->team_refund != 0) ? $l->team_fee : (($l->has_team) ? $l->team_registration_fee : $l->team_fee),
                'team_late_fee' => $l->team_late_fee,
                'team_refund' => $l->team_refund,
                'team_late_refund' => $l->team_late_refund,
            ];

            $processed = [];
            $athletes = $l->athletes()
                        ->where('in_waitlist', false)
                        ->where('status', '!=', RegistrationAthlete::STATUS_SCRATCHED)
                        ->get();

            foreach ($athletes as $a) { /** @var RegistrationAthlete $a */
                if (!in_array($a->id, $new['athletes'])) {
                    if (key_exists($a->id, $snapshot['levels'][$l->id]['athletes'])) {
                        if (!isset($snapshot['levels'][$l->id]['athletes'][$a->id]['new']['new_fee'])) {
                            unset($snapshot['levels'][$l->id]['athletes'][$a->id]);
                        }
                    }
                    continue;
                }

                if (!isset($snapshot['levels'][$l->id]['athletes'][$a->id])) {
                    $snapshot['levels'][$l->id]['athletes'][$a->id] = [
                        'old' => [
                            'was_late' => false,
                            'fee' => 0,
                            'late_fee' => 0,
                            'refund' => 0,
                            'late_refund' => 0,
                        ],
                    ];
                }

                $snapshot['levels'][$l->id]['athletes'][$a->id]['new'] = [
                    'was_late' => $a->was_late,
                    'fee' => $a->fee,
                    'late_fee' => $a->late_fee,
                    'refund' => $a->refund,
                    'late_refund' => $a->late_refund,
                ];

                $processed[] = $a->id;
            }

            foreach ($snapshot['levels'][$l->id]['athletes'] as $id => $a) {
                if (!in_array($id, $processed)) {
                    if (!isset($snapshot['levels'][$l->id]['athletes'][$id]['new']['new_fee'])) {
                        unset($snapshot['levels'][$l->id]['athletes'][$id]);
                    }
                }
            }

            $processed = [];
            $specialists = $l->specialists()
                            ->whereHas('events', function (Builder $q0) {
                                $q0->where('in_waitlist', false)
                                    ->where('status', '!=', RegistrationSpecialistEvent::STATUS_SPECIALIST_SCRATCHED);
                            })
                            ->get();
            foreach ($specialists as $s) { /** @var RegistrationSpecialist $s */
                $events = $s->events()
                        ->where('in_waitlist', false)
                        ->where('status', '!=', RegistrationAthlete::STATUS_SCRATCHED)
                        ->get();

                foreach ($events as $se) { /** @var RegistrationSpecialistEvent $se */
                    if (!in_array($se->id, $new['specialist_events'])) {
                        if (key_exists($s->id, $snapshot['levels'][$l->id]['specialists'])) {
                            if (key_exists($se->id, $snapshot['levels'][$l->id]['specialists'][$s->id])) {
                                if(!isset($snapshot['levels'][$l->id]['specialists'][$s->id][$se->id]['new']['total'])){
                                    unset($snapshot['levels'][$l->id]['specialists'][$s->id][$se->id]);
                                }
                            }

                            if (count($snapshot['levels'][$l->id]['specialists'][$s->id]) < 1) {
                                unset($snapshot['levels'][$l->id]['specialists'][$s->id]);
                            }
                        }
                        continue;
                    }

                    if (!isset($snapshot['levels'][$l->id]['specialists'][$s->id])) {
                        $snapshot['levels'][$l->id]['specialists'][$s->id] = [];
                    }

                    if (!isset($snapshot['levels'][$l->id]['specialists'][$s->id][$se->id])) {
                        $snapshot['levels'][$l->id]['specialists'][$s->id][$se->id] = [
                            'old' => [
                                'was_late' => false,
                                'fee' => 0,
                                'late_fee' => 0,
                                'refund' => 0,
                                'late_refund' => 0,
                            ],
                        ];
                    }

                    $snapshot['levels'][$l->id]['specialists'][$s->id][$se->id]['new'] = [
                        'was_late' => $se->was_late,
                        'fee' => $se->fee,
                        'late_fee' => $se->late_fee,
                        'refund' => $se->refund,
                        'late_refund' => $se->late_refund,
                    ];

                    $processed[] = $se->id;
                }
            }

            foreach ($snapshot['levels'][$l->id]['specialists'] as $sid => $s) {
                foreach ($snapshot['levels'][$l->id]['specialists'][$sid] as $seid => $se) {
                    if (!in_array($seid, $processed)) {
                        if (!isset($snapshot['levels'][$l->id]['specialists'][$sid][$seid]['new']['total'])) {
                            unset($snapshot['levels'][$l->id]['specialists'][$sid][$seid]);
                        }
                    }
                }
                if (count($snapshot['levels'][$l->id]['specialists'][$sid]) < 1) {
                    unset($snapshot['levels'][$l->id]['specialists'][$sid]);
                }
            }
        }

        return $snapshot;
    }
}