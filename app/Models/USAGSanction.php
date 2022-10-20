<?php

namespace App\Models;

use App\Exceptions\CustomBaseException;
use App\Traits\Excludable;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Throwable;

class USAGSanction extends Model
{
    use Excludable;
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'usag_sanctions';

    protected $casts = [
        'payload' => 'json',
    ];

    protected $guarded = ['id'];

    public const SANCTION_STATUS_PENDING = 1;
    public const SANCTION_STATUS_DISMISSED = 2;
    public const SANCTION_STATUS_MERGED = 3;
    public const SANCTION_STATUS_UNASSIGNED = 4;
    public const SANCTION_STATUS_HIDE = 5;
    public const SANCTION_STATUS_DELETE = 6;

    public const SANCTION_ACTION_ADD = 1;
    public const SANCTION_ACTION_UPDATE = 2;
    public const SANCTION_ACTION_DELETE = 3;
    public const SANCTION_ACTION_CHANGE_VENDOR = 3;

    public const SANCTION_SUPPORTED_CATEGORIES = [
        'men' => LevelCategory::GYMNASTICS_MEN,
        'women' => LevelCategory::GYMNASTICS_WOMEN,
    ];

    public const SANCTION_NOTIFICATION_STAGES = [
        0 => 3,
        1 => 7
    ];

    public const CATEGORY_FREEZE = 1;
    public const CATEGORY_UNFREEZE = 2;

    public const LEVEL_UNCHANGED = 1;
    public const LEVEL_ADDED = 2;
    public const LEVEL_REMOVED = 3;
    public const LEVEL_ENABLED = 4;
    public const LEVEL_DISABLED = 5;

    protected $appends = ['action_status','status_label'];

    public function gym()
    {
        return $this->belongsTo(Gym::class);
    }

    public function meet()
    {
        return $this->belongsTo(Meet::class);
    }

    public function level_category()
    {
        return $this->belongsTo(LevelCategory::class, 'level_category_id');
    }

    public function usag_reservations()
    {
        return $this->hasMany(USAGReservation::class, 'usag_reservations_id');
    }

    public function parent()
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function getActionStatusAttribute()
    {
        if (isset($this->action)) {
            if ($this->action == self::SANCTION_ACTION_ADD) {
                return 'New Sanction';
            } elseif ($this->action == self::SANCTION_ACTION_UPDATE) {
                return 'Details Updated';
            } elseif ($this->action == self::SANCTION_ACTION_UPDATE) {
                return 'Sanction Removed';
            } else {
                return 'Vendor Change';
            }
        }

        return 'Action not define';
    }

    public function getStatusLabelAttribute()
    {
        if (isset($this->status)) {
            if ($this->status == self::SANCTION_STATUS_PENDING) {
                return 'Pending';
            } elseif ($this->status == self::SANCTION_STATUS_DISMISSED) {
                return 'Dismissed';
            } elseif ($this->status == self::SANCTION_STATUS_MERGED) {
                return 'Merged';
            } else {
                return 'Unassigned';
            }
        }

        return 'Status not define';
    }

    public static function statusColor($status = null)
    {
        if ($status) {
            if ($status == self::SANCTION_STATUS_PENDING) {
                return 'text-danger';
            } elseif ($status == self::SANCTION_STATUS_DISMISSED) {
                return 'text-warning';
            } elseif ($status == self::SANCTION_STATUS_MERGED) {
                return 'text-success';
            } else {
                return 'text-info';
            }
        }

        return 'text-black';
    }

    public static function actionColor($action = null)
    {
        if ($action) {
            if ($action == self::SANCTION_ACTION_UPDATE) {
                return 'bg-danger';
            } elseif ($action == self::SANCTION_ACTION_ADD) {
                return 'bg-success';
            }
        }
    }

