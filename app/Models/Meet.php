<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Exceptions\CustomBaseException;
use App\Helper;
use stdClass;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use App\Traits\Excludable;
use Barryvdh\Snappy\Facades\SnappyPdf as PDF;
use Barryvdh\Snappy\PdfWrapper;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use App\Services\USAIGCService;
use Throwable;
use App\Models\MeetTransaction;
use App\Models\MeetRegistration;
// use App\Models\AuditEvent;

class Meet extends Model
{
    use Excludable;

    public const REGISTRATION_STATUS_CLOSED = 1;
    public const REGISTRATION_STATUS_OPEN = 2;
    public const REGISTRATION_STATUS_LATE = 3;
    public const REGISTRATION_STATUS_OPENING_SOON = 4;

    public const REPORT_TYPE_SUMMARY = 'summary';
    public const REPORT_TYPE_ENTRY = 'participation';
    public const REPORT_TYPE_ENTRY_NOT_ATHLETE = 'participation-not-athlete';
    public const REPORT_TYPE_COACHES = 'coaches';
    public const REPORT_TYPE_USAIGC_COACHES_SIGN_IN = 'usaigc-coach-signin';
    public const REPORT_TYPE_NGA_COACHES_SIGN_IN = 'nga-coach-signin';
    public const REPORT_TYPE_SPECIALISTS = 'specialists';
    public const REPORT_TYPE_REFUNDS = 'refunds';
    public const REPORT_TYPE_PROSCOREEXPORT = 'proscore-export';
    public const REPORT_TYPE_MEETENTRY = 'meet-entry';
    public const REPORT_TYPE_SCRATCH = 'scratch';
    public const REPORT_TYPE_REGISTRATION_DETAIL = 'registration-detail';
    public const REPORT_TYPE_EVENT_SPECIALIST = 'event-specialist';
    public const REPORT_TYPE_LEO_T_SHIRT = 'leo-t-shirt';
    public const REPORT_TYPE_LEO_T_SHIRT_GYM = 'leo-t-shirt-gym';
    public const REPORT_TYPE_SPECIALISTS_BY_LEVEL = 'specialist-by-level';

    protected $guarded = ['id'];

    const MEET_MASS_MAILER = 'meet_mass_mailer';

    protected $appends = ['formatted_date','registration_status','sanction_bodies','usag_reservations','usag_meet_sanctions','meet_address'];

    protected $dates = [
        'start_date', 'end_date', 'registration_start_date', 'registration_end_date',
        'registration_scratch_end_date', 'late_registration_start_date',
        'late_registration_end_date', 'registration_first_discount_end_date', 'registration_second_discount_end_date',
        'registration_third_discount_end_date'
    ];

    public const MEET_COPY_RULES = [
        'meet' => ['required', 'integer'],
        'general' => ['sometimes'],
        'venue' => ['sometimes'],
        'registration' => ['sometimes'],
        'payment' => ['sometimes'],
        'categories' => ['sometimes'],
        'contact' => ['sometimes'],
    ];

    public const REGISTRATION_STATUS = [
        self::REGISTRATION_STATUS_OPEN => 'Open',
    ];

    public const UPDATE_STEP_1_RULES = TemporaryMeet::CREATE_STEP_1_RULES;

    public const UPDATE_STEP_2_RULES = [
        'registration_start_date' => ['required', 'date_format:m/d/Y'],
        'registration_end_date' => ['required', 'date_format:m/d/Y', 'after_or_equal:registration_start_date'],
        'registration_scratch_end_date' => ['required', 'date_format:m/d/Y'],

        'allow_late_registration' => ['sometimes'],
        'late_registration_fee' => ['required_with:allow_late_registration', 'numeric'],
        'late_registration_start_date' => ['required_with:allow_late_registration', 'date_format:m/d/Y', 'after:registration_end_date'],
        'late_registration_end_date' => ['required_with:allow_late_registration', 'date_format:m/d/Y', 'after_or_equal:late_registration_start_date'],

        'athlete_limit' => ['nullable', 'integer', 'gt:0'],

        'registration_first_discount_end_date' => ['required_with:registration_first_discount_is_enable', 'sometimes', 'date_format:m/d/Y', 'after_or_equal:registration_start_date'],
        'registration_second_discount_end_date' => ['required_with:registration_second_discount_is_enable','sometimes', 'date_format:m/d/Y', 'after_or_equal:registration_first_discount_end_date'],
        'registration_third_discount_end_date' => ['required_with:registration_third_discount_is_enable','sometimes', 'date_format:m/d/Y', 'after_or_equal:registration_second_discount_end_date'],

        'registration_first_discount_amount' => ['required_with:registration_first_discount_end_date', 'string'],
        'registration_second_discount_amount' => ['required_with:registration_second_discount_amount', 'string'],
        'registration_third_discount_amount' => ['required_with:registration_third_discount_amount', 'string'],

        'registration_first_discount_is_enable' => ['sometimes'],
        'registration_second_discount_is_enable' => ['sometimes'],
        'registration_third_discount_is_enable' => ['sometimes'],
    ];

    public const UPDATE_STEP_6_RULES = [
        'accept_paypal' => ['sometimes'],
        'accept_ach' => ['sometimes'],
        'accept_mailed_check' => ['sometimes'],
        'accept_deposit' => ['sometimes'],
        'deposit_ratio' => ['nullable', 'integer', 'gt:0'],
        'mailed_check_instructions' => ['required_with:accept_mailed_check', 'string'],

        'defer_handling_fees' => ['sometimes'],
        'defer_processor_fees' => ['sometimes'],

        'process_refunds' => ['sometimes'],
    ];

    public const UPDATE_STEP_3_RULES = TemporaryMeet::CREATE_STEP_3_RULES;
    public const UPDATE_STEP_4_RULES = TemporaryMeet::CREATE_STEP_4_RULES;
    public const UPDATE_STEP_5_RULES = TemporaryMeet::CREATE_STEP_5_RULES;

    const STATUS_COLOR = [1=>'danger', 2=>'success', 3=>'warning', 4=>'info'];

    const STATUS_ARRAY = [
        self::REGISTRATION_STATUS_CLOSED =>'Closed',
        self::REGISTRATION_STATUS_OPEN=>'Open',
        self::REGISTRATION_STATUS_LATE=>'Late',
        self::REGISTRATION_STATUS_OPENING_SOON=>'Opening Soon'
];

    public const PROFILE_PICTURE_RULES = [
        'meet_picture' => ['nullable', 'mimes:jpeg,png,jpg', 'dimensions:min_width=100,min_height=100']
    ];

    public function gym()
    {
        return $this->belongsTo(Gym::class);
    }

    public function tshirt_chart()
    {
        return $this->belongsTo(ClothingSizeChart::class, 'tshirt_size_chart_id');
    }

    public function leo_chart()
    {
        return $this->belongsTo(ClothingSizeChart::class, 'leo_size_chart_id');
    }

    public function venue_state()
    {
        return $this->belongsTo(State::class, 'venue_state_id');
    }

    public function admissions()
    {
        return $this->hasMany(MeetAdmission::class);
    }
    public function isWaitlistApproved($ID)
    {
        // return DB::table('',)
        $query = 'SELECT status FROM meet_transactions WHERE meet_registration_id ='.$ID;
        $result = DB::select($query);
        return ( $result == 6 );
    }

    public function categories()
    {
        return $this->belongsToMany(LevelCategory::class, 'category_meet')
            ->using(CategoryMeet::class)
            ->withPivot(CategoryMeet::PIVOT_FIELDS)
            ->withTimestamps();
    }


    public function meetCategories()
    {
        return $this->hasMany(CategoryMeet::class,'meet_id');
    }
    public function deposit()
    {
        return $this->hasMany(Deposit::class,'meet_id');
    }

    public function levels()
    {
        return $this->belongsToMany(AthleteLevel::class, 'level_meet')
            ->using(LevelMeet::class)
            ->withPivot(LevelMeet::PIVOT_FIELDS)
            ->withTimestamps();
    }

    public function activeLevels()
    {
        return $this->belongsToMany(AthleteLevel::class, 'level_meet')
            ->using(LevelMeet::class)
            ->withPivot(LevelMeet::PIVOT_FIELDS)
            ->wherePivot('disabled', false)
            ->withTimestamps();
    }

    public function files()
    {
        return $this->hasMany(MeetFile::class);
    }

    public function registrations()
    {
        return $this->hasMany(MeetRegistration::class);
    }

    public function competition_format()
    {
        return $this->belongsTo(MeetCompetitionFormat::class, 'meet_competition_format_id');
    }

    public function subscription()
    {
        return $this->hasMany(MeetSubscription::class,'meet_id');
    }

    public function getMeetAddressAttribute()
    {
        $address = $this->venue_addr_1;
        if (!empty($this->venue_addr_2) && $this->venue_addr_2 != null) {
            $address = $address . ', ' . $this->venue_addr_2;
        }

        $state = null;
        if(!empty($this->venue_state) && $this->venue_state != null){
            $state =  $this->venue_state->name;
        }

        return $address . ', ' . $this->venue_city . ', ' . ($state != null)?$state.', ':'' . $this->venue_zipcode;
    }

    public function getUsagMeetSanctionsAttribute()
    {
        if (Auth::check()) {
            $user = Auth::user();

            $sanctions = $user->gyms()
                ->where('is_archived', false)
                ->with([
                    'usag_sanctions' => function (Relation $q0) {
                        $q0->where('meet_id', $this->id)
                            ->where('status', USAGSanction::SANCTION_STATUS_PENDING)
                            ->orWhere('status', USAGSanction::SANCTION_STATUS_MERGED);
                    }])->whereHas('usag_sanctions', function (Builder $q0) {
                    $q0->where('meet_id', $this->id)
                        ->where('status', USAGSanction::SANCTION_STATUS_PENDING)
                        ->orWhere('status', USAGSanction::SANCTION_STATUS_MERGED);
                })->get();

            return $sanctions;
        }
    }

    public function usag_sanctions()
    {
        return $this->hasMany(USAGSanction::class);
    }

    public function getUsagReservationsAttribute()
    {
        if (Auth::check()) {
            $usagSanctions = $this->usag_sanctions->pluck('id');
            $usagReservations = USAGReservation::whereIn('usag_sanction_id', $usagSanctions)->get();
            // $usagReservations = USAGReservation::whereIn('usag_sanction_id', $usagSanctions)->exclude(['payload'])->get();

            return $usagReservations;
        }
    }

    public function canBeDeleted()
    {
        return $this->registrations()
                    ->where('status', '!=', MeetRegistration::STATUS_CANCELED)
                    ->count() < 1;
    }

    public function canBeUnpublished()
    {
        return (
            $this->registrations()
                ->where('status', '!=', MeetRegistration::STATUS_CANCELED)
                ->count() < 1
            ) && $this->is_published;
    }

    public function net_fee()
    {
        return $this->late_fee - $this->late_refund;
    }

    public function getSanctionBodiesAttribute()
    {
        if (Auth::check()) {
            $sanctionBodies = [];

            foreach ($this->meetCategories as $category) {
                $sanctionBodies[] = SanctioningBody::SANCTION_BODY[$category->sanctioning_body_id];
            }

            if (count($sanctionBodies) > 0) {
                return array_unique($sanctionBodies);
            }

            return ['--'];
        }
    }

    /**
     * @return string
     */
    public function getFormattedDateAttribute()
    {
        if (Auth::check()) {
            $startDate = Carbon::parse($this->start_date);
            $endDate = Carbon::parse($this->end_date);
            $startOfMonth = Carbon::parse($startDate)->startOfMonth()->format('Y-m-d');
            $endOfMonth = Carbon::parse($endDate)->endOfMonth()->format('Y-m-d');

            if ($startDate == $endDate) {
                return Carbon::parse($this->start_date)->format('F jS Y');
            } elseif ($startDate->format('Y-m-d') == $startOfMonth && $endDate->format('Y-m-d') == $endOfMonth && $startDate->month == $endDate->month) {
                return $startDate->format('F Y');
            } elseif ($startDate->month == $endDate->month) {
                return $endDate->format('F') . '  ' . $startDate->format('jS') . ' - ' . $endDate->format('jS') . ', ' . $endDate->format('Y');
            } elseif ($startDate->month != $endDate->month) {
                return $startDate->format('F jS') . ' - ' . $endDate->format('F jS Y');
            }
        }
    }

    public function getRegistrationStatusAttribute()
    {
        $now = now()->setTime(0, 0);
        if ($this->allow_late_registration) {
            if ($now > $this->late_registration_end_date) {
                return self::REGISTRATION_STATUS_CLOSED;
            } elseif ($now >= $this->late_registration_start_date) {
                return self::REGISTRATION_STATUS_LATE;
            }
        }

        if ($now > $this->registration_end_date) {
            return self::REGISTRATION_STATUS_CLOSED;
        } elseif ($now >= $this->registration_start_date) {
            return self::REGISTRATION_STATUS_OPEN;
        } else {
            return self::REGISTRATION_STATUS_OPENING_SOON;
        }
    }

    public function registrationStatus()
    {
        $now = now()->setTime(0, 0);
        if ($this->allow_late_registration) {
            if ($now > $this->late_registration_end_date) {
                return self::REGISTRATION_STATUS_CLOSED;
            } elseif ($now >= $this->late_registration_start_date) {
                return self::REGISTRATION_STATUS_LATE;
            }
        }

        if ($now > $this->registration_end_date) {
            return self::REGISTRATION_STATUS_CLOSED;
        } elseif ($now >= $this->registration_start_date) {
            return self::REGISTRATION_STATUS_OPEN;
        } else {
            return self::REGISTRATION_STATUS_OPENING_SOON;
        }
    }

    public function isLate() {
        $now = now()->setTime(0, 0);
        return (($this->allow_late_registration) && ($now >= $this->late_registration_start_date));
    }

    public function canBeEdited()
    {
        if (!$this->is_published)
            return true;

        $now = now()->setTime(0, 0);
        $beyondStartDate = ($now > $this->end_date);

        return !($beyondStartDate || $this->is_archived);
    }

    public function editingAbilities()
    {
        $meet = $this; /** @var Meet $meet */
        $registrationStatus = $meet->registrationStatus();
        $canScratch = $meet->canScratch();
        $regularPeriod = ($registrationStatus == Meet::REGISTRATION_STATUS_OPEN);
        $latePeriod = ($registrationStatus == Meet::REGISTRATION_STATUS_LATE);

        return [
            'scratch' => $canScratch,
            'change_details' => $regularPeriod || $latePeriod,
            'change_number' => false,
            'change_level' => $regularPeriod || $latePeriod,
            'add_specialist_events' =>  $regularPeriod || $latePeriod,
        ];
    }