    public static function calculateFinalState(Gym $gym, string $sanction, bool $showMeetData = false) {
        $finalState = [];
        $initialState = [];
        $detailedSteps = [];
        $availableLevels = [];

        $meet = null;
        $assignableMeets = [];
        $meetFields = [
            'id', 'profile_picture', 'name', 'start_date', 'end_date',
            'registration_start_date', 'registration_end_date', 'registration_scratch_end_date',
            'allow_late_registration', 'late_registration_start_date', 'late_registration_end_date',
            'venue_name', 'venue_addr_1', 'venue_addr_2','registration_first_discount_is_enable',
            'registration_second_discount_is_enable', 'registration_third_discount_is_enable'
        ];

        $categoryData = [
            'frozen' => [
                'initial' => false,
                'final' => false,
            ],
        ];

        $sanctions = $gym->usag_sanctions()
                        ->where('number', $sanction)
                        ->orderBy('created_at', 'asc')
                        ->get(); /** @var Collection $sanctions */
        if ($sanctions->count() < 1)
            throw new CustomBaseException('No such sanction from USAG', -1);

        if ($sanctions->where('status', USAGSanction::SANCTION_STATUS_PENDING)->count() < 1)
            throw new CustomBaseException('All events for this sanction were already processed.', -1);

        $parent = $sanctions->where('action', self::SANCTION_ACTION_ADD)
                            ->first(); /** @var USAGSanction $parent */
        if ($parent === null)
            throw new CustomBaseException('Something went wrong while fetching sanction details (No parent node)', -1);

        if ($parent->status == self::SANCTION_STATUS_UNASSIGNED)
            throw new CustomBaseException('This sanction is not assigned', -1);

        if ($parent->status == self::SANCTION_STATUS_DISMISSED)
            throw new CustomBaseException('This sanction was dismissed', -1);

        $categoryData['id'] = $parent->level_category->id;
        $categoryData['name'] = $parent->level_category->name;

        $deletion = $sanctions->where('action', self::SANCTION_ACTION_DELETE)
                                ->first(); /** @var USAGSanction $deletion */
        if (($deletion !== null) && ($deletion->status == self::SANCTION_STATUS_MERGED))
            throw new CustomBaseException('This sanction was deleted', -1);

        $usagMeetData = [
            'start_date' => null,
            'end_date' => null,
            'registration_start_date' => null,
            'registration_end_date' => null,
            'scratch_date' => null,
            'venue_name' => null,
            'venue_addr_1' => null,
            'venue_addr_2' => null,
        ];

        if ($parent->meet_id === null) {
            if ($parent->status != self::SANCTION_STATUS_PENDING)
                throw new CustomBaseException('Something went wrong while fetching sanction details (parent node should be pending for unassigned sanctions)', -1);

            if ($showMeetData) {
                $assignableMeetsCollection = $gym->meets()
                                                ->where('is_archived', false)
                                                ->where('is_published', true)
                                                ->whereHas('categories', function (Builder $query) use ($parent) {
                                                    $query->where('level_category_id', $parent->level_category_id)
                                                        ->where('sanctioning_body_id', SanctioningBody::USAG)
                                                        ->where('officially_sanctioned', false);
                                                })->orderBy('name', 'ASC')
                                                ->get();
                foreach ($assignableMeetsCollection as $m) { /** @var Meet $m */
                    $assignableMeet = [];
                    foreach ($meetFields as $field) {
                        $assignableMeet[$field] = $m->$field;
                    }
                    $assignableMeets[] = $assignableMeet;
                }
            }
        } else {
            $meet = $parent->meet; /** @var Meet $meet */
            if ($parent->status != self::SANCTION_STATUS_MERGED)
                throw new CustomBaseException('Something went wrong while fetching sanction details (parent node should be merged for assigned sanctions)', -1);

            $category = $meet->categories()
                                ->where('sanctioning_body_id', SanctioningBody::USAG)
                                ->where('level_category_id', $parent->level_category_id)
                                ->where('officially_sanctioned', true)
                                ->first(); /** @var LevelCategory $category */
            if ($category === null)
                throw new CustomBaseException('Something went wrong while fetching sanction details (category missing from assigned meet)', -1);

            $categoryData['frozen'] = [
                'initial' => $category->pivot->frozen,
                'final' => $category->pivot->frozen
            ];

            $levels = $meet->levels()
                            ->where('sanctioning_body_id', SanctioningBody::USAG)
                            ->where('level_category_id', $parent->level_category_id)
                            ->get();
            foreach ($levels as $l) { /** @var AthleteLevel $l */
                $data = [
                    'id' => $l->id,
                    'name' => $l->name,
                    'disabled' => $l->pivot->disabled,
                    'has_registrations' => $meet->hasActiveLevelRegistrations($l->id),
                    'registration_fee' => $l->pivot->registration_fee,
                    'late_registration_fee' => $l->pivot->late_registration_fee,
                    'allow_teams' => $l->pivot->allow_teams,
                    'team_registration_fee' => $l->pivot->team_registration_fee,
                    'team_late_registration_fee' => $l->pivot->team_late_registration_fee,
                    'enable_athlete_limit' => $l->pivot->enable_athlete_limit,
                    'athlete_limit' => $l->pivot->athlete_limit,
                ];
                $initialState[$l->code] = $data;

                $data['action'] = self::LEVEL_UNCHANGED;
                $finalState[$l->code] = $data;
            }
        }

        $levels = AthleteLevel::where('sanctioning_body_id', SanctioningBody::USAG)
                                ->where('level_category_id', $parent->level_category_id)
                                ->where('is_disabled', false)
                                ->get();
        foreach ($levels as $l) { /** @var AthleteLevel $l */
            $availableLevels[$l->code] = [
                'id' => $l->id,
                'name' => $l->name,
            ];
        }

        $pending = $sanctions->where('status', self::SANCTION_STATUS_PENDING);
        foreach ($pending as $s) { /** @var USAGSanction $s */
            $detailedStep = [
                'type' => $s->action,
                'freeze' => null,
                'timestamp' => $s->timestamp,
                'issues' => [],
                'added' => [],
                'removed' => [],
            ];

            $payload = $s->payload['Sanction'];

            #region USAG MEET DATA
            if (isset($payload['CompetitionStartDate']) && ($payload['CompetitionStartDate'] != '')) {
                try {
                    $date = Carbon::createFromFormat(DATE_ATOM, $payload['CompetitionStartDate']);
                    $usagMeetData['start_date'] = $date->startOfDay();
                } catch (Throwable $e) {
                    $detailedStep['issues'][] = 'Invalid Competition Start Date ' . $payload['CompetitionStartDate'];
                }
            }

            if (isset($payload['CompetitionEndDate']) && ($payload['CompetitionEndDate'] != '')) {
                try {
                    $date = Carbon::createFromFormat(DATE_ATOM, $payload['CompetitionEndDate']);
                    $usagMeetData['end_date'] = $date->startOfDay();
                } catch (Throwable $e) {
                    $detailedStep['issues'][] = 'Invalid Competition End Date ' . $payload['CompetitionEndDate'];
                }
            }

            if (isset($payload['ReservationPeriodOpens']) && ($payload['ReservationPeriodOpens'] != '')) {
                try {
                    $date = Carbon::createFromFormat(DATE_ATOM, $payload['ReservationPeriodOpens']);
                    $usagMeetData['registration_start_date'] = $date->startOfDay();
                } catch (Throwable $e) {
                    $detailedStep['issues'][] = 'Invalid Reservation Period Start Date ' . $payload['ReservationPeriodOpens'];
                }
            }

            if (isset($payload['ReservationPeriodCloses']) && ($payload['ReservationPeriodCloses'] != '')) {
                try {
                    $date = Carbon::createFromFormat(DATE_ATOM, $payload['ReservationPeriodCloses']);
                    $usagMeetData['registration_end_date'] = $date->startOfDay();
                } catch (Throwable $e) {
                    $detailedStep['issues'][] = 'Invalid Reservation Period End Date ' . $payload['ReservationPeriodCloses'];
                }
            }

            if (isset($payload['CancellationCloseDate']) && ($payload['CancellationCloseDate'] != '')) {
                try {
                    $date = Carbon::createFromFormat(DATE_ATOM, $payload['CancellationCloseDate']);
                    $usagMeetData['scratch_date'] = $date->startOfDay();
                } catch (Throwable $e) {
                    $detailedStep['issues'][] = 'Invalid Cancellation Close Date ' . $payload['CancellationCloseDate'];
                }
            }

            if (isset($payload['SiteName'])) {
                $usagMeetData['venue_name'] = $payload['SiteName'];
            }

            if (isset($payload['SiteAddress1'])) {
                $usagMeetData['venue_addr_1'] = $payload['SiteAddress1'];
            }

            if (isset($payload['SiteAddress2'])) {
                $usagMeetData['venue_addr_2'] = $payload['SiteAddress2'];
            }
            #endregion


            switch ($s->action) {
                case self::SANCTION_ACTION_UPDATE:
                    if ($categoryData['frozen'] == self::CATEGORY_FREEZE) {
                        $detailedStep['freeze'] = self::CATEGORY_UNFREEZE;
                        $categoryData['frozen']['final'] = false;
                    }
                    // continue below

                case self::SANCTION_ACTION_ADD:
                    if ($categoryData['frozen']['final'])
                        throw new CustomBaseException("This category was frozen", -1);

                    $payload = $s->payload;

                    if (!isset($payload['Sanction']['Levels'])) {
                        $detailedStep['issues'][] = 'Levels missing from the data sent by USAG';
                        $detailedSteps[] = $detailedStep;
                    } else {
                        $payload = $payload['Sanction']['Levels'];

                        if (isset($payload['Remove'])) {
                            foreach ($payload['Remove'] as $code) {

                                if (key_exists($code, $finalState)) { // Level exists in meet.

                                    if ($finalState[$code]['disabled']) { // If level is disabled already

                                        $detailedStep['issues'][] =
                                            'Trying to remove level "' . $availableLevels[$code]['name'] .
                                            '" that is already disabled in this meet, at this point';

                                    } else {
                                        // If the level has registrations, not allowed --disable instead of removing--
                                        if ($finalState[$code]['has_registrations']) {
                                            $detailedStep['issues'][] =
                                                'Trying to remove level "' . $availableLevels[$code]['name'] .
                                                '" that already has registrations.';

                                            /*
                                            $finalState[$code]['action'] = self::LEVEL_DISABLED;
                                            $finalState[$code]['disabled'] = true;
                                            */
                                        } else {
                                            $finalState[$code]['action'] = self::LEVEL_REMOVED;
                                            $detailedStep['removed'][] = $finalState[$code];

                                        }
                                    }

                                } else { // If trying to remove a level that's not in the meet

                                    if (key_exists($code, $availableLevels)) { // If this level available at all in our database

                                        $detailedStep['issues'][] =
                                            'Trying to remove level "' . $availableLevels[$code]['name'] .
                                            '" that didn\'t exist in this meet, at this point';

                                    } else { // This level is not in our databasee

                                        $detailedStep['issues'][] =
                                            'Trying to remove unknown level "' . $code . '". Please contact us.';

                                    }
                                }
                            }
                        }

                        if (isset($payload['Add'])) {
                            foreach ($payload['Add'] as $code) {

                                if (!key_exists($code, $availableLevels)) { // This level is not in our databasee
                                    $detailedStep['issues'][] =
                                        'Trying to add unknown level "' . $code . '". Please contact us.';
                                } else { // Level is known to us. Proceed.
                                    if (key_exists($code, $finalState)) { // Level exists in meet already ?

                                        if ($finalState[$code]['disabled']) {   // If existing level is disabled, reenable it

                                            $finalState[$code]['action'] = self::LEVEL_ENABLED;
                                            $finalState[$code]['disabled'] = false;
                                            $detailedStep['added'][$code] = $finalState[$code];

                                        } else { // else, can't add a level twice

                                            $detailedStep['issues'][] =
                                            'Trying to add level "' . $availableLevels[$code]['name'] .
                                            '" that already exists in this meet, at this point';

                                        }
                                    } else { // All good, add new level.

                                        $finalState[$code] = $availableLevels[$code];
                                        $finalState[$code]['disabled'] = false;
                                        $finalState[$code]['has_registrations'] = (
                                            $meet !== null ?
                                            $meet->hasActiveLevelRegistrations($availableLevels[$code]['id']) :
                                            0
                                        );
                                        $finalState[$code]['action'] = self::LEVEL_ADDED;
                                        $detailedStep['added'][$code] = $finalState[$code];

                                    }
                                }
                            }
                        }
                    }



                    break;

                case self::SANCTION_ACTION_CHANGE_VENDOR:
                case self::SANCTION_ACTION_DELETE:
                    $detailedStep['freeze'] = self::CATEGORY_FREEZE;
                    $categoryData['frozen']['final'] = true;
                    break;

                default:
                    break;
            }

            $detailedSteps[] = $detailedStep;
        }

        $result = [
            'initial' => $initialState,
            'details' => $detailedSteps,
            'final' => $finalState,
            'gym' => [
                'id' => $gym->id,
                'name' => $gym->name,
            ],
            'category' => $categoryData,
        ];

        if ($showMeetData) {
            $result['meet'] = null;
            $result['usag_meet_data'] = $usagMeetData;
            if ($meet !== null) {
                $result['meet'] = [];

                foreach ($meetFields as $field) {
                    $result['meet'][$field] = $meet->$field;
                }
            }

            $result['assignable_meets'] = $assignableMeets;
        }

        return $result;
    }