    public function isEditRestricted()
    {
        return (
            $this->registrations()
                ->where('status', '!=', MeetRegistration::STATUS_CANCELED)
                ->count() > 0
        );
    }

    public function hasActiveBodyRegistrations(int $body = null)
    {
        $body = ($body === null ? [
            SanctioningBody::USAG,
            SanctioningBody::USAIGC,
            SanctioningBody::AAU,
            SanctioningBody::NGA,
        ] : $body);

        if (is_array($body)) {
            $result = [
                SanctioningBody::USAG => false,
                SanctioningBody::USAIGC => false,
                SanctioningBody::AAU => false,
                SanctioningBody::NGA => false,
            ];
            foreach ($body as $b)
                $result[$b] = $this->_hasActiveBodyRegistrations($b);

            return $result;
        } else {
            if (
                !in_array($body, [
                    SanctioningBody::USAG,
                    SanctioningBody::USAIGC,
                    SanctioningBody::AAU,
                    SanctioningBody::NGA,
                ])
            )
                throw new CustomBaseException('Invalid sanctioning body.');

            return $this->_hasActiveBodyRegistrations($body);
        }
    }

    public function _hasActiveBodyRegistrations(int $body)
    {
        $result = [];

        $body = SanctioningBody::find($body); /** @var Sanctioningbody $body */
        if ($body === null)
            throw new CustomBaseException('Invalid sanctioning body.');

        $availableCategories = $body->levels()
                                    ->distinct()
                                    ->get('level_category_id');
        foreach ($availableCategories as $c)
            $result[$c->level_category_id] = false;

        $body = $body->id;
        $categories = $this->categories()->wherePivot('sanctioning_body_id', $body)->get();
        foreach ($categories as $c) { /** @var LevelCategory $c */
            $category = $c->id;
            $result[$category] = ($this->registrations()
                ->where('status', '!=', MeetRegistration::STATUS_CANCELED)
                ->where(function (Builder $whereQuery) use ($body, $category) {
                    $whereQuery->whereHas('athletes', function (Builder $query) use ($body, $category) {
                        $query->whereHas('registration_level', function (Builder $query2) use ($body, $category) {
                            $query2->whereHas('level', function (Builder $query3) use ($body, $category) {
                                $query3->where('sanctioning_body_id', $body)
                                        ->where('level_category_id', $category);
                            });
                        });
                    })->orWhereHas('specialists', function (Builder $query) use ($body, $category) {
                        $query->whereHas('registration_level', function (Builder $query2) use ($body, $category) {
                            $query2->whereHas('level', function (Builder $query3) use ($body, $category) {
                                $query3->where('sanctioning_body_id', $body)
                                        ->where('level_category_id', $category);
                            });
                        });
                    });
                })->count() > 0);
        }

        return $result;
    }

    public function hasActiveLevelRegistrations(int $level)
    {
        $result = 0;

        $level = AthleteLevel::find($level); /** @var AthleteLevel $level */
        if ($level === null)
            throw new CustomBaseException('Invalid level.');

        $level = $level->id;
        return (
            $this->registrations()
                ->where('status', '!=', MeetRegistration::STATUS_CANCELED)
                ->where(function (Builder $whereQuery) use ($level) {
                    $whereQuery->whereHas('athletes', function (Builder $query) use ($level) {
                        $query->whereHas('registration_level', function (Builder $query2) use ($level) {
                            $query2->where('level_id', $level);
                        });
                    })->orWhereHas('specialists', function (Builder $query) use ($level) {
                        $query->whereHas('registration_level', function (Builder $query2) use ($level) {
                            $query2->where('level_id', $level);
                        });
                    });
                })->count() > 0
        );
    }

    public function getUsedSlots()
    {
        $slots = [];
        $total = 0;
        foreach($this->levels as $level) {
            $gender = 'both';
            $query = '
                select
                    count (*)
                from
                    registration_athletes, level_registration, meet_registrations
                where
                    registration_athletes.meet_registration_id = meet_registrations.id and
                    level_registration.meet_registration_id = meet_registrations.id and
                    registration_athletes.level_registration_id = level_registration.id and
                    level_registration.level_id = ' . $level->id . ' and
                    meet_registrations.status = ' . MeetRegistration::STATUS_REGISTERED . ' and
                    meet_registrations.meet_id = ' . $this->id . ' and
                    (registration_athletes.status = ' . RegistrationAthlete::STATUS_REGISTERED . ' or
                    registration_athletes.status = ' . RegistrationAthlete::STATUS_PENDING_RESERVED . ')';

            if (!($level->pivot->allow_men && $level->pivot->allow_women)) {
                $gender = ($level->pivot->allow_men ? 'male' : 'female');
                $query .= ' and registration_athletes.gender = \'' . $gender . '\'';
            }

            $slots[$level->id][$gender]['count'] = DB::select($query)[0]->count;
            $slots[$level->id][$gender]['limit'] = ($level->pivot->enable_athlete_limit ? $level->pivot->athlete_limit : null);

            $total += $slots[$level->id][$gender]['count'];
        }

        $slots['total'] = $total;
        return $slots;
    }

    public function isWaitList() {
        $slots = $this->getUsedSlots();
        $now = now()->setTime(0, 0);

        return
            (
                ($this->registrationStatus() == self::REGISTRATION_STATUS_CLOSED) &&
                ($now < $this->start_date)
            ) ||
            (
                ($this->athlete_limit !== null) &&
                ($slots['total'] >= $this->athlete_limit)
            );
    }

    public function canScratch()
    {
        $now = now()->setTime(0, 0);
        return ($now >= $this->registration_start_date) &&
            ($now <= $this->registration_scratch_end_date);
    }

    public function getUserRegistrations(User $user) {
        return $this->registrations()
                ->whereIn('gym_id', $user->gyms()->pluck('id')->toArray())
                ->where('status', '!=', MeetRegistration::STATUS_CANCELED)
                ->get();
    }

    public static function getSingleFileRules() {
        return TemporaryMeet::getSingleFileRules();
    }

    public static function getStepFourRules()
    {
        return TemporaryMeet::getStepFourRules();
    }

    public static function getProfilePictureRules()
    {
        $rules = self::PROFILE_PICTURE_RULES;
        $rules['meet_picture'][] = 'max:' . Setting::profilePictureMaxSize();
        return $rules;
    }

    public function storeProfilePicture(UploadedFile $profilePicture) : bool
    {
        $old = $this->profile_picture;
        $this->profile_picture = Storage::url(Storage::putFile('public/images/meet', $profilePicture));
        Helper::removeOldFile($old, config('app.default_meet_picture'));
        return $this->save();
    }

    public function clearProfilePicture() : bool
    {
        $default = config('app.default_meet_picture');
        $old = $this->profile_picture;
        $this->profile_picture = $default;
        Helper::removeOldFile($old, $default);
        return $this->save();
    }

    public function oldOrValue(string $attr)
    {
        $result = null;

        $old = old($attr);
        if ($old !== null)
            $result = $old;
        else {
            $result = $this[$attr];
        }

        return $result;
    }

    public function toggleArchived(bool $archived)
    {
        DB::beginTransaction();

        try {
            if (!($archived xor $this->is_archived))
                throw new CustomBaseException(
                    'This meets is ' . ($this->is_archived ? 'already' : 'not') . ' archived.', -1
                );

            $this->is_archived = $archived;
            $this->save();

            AuditEvent::meetArchivalStatusChanged(request()->_managed_account, auth()->user(), $this, $archived);
            DB::commit();
            return true;
        } catch(\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }
    public function closeMeet()
    {
        DB::beginTransaction();

        try {
            $this->registration_end_date = date('Y-m-d',strtotime("yesterday"));
            $this->save();
            DB::commit();
            return true;
        } catch(\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function togglePublished(bool $publish)
    {
        DB::beginTransaction();

        try {

            switch ($publish) {
                case true:
                    if ($this->is_published)
                        throw new CustomBaseException('This meet is already publisehd', -1);
                    break;

                default:
                    if (!$this->is_published)
                        throw new CustomBaseException('This meet is already unpublisehd', -1);

                    if (!$this->canBeUnpublished())
                        throw new CustomBaseException('This meet cannot be unpublished', -1);
                    break;
            }

            $this->is_published = $publish;
            $this->save();

            AuditEvent::meetPublishingStatusChanged(request()->_managed_account, auth()->user(), $this, $publish);
            DB::commit();
            return true;
        } catch(\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function handling_fee()
    {
        if ($this->handling_fee_override !== null) {
            $this->handling_fee_override;
        } else if ($this->gym->handling_fee_override !== null) {
            $this->gym->handling_fee_override;
        } else if ($this->gym->user->handling_fee_override !== null) {
            $this->gym->user->handling_fee_override;
        } else {
            return Setting::feeHandling();
        }
    }

    public function balance_fee()
    {
        return 0; // or Setting::feeBalance();. Balance fee is not used.
    }

    public function cc_fee()
    {
        if ($this->cc_fee_override !== null) {
            $this->cc_fee_override;
        } else if ($this->gym->cc_fee_override !== null) {
            $this->gym->cc_fee_override;
        } else if ($this->gym->user->cc_fee_override !== null) {
            $this->gym->user->cc_fee_override;
        } else {
            return Setting::feeCC();
        }
    }

    public function paypal_fee()
    {
        if ($this->paypal_fee_override !== null) {
            $this->paypal_fee_override;
        } else if ($this->gym->paypal_fee_override !== null) {
            $this->gym->paypal_fee_override;
        } else if ($this->gym->user->paypal_fee_override !== null) {
            $this->gym->user->paypal_fee_override;
        } else {
            return Setting::feePayPal();
        }
    }

    public function ach_fee()
    {
        if ($this->ach_fee_override !== null) {
            $this->ach_fee_override;
        } else if ($this->gym->ach_fee_override !== null) {
            $this->gym->ach_fee_override;
        } else if ($this->gym->user->ach_fee_override !== null) {
            $this->gym->user->ach_fee_override;
        } else {
            return Setting::feeACH();
        }
    }

    public function check_fee()
    {
        if ($this->check_fee_override !== null) {
            $this->check_fee_override;
        } else if ($this->gym->check_fee_override !== null) {
            $this->gym->check_fee_override;
        } else if ($this->gym->user->check_fee_override !== null) {
            $this->gym->user->check_fee_override;
        } else {
            return Setting::feeCheck();
        }
    }

    public static function retrieveMeet(string $id, bool $archived = false) : Meet
    {
        $meet = self::where('id', $id)->first();
        if ($meet == null)
            throw new CustomBaseException(
                'There is no such meet' ,
                -1
            );

        if (!$archived && $meet->is_archived)
            throw new CustomBaseException('You cannot access archived meets', -1);

        return $meet;
    }

    public function updateStepOne(array $attr)
    {
        DB::beginTransaction();

        try {

            $start_date = (new \DateTime($attr['start_date']))->setTime(0, 0);
            $end_date = (new \DateTime($attr['end_date']))->setTime(0, 0);
            $tshirtChart = null;
            $leoChart = null;
            $state = null;

            if ($this->registration_end_date >= $this->start_date)
                throw new CustomBaseException('The meet start date should be after the registration end date.');

            if ($this->registration_scratch_end_date > $this->start_date)
                throw new CustomBaseException('The scratch date should be before or equal to the meet start date.');

            if (($this->allow_late_registration) && ($this->late_registration_end_date >= $this->start_date))
                throw new CustomBaseException('The meet start date should be after the late registration end date.');

            $state = State::where('code', $attr['venue_state_id'])->first();
            if (($state == null) || ($state->code == 'WW'))
                throw new CustomBaseException('No such state.', '-1');
            $state = $state->id;

            if (isset($attr['tshirt_size_chart_id'])) {
                $tshirtChart = ClothingSizeChart::find($attr['tshirt_size_chart_id']);
                if (($tshirtChart == null) || $tshirtChart->is_leo)
                    throw new CustomBaseException('No such T-Shirt chart.', '-1');
                $tshirtChart= $tshirtChart->id;
            }

            if (isset($attr['leo_size_chart_id'])) {
                $leoChart = ClothingSizeChart::find($attr['leo_size_chart_id']);
                if (($leoChart == null) || !$leoChart->is_leo)
                    throw new CustomBaseException('No such Leotard chart.', '-1');
                $leoChart = $leoChart->id;
            }

            $oldAdmissions = [];
            foreach ($this->admissions as $oldAdmission) {
                $oldAdmissions[] = [
                    'name' => $oldAdmission->name,
                    'type' => $oldAdmission->type,
                    'amount' => (float) $oldAdmission->amount
                ];
            }

            $oldAdmissionsJson = json_encode($oldAdmissions);
            if ($oldAdmissions === false)
                throw new CustomBaseException('Server error', ErrorCodeCategory::getCategoryBase('General') + 1);

            $newAdmissions = [];
            $admissions = json_decode($attr['admissions']);
            if ($admissions === null)
                throw new CustomBaseException('Server error', ErrorCodeCategory::getCategoryBase('General') + 1);

            if (!(is_array($admissions) && (count($admissions) > 0)))
                throw new CustomBaseException('At least one admissions should be specified.', '-1');

            foreach ($admissions as $admission) {
                $nameLen = strlen($admission->name);

                if (!(isset($admission->name) && ($nameLen > 0) && ($nameLen < 256)))
                    throw new CustomBaseException('Invalid admission name `' . $admission->name . '`.', '-1');
                $admission->name = Helper::title($admission->name);

                if (!(isset($admission->type) && Helper::isInteger($admission->type)))
                    throw new CustomBaseException('Invalid admission type `' . $admission->type . '`.', '-1');
                $admission->type = (int) $admission->type;

                if ($admission->type == MeetAdmission::TYPE_PAID) {
                    if (!(isset($admission->amount) && Helper::isFloat($admission->amount)))
                    throw new CustomBaseException('Invalid admission amount `' . $admission->amount .'`.', '-1');
                        $admission->amount = (float) $admission->amount;
                } else {
                    $admission->amount = (float) 0;
                }

                $newAdmission = [
                    'name' => $admission->name,
                    'type' => $admission->type,
                    'amount' => $admission->amount,
                ];

                $newAdmissions[] = $newAdmission;
            }

            $newAdmissionsJson = json_encode($newAdmissions);
            if ($newAdmissions === false)
                throw new CustomBaseException('Server error', ErrorCodeCategory::getCategoryBase('General') + 1);

            $old = [
                'name' => $this->name,
                'description' => $this->description,
                'start_date' => $this->start_date,
                'end_date' => $this->end_date,
                'website' => $this->website,
                'equipement' => $this->equipement,
                'notes' => $this->notes,
                'special_annoucements' => $this->special_annoucements,
                'tshirt_size_chart_id' => $this->tshirt_size_chart_id,
                'leo_size_chart_id' => $this->leo_size_chart_id,
                'admissions' => $oldAdmissionsJson,
                'mso_meet_id' => $this->mso_meet_id,
                'venue_name' => $this->venue_name,
                'venue_addr_1' => $this->venue_addr_1,
                'venue_addr_2' => $this->venue_addr_2,
                'venue_city' => $this->venue_city,
                'venue_state_id' => $this->venue_state_id,
                'venue_zipcode' => $this->venue_zipcode,
                'venue_website' => $this->venue_website,
                'show_participate_clubs' => $this->show_participate_clubs,
            ];

            $newData = [
                'name' => $attr['name'],
                'description' => $attr['description'],
                'start_date' => $start_date,
                'end_date' => $end_date,
                'website' => ($this->isEditRestricted() ? $this->website : $attr['website']),
                'equipement' => $attr['equipement'],
                'notes' => $attr['notes'],
                'special_annoucements' => $attr['special_annoucements'],
                'tshirt_size_chart_id' => ($this->isEditRestricted() ? $this->tshirt_size_chart_id : $tshirtChart),
                'leo_size_chart_id' => ($this->isEditRestricted() ? $this->leo_size_chart_id : $leoChart),
                'mso_meet_id' => (isset($attr['mso_meet_id']) ? $attr['mso_meet_id'] : null),
                'venue_name' => $attr['venue_name'],
                'venue_addr_1' => $attr['venue_addr_1'],
                'venue_addr_2' => $attr['venue_addr_2'],
                'venue_city' => $attr['venue_city'],
                'venue_state_id' => $state,
                'venue_zipcode' => $attr['venue_zipcode'],
                'venue_website' => $attr['venue_website'],
                'show_participate_clubs' => isset($attr['show_participate']) ? true : false,
            ];

            $new = $newData + ['admissions' => $newAdmissionsJson];

            $diff = AuditEvent::attributeDiff($old, $new);
            if (count($diff) < 1)
                return true;

            $this->admissions()->delete();
            foreach ($newAdmissions as $admission)
                $this->admissions()->create($admission);

            $this->update($newData);
            $this->save();

            AuditEvent::meetUpdated(
                request()->_managed_account, auth()->user(), $this, $diff
            );
            DB::commit();
            return true;
        } catch(\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }
    public function updateStepSix(array $attr)
    {
        DB::beginTransaction();
        try {

            $today = now()->setTime(0, 0);

            $deposit_ratio = null;

            $accept_paypal = false; //hide paypal payment option.
            $accept_ach = true; //cc and ach default payment decision on 15-3-21, so here ach value set true.
            $accept_mailed_check = isset($attr['accept_mailed_check']);
            $accept_deposit = isset($attr['accept_deposit']);

            $defer_handling_fees = isset($attr['defer_handling_fees']);
            $defer_processor_fees = isset($attr['defer_processor_fees']);

            if ($accept_deposit) {
                if (!isset($attr['deposit_ratio']))
                    throw new CustomBaseException('Please provide deposit ratio', '-1');
                $deposit_ratio = $attr['deposit_ratio'];
                if (!Helper::isInteger($deposit_ratio))
                    throw new CustomBaseException('Invalid deposit ratio value.', '-1');
                $deposit_ratio = (int) $deposit_ratio;

                if ($deposit_ratio < 1)
                    throw new CustomBaseException('Deposit ratio must be greater than 0.', '-1');
            }

            $old = [
               
                'accept_paypal' => $this->accept_paypal,
                'accept_ach' => $this->accept_ach,
                'accept_mailed_check' => $this->accept_mailed_check,
                'accept_deposit' => $this->accept_deposit,
                'deposit_ratio' => $this->deposit_ratio,
                
                'mailed_check_instructions' => $this->mailed_check_instructions,
                'defer_handling_fees' => $this->defer_handling_fees,
                'defer_processor_fees' => $this->defer_processor_fees,
            ];

            $new = [
                
                'accept_paypal' => $accept_paypal,
                'accept_ach' => $accept_ach,
                'accept_mailed_check' => $accept_mailed_check,
                'accept_deposit' => $accept_deposit,
                'deposit_ratio' => ($deposit_ratio != null ? $deposit_ratio : 0),
                'mailed_check_instructions' => isset($attr['mailed_check_instructions']) ? $attr['mailed_check_instructions'] : null,
                'defer_handling_fees' => ($this->isEditRestricted() ? $this->defer_handling_fees : $defer_handling_fees),
                'defer_processor_fees' => ($this->isEditRestricted() ? $this->defer_processor_fees : $defer_processor_fees),
            ];

            $diff = AuditEvent::attributeDiff($old, $new);
            if (count($diff) < 1)
                return true;

            AuditEvent::meetUpdated(
                request()->_managed_account, auth()->user(), $this, $diff
            );
            
            $this->update($new);
            $this->save();
            DB::commit();
            return true;
        } catch(\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }
    public function updateStepTwo(array $attr)
    {
        DB::beginTransaction();

        try {

            $today = now()->setTime(0, 0);


            $registration_start_date = (new \DateTime($attr['registration_start_date']))->setTime(0, 0);
            if ($this->isEditRestricted())
                $registration_start_date = $this->registration_start_date;

            if ($this->registration_start_date != $registration_start_date) {
                if ($registration_start_date < $today)
                    throw new CustomBaseException('The registration start date must be a date after today.');
            }

            $registration_end_date = (new \DateTime($attr['registration_end_date']))->setTime(0, 0);
            $scratch_date = (new \DateTime($attr['registration_scratch_end_date']))->setTime(0, 0);

            if ($registration_end_date >= $this->start_date)
                throw new CustomBaseException('The meet start date should be after the registration end date.');

            if ($this->registration_scratch_end_date != $scratch_date) {
                if ($scratch_date <= $today)
                    throw new CustomBaseException('The registration scratch date must be a date after today.');
            }

            if ($scratch_date > $this->start_date)
                throw new CustomBaseException('The scratch date should be before or equal to the meet start date.');

            $allow_late = isset($attr['allow_late_registration']);
            $late_fee = null;
            $late_registration_start_date = null;
            $late_registration_end_date = null;

            $athlete_limit = null;
            $deposit_ratio = null;

            if ($allow_late) {
                if (!Helper::isFloat($attr['late_registration_fee']))
                    throw new CustomBaseException('Invalid late registration fee value.', '-1');
                $late_fee = (float) $attr['late_registration_fee'];

                $late_registration_start_date = (new \DateTime($attr['late_registration_start_date']))->setTime(0, 0);
                $late_registration_end_date = (new \DateTime($attr['late_registration_end_date']))->setTime(0, 0);

                if ($late_registration_end_date >= $this->start_date)
                    throw new CustomBaseException('The meet start date should be after the late registration end date.');
            }

            if (isset($attr['athlete_limit'])) {
                if (!Helper::isInteger($attr['athlete_limit']))
                    throw new CustomBaseException('Invalid athlete limit value.', '-1');
                $athlete_limit = (int) $attr['athlete_limit'];

                if ($athlete_limit < 1)
                    throw new CustomBaseException('Invalid athlete limit value.', '-1');
            }

            $first_end_date = null;
            $first_discount = null;
            $second_end_date = null;
            $second_discount = null;
            $third_end_date = null;
            $third_discount = null;
            $registration_first_discount_is_enable = isset($attr['registration_first_discount_is_enable']);
            $registration_second_discount_is_enable = isset($attr['registration_second_discount_is_enable']);
            $registration_third_discount_is_enable = isset($attr['registration_third_discount_is_enable']);

            if($registration_first_discount_is_enable)
            {
                $first_end_date = (new \DateTime($attr['registration_first_discount_end_date']))->setTime(0, 0);
                $first_discount = $attr['registration_first_discount_amount'];
                if (!Helper::isFloat($first_discount) && $first_discount <= 0)
                    throw new CustomBaseException('Invalid Discount value ', '-1');
                $first_discount = (float) $first_discount;
                if ($first_end_date < $registration_start_date)
                    throw new CustomBaseException('The first discount end date should be after or equal the registration start date.');

                if($registration_second_discount_is_enable)
                {
                    $second_end_date = (new \DateTime($attr['registration_second_discount_end_date']))->setTime(0, 0);
                    $second_discount = $attr['registration_second_discount_amount'];
                    if (!Helper::isFloat($second_discount) && $second_discount <= 0)
                        throw new CustomBaseException('Invalid Discount value ', '-1');
                    $second_discount = (float) $second_discount;


                    if ($second_end_date <= $first_end_date)
                        throw new CustomBaseException('The second discount end date should be after the first discount end date.');

                    if($registration_third_discount_is_enable)
                    {
                        $third_end_date = (new \DateTime($attr['registration_third_discount_end_date']))->setTime(0, 0);
                        $third_discount = $attr['registration_third_discount_amount'];
                        if (!Helper::isFloat($third_discount) && $third_discount <= 0)
                            throw new CustomBaseException('Invalid Discount value ', '-1');
                        $third_discount = (float) $third_discount;
                        if ($third_end_date > $late_registration_start_date && $allow_late)
                            throw new CustomBaseException('The third discount end date should be before the late registration start date. ');
                    }
                }
            }

            $old = [
                'registration_start_date' => $this->registration_start_date,
                'registration_end_date' => $this->registration_end_date,
                'registration_scratch_end_date' => $this->registration_scratch_end_date,
                'allow_late_registration' => $this->allow_late_registration,
                'late_registration_fee' => $this->late_registration_fee,
                'late_registration_start_date' => $this->late_registration_start_date,
                'late_registration_end_date' => $this->late_registration_end_date,
                'athlete_limit' => $this->athlete_limit,
                
                'registration_first_discount_end_date' => $this->registration_first_discount_end_date,
                'registration_first_discount_amount' => $this->registration_first_discount_amount,
                'registration_second_discount_end_date' => $this->registration_second_discount_end_date,
                'registration_second_discount_amount' => $this->registration_second_discount_amount,
                'registration_third_discount_end_date' => $this->registration_third_discount_end_date,
                'registration_third_discount_amount' => $this->registration_third_discount_amount,

                'registration_first_discount_is_enable' => $this->registration_first_discount_is_enable,
                'registration_second_discount_is_enable' => $this->registration_second_discount_is_enable,
                'registration_third_discount_is_enable' => $this->registration_third_discount_is_enable,
            ];

            $new = [
                'registration_start_date' => $registration_start_date,
                'registration_end_date' => $registration_end_date,
                'registration_scratch_end_date' => $scratch_date,
                'allow_late_registration' => $allow_late,
                'late_registration_fee' => ($late_fee != null ? $late_fee : 0),
                'late_registration_start_date' => $late_registration_start_date,
                'late_registration_end_date' => $late_registration_end_date,
                'athlete_limit' => $athlete_limit,
                
                'registration_first_discount_end_date' => $first_end_date,
                'registration_second_discount_end_date' => $second_end_date,
                'registration_third_discount_end_date' => $third_end_date,
        
                'registration_first_discount_amount' => $first_discount,
                'registration_second_discount_amount' => $second_discount,
                'registration_third_discount_amount' => $third_discount,

                'registration_first_discount_is_enable' => $registration_first_discount_is_enable,
                'registration_second_discount_is_enable' => $registration_second_discount_is_enable,
                'registration_third_discount_is_enable' => $registration_third_discount_is_enable,
            ];

            $diff = AuditEvent::attributeDiff($old, $new);
            if (count($diff) < 1)
                return true;

            AuditEvent::meetUpdated(
                request()->_managed_account, auth()->user(), $this, $diff
            );
            
            $this->update($new);
            $this->save();
            DB::commit();
            return true;
        } catch(\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function updateStepThree(array $attr)
    {
        DB::beginTransaction();
        try {
            $sb_ar = [];
            foreach ($attr['sanction_body_no'] as $key => $value) {
                foreach ($value as $k => $v) {
                    $v = trim($v);
                    if($v != "" || $v != null)
                    {
                        $sb_ar[$key][$k] = $v;
                    }
                }
            }
            $restrictedBodies = $this->hasActiveBodyRegistrations();

            $competition_format = MeetCompetitionFormat::find($attr['meet_competition_format_id']);
            if ($competition_format === null)
                throw new CustomBaseException('No such competition format', -1);

            $categories = json_decode($attr['categories']);
            if ($categories === null)
                throw new CustomBaseException('Server error', ErrorCodeCategory::getCategoryBase('General') + 1);

            if (!(is_array($categories) && (count($categories) > 0)))
                throw new CustomBaseException('At least one category should be specified.', '-1');

            $oldCategories = [];
            foreach ($this->categories as $oldCategory) { /** @var LevelCategory $oldCategory */
                $oldCategories[] = [
                    'id' => $oldCategory->id,
                    'body_id' => $oldCategory->pivot->sanctioning_body_id,
                    'sanction' => $oldCategory->pivot->sanction_no,
                    'officially_sanctioned' => $oldCategory->pivot->officially_sanctioned,
                    'requires_sanction' => $oldCategory->pivot->requiresSanction(),
                ];
            }
            $oldCategoriesJson = json_encode($oldCategories);
            if ($oldCategoriesJson === false)
                throw new CustomBaseException('Server error', ErrorCodeCategory::getCategoryBase('General') + 1);

            $newCategories = [];
            foreach ($categories as $categoryData) {
                if (!isset($categoryData->id))
                    throw new CustomBaseException('Missing category data (id).', '-1');
                $category = LevelCategory::find($categoryData->id);
                if ($category == null)
                    throw new CustomBaseException('Wrong category data (id).', '-1');

                if (!isset($categoryData->body_id))
                    throw new CustomBaseException('Missing category data (body id).', '-1');
                $body = SanctioningBody::find($categoryData->body_id);
                if ($body == null)
                    throw new CustomBaseException('Wrong category data (body id).', '-1');

                $categoryData->id = $category->id;
                $categoryData->body_id = $body->id;
                // $categoryData->sanction = null;
                $categoryData->sanction = (isset($sb_ar[$body->id]) && isset($sb_ar[$body->id][$category->id])) ? $sb_ar[$body->id][$category->id] : null;
                $categoryData->officially_sanctioned = false;
                $categoryData->requires_sanction = LevelCategory::requiresSanction($categoryData->body_id);
                $flag = false;
                foreach ($oldCategories as $key => $oldCategory) {
                    if (
                        ($oldCategory['id'] == $categoryData->id) &&
                        ($oldCategory['body_id'] == $categoryData->body_id) &&
                        ($oldCategory['sanction'] == $categoryData->sanction)
                    ) {
                        $flag = true;
                        unset($oldCategories[$key]);
                    }
                }
                if (!$flag) {
                    $newCategories[] = [
                        'id' => $categoryData->id,
                        'body_id' => $categoryData->body_id,
                        'sanction' => $categoryData->sanction,
                        'officially_sanctioned' => $categoryData->officially_sanctioned,
                        'requires_sanction' => $categoryData->requires_sanction,
                    ];
                }
            }

            $newCategoriesJson = json_encode($categories);
            if ($newCategoriesJson === false)
                throw new CustomBaseException('Server error', ErrorCodeCategory::getCategoryBase('General') + 1);

            $categoriesChanged = (count($oldCategories) > 0) || (count($newCategories) > 0);
            if ($categoriesChanged) {
                foreach ($oldCategories as $category) {
                    if ($restrictedBodies[$category['body_id']][$category['id']])
                        throw new CustomBaseException('You are not allowed to remove categories that have registrations.', -1);

                    if($category['requires_sanction']) {
                        if($category['officially_sanctioned'])
                            throw new CustomBaseException('You are not allowed to remove categories that have been assigned a sanction.', -1);
                    }

                    $matches = CategoryMeet::where('meet_id', $this->id)
                                    ->where('level_category_id', $category['id'])
                                    ->where('sanctioning_body_id', $category['body_id'])
                                    ->delete();
                }

                foreach ($newCategories as $category) {
                    CategoryMeet::create([
                        'meet_id' => $this->id,
                        'level_category_id' => $category['id'],
                        'sanction_no' => $category['sanction'],
                        'sanctioning_body_id' => $category['body_id'],
                        'officially_sanctioned' => $category['officially_sanctioned'],
                    ]);
                }
            }


            $this->load('categories');
            $levels_required = false;
            foreach ($this->categories as $c) { /** @var LevelCategory $c */
                $p =  $c->pivot; /** @var CategoryMeet $p */
                if (!$p->requiresSanction() || $p->officially_sanctioned)
                    $levels_required = true;
            }

            $levelFields = [
                'male', 'female', 'registration_fee', 'late_registration_fee', 'allow_specialist',
                'specialist_registration_fee', 'specialist_late_registration_fee', 'allow_team',
                'team_registration_fee', 'team_late_registration_fee', 'enable_athlete_limit',
                'athlete_limit', 'registration_fee_first'
            ];
            $levelGenderMatrix = [];
            $levels = json_decode($attr['levels']);
            if ($levels === null)
                throw new CustomBaseException('Server error', ErrorCodeCategory::getCategoryBase('General') + 1);

            if (!is_array($levels))
                throw new CustomBaseException('Invalid levels data format.', '-1');

            if ($levels_required && !(count($levels) > 0))
                throw new CustomBaseException('At least one level should be specified.', '-1');

            $oldLevels = [];

            $this->load('activeLevels');
            foreach ($this->activeLevels as $level) {
                $oldLevels[] = [
                    'id' => $level->id,
                    'male' => $level->pivot->allow_men,
                    'female' => $level->pivot->allow_women,
                    'registration_fee' => (float) $level->pivot->registration_fee,
                    'late_registration_fee' => (float) $level->pivot->late_registration_fee,
                    'allow_specialist' => $level->pivot->allow_specialist,
                    'specialist_registration_fee' => (float) $level->pivot->specialist_registration_fee,
                    'specialist_late_registration_fee' => (float) $level->pivot->specialist_late_registration_fee,
                    'allow_teams' => $level->pivot->allow_teams,
                    'team_registration_fee' => (float) $level->pivot->team_registration_fee,
                    'team_late_registration_fee' => (float) $level->pivot->team_late_registration_fee,
                    'enable_athlete_limit' => $level->pivot->enable_athlete_limit,
                    'athlete_limit' => (int) $level->pivot->athlete_limit,

                    'registration_fee_first' => (float) $level->pivot->registration_fee_first, 
                    // 'registration_fee_second' => (float) $level->pivot->registration_fee_second,
                    // 'registration_fee_third' => (float) $level->pivot->registration_fee_third
                ];
            }
            $oldLevelsJson = json_encode($oldLevels);
            if ($oldLevelsJson === false)
                throw new CustomBaseException('Server error', ErrorCodeCategory::getCategoryBase('General') + 1);

            $newLevels = [];
            $oldMeetLevelsToKeep = [];
            // print_r($this->first_discount_enable);
            // print_r($levels); 
            // die();
            foreach ($levels as $levelData) {
                if (!isset($levelData->id))
                    throw new CustomBaseException('Missing level data (id).', '-1');

                $level = AthleteLevel::find($levelData->id);
                if ($level == null)
                    throw new CustomBaseException('Wrong level data (id).', '-1');

                foreach ($levelFields as $field) {
                    if (!isset($levelData->$field))
                        throw new CustomBaseException('Missing level data (' . $field . ').', '-1');
                }

                if ($level->level_category->male && $level->level_category->female) {
                    if (!($levelData->male || $levelData->female))
                        throw new CustomBaseException('Wrong level data : at least one gender should be allowed', -1);
                } else {
                    $levelData->male = $level->level_category->male;
                    $levelData->female = $level->level_category->female;
                }

                if (key_exists($level->id, $levelGenderMatrix)) {
                    $duplicate = $levelGenderMatrix[$level->id];
                    if (($levelData->male && $duplicate['male']) ||
                        ($levelData->female && $duplicate['female']))
                    throw new CustomBaseException('Wrong level data : duplicate levels', -1);
                };

                if(!Helper::isFloat($levelData->registration_fee))
                    throw new CustomBaseException('Wrong level data : invalid registration fee', -1);
                $levelData->registration_fee = (float) $levelData->registration_fee;

                if($this->registration_first_discount_is_enable)
                {
                    if(!Helper::isFloat($levelData->registration_fee_first))
                        throw new CustomBaseException('Wrong level data : invalid early registration fee', -1);
                    $levelData->registration_fee_first = (float) $levelData->registration_fee_first;
                }
                else{
                    $levelData->registration_fee_first = 0;
                }
                // if($this->registration_second_discount_is_enable)
                // {
                //     if(!Helper::isFloat($levelData->registration_fee_second))
                //         throw new CustomBaseException('Wrong level data : invalid second registration fee', -1);
                //     $levelData->registration_fee_second = (float) $levelData->registration_fee_second;
                // }
                // else{
                //     $levelData->registration_fee_second = 0;
                // }
                // if($this->registration_third_discount_is_enable)
                // {
                //     if(!Helper::isFloat($levelData->registration_fee_third))
                //         throw new CustomBaseException('Wrong level data : invalid first registration fee', -1);
                //     $levelData->registration_fee_third = (float) $levelData->registration_fee_third;
                // }
                // else{
                //     $levelData->registration_fee_third = 0;
                // }

                if ($this->allow_late_registration) {
                    if(!Helper::isFloat($levelData->late_registration_fee))
                        throw new CustomBaseException('Wrong level data : invalid late registration fee', -1);
                    $levelData->late_registration_fee = (float) $levelData->late_registration_fee;
                } else {
                    $levelData->late_registration_fee = 0;
                }

                if ($level->hasSpecialist() && $levelData->allow_specialist) {
                    if(!Helper::isFloat($levelData->specialist_registration_fee))
                        throw new CustomBaseException('Wrong level data : invalid specialist registration fee', -1);
                    $levelData->specialist_registration_fee = (float) $levelData->specialist_registration_fee;

                    if ($this->allow_late_registration) {
                        if (!Helper::isFloat($levelData->specialist_late_registration_fee))
                            throw new CustomBaseException('Wrong level data : invalid specialist late registration fee', -1);
                        $levelData->specialist_late_registration_fee = (float) $levelData->specialist_late_registration_fee;
                    } else {
                        $levelData->specialist_late_registration_fee = 0;
                    }
                } else {
                    $levelData->allow_specialist = false;
                    $levelData->specialist_registration_fee = 0;
                    $levelData->specialist_late_registration_fee = 0;
                }

                if ($levelData->allow_team) {
                    if(!Helper::isFloat($levelData->team_registration_fee))
                        throw new CustomBaseException('Wrong level data : invalid team registration fee', -1);
                    $levelData->team_registration_fee = (float) $levelData->team_registration_fee;

                    if ($this->allow_late_registration) {
                        if(!Helper::isFloat($levelData->team_late_registration_fee))
                            throw new CustomBaseException('Wrong level data : invalid team late registration fee', -1);
                        $levelData->team_late_registration_fee = (float) $levelData->team_late_registration_fee;
                    } else {
                        $levelData->team_late_registration_fee = 0;
                    }
                } else {
                    $levelData->allow_team = false;
                    $levelData->team_registration_fee = 0;
                    $levelData->team_late_registration_fee = 0;
                }

                if ($levelData->enable_athlete_limit) {
                    if(!(Helper::isInteger($levelData->athlete_limit) && ((int) $levelData->athlete_limit > 0)))
                        throw new CustomBaseException('Wrong level data : invalid athlete limit', -1);
                    $levelData->athlete_limit = (int) $levelData->athlete_limit;
                } else {
                    $levelData->enable_athlete_limit = false;
                    $levelData->athlete_limit = 0;
                }

                $levelGenderMatrix[$level->id] = [
                    'male' => $levelData->male,
                    'female' => $levelData->female,
                ];

                // Find if any existing levels match, make updates
                $oldLevel = $this->levels()->where('athlete_level_id', $levelData->id)
                    ->wherePivot('allow_men', $levelData->male)
                    ->wherePivot('allow_women', $levelData->female)
                    ->first(); /** @var AthleteLevel $oldLevel */
                    

                if ($oldLevel !== null) {
                    $hasChanges =
                        ($oldLevel->pivot->registration_fee != $levelData->registration_fee) ||

                        ($oldLevel->pivot->registration_fee_first != $levelData->registration_fee_first) ||
                        ($oldLevel->pivot->late_registration_fee != $levelData->late_registration_fee) ||

                        ($oldLevel->pivot->allow_specialist != $levelData->allow_specialist) ||
                        ($oldLevel->pivot->specialist_registration_fee != $levelData->specialist_registration_fee) ||
                        ($oldLevel->pivot->specialist_late_registration_fee != $levelData->specialist_late_registration_fee) ||

                        ($oldLevel->pivot->allow_teams != $levelData->allow_team) ||
                        ($oldLevel->pivot->team_registration_fee != $levelData->team_registration_fee) ||
                        ($oldLevel->pivot->team_late_registration_fee != $levelData->team_late_registration_fee) ||

                        ($oldLevel->pivot->enable_athlete_limit != $levelData->enable_athlete_limit) ||
                        ($oldLevel->pivot->athlete_limit != $levelData->athlete_limit);

                    if ($hasChanges && ($restrictedBodies[$oldLevel->sanctioning_body_id][$oldLevel->level_category_id]))
                        throw new CustomBaseException('You are not allowed to make changes to levels that have registrations.', -1);

                    $oldLevel->pivot->registration_fee = $levelData->registration_fee;

                    $oldLevel->pivot->registration_fee_first = $levelData->registration_fee_first;
                    // $oldLevel->pivot->registration_fee_second = $levelData->registration_fee_second;
                    // $oldLevel->pivot->registration_fee_third = $levelData->registration_fee_third;

                    $oldLevel->pivot->late_registration_fee = $levelData->late_registration_fee;

                    $oldLevel->pivot->allow_specialist = $levelData->allow_specialist;
                    $oldLevel->pivot->specialist_registration_fee = $levelData->specialist_registration_fee;
                    $oldLevel->pivot->specialist_late_registration_fee = $levelData->specialist_late_registration_fee;

                    $oldLevel->pivot->allow_teams = $levelData->allow_team;
                    $oldLevel->pivot->team_registration_fee = $levelData->team_registration_fee;
                    $oldLevel->pivot->team_late_registration_fee = $levelData->team_late_registration_fee;

                    $oldLevel->pivot->enable_athlete_limit = $levelData->enable_athlete_limit;
                    $oldLevel->pivot->athlete_limit = $levelData->athlete_limit;

                    $oldLevel->pivot->disabled = false;

                    $oldLevel->pivot->save();
                    $oldLevel->save();

                    $oldMeetLevelsToKeep[] = $oldLevel->pivot->id;
                } else { // If not, then this is a new level.
                    $newLevels[] = [
                        'id' => $levelData->id,
                        'male' => $levelData->male,
                        'female' => $levelData->female,
                        'registration_fee' => $levelData->registration_fee,
 
                        'registration_fee_first' => $levelData->registration_fee_first,
                        // 'registration_fee_second' => $levelData->registration_fee_second,
                        // 'registration_fee_third' => $levelData->registration_fee_third,

                        'late_registration_fee' => $levelData->late_registration_fee,
                        'allow_specialist' => $levelData->allow_specialist,
                        'specialist_registration_fee' => $levelData->specialist_registration_fee,
                        'specialist_late_registration_fee' => $levelData->specialist_late_registration_fee,
                        'allow_teams' => $levelData->allow_team,
                        'team_registration_fee' => $levelData->team_registration_fee,
                        'team_late_registration_fee' => $levelData->team_late_registration_fee,
                        'enable_athlete_limit' => $levelData->enable_athlete_limit,
                        'athlete_limit' => $levelData->athlete_limit,
                    ];
                }
            }

            $levelsToBeDeleted = LevelMeet::where('meet_id', $this->id)
                                    ->where('disabled', false)
                                    ->whereNotIn('id', $oldMeetLevelsToKeep)
                                    ->get();

            foreach ($levelsToBeDeleted as $l) { /** @var LevelMeet $l */
                $al = $l->athlete_level; /** @var AthleteLevel $al */
                $lc = $al->level_category; /** @var LevelCategory $lc */

                if ($restrictedBodies[$al->sanctioning_body_id][$al->level_category_id])
                    throw new CustomBaseException('You are not allowed to remove levels that have registrations.', -1);

                if($lc->requiresSanction($al->sanctioning_body_id)) {
                    $cm = CategoryMeet::where('meet_id', $this->id)
                            ->where('sanctioning_body_id', $al->sanctioning_body_id)
                            ->where('level_category_id', $lc->id)
                            ->first(); /** @var CategoryMeet $cm */
                    if ($cm === null)
                        throw new CustomBaseException('Something went wrong (Cannot find associated category).', -1);

                    if($cm->officially_sanctioned)
                        throw new CustomBaseException('You are not allowed to remove levels in categories that have been assigned a sanction.', -1);
                }

                $l->delete();
            }

            $newLevelsJson = json_encode($levels);
            if ($levels === false)
                throw new CustomBaseException('Server error', ErrorCodeCategory::getCategoryBase('General') + 1);

            $levelsChanged = ($levelsToBeDeleted->count() > 0) || (count($newLevels) > 0);
            if ($levelsChanged) {
                foreach ($newLevels as $level) {
                    $al = AthleteLevel::find($level['id']); /** @var AthleteLevel $al */
                    if ($al == null)
                        throw new CustomBaseException('Something went wrong (Cannot find associated level).', '-1');

                    $lc = $al->level_category; /** @var LevelCategory $lc */

                    if($lc->requiresSanction($al->sanctioning_body_id)) {
                        $cm = CategoryMeet::where('meet_id', $this->id)
                                ->where('sanctioning_body_id', $al->sanctioning_body_id)
                                ->where('level_category_id', $lc->id)
                                ->first(); /** @var CategoryMeet $cm */
                        if ($cm === null)
                            throw new CustomBaseException('Something went wrong (Cannot find associated category).', -1);

                        if($cm->officially_sanctioned)
                            throw new CustomBaseException('You are not allowed to add levels in categories that have been assigned a sanction.', -1);
                    } 

                    LevelMeet::create([
                        'meet_id' => $this->id,
                        'athlete_level_id' => $level['id'],
                        'allow_men' => $level['male'],
                        'allow_women' => $level['female'],
                        'registration_fee' => $level['registration_fee'],

                        'registration_fee_first' => $level['registration_fee_first'],
                        // 'registration_fee_second' => $level['registration_fee_second'],
                        // 'registration_fee_third' => $level['registration_fee_third'],
                        
                        'late_registration_fee' => $level['late_registration_fee'],
                        'allow_specialist' => $level['allow_specialist'],
                        'specialist_registration_fee' => $level['specialist_registration_fee'],
                        'specialist_late_registration_fee' => $level['specialist_late_registration_fee'],
                        'allow_teams' => $level['allow_teams'],
                        'team_registration_fee' => $level['team_registration_fee'],
                        'team_late_registration_fee' => $level['team_late_registration_fee'],
                        'enable_athlete_limit' => $level['enable_athlete_limit'],
                        'athlete_limit' => $level['athlete_limit']
                    ]);
                }
            }

            $old = [
                'meet_competition_format_id' => $this->competition_format->id,
                'meet_competition_format_other' => $this->meet_competition_format_other,
                'team_format' => $this->team_format
            ];

            if ($categoriesChanged)
                $old += ['categories' => $oldCategoriesJson];

            if ($levelsChanged)
                $old += ['levels' => $oldLevelsJson];

            $newData = [
                'meet_competition_format_id' => $competition_format->id,
                'meet_competition_format_other' => isset($attr['meet_competition_format_other']) ? $attr['meet_competition_format_other'] : null,
                'team_format' => isset($attr['team_format']) ? $attr['team_format'] : null,
            ];

            $new = $newData;

            if ($categoriesChanged)
                $new += ['categories' => $newCategoriesJson];

            if ($levelsChanged)
                $new += ['levels' => $newLevelsJson];

            $diff = AuditEvent::attributeDiff($old, $new);
            if (count($diff) > 0) {
                AuditEvent::meetUpdated(
                    request()->_managed_account, auth()->user(), $this, $diff
                );
            }

            $this->update($newData);
            $this->save();

            DB::commit();
            return true;
        } catch(\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function updateStepFour(array $attr)
    {
        DB::beginTransaction();

        try {
            $storedFiles = [];
            $filesToDelete = [];

            $schedule = null;
            $files = null;
            $filesChanged = false;

            $old = [
                'schedule' => $this->schedule
            ];

            $oldSchedule = null;
            if ($this->schedule !== null) {
                $oldSchedule = json_decode($this->schedule);
                if ($oldSchedule === null)
                    throw new CustomBaseException('Server error', ErrorCodeCategory::getCategoryBase('General') + 1);

                    $keepOldSchedule = isset($attr['keep_schedule']) && $attr['keep_schedule'];
                    if ($keepOldSchedule)
                        $schedule = $this->schedule;
                    else
                        $filesToDelete[] = $oldSchedule->path;
            }

            if (isset($attr['schedule'])) {
                $schedule = Storage::url(Storage::putFile(
                    'public/files/' . $this->gym->id . '/meet', $attr['schedule']
                ));
                $storedFiles[] = $schedule;

                $schedule = [
                    'name' => $attr['schedule']->getClientOriginalName(),
                    'path' => $schedule
                ];

                $schedule = json_encode($schedule);
                if ($schedule === false)
                    throw new CustomBaseException('Server error', ErrorCodeCategory::getCategoryBase('General') + 1);

                if ($oldSchedule !== null)
                    $filesToDelete[] = $oldSchedule->path;
            }

            $filesToKeep = (isset($attr['uploaded_files']) ? $attr['uploaded_files'] : []);

            $keep = [];
            $deleted = [];
            $oldFiles = [];
            if ($this->files !== null) {
                foreach ($this->files as $oldFile) {
                    if (in_array($oldFile->path, $filesToKeep)) {
                        $keep[] = [
                            'name' => $oldFile->name,
                            'path' => $oldFile->path,
                            'description' => $oldFile->description
                        ];
                    } else {
                        $this->files()->find($oldFile->id)->delete();
                        $deleted[] = $oldFile;
                    }

                    $oldFiles[] = [
                        'name' => $oldFile->name,
                        'path' => $oldFile->path,
                        'description' => $oldFile->description
                    ];
                }
            }

            $oldFiles = json_encode($oldFiles);
            if ($oldFiles === false)
                throw new CustomBaseException('Server error', ErrorCodeCategory::getCategoryBase('General') + 1);

            $files = $keep;

            $limit = Setting::meetFileMaxCount();

            if (isset($attr['files'])) {
                $fileCount = count($attr['files']);

                if (($fileCount != count($attr['description'])))
                    throw new CustomBaseException('File / Description array mismatch', -1);

                if (($fileCount + count($filesToKeep)) > $limit)
                    throw new CustomBaseException('Only ' . $limit . ' files can be uploaded.', -1);

                $singleFileRules = self::getSingleFileRules();
                
                for ($i = 0; $i < $fileCount; $i++) {
                    if (strlen($attr['description'][$i]) > 255)
                        throw new CustomBaseException('Descriptions should be less than 255 characters long.', -1);

                    $validator = Validator::make(['file' => $attr['files'][$i]], ['file' => $singleFileRules]);
                    if ($validator->fails()) {
                        throw new CustomBaseException('Wrong file type or file bigger than ' .
                        Helper::formatByteSize(Setting::meetFileMaxSize() * 1024) , -1);
                    }

                    $file = $attr['files'][$i];
                    

                    $file = Storage::url(Storage::putFile(
                        'public/files/' . $this->gym->id . '/meet', $file
                    ));

                    $storedFiles[] = $file;

                    $this->files()->create([
                        'name' => $attr['files'][$i]->getClientOriginalName(),
                        'path' => $file,
                        'description' => $attr['description'][$i]
                    ]);

                    $files[] = [
                        'name' => $attr['files'][$i]->getClientOriginalName(),
                        'path' => $file,
                        'description' => $attr['description'][$i]
                    ];
                } 
                
            }

            $files = json_encode($files);
            if ($files === false)
                throw new CustomBaseException('Server error', ErrorCodeCategory::getCategoryBase('General') + 1);

            $newData = [
                'schedule' => $schedule
            ];

            $new = $newData;

            if ((count($deleted) > 0) || (count($storedFiles) > 0)) {
                $old['files'] = $oldFiles;
                $new['files'] = $files;
            }

            $diff = AuditEvent::attributeDiff($old, $new);

            if (count($diff) < 1)
                return true;

            AuditEvent::meetUpdated(
                request()->_managed_account, auth()->user(), $this, $diff
            );

            $this->update($newData);
            $this->save();

            foreach ($deleted as $file)
                $filesToDelete[] = $file->path;

            DB::commit();
            return true;
        } catch(\Throwable $e) {
            DB::rollBack();
            // print_r($storedFiles); die();
            foreach ($storedFiles as $file)
                Helper::removeOldFile($file, null);

            throw $e;
        } finally {
            foreach ($filesToDelete as $file)
                Helper::removeOldFile($file, null);
        }
    }

    public function updateStepFive(array $attr)
    {
        DB::beginTransaction();

        try {

            $old = [
                'primary_contact_first_name' => $this->primary_contact_first_name,
                'primary_contact_last_name' => $this->primary_contact_last_name,
                'primary_contact_email' => $this->primary_contact_email,
                'primary_contact_phone' => $this->primary_contact_phone,
                'primary_contact_fax' => $this->primary_contact_fax,
                'get_mail_primary' =>  $this->get_mail_primary,

                'secondary_contact' => $this->secondary_contact,
                'secondary_contact_first_name' =>  $this->secondary_contact_first_name,
                'secondary_contact_last_name' =>  $this->secondary_contact_last_name,
                'secondary_contact_email' =>  $this->secondary_contact_email,
                'secondary_contact_job_title' =>  $this->secondary_contact_job_title,
                'secondary_contact_phone' =>  $this->secondary_contact_phone,
                'secondary_contact_fax' =>  $this->secondary_contact_fax,
                'secondary_cc' =>  $this->secondary_cc,
                'get_mail_secondary' =>  $this->get_mail_secondary,
            ];

            $hasSecondary = isset($attr['secondary_contact']);
            $new = [
                'primary_contact_first_name' => $attr['primary_contact_first_name'],
                'primary_contact_last_name' => $attr['primary_contact_last_name'],
                'primary_contact_email' => $attr['primary_contact_email'],
                'primary_contact_phone' => $attr['primary_contact_phone'],
                'primary_contact_fax' => $attr['primary_contact_fax'],
                'get_mail_primary' => isset($attr['get_mail_primary']) ??  false,

                'secondary_contact' => $hasSecondary,
                'secondary_contact_first_name' => ($hasSecondary ? $attr['secondary_contact_first_name'] : null),
                'secondary_contact_last_name' => ($hasSecondary ? $attr['secondary_contact_last_name'] : null),
                'secondary_contact_email' => ($hasSecondary ? $attr['secondary_contact_email'] : null),
                'secondary_contact_job_title' => ($hasSecondary ? $attr['secondary_contact_job_title'] : null),
                'secondary_contact_phone' => ($hasSecondary ? $attr['secondary_contact_phone'] : null),
                'secondary_contact_fax' => ($hasSecondary ? $attr['secondary_contact_fax'] : null),
                'secondary_cc' => ($hasSecondary ? isset($attr['secondary_cc']) : false),
                'get_mail_secondary' => ($hasSecondary ? isset($attr['get_mail_secondary']) : false),
            ];

            $diff = AuditEvent::attributeDiff($old, $new);
            if (count($diff) < 1)
                return true;

            AuditEvent::meetUpdated(
                request()->_managed_account, auth()->user(), $this, $diff
            );

            $this->update($new);
            $this->save();

            DB::commit();
            return true;
        } catch(\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function generateRefundReport(Gym $gym = null) : PdfWrapper {
        try {
            $base = $this->registrations()
                        ->where('status', MeetRegistration::STATUS_REGISTERED);
            /** @var Builder $base */

            if ($gym !== null)
                $base = $base->where('gym_id', $gym->id);

            $registrations = $base->select([
                    'id', 'gym_id', 'meet_id', 'late_refund', 'status'
                ])->with([
                    'gym' => function ($q) {
                        $q->select([
                            'id', 'name', 'addr_1', 'addr_2', 'city', 'state_id', 'zipcode',
                            'country_id', 'office_phone',
                        ]);
                    },
                    'gym.state' => function ($q) {
                        $q->select([
                            'id', 'code'
                        ]);
                    },
                    'gym.country' => function ($q) {
                        $q->select([
                            'id', 'name'
                        ]);
                    },
                    'athletes' => function ($q) {
                        $q->select([
                            'id', 'meet_registration_id', 'first_name', 'last_name', 'gender', 'dob',
                            'fee', 'late_fee', 'refund', 'late_refund', 'status', 'updated_at'
                        ])->whereIn('status', [
                            RegistrationAthlete::STATUS_SCRATCHED,
                            RegistrationAthlete::STATUS_REGISTERED
                        ])->where(DB::raw(
                            '(registration_athletes.refund + registration_athletes.late_refund)'
                        ), '>', 0);
                    },
                    'specialists' => function ($q) {
                        $q->select([
                            'id', 'meet_registration_id', 'first_name', 'last_name', 'gender', 'dob',
                        ])->with([
                            'events' => function ($q) {
                                $q->select([
                                    'id', 'specialist_id', 'event_id',
                                    'late_fee', 'refund', 'late_refund', 'status', 'updated_at'
                                ])->whereIn('status', [
                                    RegistrationSpecialistEvent::STATUS_SPECIALIST_SCRATCHED,
                                    RegistrationSpecialistEvent::STATUS_SPECIALIST_REGISTERED
                                ])->with([
                                    'specialist_event' => function ($q) {
                                        $q->select([
                                            'id', 'name'
                                        ]);
                                    },
                                ])->where(DB::raw(
                                    '(registration_specialist_events.refund +
                                        registration_specialist_events.late_refund)'
                                ), '>', 0);
                            },
                        ]);
                    },
                    'levels' => function ($q) {
                        $q->where(DB::raw(
                            '(level_registration.team_refund + level_registration.team_late_refund)'
                        ), '>', 0)->with([
                            'sanctioning_body' => function ($q) {
                                $q->select(['id', 'initialism']);
                            },
                            'level_category' => function ($q) {
                                $q->select(['id', 'name']);
                            },
                        ])->orderBy('sanctioning_body_id');
                    },
                ])->orderBy('created_at', 'DESC')
                ->get();

            foreach ($registrations as $i => $registration) { /** @var MeetRegistration $registration */
                $total = 0;
                $credit_used = 0;
                $credit_row = MeetCredit::where('meet_registration_id',$registration->id)->where('gym_id', $registration->gym->id)->where('meet_id', $registration->meet->id)->first();
                
                if($credit_row != null && $credit_row->count() > 0)
                {
                    $credit_used = $credit_row->used_credit_amount;
                }
                $registration->credit_used = $credit_used;
                foreach ($registration->specialists as $j => $specialist) { /** @var RegistrationSpecialist $specialist */
                    if ($specialist->events->count() < 1)
                        unset($registrations[$i]->specialists[$j]);
                    else
                        $total += $specialist->refund_fee();
                }

                $registration->athlete_count = $registration->athletes->count() + $registration->specialists->count();

                if (
                    ($registration->athlete_count < 1) &&
                    ($registration->levels->count() < 1) &&
                    ($registration->late_refund <= 0)
                ) {
                    unset($registrations[$i]);
                    continue;
                }

                foreach ($registration->athletes as $athlete) { /** @var RegistrationAthlete $athlete */
                    $total += $athlete->refund_fee();
                }

                foreach ($registration->levels as $level) { /** @var AthleteLevel $level */
                    $total += $level->pivot->refund_fee();
                }

                $total += $registration->late_refund;

                $registration->total = $total;
            }

            $data = [
                'host' => $this->gym,
                'meet' => $this,
                'registrations' => $registrations
            ];

            return PDF::loadView('PDF.host.meet.reports.refund', $data); /** @var PdfWrapper $pdf */
        } catch(\Throwable $e) {
            throw $e;
        }
    }
    //When Meet Registrant that time call this function
    public function registrantMeetEntryAndStoreReport($meet, $gym)
    {
        $meet = $this->retrieveMeet($meet);/** @var Meet $meet */
        try {
            $base = $this->registrations()->where('status', MeetRegistration::STATUS_REGISTERED); /** @var Builder $base */

            if ($gym !== null)
                $base = $base->where('gym_id', $gym->id);

            $registrations = $base->with('gym.user')->select([
                'id', 'gym_id', 'meet_id', 'status'
            ])->with(['gym' => function ($q) {
                    $q->select(['id', 'name', 'addr_1', 'addr_2', 'city', 'state_id', 'zipcode','country_id', 'office_phone','user_id']);
                }])->orderBy('created_at', 'DESC')->first();

            $data = [
                'host' => $this->gym,
                'meet' => $this,
                'registrations' => $registrations
            ];

            $pdf = PDF::loadView('PDF.host.meet.reports.meet-register-entry', $data);
            $gymName = strtolower(str_replace(' ', '_', $meet->name));
            $pdfName = 'regi_meet_en_' . time() . '_' . $gymName . '.pdf';

            $pdf->save(storage_path('registrant_meet_entry\\' . $pdfName));

            return storage_path("registrant_meet_entry\\{$pdfName}");
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function generateMeetEntryReport(Gym $gym = null, $single = true) : PdfWrapper {
        try {
            $base = $this->registrations()
                        ->where('status', MeetRegistration::STATUS_REGISTERED);
            /** @var Builder $base */

            if ($gym !== null)
                $base = $base->where('gym_id', $gym->id);

            $registrations = $base->select([
                    'id', 'gym_id', 'meet_id', 'status'
                ])->with([
                    'gym' => function ($q) {
                        $q->select([
                            'id', 'user_id', 'name', 'addr_1', 'addr_2', 'city', 'state_id', 'zipcode',
                            'country_id', 'office_phone',
                        ]);
                    }
                ,'athletes'])->orderBy('created_at', 'DESC')
                ->get();

            // sort registrations based on gym->namespace
            $registrations = $registrations->sortBy(function($registration, $key) {
                return $registration->gym->name;
            });
            $data = [
                'host' => $this->gym,
                'meet' => $this,
                'registrations' => $registrations,
                'single' => $single
            ];

            return PDF::loadView('PDF.host.meet.reports.meet-entry', $data); /** @var PdfWrapper $pdf */
        } catch(\Throwable $e) {
            throw $e;
        }
    }

    public function generateScratchReport(Gym $gym = null) : PdfWrapper   {
        try {
            
            $meetRegistration = resolve(MeetRegistration::class);
            $registrationAuditReport = [
                'athlete' => [
                    'new' => [],
                    'moved' => [],
                    'scratched' => []
                ],
                'specialist' => [
                    'new' => [],
                    'moved' => [],
                    'scratched' => []
                ],
                'coach' => [
                    'new' => [],
                    'moved' => [],
                    'scratched' => []
                ]
            ];
            $base = $this->registrations()
                        ->where('status', MeetRegistration::STATUS_REGISTERED);
            /** @var Builder $base */

            if ($gym !== null)
                $base = $base->where('gym_id', $gym->id);

            $registrations = $base->select([
                    'id', 'gym_id', 'meet_id', 'late_refund', 'status'
                ])->with([
                    'gym' => function ($q) {
                        $q->select([
                            'id', 'name', 'addr_1', 'addr_2', 'city', 'state_id', 'zipcode',
                            'country_id', 'office_phone',
                        ]);
                    },
                    'gym.state' => function ($q) {
                        $q->select([
                            'id', 'code'
                        ]);
                    },
                    'gym.country' => function ($q) {
                        $q->select([
                            'id', 'name'
                        ]);
                    },
                    'athletes' => function ($q) {
                        $q->select([
                            'id', 'meet_registration_id','level_registration_id', 'first_name', 'last_name', 'gender', 'dob',
                            'fee', 'late_fee', 'refund', 'late_refund', 'status', 'updated_at'
                        ])->whereIn('status', [
                            RegistrationAthlete::STATUS_SCRATCHED,
                        ])->where(DB::raw(
                            '(registration_athletes.refund + registration_athletes.late_refund)'
                        ), '>=', 0);
                    },
                    'levels' => function ($q) {
                        $q->where(DB::raw(
                            '(level_registration.team_refund + level_registration.team_late_refund)'
                        ), '>', 0)->with([
                            'sanctioning_body' => function ($q) {
                                $q->select(['id', 'initialism']);
                            },
                            'level_category' => function ($q) {
                                $q->select(['id', 'name']);
                            },
                        ])->orderBy('sanctioning_body_id');
                    },
                ])->orderBy('created_at', 'DESC')
                ->get();

            foreach ($registrations as $i => $registration) { /** @var MeetRegistration $registration */
                $total = 0;

                foreach ($registration->specialists as $j => $specialist) { /** @var RegistrationSpecialist $specialist */
                    if ($specialist->events->count() < 1)
                        unset($registrations[$i]->specialists[$j]);
                    else
                        $total += $specialist->refund_fee();

                    //if Specialist event count is not 0 then process, if Event refund amount is < 0 then unset.
                    foreach ($specialist->events as $event){
                        if ($event->refund < 1)
                            unset($registrations[$i]->specialists[$j]);
                    }
                }

                $registration->athlete_count = $registration->athletes->count() + $registration->specialists->count();

                if (
                    ($registration->athlete_count < 1) &&
                    ($registration->levels->count() < 1) &&
                    ($registration->late_refund <= 0)
                ) {
                    unset($registrations[$i]);
                    continue;
                }

                foreach ($registration->athletes as $athlete) { /** @var RegistrationAthlete $athlete */
                    $total += $athlete->refund_fee();
                }

                foreach ($registration->levels as $level) { /** @var AthleteLevel $level */
                    $total += $level->pivot->refund_fee();
                }

                $total += $registration->late_refund;

                $registration->total = $total;
                // dd($registration->athletes);
                $auditEvent = AuditEvent::where('object_id',$registration->id)->where('type_id',502)->get();
                foreach ($auditEvent as $key => $value) {
                    $vs = $meetRegistration->process_audit_event((object) $value->event_meta);
                    $registrationAuditReport = $this->mergeValue($vs,$registrationAuditReport);
                }
                $registration->audit_report = $registrationAuditReport;
            }

            $data = [
                'host' => $this->gym,
                'meet' => $this,
                'registrations' => $registrations
            ];
            // return view('PDF.host.meet.reports.scratch', $data);
            return PDF::loadView('PDF.host.meet.reports.scratch', $data); /** @var PdfWrapper $pdf */
        } catch(\Throwable $e) {
            throw $e;
        }
    }
    public function generateGymRegistrationReport(Gym $gym = null) : PdfWrapper   {
        try{
            $base = $this->registrations()->where('status', MeetRegistration::STATUS_REGISTERED);
            
            // print_r($base);
            if ($gym !== null)
            {
                $base_r = $base->where('gym_id', $gym->id);
                $meet_id = $base_r->select(['meet_id'])->first();
            }
            else
            {
                $meet_id = $base->select(['meet_id'])->first();
            }
            $re_gyms = MeetRegistration::select(['gym_id'])->where('meet_id',$meet_id->meet_id)->get();

            $gym_name = [];
            foreach ($re_gyms as $key => $value) {
                $k = Gym::where('id',$value->gym_id)->first();
                $gym_name[] = $k;
            }
            $data = [
                'host' => $this->gym,
                'meet' => $this,
                'gyms' => $gym_name,
                'cont' => count($gym_name)
            ];
            return PDF::loadView('PDF.host.meet.reports.gyms-report', $data); /** @var PdfWrapper $pdf */
        }
        catch(\Throwable $e)
        {
            throw $e;
        }
    }
    public function generateNGACoachSignInReport(Gym $gym = null) : PdfWrapper   {
        try{
            
            $base = $this->registrations()->where('status', MeetRegistration::STATUS_REGISTERED);
            
            // print_r($base);
            if ($gym !== null)
            {
                $base_r = $base->where('gym_id', $gym->id);
                $meet_id = $base_r->select(['meet_id'])->first();
            }
            else
            {
                $meet_id = $base->select(['meet_id'])->first();
            }
            $re_gyms = [];
            if($meet_id != null)
                $re_gyms = MeetRegistration::select(['gym_id'])->where('meet_id',$meet_id->meet_id)->get();
            $gym_name = [];
            foreach ($re_gyms as $key => $value) {
                $k = Gym::where('id',$value->gym_id)->first();
                $coaches = $k->getCoachesFromMeetRegistrations($meet_id->meet_id);
                $gym_name[$k->id]['gyms'] = $k;
                $gym_name[$k->id]['coaches'] = $coaches;
            }
            
            $data = [
                'host' => $this->gym,
                'meet' => $this,
                'gyms' => $gym_name,
                'cont' => count($gym_name),
                'background_logo' => asset('img/nga_background.png')
            ];
            
            $pdf =  PDF::loadView('PDF.host.meet.reports.nga-coach-report', $data); /** @var PdfWrapper $pdf */
            // $pdf->setOption('background-image', $bi);
            return $pdf;
        }
        catch(\Throwable $e)
        {
            throw $e;
        }
    }
    public function generateUSAIGCCoachSignInReport(Gym $gym = null) : PdfWrapper   {
        try{
            $base = $this->registrations()->where('status', MeetRegistration::STATUS_REGISTERED);
            
            // print_r($base);
            if ($gym !== null)
            {
                $base_r = $base->where('gym_id', $gym->id);
                $meet_id = $base_r->select(['meet_id'])->first();
            }
            else
            {
                $meet_id = $base->select(['meet_id'])->first();
            }
            $re_gyms = [];
            if($meet_id != null)
                $re_gyms = MeetRegistration::select(['gym_id'])->where('meet_id',$meet_id->meet_id)->get();

            $gym_name = [];
            foreach ($re_gyms as $key => $value) {
                $k = Gym::where('id',$value->gym_id)->first();
                $coaches = $k->getCoachesFromMeetRegistrations($meet_id->meet_id);
                $gym_name[$k->id]['gyms'] = $k;
                $gym_name[$k->id]['coaches'] = $coaches;
            }
            // dd($gym_name); die();
            $data = [
                'host' => $this->gym,
                'meet' => $this,
                'gyms' => $gym_name,
                'cont' => count($gym_name)
            ];
            return PDF::loadView('PDF.host.meet.reports.usaigc-coach-report', $data); /** @var PdfWrapper $pdf */
        }
        catch(\Throwable $e)
        {
            throw $e;
        }
    }
    public function mergeValue($data, $result = [])
    {
        foreach ($data as $key => $value) {
            foreach ($value as $subkey => $subvalue) {
                if (is_array($subvalue)) {
                    $result[$key][$subkey] = array_merge($result[$key][$subkey], $subvalue);
                }
            }
        }
        return $result;
    }
    public function generateRegistrationDetailReport(Gym $gym = null) : PdfWrapper   {
        try {
            $base = $this->registrations()
                        ->where('status', MeetRegistration::STATUS_REGISTERED);

            // dd($base);
            /** @var Builder $base */

            if ($gym !== null)
                $base = $base->where('gym_id', $gym->id);

            $registrations = $base->select([
                    'id', 'gym_id', 'meet_id', 'late_refund', 'status', 'was_late','late_fee'
                ])->orderBy('created_at', 'DESC')
                ->get();


            $data = [
                'host' => $this->gym,
                'meet' => $this,
                'registrations' => $registrations,
                'reg_fees' => 0,
                'reg_meet_fees' => 0,
                'team_fees' => 0,
                'team_meet_fees' => 0,
                'admin_fees' => 0,
                'admin_meet_fees' => 0,
                'card_fees' => 0,
                'card_meet_fees' => 0,
                'refund_fees' => 0,
                'refund_meet_fees' => 0,
                'total_fees' => 0,
                'total_meet_fees' => 0,
            ];

            $fee_s = [
                'reg_fees' => 0,
                'reg_meet_fees' => 0,
                'team_fees' => 0,
                'team_meet_fees' => 0,
                'admin_fees' => 0,
                'admin_meet_fees' => 0,
                'card_fees' => 0,
                'card_meet_fees' => 0,
                'refund_fees' => 0,
                'refund_meet_fees' => 0,
                'total_fees' => 0,
                'total_meet_fees' => 0,
                'late_fee' => 0,
            ];

            $alreadGone = array();
            $meetRegistration = resolve(MeetRegistration::class);
            $registrationAuditReport = [
                'athlete' => [
                    'new' => [],
                    'moved' => [],
                    'scratched' => []
                ],
                'specialist' => [
                    'new' => [],
                    'moved' => [],
                    'scratched' => []
                ],
                'coach' => [
                    'new' => [],
                    'moved' => [],
                    'scratched' => []
                ]
            ];

            foreach ($registrations as $i => $registration) {/** @var MeetRegistration $registration */
                $adminFee = 0;
                $cardFees = 0;
                $adminMeetFee = 0;
                $cardMeetFees = 0;
                $flag=0;
                $specialistFee = 0;
                $specialistLateFee = 0;
                $specialistRefundFee = 0;
                $specialistLateRefundFee = 0;
                $speicalist_total_fee = 0;

                $history = [];
                $used_credit = 0;
                // echo $registration->id .' '.$registration->gym->id.' '.$registration->meet->id.'<br>';
                $credit_row = MeetCredit::where('meet_registration_id',$registration->id)->where('gym_id', $registration->gym->id)->where('meet_id', $registration->meet->id)->first();
                // dd($credit_row);
                if($credit_row != null)
                {
                    $used_credit = $credit_row->used_credit_amount;
                }
                // dd($used_credit);
                $auditEvent = AuditEvent::where('object_id',$registration->id)->where('type_id',502)->get();
                // dd($auditEvent);
                foreach ($auditEvent as $key => $value) {
                    $vs = $meetRegistration->process_audit_event((object) $value->event_meta);
                    $registrationAuditReport = $this->mergeValue($vs,$registrationAuditReport);
                }
                $registrations[$i]['audit_report'] = $registrationAuditReport;

                // dd($registrations[$i]['audit_report']);
                if(count($registration->specialists) > 0){
                    foreach ($registration->specialists as $specialist) {
                        if(count($specialist->events) > 0){
                            $specialistFee += $specialist->events->sum('fee');
                            $specialistRefundFee += $specialist->events->sum('refund');
                            $specialistLateFee += $specialist->events->sum('late_fee');
                            $specialistLateRefundFee += $specialist->events->sum('late_refund');
                        }
                    }
                }
                $reg_meet_fees = 0;
                $teamFee = 0;
                $refund_meet_fees = 0;
                $team_refund = 0;
                foreach ($registration->levels as $level) { /** @var AthleteLevel $level */
                    $teamFee += $level->pivot->team_fee;
                    $team_refund += $level->pivot->team_refund;
                }
                
                $registrations[$i]['transactions'] = $registration->transactions()
                ->where('status', MeetTransaction::STATUS_COMPLETED)
                ->orderBy('created_at', 'ASC')
                ->get();
                $team_late = 0;
                $usag_refund = 0;
                $regular_refund = 0;
                $team_already_mentioned = [];
                $allaround_late_fee_calculate = false;
                // blank payment id is not working - need to have previous payment id assigned to it
                // dd($registrations[$i]['transactions']);
                $registered_team_ids = [];
                foreach ($registrations[$i]['transactions'] as $j => $transaction) {
                    foreach ($transaction->breakdown['level_team_fees'] as $key => $value) {
                        if($transaction->breakdown['level_team_fees'][$key]['fee'] > 0)
                            $registered_team_ids[$key] = array(
                                'transaction_id' => $transaction->id
                        );
                    }
                }
                foreach ($registrations[$i]['transactions'] as $j => $transaction) {
                    // echo $usag_refund;
                    if (!isset($transaction->breakdown['level_team_fees'])) {
                        unset($registrations[$i]['transactions'][$j]);
                        continue;
                    }
                    // echo '<br>Transaction ID : ' .  $transaction->id .'<br>';
                    // dd($transaction->breakdown['level_team_fees']);
                    
                    // if($transaction->breakdown['level_team_fees']['fee'] > 0)
                    //     dd($transaction->breakdown['level_team_fees']);
                    // dd($registrations[$i]);
                    // $levelTeamFees = isset($transaction->breakdown['level_team_fees']) ? $transaction->breakdown['level_team_fees'] :[];
                    // dd($levelTeamFees);
                    // $l_r_s = DB::select('SELECT distinct level_registration_id FROM registration_specialists WHERE transaction_id = '.$transaction->id);
                    $l_r_s = DB::select('SELECT distinct rs.level_registration_id FROM registration_specialists as rs 
                            JOIN registration_specialist_events as rse ON rs.id = rse.specialist_id 
                            WHERE rse.transaction_id = '.$transaction->id);

                    $l_r_a = DB::select('SELECT distinct level_registration_id FROM registration_athletes WHERE transaction_id = '.$transaction->id);

                    $l_r_s = array_map(function ($value) {
                        return $value->level_registration_id;
                    }, $l_r_s);

                    $l_r_a = array_map(function ($value) {
                        return $value->level_registration_id;
                    }, $l_r_a);

                    $l_r = array_merge($l_r_s,$l_r_a);
                    $l_r = array_unique($l_r);
                    // dd($l_r);
                    // print_r($l_r);
                    // echo '<br><br>';

                    $levels_with_new_team = [];
                    foreach($transaction->breakdown['level_team_fees'] as $ks=>$vs)
                    {
                        if($vs['fee'] > 0)
                        {
                            if(!in_array($ks,$l_r))
                            {
                                $levels_with_new_team[] = $ks;
                            }
                        }
                    }
                    if(!empty($levels_with_new_team))
                    {
                        $l_r = array_merge($l_r,$levels_with_new_team);
                        $l_r = array_unique($l_r);
                    }
                    
                    if (count($l_r) > 0) {
                        $levels = $registration->levels()->wherePivotIn('id', $l_r)->get();
                        // dd($levels);
                        $k = 0;
                        $level_reg_history = [];
                        $team_late = 0;
                        $total_difference = 0;
                        // echo '<br>Transaction ID : ' .  $j .'<br>';
                        foreach ($levels as $l) { /** @var AthleteLevel $l */
                            $specialist_count = 0;
                            // dd($l->pivot->athletes);
                            $at_count = $l->pivot->athletes->where('transaction_id',$transaction->id)->count(); 
                            $at_count_t = $l->pivot->athletes->where('transaction_id',$transaction->id);
                            $net_athlete_value = 0;
                            foreach ($at_count_t as $key => $value) {
                                if($l->sanctioning_body_id == 1) //USAG
                                {
                                    if($value->status == RegistrationAthlete::STATUS_REGISTERED)
                                    {
                                        // echo ('refund '.$value->refund );
                                        $u_refund = ($value->refund + $value->late_refund);
                                        $usag_refund += $u_refund > 0 ? $u_refund : 0;
                                        // var_dump($value);
                                        // echo $value->refund. ' ' . $value->fee .'<br>';
                                        // echo 'usag refund : '. $u_refund . '<br>';
                                    }
                                    else if($value->status == RegistrationAthlete::STATUS_SCRATCHED)
                                    {
                                        $usag_refund += $value->refund + $value->late_refund;
                                    }
                                }
                                else
                                {
                                    $regular_refund += $value->refund;
                                }
                                if($value->was_late)
                                    $net_athlete_value += $value->fee + $value->late_fee;
                                else
                                    $net_athlete_value += $value->fee;
                            }

                            $late_at_count = $l->pivot->athletes->where('transaction_id',$transaction->id)->where('was_late',true)->count();
                            // dd($at_count);
                            $specialist_fee = $l->pivot->specialist_registration_fee;
                            $sf_total = 0;
                            // $spec = $l->pivot->specialists->where('transaction_id',$transaction->id);
                            // $spec = $l->pivot->specialists->where('transaction_id',$transaction->id);


                            // dd($l);
                            $specialist_athletes = DB::select('SELECT * FROM registration_specialists as rs 
                                        JOIN registration_specialist_events as rse ON rs.id = rse.specialist_id 
                                        WHERE rse.transaction_id = '.$transaction->id .' AND rs.level_registration_id = '.$l->pivot->id);

                            $specialist_athletes_count = DB::select('SELECT count(distinct rs.id) as numb FROM registration_specialists as rs 
                                        JOIN registration_specialist_events as rse ON rs.id = rse.specialist_id 
                                        WHERE rse.transaction_id = '.$transaction->id .' AND rs.level_registration_id = '.$l->pivot->id);

                            // echo '<br>Transaction ID : ' .  $transaction->id .' ' .$l->pivot->id.'<br>';
                            // print_r($specialist_athletes_count) . '<br>';
                            // dd($specialist_athletes_count[0]->numb);
                            $cttr = 0;
                            if($specialist_athletes_count == [])
                                $cttr = 0;
                            else
                                $cttr = $specialist_athletes_count[0]->numb;

                            foreach ($specialist_athletes as $e) {
                                if($e->was_late)
                                {
                                    $sf_total += $e->fee + $e->late_fee;
                                }
                                else
                                {
                                    $sf_total += $e->fee;
                                }
                                $specialist_count += 1;
                            }
                            if(isset($team_already_mentioned[$l->id]))
                            {
                                $team_count = 0;
                                $team_fee = 0;
                                $team_late = 0;
                            }
                            else
                            {
                                // dd($registered_team_ids);
                                // echo 'level ' . $l->pivot->id . '<br>';
                                if(isset($registered_team_ids[$l->pivot->id]))
                                {
                                    // echo $registered_team_ids[$l->pivot->id]['transaction_id'] . ' ' . $transaction->id . '<br>';
                                    // dd($registered_team_ids[$l->pivot->id]['transaction_id']);
                                    if($registered_team_ids[$l->pivot->id]['transaction_id'] == $transaction->id)
                                    {
                                        // echo 'Team Fee : ' . $l->pivot->team_fee . '<br>';
                                        $team_count = ( $l->pivot->allow_teams ? 1 : 0 ) * ($l->pivot->team_fee > 0 ? 1 : 0);
                                        $team_fee = $l->pivot->team_fee;
                                        $team_late +=  $l->pivot->team_late ? $l->pivot->team_late_registration_fee : 0;
        
                                        if($team_count > 0)
                                            $team_already_mentioned[$l->id] = 1;
                                    }
                                    else
                                    {
                                        $team_count = 0;
                                        $team_fee = 0;
                                        $team_late = 0;
                                    }
                                }
                                else
                                {
                                    $team_count = ( $l->pivot->allow_teams ? 1 : 0 ) * ($l->pivot->team_fee > 0 ? 1 : 0);
                                    $team_fee = $l->pivot->team_fee;
                                    $team_late +=  $l->pivot->team_late ? $l->pivot->team_late_registration_fee : 0;
    
                                    if($team_count > 0)
                                        $team_already_mentioned[$l->id] = 1;
                                }

                               
                            }
                            // dd($l);
                            $onetimelatefee = 0;
                            if($registration->was_late && $l->pivot->was_late && $allaround_late_fee_calculate == false)
                            {
                                $onetimelatefee = $registration->late_fee;
                                $allaround_late_fee_calculate = true;
                            }


                            $speicalist_total_fee += $sf_total;
                            $speicalist = $cttr;
                            // $specialist = $specialist_count;
                            $reg_fee = $l->pivot->registration_fee;
                            $late_reg_fee = $l->pivot->late_registration_fee;
                            // $team_count = ( $l->pivot->allow_teams ? 1 : 0 ) * ($l->pivot->team_fee > 0 ? 1 : 0);
                            // $team_fee = $l->pivot->team_fee;
                            // $team_late +=  $l->pivot->team_late ? $l->pivot->team_late_registration_fee : 0;
                            // $re_total = $reg_fee * $at_count + $team_fee * $team_count + $sf_total + $late_at_count * $late_reg_fee;
                            $individual_team_late = ($team_count == 1 && $l->pivot->was_late > 0) ? $l->pivot->team_late_registration_fee : 0;
                            $re_total = $net_athlete_value + ($team_fee * $team_count) + $sf_total + $individual_team_late + $onetimelatefee; // + $late_at_count * $late_reg_fee;
                            // echo 'Net Athlete Value : ' . $net_athlete_value . '<br>';
                            // echo 'Team Fee : ' . $team_fee . '<br>';
                            // echo 'Team Count : ' . $team_count . '<br>';
                            // echo 'Specialist Fee : ' . $sf_total . '<br>';
                            // echo 'Late Athlete Count : ' . $late_at_count . '<br>';
                            // echo 'Late Reg Fee : ' . $late_reg_fee . '<br>';
                            // echo 'Total : ' . $re_total . '<br>';
                            // echo 'team late : ' . $individual_team_late .'<br>';

                            // dd($speicalist);
                            // echo $re_total . ' <br>';
                            // dd($re_total);
                            if($re_total > 0)
                            {
                                $level_reg_history[$l->id] = [
                                    'name' => $l->abbreviation,
                                    'at_count' => $at_count,
                                    'specialists' => $speicalist,
                                    'team_count' => $team_count,
                                    'entry_fee' => $reg_fee,
                                    'specialist_registration_fee' => $specialist_fee,
                                    'team_fee' => $team_fee,
                                    'total_fee' => $re_total
                                ];
                                if(!isset($history[$l->id]))
                                    $history[$l->id] = $level_reg_history[$l->id];

                                $k +=1;
                                $total_difference += $re_total;
                                
                            } 
                            // dd($l);
                        }
                        $registrations[$i]['transactions'][$j]['level_reg_history'] = $level_reg_history;
                        $registrations[$i]['transactions'][$j]['level_payment_sum'] = $total_difference;
                    }
                    else
                    {
                        $total_team_sum = 0;
                        foreach ($transaction->breakdown['level_team_fees'] as $key => $value) {
                            if($value['fee'] > 0)
                            {
                                // echo 'level in else ' . $key . '<br>';
                                $l = $registration->levels()->wherePivot('id', $key)->first();
                                // dd($l);
                                $team_count = ( $l->pivot->allow_teams ? 1 : 0 ) * ($l->pivot->team_fee > 0 ? 1 : 0);
                                $team_fee = $l->pivot->team_fee;
                                $team_late +=  $l->pivot->team_late ? $l->pivot->team_late_registration_fee : 0;

                                if($team_count > 0)
                                    $team_already_mentioned[$l->id] = 1;
                                
                                $level_team_reg_history[$l->id] = [
                                    'name' => $l->abbreviation,
                                    'at_count' => 0,
                                    'specialists' => 0,
                                    'team_count' => 1,
                                    'entry_fee' => $team_fee,
                                    'specialist_registration_fee' => 0,
                                    'team_fee' => $team_fee,
                                    'total_fee' => $team_fee
                                ];
                                $total_team_sum += $team_fee;
                            }
                        }
                        $registrations[$i]['transactions'][$j]['level_reg_history'] = $level_team_reg_history;
                        $registrations[$i]['transactions'][$j]['level_payment_sum'] = $total_team_sum;
                        $level_team_reg_history = [];
                    }
                }
                // dD("check");
                // dd($registrations[0]['transactions']);
                foreach ($registrations[$i]['transactions'] as $j => $transaction) {
                    $adminFee += (isset($transaction->breakdown['gym']['deposit_handling']) && $transaction->breakdown['gym']['deposit_handling'] > 0) ? $transaction->breakdown['gym']['deposit_handling'] : $transaction->breakdown['gym']['handling'];
                    $cardFees += $transaction->breakdown['gym']['processor'];
                    $adminMeetFee += (isset($transaction->breakdown['host']['deposit_handling']) && $transaction->breakdown['host']['deposit_handling'] > 0) ?  $transaction->breakdown['host']['deposit_handling'] : $transaction->breakdown['host']['handling'];
                    $cardMeetFees += $transaction->breakdown['host']['processor'];
                }

                // dd($usag_refund .' '. $regular_refund );
                $fee_s['admin_fees'] = $adminFee;
                $fee_s['card_fees'] = $cardFees;
                $fee_s['admin_meet_fees'] = $adminMeetFee;
                $fee_s['card_meet_fees'] = $cardMeetFees;
                $fee_s['reg_fees'] = ($registration->athletes->sum('fee') + $reg_meet_fees);
                $fee_s['specialist_fee'] = $speicalist_total_fee;
                $fee_s['reg_meet_fees'] = $reg_meet_fees; //trackthis
                $fee_s['team_fees'] = $teamFee;
                $fee_s['team_meet_fees'] = 0;
                // $registration->athletes->sum('refund') +
                // echo $usag_refund .' '. $regular_refund .' '. $specialistRefundFee .' '. $specialistLateRefundFee .' '. $used_credit .' '. $team_refund;
                $fee_s['refund_fees'] = $usag_refund + $regular_refund + $specialistRefundFee + $specialistLateRefundFee - $used_credit + $team_refund;
                $fee_s['refund_fees'] = $fee_s['refund_fees'] > 0 ? $fee_s['refund_fees'] : 0;
                $fee_s['used_credit'] = $used_credit > 0 ? - $used_credit : 0;
                // echo 'USAG REFUND';
                // dd($usag_refund , $regular_refund , $specialistRefundFee , $specialistLateRefundFee , $used_credit , $team_refund);
                $fee_s['refund_meet_fees'] = $refund_meet_fees;
                $fee_s['late_fee'] = $registration->athletes->sum('late_fee') + $specialistLateFee + $team_late + ($registration->was_late ? $registration->late_fee : 0);

                // $fee_s['total_fees'] = $fee_s['reg_fees'] + $fee_s['admin_fees'] + $fee_s['card_fees'] + $fee_s['late_fee'] - $fee_s['refund_fees'];
                $fee_s['total_fees'] = $fee_s['team_fees'] + $fee_s['reg_fees'] + $fee_s['specialist_fee'] + $fee_s['admin_fees'] + $fee_s['card_fees'] + $fee_s['late_fee'] - $used_credit; // - $fee_s['refund_fees'];
                $fee_s['total_meet_fees'] = $fee_s['reg_meet_fees'] + $fee_s['admin_meet_fees'] + $fee_s['card_meet_fees'] + $fee_s['team_meet_fees']; // - $fee_s['refund_meet_fees'];

                $data['feeArr'][] = $fee_s;

            }
            $data['total_fees'] = $data['reg_fees'] + $data['admin_fees'] + $data['card_fees'];// - $data['refund_fees'];
            $data['total_meet_fees'] = $data['reg_meet_fees'] + $data['admin_meet_fees'] + $data['card_meet_fees'] + $data['team_meet_fees'];// - $data['refund_meet_fees'];
            // $data['changes'] = $registrationAuditReport;
            // dd($data['changes']);
            // dd($data);
            // die();
            return PDF::loadView('PDF.host.meet.reports.registration-detail', $data);
        } catch(\Throwable $e) {
            throw $e;
        }
    }
    public function generateEntryReport(Gym $gym = null) : PdfWrapper {
        try {
            $base = $this->registrations()
                        ->where('status', MeetRegistration::STATUS_REGISTERED);
            /** @var Builder $base */

            if ($gym !== null)
                $base = $base->where('gym_id', $gym->id);

            $registrations = $base->select([
                    'id', 'gym_id', 'meet_id', 'late_refund', 'status'
                ])->with([
                    'gym' => function ($q) {
                        $q->select([
                            'id', 'name', 'short_name',
                        ])->withCount('athletes');
                    },
                    'levels' => function ($q) {

                    }
                ])->orderBy('created_at', 'DESC')
                ->get();

            $sanctions = [];
            $santion_class = [
                SanctioningBody::USAG => 'usag',
                SanctioningBody::USAIGC => 'usaigc',
                SanctioningBody::AAU => 'aau',
                SanctioningBody::NGA => 'nga'
            ];
            

            // sort $this->levels by sanctioning body and sort sanctions based on it
            $this->levels = $this->levels->sortBy(function($level) {
                return $level->sanctioning_body_id;
            });

            foreach($this->levels as $level) {
                $sanction = SanctioningBody::find($level->sanctioning_body_id)->initialism;
                if(isset($sanctions[$sanction]))
                    $sanctions[$sanction] += 1;
                else
                    $sanctions[$sanction] = 1;
            }
            $data = [
                'meet' => $this,
                'registrations' => $registrations,
                'levels' => $this->levels,
                'sanctions' => $sanctions,
                'sanction_class' => $santion_class
            ];
            return PDF::loadView('PDF.host.meet.reports.team_not_athlete', $data); /** @var PdfWrapper $pdf */
        } catch(\Throwable $e) {
            throw $e;
        }
    }
    public function generateSummaryReport(Gym $gym = null) : PdfWrapper {
        try {
            $base = $this->registrations()
                        ->where('status', MeetRegistration::STATUS_REGISTERED);
            /** @var Builder $base */

            if ($gym !== null)
                $base = $base->where('gym_id', $gym->id);

            $registrations = $base->select([
                    'id', 'gym_id', 'meet_id', 'late_refund', 'status'
                ])->with([
                    'gym' => function ($q) {
                        $q->select([
                            'id', 'name', 'short_name',
                        ])->withCount('athletes');
                    },
                    'levels' => function ($q) {

                    }, 
                    'athletes' => function ($q) {

                    },
                ])->orderBy('created_at', 'DESC')
                ->get();

            $esExists = false;
            foreach ($registrations as $registration) {
                if(count($registration->specialists->toArray())){
                    $esExists = true;
                    break;
                }
            }
            $sanctions = [];
            $santion_class = [
                SanctioningBody::USAG => 'usag',
                SanctioningBody::USAIGC => 'usaigc',
                SanctioningBody::AAU => 'aau',
                SanctioningBody::NGA => 'nga'
            ];
            

            // sort $this->levels by sanctioning body and sort sanctions based on it
            $this->levels = $this->levels->sortBy(function($level) {
                return $level->sanctioning_body_id;
            });

            foreach($this->levels as $level) {
                $sanction = SanctioningBody::find($level->sanctioning_body_id)->initialism;
                if(isset($sanctions[$sanction]))
                    $sanctions[$sanction] += 1;
                else
                    $sanctions[$sanction] = 1;
            }

            $data = [
                'meet' => $this,
                'registrations' => $registrations,
                'levels' => $this->levels,
                'sanctions' => $sanctions,
                'esExists' => $esExists,
                'sanction_class' => $santion_class
            ];
            return PDF::loadView('PDF.host.meet.reports.summary', $data); /** @var PdfWrapper $pdf */
        } catch(\Throwable $e) {
            throw $e;
        }
    }

    public function generateEventSpecialistReport(Gym $gym = null, $type = 0) : PdfWrapper {
        try {
            $base = $this->registrations()
                        ->where('status', MeetRegistration::STATUS_REGISTERED);
            /** @var Builder $base */

            if ($gym !== null)
                $base = $base->where('gym_id', $gym->id);
            $registrations = $base->with('levels')->select([
                    'id', 'gym_id', 'meet_id', 'status'
                ])->orderBy('created_at', 'DESC')
                ->get();
            $events  = AthleteSpecialistEvents::all()->pluck('abbreviation','id')->toArray();
            // sort $registrations->levels by sanctioning_body_id
            $registrations->each(function($registration) {
                $registration->levels = $registration->levels->sortBy('sanctioning_body_id');
            });
            $report_data_gym = [];
            $report_data_level = [];
            if($type == 0) // by gym
            {
                foreach ($registrations as $key => $value) {
                    // $report_data_gym[$value->gym->name] = [];
                    foreach ($value->levels as $key1 => $value1) {
                        if($value1->pivot->specialists->count() > 0)
                            $report_data_gym[$value->gym->name][$value1->sanctioning_body->initialism][$value1->level_category->name][$value1->name][] = $value1->pivot->specialists;
                    }
                }
                $data = [
                    'host' => $this->gym,
                    'meet' => $this,
                    'registrations' => $registrations,
                    'report_data_gym' => $report_data_gym,
                    'events' => $events
                ];
                return PDF::loadView('PDF.host.meet.reports.event-specialist', $data); /** @var PdfWrapper $pdf */
            }
            else // by level
            {
                foreach ($registrations as $key => $value) {
                    foreach ($value->levels as $key1 => $value1) {
                        if($value1->pivot->specialists->count() > 0)
                            $report_data_level[$value1->sanctioning_body->initialism][$value1->level_category->name][$value1->name][$value->gym->name] = $value1->pivot->specialists;
                    }
                }
                $data = [
                    'host' => $this->gym,
                    'meet' => $this,
                    'registrations' => $registrations,
                    'report_data_level' => $report_data_level,
                    'events' => $events,
                ];
                // dd($data);
                return PDF::loadView('PDF.host.meet.reports.event-specialist-level', $data); /** @var PdfWrapper $pdf */
            }
            
        } catch(\Throwable $e) {
            throw $e;
        }
    }

    public function generateLeoTShirtReport(Gym $gym = null) : PdfWrapper{
        try {
            $base = $this->registrations()
                        ->where('status', MeetRegistration::STATUS_REGISTERED);
            /** @var Builder $base */

            if ($gym !== null)
                $base = $base->where('gym_id', $gym->id);

            $registrations = $base->select([
                    'id', 'gym_id', 'meet_id', 'status'
                ])->orderBy('created_at', 'DESC')
                ->get();
            $leo_size = [];
            $re_leo_size = [];
            $leo_size_total = [];
            $sub_total = 0;
            
            $tshirt_size = [];
            $re_tshirt_size = [];
            $tshirt_size_total = [];
            $sub_total_tshirt = 0;
            if($this->leo_chart && $registrations){
                $leo_size = $this->leo_chart->sizes->pluck('size','id')->toArray();
                foreach ($registrations as $i => $registration) {
                    $re_leo_size[$i]['name'] = $registration->gym->name;
                    $re_to = 0;
                    foreach ($leo_size as $le_s_id => $val) {
                        $le_at = $registration->athletes->where('leo_size_id', $le_s_id)->count();
                        $re_leo_size[$i][$le_s_id] = $le_at;
                        $re_to  +=  $le_at;
                        $leo_size_total[$le_s_id] = isset($leo_size_total[$le_s_id]) ? $leo_size_total[$le_s_id] : 0;
                        $leo_size_total[$le_s_id] +=  $le_at;
                        $sub_total +=  $le_at;
                    }
                    $re_leo_size[$i]['total'] = $re_to;
                }
            }
            if($this->tshirt_chart && $registrations){
                $tshirt_size = $this->tshirt_chart->sizes->pluck('size','id')->toArray();
                foreach ($registrations as $i => $registration) {
                    $re_tshirt_size[$i]['name'] = $registration->gym->name;
                    $re_to = 0;
                    foreach ($tshirt_size as $le_s_id => $val) {
                        $le_at = $registration->athletes->where('tshirt_size_id', $le_s_id)->count();
                        $re_tshirt_size[$i][$le_s_id] = $le_at;
                        $re_to  +=  $le_at;
                        $tshirt_size_total[$le_s_id] = isset($tshirt_size_total[$le_s_id]) ? $tshirt_size_total[$le_s_id] : 0;
                        $tshirt_size_total[$le_s_id] +=  $le_at;
                        $sub_total_tshirt +=  $le_at;
                    }
                    $re_tshirt_size[$i]['total'] = $re_to;
                }
            }
            $data = [
                'host' => $this->gym,
                'meet' => $this,
                'leo_size' => $leo_size,
                're_leo_size' => $re_leo_size,
                'leo_size_total' => $leo_size_total,
                
                'tshirt_size' => $tshirt_size,
                're_tshirt_size' => $re_tshirt_size,
                'tshirt_size_total' => $tshirt_size_total,
                
                'registrations' => $registrations,
                'sub_total' => $sub_total,
                'sub_total_tshirt' => $sub_total_tshirt,
            ];
            return PDF::loadView('PDF.host.meet.reports.leo-t-shirt', $data); /** @var PdfWrapper $pdf */
        } catch(\Throwable $e) {
            throw $e;
        }
    }
    public function generateLeoTShirtGymReport(Gym $gym = null) : PdfWrapper{
        try {
            $base = $this->registrations()
                        ->where('status', MeetRegistration::STATUS_REGISTERED);
            /** @var Builder $base */

            if ($gym !== null)
                $base = $base->where('gym_id', $gym->id);

            $registrations = $base->select([
                    'id', 'gym_id', 'meet_id', 'status'
                ])->orderBy('created_at', 'DESC')
                ->get();
            $leo_size = [];
            $re_leo_size = [];
            $leo_size_total = [];
            $sub_total = 0;
            if($this->leo_chart && $registrations){
                $leo_size = $this->leo_chart->sizes->pluck('size','id')->toArray();

                foreach ($registrations as $i => $registration) {
                    $data_merge = [];
                    foreach ($leo_size as $le_s_id => $val) {
                        $data['count'] = 0;
                        $data['name'] = $val;
                        $count =  $registration->athletes->where('leo_size_id', $le_s_id)->count();
                        if($count == 0)
                            continue;
                        $data['count'] = $count;
                        $data_merge[] = $data;
                    }
                    $registrations[$i]['leo_size'] = $data_merge;
                }

                // foreach ($leo_size as $le_s_id => $val) {
                //     $data = [];
                //     foreach ($registrations as $i => $registration) {
                //         $data['count'] = 0;
                //         $data['name'] = $val;
                //         $count =  $registration->athletes->where('leo_size_id', $le_s_id)->count();
                //         $data['count'] += $count;
                //         // $re_leo_size[] = $data;
                //         $registrations[$i]['leo_size'] = $data;
                //     }
                    
                // }
            }
            $data = [
                'host' => $this->gym,
                'meet' => $this,
                'leo_size' => $leo_size,
                're_leo_size' => $re_leo_size,
                'leo_size_total' => $leo_size_total,
                'registrations' => $registrations,
                'sub_total' => $sub_total
            ];

            return PDF::loadView('PDF.host.meet.reports.leo-t-shirt-gym', $data); /** @var PdfWrapper $pdf */
        } catch(\Throwable $e) {
            throw $e;
        }
    }
}