    public static function merge(Gym $gym, string $sanction, string $meet, array $data, $meetDataSwitches = []) {
        $result = [];

        DB::beginTransaction();
        try {
            $sanctions = $gym->usag_sanctions()
                        ->lockForUpdate()
                        ->where('number', $sanction)
                        ->where('status', self::SANCTION_STATUS_PENDING)
                        ->orderBy('created_at', 'asc')
                        ->get(); /** @var Collection $sanctions */

            $calculated = self::calculateFinalState($gym, $sanction, true);

            if (isset($meet) && ($calculated['meet'] == null)) {
                $matches = false;
                foreach ($calculated['assignable_meets'] as $am) {
                    if ($am['id'] == $meet) {
                        $matches = true;
                        break;
                    }
                }

                if (!$matches)
                    throw new CustomBaseException('This sanction cannot be assigned to meet with id "' . $meet . '"', -1);
            } else {
                if ($calculated['meet'] === null)
                    throw new CustomBaseException('You need to choose a meet to assign this sanction to', -1);

                $meet = $calculated['meet']['id'];
            }

            $meet = $gym->meets()->find($meet); /** @var Meet $meet */
            if ($meet === null)
                throw new CustomBaseException('Something went wrong (Failed to load meet)', -1);

            $category = $meet->categories()
                        ->where('level_category_id', $calculated['category']['id'])
                        ->where('sanctioning_body_id', SanctioningBody::USAG)
                        ->first(); /** @var LevelCategory $category */
            if ($category === null)
                throw new CustomBaseException('Something went wrong (Failed to load category)', -1);

            $categoryMeet = CategoryMeet::lockForUpdate()
                                        ->where('meet_id', $meet->id)
                                        ->where('level_category_id', $calculated['category']['id'])
                                        ->where('sanctioning_body_id', SanctioningBody::USAG)
                                        ->first(); /** @var CategoryMeet $categoryMeet */
            if ($categoryMeet === null)
                throw new CustomBaseException('Something went wrong (Failed to load category pivot)', -1);

            #region MEET DATA UPDATE
            if (is_array($meetDataSwitches)) {
                $usagMeetData = $calculated['usag_meet_data'];
                $regEndField = ($meet->allow_late_registration ? 'late_registration_end_date' : 'registration_end_date');
                $regEndFieldText = ($meet->allow_late_registration ? 'late' : '') . ' registration end date';

                if (isset($meetDataSwitches['venue_name']) && $meetDataSwitches['venue_name'] && ($usagMeetData['venue_name'] !== null)) {
                    $meet->venue_name = $usagMeetData['venue_name'];
                }

                if (isset($meetDataSwitches['venue_addr_1']) && $meetDataSwitches['venue_addr_1'] && ($usagMeetData['venue_addr_1'] !== null)) {
                    $meet->venue_addr_1 = $usagMeetData['venue_addr_1'];
                }

                if (isset($meetDataSwitches['venue_addr_2']) && $meetDataSwitches['venue_addr_2'] && ($usagMeetData['venue_addr_2'] !== null)) {
                    $meet->venue_addr_2 = $usagMeetData['venue_addr_2'];
                }

                if (isset($meetDataSwitches['start_date']) && $meetDataSwitches['start_date'] && ($usagMeetData['start_date'] !== null)) {
                    $meet->start_date = $usagMeetData['start_date'];
                }

                if (isset($meetDataSwitches['end_date']) && $meetDataSwitches['end_date'] && ($usagMeetData['end_date'] !== null)) {
                    $meet->end_date = $usagMeetData['end_date'];
                }

                if (isset($meetDataSwitches['registration_start_date']) && $meetDataSwitches['registration_start_date'] && ($usagMeetData['registration_start_date'] !== null)) {
                    $meet->registration_start_date = $usagMeetData['registration_start_date'];
                }

                if (isset($meetDataSwitches['registration_end_date']) && $meetDataSwitches['registration_end_date'] && ($usagMeetData['registration_end_date'] !== null)) {
                    $meet->$regEndField = $usagMeetData['registration_end_date'];
                }

                if (isset($meetDataSwitches['scratch_date']) && $meetDataSwitches['scratch_date'] && ($usagMeetData['scratch_date'] !== null)) {
                    $meet->registration_scratch_end_date = $usagMeetData['scratch_date'];
                }

                if ($meet->start_date > $meet->end_date)
                    throw new CustomBaseException('The meet end date needs to be a date after the meet start date.', -1);

                if ($meet->registration_start_date > $meet->$regEndField)
                    throw new CustomBaseException('The ' . $regEndFieldText . ' date needs to be a date before the registration start date.', -1);

                if ($meet->allow_late_registration) {
                    if ($meet->late_registration_start_date > $meet->$regEndField)
                        throw new CustomBaseException('The ' . $regEndFieldText . ' date needs to be a date before the late registration start date.', -1);
                }

                if ($meet->$regEndField >= $meet->start_date)
                        throw new CustomBaseException('The meet start date should be a date after the '. $regEndFieldText . '.');

                if ($meet->registration_scratch_end_date > $meet->start_date)
                    throw new CustomBaseException('The scratch date should be before or equal to the meet start date.');
            }
            $meet->save();
            #endregion

            $incomingLevels = $data['final'];
            foreach ($calculated['final'] as $code => $level) {
                if (!isset($incomingLevels[$code]))
                    throw new CustomBaseException('Missing level: ' . $code, -1);

                switch ($level['action']) {
                    case self::LEVEL_ENABLED:
                        $levelMeet = $meet->levels()->where('athlete_level_id', $level['id'])->first(); /** @var LevelMeet $levelMeet */
                        if ($levelMeet === null)
                            throw new CustomBaseException('Something went wrong (Failed to load level)', -1);

                        $levelMeet->update([
                            'disabled' => $level['disabled'],
                        ]);

                        if (!$level['has_registrations']) {
                            $levelMeet->update([
                                'registration_fee' => $incomingLevels[$code]['registration_fee'],
                                
                                'registration_fee_first' => $incomingLevels[$code]['registration_fee_first'],
                                'registration_fee_second' => $incomingLevels[$code]['registration_fee_second'],
                                'registration_fee_third' => $incomingLevels[$code]['registration_fee_third'],

                                'late_registration_fee' => $incomingLevels[$code]['late_registration_fee'],
                                'allow_teams' => $incomingLevels[$code]['allow_teams'],
                                'team_registration_fee' => $incomingLevels[$code]['team_registration_fee'],
                                'team_late_registration_fee' => $incomingLevels[$code]['team_late_registration_fee'],
                                'enable_athlete_limit' => $incomingLevels[$code]['enable_athlete_limit'],
                                'athlete_limit' => $incomingLevels[$code]['athlete_limit'],
                                'disabled' => $level['disabled'],
                            ]);
                        }
                        break;

                    case self::LEVEL_ADDED:
                        LevelMeet::create([
                            'athlete_level_id' => $level['id'],
                            'meet_id' => $meet->id,
                            'allow_men' => $category->male,
                            'allow_women'=> $category->female,
                            'registration_fee' => $incomingLevels[$code]['registration_fee'],

                            'registration_fee_first' => $incomingLevels[$code]['registration_fee_first'],
                            'registration_fee_second' => $incomingLevels[$code]['registration_fee_second'],
                            'registration_fee_third' => $incomingLevels[$code]['registration_fee_third'],
                            
                            'late_registration_fee' => $incomingLevels[$code]['late_registration_fee'],
                            'allow_specialist' => false,
                            'specialist_registration_fee' => 0,
                            'specialist_late_registration_fee' => 0,
                            'allow_teams' => $incomingLevels[$code]['allow_teams'],
                            'team_registration_fee' => $incomingLevels[$code]['team_registration_fee'],
                            'team_late_registration_fee' => $incomingLevels[$code]['team_late_registration_fee'],
                            'enable_athlete_limit' => $incomingLevels[$code]['enable_athlete_limit'],
                            'athlete_limit' => $incomingLevels[$code]['athlete_limit'],
                        ]);

                    case self::LEVEL_DISABLED:
                        // Should not occur
                        break;

                    case self::LEVEL_REMOVED:
                        $levelMeet = $meet->levels()->where('athlete_level_id', $level['id'])->first(); /** @var LevelMeet $levelMeet */
                        if ($levelMeet !== null)
                            $levelMeet->pivot->delete();
                        break;
                }
            }

            foreach ($sanctions as $s) { /** @var USAGSanction $s */
                $s->meet_id = $meet->id;
                $s->status = self::SANCTION_STATUS_MERGED;
                $s->save();

                AuditEvent::usagSanctionProcessed(request()->_managed_account, auth()->user(), $s);
            }

            $categoryMeetData = [];
            if (!$categoryMeet->officially_sanctioned) {
                $categoryMeetData['officially_sanctioned'] = true;
                $categoryMeetData['sanction_no'] = $sanction;
            }
            $categoryMeetData['frozen'] = $calculated['category']['frozen']['final'];

            CategoryMeet::where('meet_id', $meet->id)
                        ->where('level_category_id', $calculated['category']['id'])
                        ->where('sanctioning_body_id', SanctioningBody::USAG)
                        ->update($categoryMeetData);

            DB::commit();
            return $meet->fresh()->load(['categories', 'levels']);
        } catch (Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
