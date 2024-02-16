<?php

namespace App\Models;

use App\Exceptions\CustomBaseException;
use App\Helper;
use App\Mail\Registrant\GymRegisteredMailable;
use App\Mail\Registrant\TransportHelpMailable;
use App\Mail\Registrant\GymRegistrationUpdatedMailable;
use App\Traits\Excludable;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Throwable;

class USAGReservation extends Model
{
    use Excludable;
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'usag_reservations';

    protected $casts = [
        'payload' => 'json',
    ];

    protected $guarded = ['id'];

    protected $appends = ['status_label','action_status'];


    public const RESERVATION_STATUS_PENDING = 1;
    public const RESERVATION_STATUS_DISMISSED = 2;
    public const RESERVATION_STATUS_MERGED = 3;
    public const RESERVATION_STATUS_UNASSIGNED = 4;
    public const RESERVATION_STATUS_HIDE = 5;
    public const RESERVATION_STATUS_DELETE = 6;

    public const RESERVATION_ACTION_ADD = 1;
    public const RESERVATION_ACTION_UPDATE = 2;
    public const RESERVATION_ACTION_SCRATCH = 3;

    public const ITEM_ACTION_ADD = 'Add';
    public const ITEM_ACTION_UPDATE = 'Update';
    public const ITEM_ACTION_SCRATCH = 'Scratch';

    public const RESERVATION_NOTIFICATION_STAGES = [
        0 => 3,
        1 => 7
    ];

    public function level_category()
    {
        return $this->belongsTo(LevelCategory::class, 'level_category_id');
    }

    public function getActionStatusAttribute()
    {
        if (isset($this->action)) {
            if ($this->action == self::RESERVATION_ACTION_ADD) {
                return 'New Reservation';
            } elseif ($this->action == self::RESERVATION_ACTION_UPDATE) {
                return 'Details Updated';
            }else{
                return 'Reservation Removed';
            }
        }

        return 'Action not define';
    }

    public function getStatusLabelAttribute()
    {
        if (isset($this->status)) {
            if ($this->status == self::RESERVATION_STATUS_PENDING) {
                return 'Pending';
            } elseif ($this->status == self::RESERVATION_STATUS_DISMISSED) {
                return 'Dismissed';
            } elseif ($this->status == self::RESERVATION_STATUS_MERGED) {
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
            if ($status == self::RESERVATION_STATUS_PENDING) {
                return 'text-danger';
            } elseif ($status == self::RESERVATION_STATUS_DISMISSED) {
                return 'text-warning';
            } elseif ($status == self::RESERVATION_STATUS_MERGED) {
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
            if ($action == self::RESERVATION_ACTION_UPDATE) {
                return 'bg-danger';
            } elseif ($action == self::RESERVATION_ACTION_ADD) {
                return 'bg-success';
            }
        }
    }

    public function gym()
    {
        return $this->belongsTo(Gym::class);
    }

    public function usag_sanction()
    {
        return $this->belongsTo(USAGSanction::class, 'usag_sanction_id');
    }

    public function parent()
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public static function calculateFinalState(Gym $gym, string $sanction, Collection &$reservations = null) {
        $finalState = [
            'ids' => [
                'added' => [
                    'athletes' => [],
                    'coaches' => [],
                ],
                'moved' => [
                ],
                'scratched' => [
                    'athletes' => [],
                    'coaches' => [],
                ],
            ],
            'levels' => [],
            'coaches' => [],
        ];
        $initialState = [
            'levels' => [],
            'coaches' => [],
        ];
        $detailedSteps = [];
        $reservation_ids = [];
        $scratchAthletesList = [];
        $reservations = $gym->usag_reservations()
                        ->whereHas('usag_sanction', function (Builder $q0) use ($sanction) {
                            $q0->where('number', $sanction)
                                ->where('status', USAGSanction::SANCTION_STATUS_MERGED);
                        })->where('status', self::RESERVATION_STATUS_PENDING)
                        ->orderBy('created_at', 'asc')
                        ->get(); /** @var Collection $reservations */;

        if ($reservations->count() < 1)
            throw new CustomBaseException('You have no reservations for this sanctions', -1);

        $reservation_ids = $reservations->pluck('id')->toArray();

        $sanction = USAGSanction::where('number', $sanction)
                                ->where('action', USAGSanction::SANCTION_ACTION_ADD)
                                ->where('status', USAGSanction::SANCTION_STATUS_MERGED)
                                ->first(); /** @var USAGSanction $sanction */
        if ($sanction === null)
            throw new CustomBaseException('Could not retrieve the sanction for this reservation', -1);

        $meet = $sanction->meet; /** @var Meet $meet */
        if ($meet === null)
            throw new CustomBaseException('The sanction host has not yet assigned this sanction to one of their meets. Please try again later.', -1);

        $category = $meet->categories()
                        ->where('id', $sanction->level_category_id)
                        ->where('sanctioning_body_id', SanctioningBody::USAG)
                        ->where('officially_sanctioned', true)
                        ->first(); /** @var LevelCategory $category */
        if ($category === null)
            throw new CustomBaseException('Could not retrieve the category for this reservation', -1);

        if ($category->pivot->frozen)
            throw new CustomBaseException('The category for this reservation on its assigned meet is frozen and cannot accept new reservations', -1);

        $hasRegistration = false;

        try {
            DB::beginTransaction();
            $registration = $meet->registrations()
                                ->where('gym_id', $gym->id)
                                ->where('status', '!=', MeetRegistration::STATUS_CANCELED)
                                ->firsT(); /** @var MeetRegistration $registration */
            if ($registration instanceof MeetRegistration) { // calculate initial state
                $hasRegistration = true;
                $initialState = [
                    'levels' => [],
                    'coaches' => [],
                ];

                $levels = $registration->levels()
                                        ->where('sanctioning_body_id', SanctioningBody::USAG)
                                        ->get();
                foreach ($levels as $level) { /** @var AthleteLevel $level */
                    $registrationLevel = $level->pivot; /** @var LevelRegistration $registrationLevel */

                    $initialState['levels'][$registrationLevel->id] = [
                        'code' => $level->code,
                        'name' => $level->name,
                        'abbreviation' => $level->abbreviation,
                        'athletes' => [],
                        'specialists' => [],
                    ];

                    $athletes = $registrationLevel->athletes()
                                                ->whereNotNull('usag_no')
                                                ->get();
                    foreach ($athletes as $athlete) { /** @var RegistrationAthlete $athlete */
                        $athleteData = [
                            'id' => $athlete->id,
                            'first_name' => $athlete->first_name,
                            'last_name' => $athlete->last_name,
                            'gender' => $athlete->gender,
                            'dob' => $athlete->dob,
                            'is_us_citizen' => $athlete->is_us_citizen,
                            'status' => $athlete->status,
                        ];

                        $initialState['levels'][$registrationLevel->id]['athletes'][$athlete->usag_no] = $athleteData;
                    }

                    // USAG has no specialists for now.

                    $coaches = $registration->coaches()
                                            ->whereNotNull('usag_no')
                                            ->get();
                    foreach ($coaches as $coach) { /** @var RegistrationCoach $coach */
                        $coachData = [
                            'id' => $coach->id,
                            'first_name' => $coach->first_name,
                            'last_name' => $coach->last_name,
                            'gender' => $coach->gender,
                            'dob' => $coach->dob,
                            'status' => $coach->status,
                        ];

                        $initialState['coaches'][$coach->usag_no] = $coachData;
                    }
                }
            } else {
                $registration = $meet->registrations()
                                    ->create([
                                        'gym_id' => $gym->id,
                                        'was_late' => false,
                                        'late_fee' => 0,
                                        'late_refund' => 0,
                                        'status' => MeetRegistration::STATUS_REGISTERED
                                    ]);
            }

            $TwentyYearsAgo =  Carbon::now()->subDecades(3);

            foreach ($reservations as $r) { /** @var USAGReservation $r */
                $hasChanges = false;
                $coaches = [];
                $detailedStep = [
                    'type' => $r->action,
                    'timestamp' => $r->timestamp,
                    'issues' => [],
                    'added' => [
                        'athletes' => [],
                        'coaches' => [],
                    ],
                    'moved' => [
                    ],
                    'updated' => [
                        'athletes' => [],
                        'coaches' => [],
                    ],
                    'scratched' => [
                        'athletes' => [],
                        'coaches' => [],
                    ],
                ];

                if (isset($r->payload['Reservation']['Details']) && isset($r->payload['Reservation']['Details']['Gymnasts'])) {
                    $athleteActions = $r->payload['Reservation']['Details']['Gymnasts'];

                    if (isset($athleteActions[self::ITEM_ACTION_SCRATCH])) {
                        $athletes = $athleteActions[self::ITEM_ACTION_SCRATCH];

                        foreach ($athletes as $a) {
                            $hasChanges = true;
                            $athlete = $registration->athletes()
                                                    ->where('usag_no', $a['USAGID'])
                                                    ->where('status', '!=', RegistrationAthlete::STATUS_SCRATCHED)
                                                    ->first(); /** @var RegistrationAthlete $athlete */
                            if ($athlete !== null) {
                                if ($athlete->in_waitlist) {
                                    $detailedStep['issues'][] = 'Trying to scratch athlete with USAG No. ' . $a['USAGID'] . ' that is currently in waitlist.';
                                    continue;
                                }

                                if ($athlete->status !== RegistrationAthlete::STATUS_REGISTERED) {
                                    if ($athlete->transaction !== null) {
                                        if ($athlete->transaction->status === MeetTransaction::STATUS_PENDING)
                                            $detailedStep['issues'][] = 'Trying to scratch athlete with USAG No. ' . $a['USAGID'] . ' that is part of a pending transaction.';
                                    } else {
                                        $detailedStep['issues'][] = 'Something went wrong while processing athlete with USAG No. ' . $a['USAGID'] . ' (missing transaction for pending athlete)';
                                        continue;
                                    }
                                }

                                $athlete->status = RegistrationAthlete::STATUS_SCRATCHED;
                                $athlete->save();

                                if (!isset($detailedStep['scratched']['athletes'][$athlete->registration_level->_uid()])) {
                                    $detailedStep['scratched']['athletes'][$athlete->registration_level->_uid()] = [
                                        'code' => $athlete->registration_level->level->code,
                                        'name' => $athlete->registration_level->level->name,
                                        'abbreviation' => $athlete->registration_level->level->abbreviation,
                                    ];
                                }

                                $finalState['ids']['scratched']['athletes'][$a['USAGID']] = (
                                    isset($finalState['ids']['scratched']['athletes'][$a['USAGID']]) ?
                                    $finalState['ids']['scratched']['athletes'][$a['USAGID']] += 1 :
                                    1
                                );
                                $scratchAthletesList[$a['USAGID']][$athlete->registration_level->_uid()] = true;
                                $detailedStep['scratched']['athletes'][$athlete->registration_level->_uid()][$a['USAGID']] = [
                                    'id' => $athlete->id,
                                    'first_name' => $athlete->first_name,
                                    'last_name' => $athlete->last_name,
                                    'gender' => $athlete->gender,
                                    'dob' => $athlete->dob,
                                    'is_us_citizen' => $athlete->is_us_citizen,
                                    'status' => RegistrationAthlete::STATUS_SCRATCHED,
                                ];
                            } else {
                                $detailedStep['issues'][] = 'Trying to scratch athlete with USAG No. ' . $a['USAGID'] . ' that could not be found in local database.';
                            }
                        }
                    }

                    if (isset($athleteActions[self::ITEM_ACTION_UPDATE])) {
                        $athletes = $athleteActions[self::ITEM_ACTION_UPDATE];

                        foreach ($athletes as $a) {
                            $hasChanges = true;
                            $athlete = $registration->athletes()
                                                    ->where('usag_no', $a['USAGID'])
                                                    ->where('status', '!=', RegistrationAthlete::STATUS_SCRATCHED)
                                                    ->first(); /** @var RegistrationAthlete $athlete */
                            if ($athlete !== null) {
                                if ($athlete->in_waitlist) {
                                    $detailedStep['issues'][] = 'Trying to update athlete with USAG No. ' . $a['USAGID'] . ' that is currently in waitlist.';
                                    continue;
                                }

                                if ($athlete->status !== RegistrationAthlete::STATUS_REGISTERED) {
                                    if ($athlete->transaction !== null) {
                                        if ($athlete->transaction->status === MeetTransaction::STATUS_PENDING)
                                            $detailedStep['issues'][] = 'Trying to update athlete with USAG No. ' . $a['USAGID'] . ' that is part of a pending transaction.';
                                    } else {
                                        $detailedStep['issues'][] = 'Something went wrong while processing athlete with USAG No. ' . $a['USAGID'] . ' (missing transaction for pending athlete)';
                                        continue;
                                    }
                                }

                                if (isset($a['Gender'])) {
                                    if (!in_array($a['Gender'], ['female', 'male'])) {
                                        $detailedStep['issues'][] = 'Invalid gender "' . $a['Gender'] . '" for athlete with USAG No. ' . $a['USAGID'];
                                        $a['Gender'] = $athlete->gender;
                                    }
                                }
                                $gender = isset($a['Gender']) ? $a['Gender'] : $athlete->gender;

                                if (isset($a['Level']) && ($a['Level'] != $athlete->registration_level->level->code)) {
                                    $newAthleteLevel = $meet->levels()
                                        ->where('sanctioning_body_id', SanctioningBody::USAG)
                                        ->where('code', $a['Level']);
                                    if ($athlete['gender'] == 'male') {
                                        $newAthleteLevel->wherePivot('allow_men', true);
                                    } else {
                                        $newAthleteLevel->wherePivot('allow_women', true);
                                    }
                                    $newAthleteLevel = $newAthleteLevel->first(); /** @var $newAthleteLevel */

                                    if ($newAthleteLevel === null) {
                                        $detailedStep['issues'][] = 'Trying to move athlete with USAG No. ' . $a['USAGID'] . ' in level "' . $a['Level'] . '" that doesn\'t exists in local database.';
                                        continue;
                                    }

                                    $oldRegistrationLevel = $athlete->registration_level;

                                    $newRegistrationLevel = $registration->levels()
                                                                    ->where('sanctioning_body_id', SanctioningBody::USAG)
                                                                    ->where('code', $a['Level'])
                                                                    ->wherePivot('allow_men', $newAthleteLevel->pivot->allow_men)
                                                                    ->wherePivot('allow_women', $newAthleteLevel->pivot->allow_women)
                                                                    ->first(); /** @var $newAthleteLevel */
                                    if ($newRegistrationLevel === null) {
                                        $newRegistrationLevel = $registration->levels()->attach($newAthleteLevel->id, [
                                                                                'allow_men' => $newAthleteLevel->pivot->allow_men,
                                                                                'allow_women' => $newAthleteLevel->pivot->allow_women,
                                                                                'registration_fee' => $newAthleteLevel->pivot->registration_fee,
                                                                                'late_registration_fee' => $newAthleteLevel->pivot->late_registration_fee,
                                                                                'allow_specialist' => $newAthleteLevel->pivot->allow_specialist,
                                                                                'specialist_registration_fee' => $newAthleteLevel->pivot->specialist_registration_fee,
                                                                                'specialist_late_registration_fee' => $newAthleteLevel->pivot->specialist_late_registration_fee,
                                                                                'allow_teams' => $newAthleteLevel->pivot->allow_teams,
                                                                                'team_registration_fee' => $newAthleteLevel->pivot->team_registration_fee,
                                                                                'team_late_registration_fee' => $newAthleteLevel->pivot->team_late_registration_fee,
                                                                                'enable_athlete_limit' => $newAthleteLevel->pivot->enable_athlete_limit,
                                                                                'athlete_limit' => $newAthleteLevel->pivot->athlete_limit,
                                                                            ]); /** @var LevelRegistration $newRegistrationLevel */

                                        $newRegistrationLevel = LevelRegistration::where('meet_registration_id', $registration->id)
                                                                                ->where('level_id', $newAthleteLevel->id)
                                                                                ->where('allow_men', $newAthleteLevel->pivot->allow_men)
                                                                                ->where('allow_women', $newAthleteLevel->pivot->allow_women)
                                                                                ->first();
                                    } else {
                                        $newRegistrationLevel = $newRegistrationLevel->pivot; /** @var LevelRegistration $newRegistrationLevel */
                                    }

                                    if (!isset($detailedStep['moved'][$newRegistrationLevel->_uid()])) {
                                        $detailedStep['moved'][$newRegistrationLevel->_uid()] = [
                                            'code' => $newRegistrationLevel->level->code,
                                            'name' => $newRegistrationLevel->level->name,
                                            'abbreviation' => $newRegistrationLevel->level->abbreviation,
                                        ];
                                    }

                                    if (!isset($finalState['ids']['moved'][$a['USAGID']])) {
                                        $finalState['ids']['moved'][$a['USAGID']] = $oldRegistrationLevel->id;
                                    }

                                    $detailedStep['moved'][$newRegistrationLevel->_uid()][$a['USAGID']] = [
                                        'id' => $athlete->id,
                                        'original_level' => $oldRegistrationLevel->id,
                                        'first_name' => $athlete->first_name,
                                        'last_name' => $athlete->last_name,
                                        'gender' => $athlete->gender,
                                        'dob' => $athlete->dob,
                                        'is_us_citizen' => $athlete->is_us_citizen,
                                        'status' => $athlete->status,
                                    ];

                                    $athlete->level_registration_id = $newRegistrationLevel->id;
                                    $athlete->save();
                                    $athlete->fresh();
                                }

                                $dob = null;
                                if (isset($a['DOB'])) {
                                    $dob = \DateTime::createFromFormat('Y-m-d', $a['DOB']);
                                    if (($dob === null) || ($dob === false)) {
                                        $detailedStep['issues'][] = 'Invalid date value "' . $a['DOB'] . '" for athlete with USAG No. ' . $a['USAGID'];
                                        $dob = null;
                                    } else {
                                        $dob = $dob->setTime(0, 0);
                                    }
                                }

                                if (isset($a['USCitizen'])) {
                                    if (!in_array($a['USCitizen'], ['0', '1'])) {
                                        $detailedStep['issues'][] = 'Invalid US citizen value "' . $a['USCitizen'] . '" for athlete with USAG No. ' . $a['USAGID'];
                                        $a['USCitizen'] = $athlete->is_us_citizen;
                                    }
                                }

                                $athleteData = [
                                    'id' => $athlete->id,
                                    'first_name' => isset($a['FirstName']) ? $a['FirstName'] : $athlete->first_name,
                                    'last_name' => isset($a['LastName']) ? $a['LastName'] : $athlete->last_name,
                                    'gender' => $gender,
                                    'dob' => ($dob !== null) ? $dob : $athlete->dob,
                                    'is_us_citizen' => isset($a['USCitizen']) ? ($a['USCitizen'] == '1') : $athlete->is_us_citizen,
                                    'status' => $athlete->status,
                                ];

                                $athlete->update($athleteData);
                                $athlete->save();

                                if (!isset($detailedStep['updated']['athletes'][$athlete->registration_level->_uid()])) {
                                    $detailedStep['updated']['athletes'][$athlete->registration_level->_uid()] = [
                                        'code' => $athlete->registration_level->level->code,
                                        'name' => $athlete->registration_level->level->name,
                                        'abbreviation' => $athlete->registration_level->level->abbreviation,
                                    ];
                                }

                                $detailedStep['updated']['athletes'][$athlete->registration_level->_uid()][$a['USAGID']] = $athleteData;
                            } else {
                                $detailedStep['issues'][] = 'Trying to edit athlete with USAG No. ' . $a['USAGID'] . ' that could not be found in local database.';
                            }
                        }
                    }

                    if (isset($athleteActions[self::ITEM_ACTION_ADD])) {
                        $athletes = $athleteActions[self::ITEM_ACTION_ADD];

                        foreach ($athletes as $a) {
                            $hasChanges = true;
                            $athlete = $registration->athletes()
                                                    ->where('usag_no', $a['USAGID'])
                                                    // ->where('status', '!=', RegistrationAthlete::STATUS_SCRATCHED)
                                                    ->first(); /** @var RegistrationAthlete $athlete */
                            
                            // if ($athlete !== null) {
                            //     dd($athlete);
                            //     $detailedStep['issues'][] = 'Trying to add athlete with USAG No. ' . $a['USAGID'] . ' that already exists in local database.';
                            //     continue;
                            // }

                            $athlete = [
                                'issues' => [],
                                'status' => RegistrationAthlete::STATUS_REGISTERED,
                            ];

                            if (isset($a['Gender'])) {
                                if (!in_array($a['Gender'], ['female', 'male'])) {
                                    $athlete['issues'][] = 'Invalid gender "' . $a['Gender'] . '" for athlete with USAG No. ' . $a['USAGID'];
                                } else {
                                    $athlete['gender'] = $a['Gender'];
                                }
                            } else {
                                $athlete['issues'][] = 'Missing gender for athlete with USAG No. ' . $a['USAGID'];
                                continue;
                            }

                            if (!isset($a['Level'])) {
                                $detailedStep['issues'][] = 'Missing level for athlete with USAG No. ' . $a['USAGID'];
                                continue;
                            }

                            $athleteLevel = $meet->levels()
                                        ->where('sanctioning_body_id', SanctioningBody::USAG)
                                        ->where('code', $a['Level']);
                            if ($athlete['gender'] == 'male') {
                                $athleteLevel->wherePivot('allow_men', true);
                            } else {
                                $athleteLevel->wherePivot('allow_women', true);
                            }
                            $athleteLevel = $athleteLevel->first(); /** @var AthleteLevel $athleteLevel */

                            if ($athleteLevel === null) {
                                $detailedStep['issues'][] = 'Trying to add athlete with USAG No. ' . $a['USAGID'] . ' in level "' . $a['Level'] . '" that doesn\'t exists in local database.';
                                continue;
                            }

                            $registrationLevel = $registration->levels()
                                                            ->where('sanctioning_body_id', SanctioningBody::USAG)
                                                            ->where('code', $a['Level'])
                                                            ->wherePivot('allow_men', $athleteLevel->pivot->allow_men)
                                                            ->wherePivot('allow_women', $athleteLevel->pivot->allow_women)
                                                            ->first(); /** @var AthleteLevel $registrationLevel */

                            if ($registrationLevel === null) {
                                $registrationLevel = $registration->levels()->attach($athleteLevel->id, [
                                                                        'allow_men' => $athleteLevel->pivot->allow_men,
                                                                        'allow_women' => $athleteLevel->pivot->allow_women,
                                                                        'registration_fee' => $athleteLevel->pivot->registration_fee,
                                                                        'late_registration_fee' => $athleteLevel->pivot->late_registration_fee,
                                                                        'allow_specialist' => $athleteLevel->pivot->allow_specialist,
                                                                        'specialist_registration_fee' => $athleteLevel->pivot->specialist_registration_fee,
                                                                        'specialist_late_registration_fee' => $athleteLevel->pivot->specialist_late_registration_fee,
                                                                        'allow_teams' => $athleteLevel->pivot->allow_teams,
                                                                        'team_registration_fee' => $athleteLevel->pivot->team_registration_fee,
                                                                        'team_late_registration_fee' => $athleteLevel->pivot->team_late_registration_fee,
                                                                        'enable_athlete_limit' => $athleteLevel->pivot->enable_athlete_limit,
                                                                        'athlete_limit' => $athleteLevel->pivot->athlete_limit,
                                                                    ]); /** @var LevelRegistration $registrationLevel */

                                $registrationLevel = LevelRegistration::where('meet_registration_id', $registration->id)
                                                                        ->where('level_id', $athleteLevel->id)
                                                                        ->where('allow_men', $athleteLevel->pivot->allow_men)
                                                                        ->where('allow_women', $athleteLevel->pivot->allow_women)
                                                                        ->first();
                            } else {
                                $registrationLevel = $registrationLevel->pivot; /** @var LevelRegistration $registrationLevel */
                            }

                            if (isset($a['FirstName'])) {
                                $athlete['first_name'] = $a['FirstName'];
                            } else {
                                $athlete['issues'][] = 'Missing first name for athlete with USAG No. ' . $a['USAGID'];
                            }

                            if (isset($a['LastName'])) {
                                $athlete['last_name'] = $a['LastName'];
                            } else {
                                $athlete['issues'][] = 'Missing last name for athlete with USAG No. ' . $a['USAGID'];
                            }

                            if (isset($a['DOB'])) {
                                $dob = \DateTime::createFromFormat('Y-m-d', $a['DOB']);
                                if (($dob === null) || ($dob === false)) {
                                    $athlete['issues'][] = 'Invalid date value "' . $a['DOB'] . '" for athlete with USAG No. ' . $a['USAGID'];
                                } else {
                                    $dob = $dob->setTime(0, 0);
                                    $athlete['dob'] = $dob;
                                }
                            } else {
                                $athlete['issues'][] = 'Missing date of birth for athlete with USAG No. ' . $a['USAGID'];
                            }

                            if (isset($a['USCitizen'])) {
                                if (!in_array($a['USCitizen'], ['0', '1'])) {
                                    $athlete['issues'][] = 'Invalid US citizen value "' . $a['USCitizen'] . '" for athlete with USAG No. ' . $a['USAGID'];
                                } else {
                                    $athlete['is_us_citizen'] = ($a['USCitizen'] == '1');
                                }
                            } else {
                                $athlete['issues'][] = 'Missing US citizen field for athlete with USAG No. ' . $a['USAGID'];
                            }

                            if(isset($scratchAthletesList[$a['USAGID']])
                            && isset($scratchAthletesList[$a['USAGID']][$registrationLevel->_uid()])
                            && $scratchAthletesList[$a['USAGID']][$registrationLevel->_uid()] == true)
                            {
                                foreach ($detailedSteps as $key => $value) {
                                    $scr = $value['scratched']['athletes'];
                                    if(isset($scr[$registrationLevel->_uid()]) && isset($scr[$registrationLevel->_uid()][$a['USAGID']]))
                                    {
                                        $finalState['ids']['scratched']['athletes'][$a['USAGID']] -= 1 ;
                                        unset($detailedSteps[$key]['scratched']['athletes'][$registrationLevel->_uid()][$a['USAGID']]);
                                        break;
                                    }
                                }
                                $scratchAthletesList[$a['USAGID']][$registrationLevel->_uid()] = false;
                                $athlete_to_update = $registration->athletes()
                                                    ->where('usag_no', $a['USAGID'])
                                                    ->first(); /** @var RegistrationAthlete $athlete */
                                $athlete_to_update->status = RegistrationAthlete::STATUS_REGISTERED;
                                $athlete_to_update->save();
                                continue;
                            }

                            if (count($athlete['issues']) < 1) {
                                unset($athlete['issues']);
                                $athleteData = $athlete;

                                $athlete['level_registration_id'] = $registrationLevel->id;
                                $athlete['usag_no'] = $a['USAGID'];
                                $athlete['usag_active'] = true;

                                $athlete = $registration->athletes()
                                                        ->create($athlete);
                                $athlete->save();

                                $athleteData['id'] = $athlete->id;
                                
                                if (!isset($detailedStep['added']['athletes'][$registrationLevel->_uid()])) {
                                    $detailedStep['added']['athletes'][$athlete->registration_level->_uid()] = [
                                        'code' => $athlete->registration_level->level->code,
                                        'name' => $athlete->registration_level->level->name,
                                        'abbreviation' => $athlete->registration_level->level->abbreviation,
                                    ];
                                }

                                $finalState['ids']['added']['athletes'][$a['USAGID']] = (
                                    isset($finalState['ids']['added']['athletes'][$a['USAGID']]) ?
                                    $finalState['ids']['added']['athletes'][$a['USAGID']]++ :
                                    1
                                );

                                $detailedStep['added']['athletes'][$registrationLevel->_uid()][$a['USAGID']] = $athleteData;
                            } else {
                                $detailedStep['issues'] = array_merge($detailedStep['issues'], $athlete['issues']);
                            }
                        }
                    }
                }

                if (isset($r->payload['Reservation']['Details']) && isset($r->payload['Reservation']['Details']['Coaches'])) {
                    $coachActions = $r->payload['Reservation']['Details']['Coaches'];

                    if (isset($coachActions[self::ITEM_ACTION_SCRATCH])) {
                        $coaches = $coachActions[self::ITEM_ACTION_SCRATCH];

                        foreach ($coaches as $c) {
                            $hasChanges = true;
                            $coach = $registration->coaches()
                                                    ->where('usag_no', $c['USAGID'])
                                                    ->where('status', '!=', RegistrationCoach::STATUS_SCRATCHED)
                                                    ->first(); /** @var RegistrationCoach $coach */
                            if ($coach !== null) {
                                if ($coach->in_waitlist) {
                                    $detailedStep['issues'][] = 'Trying to scratch coach with USAG No. ' . $c['USAGID'] . ' that is currently in waitlist.';
                                    continue;
                                }

                                if ($coach->status !== RegistrationCoach::STATUS_REGISTERED) {
                                    if ($coach->transaction !== null) {
                                        if ($coach->transaction->status === MeetTransaction::STATUS_PENDING)
                                            $detailedStep['issues'][] = 'Trying to scratch coach with USAG No. ' . $c['USAGID'] . ' that is part of a pending transaction.';
                                    } else {
                                        $detailedStep['issues'][] = 'Something went wrong while processing coach with USAG No. ' . $c['USAGID'] . ' (missing transaction for pending athlete)';
                                        continue;
                                    }
                                }

                                $coach->status = RegistrationCoach::STATUS_SCRATCHED;
                                $coach->save();

                                $finalState['ids']['scratched']['coaches'][$c['USAGID']] = (
                                    isset($finalState['ids']['scratched']['coaches'][$c['USAGID']]) ?
                                    $finalState['ids']['scratched']['coaches'][$c['USAGID']]++ :
                                    1
                                );

                                $detailedStep['scratched']['coaches'][$c['USAGID']] = [
                                    'id' => $coach->id,
                                    'first_name' => $coach->first_name,
                                    'last_name' => $coach->last_name,
                                    'status' => RegistrationCoach::STATUS_SCRATCHED,
                                ];
                            } else {
                                $detailedStep['issues'][] = 'Trying to scratch coach with USAG No. ' . $c['USAGID'] . ' that could not be found in local database.';
                            }
                        }
                    }

                    if (isset($coachActions[self::ITEM_ACTION_UPDATE])) {
                        $coaches = $coachActions[self::ITEM_ACTION_UPDATE];

                        foreach ($coaches as $c) {
                            $hasChanges = true;
                            $coach = $registration->coaches()
                                                    ->where('usag_no', $c['USAGID'])
                                                    ->where('status', '!=', RegistrationCoach::STATUS_SCRATCHED)
                                                    ->first(); /** @var RegistrationCoach $coach */
                            if ($coach !== null) {
                                if ($coach->in_waitlist) {
                                    $detailedStep['issues'][] = 'Trying to update coach with USAG No. ' . $c['USAGID'] . ' that is currently in waitlist.';
                                    continue;
                                }

                                if ($coach->status !== RegistrationCoach::STATUS_REGISTERED) {
                                    if ($coach->transaction !== null) {
                                        if ($coach->transaction->status === MeetTransaction::STATUS_PENDING)
                                            $detailedStep['issues'][] = 'Trying to update coach with USAG No. ' . $c['USAGID'] . ' that is part of a pending transaction.';
                                    } else {
                                        $detailedStep['issues'][] = 'Something went wrong while processing coach with USAG No. ' . $c['USAGID'] . ' (missing transaction for pending athlete)';
                                        continue;
                                    }
                                }

                                $coachData = [
                                    'id' => $coach->id,
                                    'first_name' => isset($c['FirstName']) ? $c['FirstName'] : $coach->first_name,
                                    'last_name' => isset($c['LastName']) ? $c['LastName'] : $coach->last_name,
                                    'status' => $coach->status,
                                ];

                                $coach->update($coachData);
                                $coach->save();

                                $detailedStep['updated']['coaches'][$c['USAGID']] = $coachData;
                            } else {
                                $detailedStep['issues'][] = 'Trying to edit coach with USAG No. ' . $c['USAGID'] . ' that could not be found in local database.';
                            }
                        }
                    }

                    if (isset($coachActions[self::ITEM_ACTION_ADD])) {
                        $coaches = $coachActions[self::ITEM_ACTION_ADD];

                        foreach ($coaches as $c) {
                            $hasChanges = true;

                            $coach = $registration->coaches()
                                                    ->where('usag_no', $c['USAGID'])
                                                    ->where('status', '!=', RegistrationCoach::STATUS_SCRATCHED)
                                                    ->first(); /** @var RegistrationCoach $coach */

                            if ($coach !== null) {
                                $detailedStep['issues'][] = 'Trying to add coach with USAG No. ' . $c['USAGID'] . ' that already exists in local database.';
                                continue;
                            }

                            $coach = [
                                'issues' => [],
                                'status' => RegistrationAthlete::STATUS_REGISTERED,
                            ];

                            if (isset($c['FirstName'])) {
                                $coach['first_name'] = $c['FirstName'];
                            } else {
                                $coach['issues'][] = 'Missing first name for coach with USAG No. ' . $c['USAGID'];
                            }

                            if (isset($c['LastName'])) {
                                $coach['last_name'] = $c['LastName'];
                            } else {
                                $coach['issues'][] = 'Missing last name for coach with USAG No. ' . $c['USAGID'];
                            }

                            if (count($coach['issues']) < 1) {
                                unset($coach['issues']);
                                $coachData = $coach;

                                $coach['gender'] = 'male'; // Default, not provided by USAG
                                $coach['dob'] = $TwentyYearsAgo; // Default, not provided by USAG
                                $coach['usag_no'] = $c['USAGID'];
                                $coach['usag_active'] = true;

                                $coach = $registration->coaches()
                                                        ->create($coach);
                                $coach->save();

                                $coachData['id'] = $coach->id;

                                $finalState['ids']['added']['coaches'][$c['USAGID']] = (
                                    isset($finalState['ids']['added']['coaches'][$c['USAGID']]) ?
                                    $finalState['ids']['added']['coaches'][$c['USAGID']]++ :
                                    1
                                );

                                $detailedStep['added']['coaches'][$c['USAGID']] = $coachData;
                            } else {
                                $detailedStep['issues'] = array_merge($detailedStep['issues'], $coach['issues']);
                            }
                        }
                    }
                }

                if ($hasChanges)
                    $detailedSteps[] = $detailedStep;
            }
            // dd($detailedSteps);
            // dd($finalState);
            // calculate final state
            $registration->fresh();

            $levels = $registration->levels()
                                    ->where('sanctioning_body_id', SanctioningBody::USAG)
                                    ->get();
            foreach ($levels as $level) { /** @var AthleteLevel $level */
                $registrationLevel = $level->pivot; /** @var LevelRegistration $registrationLevel */

                $finalState['levels'][$registrationLevel->id] = [
                    'uid' => $registrationLevel->_uid(),
                    'name' => $registrationLevel->level->name,
                    'code' => $registrationLevel->level->code,
                    'allow_men' => $registrationLevel->allow_men,
                    'allow_women' => $registrationLevel->allow_women,
                    'has_team' => $registrationLevel->has_team,
                    'was_late' => $registrationLevel->was_late,
                    'team_fee' => $registrationLevel->team_fee,
                    'team_late_fee' => $registrationLevel->team_late_fee,
                    'team_refund' => $registrationLevel->team_refund,
                    'team_late_refund' => $registrationLevel->team_late_refund,
                    'athletes' => [],
                    'specialists' => [],
                ];

                $tshirtRequired = ($meet->tshirt_size_chart_id !== null);
                $clotheSizingRequired = $tshirtRequired || ($meet->leo_size_chart_id !== null);

                $athletes = $registrationLevel->athletes()
                                            ->whereNotNull('usag_no')
                                            ->get();                        
                foreach ($athletes as $athlete) { /** @var RegistrationAthlete $athlete */
                    $tshirt = null;
                    $leo = null;

                    if ($clotheSizingRequired) {
                        $rosterAthlete = $gym->athletes()->where('usag_no', $athlete->usag_no)->first();
                        if ($rosterAthlete !== null) {
                            $tshirt = $rosterAthlete->tshirt_size_id;
                            $leo = $rosterAthlete->leo_size_id;
                        }
                    }
                    $processed_already = false;
                    if($athlete->status == RegistrationAthlete::STATUS_SCRATCHED)
                    {
                        $processed_already = $athlete->refund > 0 ? true : false;
                    }
                    $athleteData = [
                        'id' => $athlete->id,
                        'first_name' => $athlete->first_name,
                        'last_name' => $athlete->last_name,
                        'gender' => $athlete->gender,
                        'dob' => $athlete->dob,
                        'is_us_citizen' => $athlete->is_us_citizen,
                        'tshirt_size_id' => $athlete->tshirt_size_id,
                        'leo_size_id' => $athlete->leo_size_id,
                        'usag_no' => $athlete->usag_no,
                        'was_late' => $athlete->was_late,
                        'in_waitlist' => $athlete->in_waitlist,
                        'fee' => $athlete->fee,
                        'late_fee' => $athlete->late_fee,
                        'refund' => $athlete->refund,
                        'late_refund' => $athlete->late_refund,
                        'status' => $athlete->status,
                        'tshirt_size_id' => $tshirt,
                        'leo_size_id' => $leo,
                        'processed_already' => $processed_already,
                    ];

                    $finalState['levels'][$registrationLevel->id]['athletes'][$athlete->usag_no] = $athleteData;
                }

                // USAG has no specialists for now.

                $coaches = $registration->coaches()
                                        ->whereNotNull('usag_no')
                                        ->get();
                foreach ($coaches as $coach) { /** @var RegistrationCoach $coach */
                    $tshirt = null;

                    if ($tshirtRequired) {
                        $rosterCoach = $gym->coaches()->where('usag_no', $coach->usag_no)->first();
                        if ($rosterCoach !== null) {
                            $tshirt = $rosterCoach->tshirt_size_id;
                        }
                    }

                    $coachData = [
                        'id' => $coach->id,
                        'first_name' => $coach->first_name,
                        'last_name' => $coach->last_name,
                        'gender' => $coach->gender,
                        'dob' => $coach->dob,
                        'tshirt_size_id' => $coach->tshirt_size_id,
                        'usag_no' => $coach->usag_no,
                        'was_late' => $coach->was_late,
                        'in_waitlist' => $coach->in_waitlist,
                        'status' => $coach->status,
                        'tshirt_size_id' => $tshirt,
                    ];

                    $finalState['coaches'][$coach->usag_no] = $coachData;
                }
            }

            DB::rollBack();
        } catch (Throwable $e) {
            DB::rollBack();
            throw $e;
        }

        $result = [
            'initial' => $initialState,
            'details' => $detailedSteps,
            'final' => $finalState,
            'gym' => [
                'id' => $gym->id,
                'name' => $gym->name,
            ],
            'category' => $category->toArray(),
            'registration_id' => ($hasRegistration ? $registration->id : null),
            'meet' => [
                'id' => $meet->id,
                'name' => $meet->name,
            ],
            'reservation_ids' => $reservation_ids,
        ];

        return $result;
    }

    public static function merge(Gym $gym, string $sanction, array $data, array $summary, array $method, bool $useBalance,  $coupon, bool $enable_travel_arrangements, $onetimeach = null, $changes_fees=0) {
        DB::beginTransaction();
        try {
            $sanction = USAGSanction::where('number', $sanction)
                                ->where('action', USAGSanction::SANCTION_ACTION_ADD)
                                ->where('status', USAGSanction::SANCTION_STATUS_MERGED)
                                ->first(); /** @var USAGSanction $sanction */
            if ($sanction === null)
                throw new CustomBaseException('Could not retrieve the sanction for this reservation', -1);

            $meet = $sanction->meet; /** @var Meet $meet */
            $is_own = ($meet->gym->user->id == $gym->user->id);
            $tshirtRequired = $meet->tshirt_size_chart_id != null;
            $leoRequired = $meet->leo_size_chart_id != null;
            $late = $meet->isLate();
            $meetInWaitlist = $meet->isWaitList();
            $slots = $meet->getUsedSlots();
            $reservations = null; /** @var Collection $reservations */
            $state = self::calculateFinalState($gym, $sanction->number, $reservations);

            $registrationStatus = MeetRegistration::STATUS_REGISTERED;
            $athleteStatus = RegistrationAthlete::STATUS_PENDING_NON_RESERVED;
            $coachStatus = RegistrationCoach::STATUS_PENDING_NON_RESERVED;

            switch($method['type']) {
                case MeetRegistration::PAYMENT_OPTION_CARD:
                    $chosenMethod = [
                        'type' => $method['type'],
                        'id' => $method['id'],
                        'fee' => $meet->cc_fee(),
                        'mode' => MeetRegistration::PAYMENT_OPTION_FEE_MODE[$method['type']]
                    ];
                    $athleteStatus = RegistrationAthlete::STATUS_REGISTERED;
                    $coachStatus = RegistrationCoach::STATUS_REGISTERED;
                    break;

                case MeetRegistration::PAYMENT_OPTION_ACH:
                    $chosenMethod = [
                        'type' => MeetRegistration::PAYMENT_OPTION_ACH,
                        'id' => $method['id'],
                        'fee' => $meet->ach_fee(),
                        'mode' => MeetRegistration::PAYMENT_OPTION_FEE_MODE[$method['type']]
                    ];
                    $athleteStatus = RegistrationAthlete::STATUS_PENDING_RESERVED;
                    $coachStatus = RegistrationCoach::STATUS_PENDING_RESERVED;
                    break;
                case MeetRegistration::PAYMENT_OPTION_ONETIMEACH:
                    $chosenMethod = [
                        'type' => MeetRegistration::PAYMENT_OPTION_ONETIMEACH,
                        'id' => $method['id'],
                        'fee' => $meet->ach_fee(),
                        'mode' => MeetRegistration::PAYMENT_OPTION_FEE_MODE[$method['type']],
                    ];
                    $athleteStatus = RegistrationAthlete::STATUS_PENDING_RESERVED;
                    $coachStatus = RegistrationCoach::STATUS_PENDING_RESERVED;
                    break;
                case MeetRegistration::PAYMENT_OPTION_PAYPAL:
                    $chosenMethod = [
                        'type' => MeetRegistration::PAYMENT_OPTION_PAYPAL,
                        'fee' => $meet->paypal_fee(),
                        'mode' => MeetRegistration::PAYMENT_OPTION_FEE_MODE[$method['type']]
                    ];
                    $athleteStatus = RegistrationAthlete::STATUS_REGISTERED;
                    $coachStatus = RegistrationCoach::STATUS_REGISTERED;
                    break;

                case MeetRegistration::PAYMENT_OPTION_CHECK:
                    $chosenMethod = [
                        'id' => $method['id'],                  // Check number
                        'type' => MeetRegistration::PAYMENT_OPTION_CHECK,
                        'fee' => $meet->check_fee(),
                        'mode' => MeetRegistration::PAYMENT_OPTION_FEE_MODE[$method['type']]
                    ];

                    if ($useBalance)
                        throw new CustomBaseException('Allgymnastics.com balance cannot be used with mailed checks.', -1);

                    $athleteStatus = RegistrationAthlete::STATUS_PENDING_NON_RESERVED;
                    $coachStatus = RegistrationCoach::STATUS_PENDING_NON_RESERVED;
                    break;

                default:
                    throw new CustomBaseException('Invalid payment method.', -1);
            }

            #region FIND OR CREATE REGISTRATION
            $newRegistration = false;
            $registration = $meet->registrations()
                                ->where('gym_id', $gym->id)
                                ->where('status', '!=', MeetRegistration::STATUS_CANCELED)
                                ->firsT(); /** @var MeetRegistration $registration */
            if (!($registration instanceof MeetRegistration)) {
                $newRegistration = true;
                $registration = $meet->registrations()
                                    ->create([
                                        'gym_id' => $gym->id,
                                        'was_late' => $late,
                                        'late_fee' => 0,
                                        'late_refund' => 0,
                                        'status' => MeetRegistration::STATUS_REGISTERED
                                    ]); /** @var MeetRegistration $registration */
            }
            #endregion

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

            $freed_slots_tracker = [];
            $added_slots_tracker = [];
            $eligible_tracker = [];
            $eligible_total = 0;
            $waitlist_tracker = [];
            $waitlist_total = 0;
            $regular_total = 0;
            $lidMatrix = [];
            $shouldGoIntoWaitlist = [];
            $tx = [
                'athletes' => [],
                'coaches' => [],
            ];
            $txScratch = [
                'athlete' => [],
                'specialist' => [],
                'coach' => []
            ];

            if (!isset($data['coaches'], $data['levels']) || !is_array($data['coaches']) || !is_array($data['levels']))
                throw new CustomBaseException('Invalid reservation data', -1);

            $scratchAthAnyOne = true;
            $scratchAth = false;
            foreach ($state['final']['levels'] as $newLid => $l) {

                #region FIND OR CREATE REGISTRATION LEVEL
                $registrationLevel = $registration->levels()
                                                ->where('sanctioning_body_id', SanctioningBody::USAG)
                                                ->where('code', $l['code'])
                                                ->wherePivot('allow_men', $l['allow_men'])
                                                ->wherePivot('allow_women', $l['allow_women'])
                                                ->first(); /** @var AthleteLevel $registrationLevel */

                if ($registrationLevel === null) { // Level doesn't exist in this registration. Find level in the meet and add it to registration
                    $athleteLevel = $meet->levels()
                                ->where('sanctioning_body_id', SanctioningBody::USAG)
                                ->where('code', $l['code'])
                                ->wherePivot('allow_men', $l['allow_men'])
                                ->wherePivot('allow_women', $l['allow_women'])
                                ->first(); /** @var AthleteLevel $athleteLevel */


                    $registration_updated_fee = null;
                    if($meet->registration_third_discount_is_enable)
                    {
                        if(strtotime($meet->registration_third_discount_end_date->format('Y-m-d')) - strtotime(date('Y-m-d')) >= 0)
                            $registration_updated_fee = $athleteLevel->pivot->registration_fee_third;
                    }
                    if($meet->registration_second_discount_is_enable)
                    {
                        if(strtotime($meet->registration_second_discount_end_date->format('Y-m-d')) - strtotime(date('Y-m-d')) >= 0)
                            $registration_updated_fee =  $athleteLevel->pivot->registration_fee_second;
                    }
                    if($meet->registration_first_discount_is_enable)
                    {
                        if(strtotime($meet->registration_first_discount_end_date->format('Y-m-d')) - strtotime(date('Y-m-d')) >= 0)
                            $registration_updated_fee =  $athleteLevel->pivot->registration_fee_first;
                    }
                    $athleteLevel->pivot->registration_fee = $registration_updated_fee == null ? $athleteLevel->pivot->registration_fee : $registration_updated_fee;
        
                                
                    $registrationLevel = $registration->levels()->attach($athleteLevel->id, [
                        'allow_men' => $athleteLevel->pivot->allow_men,
                        'allow_women' => $athleteLevel->pivot->allow_women,
                        'registration_fee' => $athleteLevel->pivot->registration_fee,
                        'late_registration_fee' => $athleteLevel->pivot->late_registration_fee,
                        'allow_specialist' => $athleteLevel->pivot->allow_specialist,
                        'specialist_registration_fee' => $athleteLevel->pivot->specialist_registration_fee,
                        'specialist_late_registration_fee' => $athleteLevel->pivot->specialist_late_registration_fee,
                        'allow_teams' => $athleteLevel->pivot->allow_teams,
                        'team_registration_fee' => $athleteLevel->pivot->team_registration_fee,
                        'team_late_registration_fee' => $athleteLevel->pivot->team_late_registration_fee,
                        'enable_athlete_limit' => $athleteLevel->pivot->enable_athlete_limit,
                        'athlete_limit' => $athleteLevel->pivot->athlete_limit,
                    ]);

                    $registrationLevel = LevelRegistration::where('meet_registration_id', $registration->id)
                                                            ->where('level_id', $athleteLevel->id)
                                                            ->where('allow_men', $athleteLevel->pivot->allow_men)
                                                            ->where('allow_women', $athleteLevel->pivot->allow_women)
                                                            ->first(); /** @var LevelRegistration $registrationLevel */
                } else {
                    $registrationLevel = $registrationLevel->pivot; /** @var LevelRegistration $registrationLevel */
                }
                #endregion

                #region MATCH NEW $lid to FRONT END $lid
                $lid = null;
                if (isset($lidMatrix[$newLid])) {
                    $lid = $lidMatrix[$newLid];
                } else {
                    foreach ($data['levels'] as $feLid => $feL) {

                        $changesArr = $feL['changes'];

                        //#region - if any athlete scratch then $scratchAthAnyOne value is false and break loop.
                        if($scratchAthAnyOne){
                            foreach ($changesArr as $key => $value){
                                if($key == 'scratch_athlete' ){
                                    if($value == true){
                                        $scratchAthAnyOne = false;
                                        break;
                                    }
                                }
                            }
                        }
                        //#endregion

                        $flag = ($feL['sanctioning_body_id'] == SanctioningBody::USAG) && ($feL['code'] == $l['code']);
                        $flag = $flag && ($feL['pivot']['allow_men'] == $l['allow_men']);
                        $flag = $flag && ($feL['pivot']['allow_women'] == $l['allow_women']);

                        if ($flag) {
                            $lidMatrix[$newLid] = $feLid;
                            $lid = $feLid;
                            break;
                        }
                    }
                }
                if ($lid === null)
                    throw new CustomBaseException('Something went wrong. (Unable to match server level to front end level).', -1);
                #endregion

                if (!isset($data['levels'][$lid]))
                    throw new CustomBaseException('Something went wrong. (Unable to find front end data for level).', -1);

                $feLevel = $data['levels'][$lid];

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

                $registrationLevel->was_late = $registrationLevel->was_late || $late;
                $registrationLevel->has_team = $feLevel['has_team'];
                $registrationLevel->save();

                foreach ($l['athletes'] as $usag_no => $a) {
                    $oldLevel = (isset($state['final']['ids']['moved'][$usag_no]) ? $state['final']['ids']['moved'][$usag_no] : null);
                    $added = (isset($state['final']['ids']['added']['athletes'][$usag_no]) ? $state['final']['ids']['added']['athletes'][$usag_no] : 0);
                    $scratched = (isset($state['final']['ids']['scratched']['athletes'][$usag_no]) ? $state['final']['ids']['scratched']['athletes'][$usag_no] : 0);
                    $tmp = $added - $scratched;

                    if (!isset($data['levels'][$lid]['athletes'], $data['levels'][$lid]['athletes'][$usag_no]))
                        throw new CustomBaseException('Something went wrong. (Unable to find front end data for athlete).', -1);

                    $feAthlete = $data['levels'][$lid]['athletes'][$usag_no];

                    if (($oldLevel !== null) && ($oldLevel != $lid) && ($tmp < 1)) { // If athlete was moved to a different level and is not a new addition
                        $athlete = $registration->athletes()
                                                ->where('usag_no', $usag_no)
                                                ->where('status', '!=', RegistrationAthlete::STATUS_SCRATCHED)
                                                ->first(); /** @var RegistrationAthlete $athlete */
                        if ($athlete !== null) {
                            if ($athlete->in_waitlist)
                                throw new CustomBaseException('Trying to update athlete with USAG No. ' . $usag_no . ' that is currently in waitlist.', -1);

                            if ($athlete->status !== RegistrationAthlete::STATUS_REGISTERED) {
                                if ($athlete->transaction !== null) {
                                    if ($athlete->transaction->status === MeetTransaction::STATUS_PENDING)
                                        throw new CustomBaseException('Trying to update athlete with USAG No. ' . $usag_no . ' that is part of a pending transaction.', -1);
                                } else {
                                    throw new CustomBaseException('Something went wrong while processing athlete with USAG No. ' . $usag_no . ' (missing transaction for pending athlete)', -1);
                                }
                            }

                            $oldRegistrationLevel = $athlete->registration_level;

                           $snapshot['levels'][$registrationLevel->id]['athletes'][$athlete->id] = [
                               'old' => [
                                   'was_late' => $athlete->was_late,
                                   'fee' => $athlete->fee,
                                   'late_fee' => $athlete->late_fee,
                                   'refund' => $athlete->refund,
                                   'late_refund' => $athlete->late_refund,
                               ],
                               'new' => [],
                           ];

                            //#region - check = if athlete moved and new level fee is <= old level fee than count different, and add to refund.
                            $refundAmount = null;  $newLevelRegiFee = 0;
                            $newLevelRegiFee = $feLevel['registration_fee'];
                            if ($feLevel['was_late']) {
                                $newLevelRegiFee = $feLevel['late_registration_fee'] + $feLevel['registration_fee'];
                            }
                            if (($oldRegistrationLevel->id != $lid) && ($newLevelRegiFee <= $oldRegistrationLevel->registration_fee)) {
                                $refundAmount = $oldRegistrationLevel->registration_fee - $newLevelRegiFee;
                            } else {
                                $refundAmount = 0;
                            }
                            // echo 'refund' . $refundAmount . '<br>';
                            $athlete->level_registration_id = $registrationLevel->id;
                            $athlete->was_late = $athlete->was_late || $late;
                            $athlete->refund =  ($refundAmount != null) ? $refundAmount : 0;
                            $athlete->late_refund = $athlete->late_fee;
                            $athlete->fee = $registrationLevel->registration_fee;
                            if ($late)
                                $athlete->late_fee = $registrationLevel->late_registration_fee;
                            $athlete->save();
                            // dd($athlete);
                           $snapshot['levels'][$registrationLevel->id]['athletes'][$athlete->id]['new'] = [
                               'was_late' => $athlete->was_late,
                               'fee' => $athlete->fee,
                               'late_fee' => $athlete->late_fee,
                               'refund' => $athlete->refund,
                               'late_refund' => $athlete->late_refund,
                               'athlete_move' => true,
                           ];

                            $tx['athletes'][] = $athlete;

                            if (!$athlete->in_waitlist) {
                                $freed_slots_tracker[$oldRegistrationLevel->id] =
                                    isset($freed_slots_tracker[$oldRegistrationLevel->id]) ?
                                    $freed_slots_tracker[$oldRegistrationLevel->id] + 1 :
                                    1;

                                $added_slots_tracker[$registrationLevel->id] =
                                    isset($added_slots_tracker[$registrationLevel->id]) ?
                                    $added_slots_tracker[$registrationLevel->id] + 1 :
                                    1;

                                $regular_total++;
                            }
                        } else if ($tmp !== 0) { // Athlete was not created and scratched during the same bundle
                            throw new CustomBaseException('Trying to update athlete with USAG No. ' . $usag_no . ' that does not exist in local database.', -1);
                        }
                    }

                    if ($tmp < 0) { // athlete was scratched {
                        $athlete = $registration->athletes()
                                                ->where('usag_no', $usag_no)
                                                ->where('status', '!=', RegistrationAthlete::STATUS_SCRATCHED)
                                                ->first(); /** @var RegistrationAthlete $athlete */
                        if ($athlete !== null) {
                            if ($athlete->in_waitlist)
                                throw new CustomBaseException('Trying to scratch athlete with USAG No. ' . $usag_no . ' that is currently in waitlist.', -1);

                            if ($athlete->status !== RegistrationAthlete::STATUS_REGISTERED) {
                                if ($athlete->transaction !== null) {
                                    if ($athlete->transaction->status === MeetTransaction::STATUS_PENDING)
                                        throw new CustomBaseException('Trying to scratch athlete with USAG No. ' . $usag_no . ' that is part of a pending transaction.', -1);
                                } else {
                                    throw new CustomBaseException('Something went wrong while processing athlete with USAG No. ' . $usag_no . ' (missing transaction for pending athlete)', -1);
                                }
                            }

                            $snapshot['levels'][$registrationLevel->id]['athletes'][$athlete->id] = [
                                'old' => [
                                    'was_late' => $athlete->was_late,
                                    'fee' => $athlete->fee,
                                    'late_fee' => $athlete->late_fee,
                                    'refund' => $athlete->refund,
                                    'late_refund' => $athlete->late_refund,
                                ],
                                'new' => [],
                            ];

                            $athlete->status = RegistrationAthlete::STATUS_SCRATCHED;
                            $athlete->was_late = $athlete->was_late || $late;
                            $athlete->refund = $athlete->fee;
                            $athlete->late_refund = $athlete->late_fee;
                            $athlete->save();
                            
                            $txScratch['athlete'][] = $athlete;

                            $snapshot['levels'][$registrationLevel->id]['athletes'][$athlete->id]['new'] = [
                                'was_late' => $athlete->was_late,
                                'fee' => 0,
                                'late_fee' => 0,
                                'refund' => $athlete->refund,
                                'late_refund' => $athlete->late_refund,
                            ];

                            $freed_slots_tracker[$registrationLevel->id] =
                                isset($freed_slots_tracker[$registrationLevel->id]) ?
                                $freed_slots_tracker[$registrationLevel->id] + 1 :
                                1;
                        } else {
                            throw new CustomBaseException('Trying to scratch athlete with USAG No. ' . $usag_no . ' that does not exist in local database.', -1);
                        }
                    } else if ($tmp > 0) { // athlete was added
                        $athlete = $registration->athletes()
                                        ->where('usag_no', $usag_no)
                                        ->where('status', '!=', RegistrationAthlete::STATUS_SCRATCHED)
                                        ->first(); /** @var RegistrationAthlete $athlete */

                        if ($athlete !== null)
                            throw new CustomBaseException('Trying to add athlete with USAG No. ' . $usag_no . ' that already exists in local database.', -1);

                        $tshirtSize = null;
                        $leoSize = null;

                        if ($tshirtRequired) {
                            if (!isset($feAthlete['tshirt_size_id']) || ($feAthlete['tshirt_size_id'] == -1))
                                throw new CustomBaseException('T-Shirt sizes are required for this meet.', -1);

                            $tshirtSize = $meet->tshirt_chart->sizes()->where('id', $feAthlete['tshirt_size_id'])->first();
                            if ($tshirtSize == null)
                                throw new CustomBaseException('Invalid T-Shirt size for this meet.', -1);
                        }

                        if ($leoRequired && ($a['gender'] == 'female')) {
                            if (!isset($feAthlete['leo_size_id']) || ($feAthlete['leo_size_id'] == -1))
                                throw new CustomBaseException('Leo sizes are required for this meet.', -1);

                            $leoSize = $meet->leo_chart->sizes()->where('id', $feAthlete['leo_size_id'])->first();
                            if ($leoSize == null)
                                throw new CustomBaseException('Invalid Leo size for this meet.', -1);
                        }
                        $leoSizeId = isset($leoSize->id) ? $leoSize->id : null;
                        $tshirtSizeId = isset($tshirtSize->id) ? $tshirtSize->id : null;
                        $athlete = [
                            'level_registration_id' => $registrationLevel->id,
                            'first_name' => $a['first_name'],
                            'last_name' => $a['last_name'],
                            'gender' => $a['gender'],
                            'dob' => $a['dob'],
                            'is_us_citizen' => $a['is_us_citizen'],
                            'tshirt_size_id' => ($tshirtRequired ? $tshirtSizeId : null),
                            'leo_size_id' => ($leoRequired ? $leoSizeId : null),
                            'usag_no' => $usag_no,
                            'usag_active' => true,
                            'was_late' => $late,
                            'in_waitlist' => $feAthlete['to_waitlist'],
                            'fee' => 0,
                            'late_fee' => 0,
                            'refund' => 0,
                            'late_refund' => 0,
                            'status' => RegistrationAthlete::STATUS_REGISTERED,
                        ];

                        $athlete = $registration->athletes()
                                                ->create($athlete);
                        $athlete->save();

                        $tx['athletes'][] = $athlete;

                        if (!$athlete->in_waitlist) {
                            $athlete->fee = $registrationLevel->registration_fee;
                            if ($late)
                                $athlete->late_fee = $registrationLevel->late_registration_fee;
                            $athlete->save();

                            $snapshot['levels'][$registrationLevel->id]['athletes'][$athlete->id] = [
                                'old' => [
                                    'was_late' => false,
                                    'fee' => 0,
                                    'late_fee' => 0,
                                    'refund' => 0,
                                    'late_refund' => 0,
                                ],
                                'new' => [
                                    'was_late' => $athlete->was_late,
                                    'fee' => $athlete->fee,
                                    'late_fee' => $athlete->late_fee,
                                    'refund' => $athlete->refund,
                                    'late_refund' => $athlete->late_refund,
                                ],
                            ];

                            $regular_total++;

                            $eligible_tracker[$registrationLevel->id] =
                                isset($eligible_tracker[$registrationLevel->id]) ?
                                $eligible_tracker[$registrationLevel->id] + 1 :
                                1;
                            $eligible_total++;

                            $added_slots_tracker[$registrationLevel->id] =
                                isset($added_slots_tracker[$registrationLevel->id]) ?
                                $added_slots_tracker[$registrationLevel->id] + 1 :
                                1;
                        } else {
                            $shouldGoIntoWaitlist[] = $athlete->id;
                            $waitlist_tracker[$registrationLevel->id] =
                                isset($waitlist_tracker[$registrationLevel->id]) ?
                                $waitlist_tracker[$registrationLevel->id] + 1 :
                                1;

                            $waitlist_total++;
                        }
                    }

                    if ($tmp < 1) { // Not a new athlete, update fields
                        $athlete = $registration->athletes()
                                                ->where('usag_no', $usag_no)
                                                ->first(); /** @var RegistrationAthlete $athlete */
                        if ($athlete === null) {
                            if ($tmp !== 0) // Athlete was not added and scratched in the same bundle
                                throw new CustomBaseException('Something went wrong. (Unable to find existing athlete ' . $usag_no . ').', -1);
                        } else {
                            $update = [
                                'first_name' => $a['first_name'],
                                'last_name' => $a['last_name'],
                                'gender' => $a['gender'],
                                'dob' => $a['dob'],
                                'is_us_citizen' => $a['is_us_citizen'],
                            ];

                            $athlete->update($update);
                        }
                    }
                }
            }

            #region COACHES
            foreach ($state['final']['coaches'] as $usag_no => $c) {
                $added = (isset($state['final']['ids']['added']['coaches'][$usag_no]) ? $state['final']['ids']['added']['coaches'][$usag_no] : 0);
                $scratched = (isset($state['final']['ids']['scratched']['coaches'][$usag_no]) ? $state['final']['ids']['scratched']['coaches'][$usag_no] : 0);
                $tmp = $added - $scratched;

                if (!isset($data['coaches'][$usag_no]))
                    throw new CustomBaseException('Something went wrong. (Unable to find front end data for coach).', -1);

                $feCoach = $data['coaches'][$usag_no];

                if (!isset($feCoach['gender']) || !in_array($feCoach['gender'], ['male', 'female']))
                    throw new CustomBaseException('Invalid gender for coach with USAG No. ' . $usag_no . '.', -1);

                if ($tmp < 0) { // coach was scratched {
                    $coach = $registration->coaches()
                                            ->where('usag_no', $usag_no)
                                            ->where('status', '!=', RegistrationCoach::STATUS_SCRATCHED)
                                            ->first(); /** @var RegistrationCoach $coach */
                    if ($coach !== null) {
                        if ($coach->in_waitlist)
                            throw new CustomBaseException('Trying to scratch coach with USAG No. ' . $usag_no . ' that is currently in waitlist.', -1);

                        if ($coach->status !== RegistrationCoach::STATUS_REGISTERED) {
                            if ($coach->transaction !== null) {
                                if ($coach->transaction->status === MeetTransaction::STATUS_PENDING)
                                    throw new CustomBaseException('Trying to scratch coach with USAG No. ' . $usag_no . ' that is part of a pending transaction.', -1);
                            } else {
                                throw new CustomBaseException('Something went wrong while processing coach with USAG No. ' . $usag_no . ' (missing transaction for pending coach)', -1);
                            }
                        }

                        $coach->status = RegistrationCoach::STATUS_SCRATCHED;
                        $coach->was_late = $coach->was_late || $late;
                        $coach->save();

                        $txScratch['coach'][] = $coach;
                    } else {
                        throw new CustomBaseException('Trying to scratch coach with USAG No. ' . $usag_no . ' that does not exist in local database.', -1);
                    }
                } else if ($tmp > 0) { // coach was added
                    $coach = $registration->coaches()
                                    ->where('usag_no', $usag_no)
                                    ->where('status', '!=', RegistrationCoach::STATUS_SCRATCHED)
                                    ->first(); /** @var RegistrationCoach $coach */

                    if ($coach !== null)
                        throw new CustomBaseException('Trying to add coach with USAG No. ' . $usag_no . ' that already exists in local database.', -1);

                    $tshirtSize = null;

                    if ($tshirtRequired) {
                        if (!isset($feCoach['tshirt_size_id']) || ($feCoach['tshirt_size_id'] == -1))
                            throw new CustomBaseException('T-Shirt sizes are required for this meet.', -1);

                        $tshirtSize = $meet->tshirt_chart->sizes()->where('id', $feCoach['tshirt_size_id'])->first();
                        if ($tshirtSize == null)
                            throw new CustomBaseException('Invalid T-Shirt size for this meet.', -1);
                    }

                    $coach = [
                        'first_name' => $c['first_name'],
                        'last_name' => $c['last_name'],
                        'gender' => $feCoach['gender'],
                        'dob' => $c['dob'],
                        'tshirt_size_id' => ($tshirtRequired ? $tshirtSize->id : null),
                        'usag_no' => $usag_no,
                        'usag_active' => true,
                        'was_late' => $late,
                        'in_waitlist' => $meetInWaitlist,
                        'from_usag' => true,
                        'status' => RegistrationCoach::STATUS_REGISTERED,
                    ];

                    $coach = $registration->coaches()
                                            ->create($coach);
                    $coach->save();

                    $tx['coaches'][] = $coach;
                }

                if ($tmp < 1) { // Not a new coach, update fields
                    $coach = $registration->coaches()
                                            ->where('usag_no', $usag_no)
                                            ->first(); /** @var RegistrationCoach $coach */
                    if ($coach === null) {
                        if ($tmp !== 0) // Coach was not added and scratched in the same bundle
                            throw new CustomBaseException('Something went wrong. (Unable to find existing coach ' . $usag_no . ').', -1);
                    } else {
                        $update = [
                            'first_name' => $c['first_name'],
                            'last_name' => $c['last_name'],
                            'gender' => $feCoach['gender'],
                            'dob' => $c['dob'],
                        ];

                        $coach->update($update);
                    }
                }
            }
            #endregion

            $txTotal = count($tx['athletes']) + count($tx['coaches']);
            if ($txTotal < 1 && $scratchAthAnyOne)
                throw new CustomBaseException('There is no changes to be applied in this USAG reservation bundle.' .
                    ' Please wait until more updates are received from USAG', -1);

            #region LOOP OVER AND FIX LEVEL FEES AND MEET FEES
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
            #endregion

            #region SLOT VALIDATION
            $waitlistAccountedFor = 0;
            $levelSlots = [];
            $meetAvailableSlots = ($meet->athlete_limit !== null ? $meet->athlete_limit - $slots['total'] : 0);
            foreach ($registration->levels as $level) { /** @var AthleteLevel $level */
                $registrationLevel = $level->pivot; /** @var LevelRegistration $level */

                if ($registrationLevel->enable_athlete_limit) {
                    $levelGender = (
                        ($registrationLevel->allow_men && $registrationLevel->allow_women) ?
                        'both' :
                        ($registrationLevel->allow_men ? 'male' : 'female')
                    );

                    if (!isset($slots[$level->id])) {
                        $slots[$level->id][$levelGender]['count'] = $registrationLevel->athlete_limit;
                    }

                    $levelAvailableSlots = $registrationLevel->athlete_limit - $slots[$level->id][$levelGender]['count'];

                    $freed_slots = isset($freed_slots_tracker[$registrationLevel->id]) ?
                        $freed_slots_tracker[$registrationLevel->id] :
                        0;

                    $added_slots = isset($added_slots_tracker[$registrationLevel->id]) ?
                        $added_slots_tracker[$registrationLevel->id] :
                        0;

                    $levelAvailableSlots += $freed_slots - $added_slots;
                    $levelSlots[$registrationLevel->id] = $levelAvailableSlots;
                    $meetAvailableSlots += $freed_slots - $added_slots;

                    $eligible = isset($eligible_tracker[$registrationLevel->id]) ?
                                    $eligible_tracker[$registrationLevel->id]:
                                    0;

                    if ($levelAvailableSlots < 0) {
                        $slotDeficit = -$levelAvailableSlots;
                        $slotDeficit = ($slotDeficit > $eligible ? $eligible : $slotDeficit);

                        if ($slotDeficit > 0) {
                            throw new CustomBaseException($level->name . ' needs ' .
                                $slotDeficit . ' more athlete(s) to go into the waitlist.', -1);
                        }
                    }
                }
            }

            foreach ($registration->levels as $level) { /** @var AthleteLevel $level */
                $registrationLevel = $level->pivot; /** @var LevelRegistration $level */

                if ($registrationLevel->enable_athlete_limit) {
                    $levelAvailableSlots = $levelSlots[$registrationLevel->id];

                    $waitlist = isset($waitlist_tracker[$registrationLevel->id]) ?
                                        $waitlist_tracker[$registrationLevel->id]:
                                        0;

                    if (($levelAvailableSlots > 0) && ($waitlist > 0)) { // If extra waitlist slots detected in level
                        if (($meet->athlete_limit === null) || ($meetAvailableSlots > 0)) { // Check it's not due to meet limit
                            throw new CustomBaseException('Too many athletes marked for waitlist in ' .
                                $level->name . '.', -1);
                        }
                    }

                    $waitlistAccountedFor += $waitlist;
                }
            }

            if ($meet->athlete_limit !== null) {
                if ($meetAvailableSlots < 0) {
                    $slotDeficit = -$meetAvailableSlots;
                    $slotDeficit = ($slotDeficit > $eligible_total ? $eligible_total : $slotDeficit);

                    if ($slotDeficit > 0) {
                        throw new CustomBaseException('This meet needs ' . $slotDeficit .
                            ' more athlete(s) to go into the waitlist.', -1);
                    }
                } elseif (($meetAvailableSlots > 0) && (($waitlist_total - $waitlistAccountedFor) > 0)) {
                    throw new CustomBaseException('Too many athletes marked for waitlist.', -1);
                }
            }
            #endregion
            $couponAmount = 0;
            $prev_deposit = null;
            if($coupon != '' && strlen($coupon) != 0)
            {
                // print_r($meet->id. ' '. $gym->id)
                $prev_deposit = Deposit::where('meet_id',$meet->id)
                            ->where('gym_id',$gym->id)
                            ->where('token_id',$coupon)
                            ->where('is_enable',true)
                            ->where('is_used',false)
                            ->first();
                if($prev_deposit)
                {
                    $couponAmount = $prev_deposit->amount;
                    $summary['subtotal'] -=  $couponAmount;
                }
            }
            $snapshot['coupon'] = $couponAmount;

            $r_total = 0;
            $r_total += $registration->late_refund;
            // dd($registration->athletes);
            foreach ($registration->athletes as $athlete) { /** @var RegistrationAthlete $athlete */
                $r_total += $athlete->refund_fee();
            }
            // echo 'athlete : '.$r_total .'<br>';
            foreach ($registration->levels as $level) { /** @var AthleteLevel $level */
                $r_total += $level->pivot->refund_fee();
            }
            // echo 'level : '.$r_total .'<br>';
            $credit_remaining = 0;
            $credit_used = 0;
            $credit_row = MeetCredit::where('meet_registration_id',$registration->id)->where('gym_id', $gym->id)->where('meet_id', $meet->id)->first();
            
            $is_credit_amount_new = false;
            if($credit_row != null && $credit_row->count() > 0)
            {
                $credit_remaining = ($credit_row->credit_amount + $changes_fees) - $credit_row->used_credit_amount;
            }
            else
            {
                $credit_row = resolve(MeetCredit::class);
                $credit_row->meet_registration_id = $registration->id;
                $credit_row->gym_id = $gym->id;
                $credit_row->meet_id = $meet->id;
                $credit_row->credit_amount = $r_total;
                $credit_row->used_credit_amount = 0;
                $credit_row->save();
                $is_credit_amount_new = true;
                $credit_remaining = $changes_fees;
            }
            // dd($snapshot);
            #region FEE CALCULATIONS
            $incurredFees = $registration->calculateRegistrationTotal($snapshot);
            if($credit_remaining > 0 )
            {
                if($incurredFees['subtotal'] >= $credit_remaining)
                {
                    $incurredFees['subtotal'] -= $credit_remaining;
                    $credit_used = $credit_remaining;
                }
                else
                {
                    $credit_used = $incurredFees['subtotal'];
                    $incurredFees['subtotal'] = 0;
                }
            }
            $subtotal = $incurredFees['subtotal'];


            // echo 'total refund calculate : '. $r_total . '<br>';
            // echo 'total changes fee calculate : '. $changes_fees . '<br>';
            // echo 'f subtotal : '. $summary['subtotal'] . '<br>';
            // echo 'b subtotal : '. $incurredFees['subtotal'] . '<br>';
            // echo 'remaining credit : '. $credit_remaining . '<br>';
            // echo 'used credit : '. $credit_used . '<br>';
            // die();


            if ($subtotal != $summary['subtotal'] + $couponAmount)
            {
                $incurredFees = $registration->calculateRegistrationTotal($snapshot, false, $summary['subtotal']);
                $subtotal = $summary['subtotal'];
            }
                // throw new CustomBaseException('Subtotal calculation mismatch.'.$incurredFees['subtotal'].' '.$summary['subtotal'], -1);

            $host = User::lockForUpdate()->find($meet->gym->user->id); /** @var User $host */
            if ($host == null)
                throw new CustomBaseException('No such host');

            $registrant = User::lockForUpdate()->find($gym->user->id); /** @var User $registrant */
            if ($registrant == null)
                throw new CustomBaseException('No such registrant');

            $calculatedFees = MeetRegistration::calculateFees($subtotal, $meet, $is_own, $chosenMethod, $useBalance, $registrant->cleared_balance,false,$couponAmount);

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
            #endregion

            #region PAYMENT
            $needRegularTransaction = ($regular_total > 0);
            $needWaitlistTransaction = ($waitlist_total > 0) || ($meetInWaitlist);
            $waitlistTransaction = null;

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

            $transaction = null;
            if ($gymSummary['total'] == 0 && !$useBalance) {
                $needRegularTransaction = false;
            }
            if ($needRegularTransaction) {
                if ($useBalance && ($gymSummary['used_balance'] > 0) && ($gymSummary['total'] == 0)) {
                    $chosenMethod = [
                        'type' => MeetRegistration::PAYMENT_OPTION_BALANCE,
                        'id' => null,
                        'fee' => $meet->balance_fee(),
                        'mode' => MeetRegistration::PAYMENT_OPTION_FEE_MODE[MeetRegistration::PAYMENT_OPTION_BALANCE]
                    ];

                    $athleteStatus = RegistrationAthlete::STATUS_REGISTERED;
                    $coachStatus = RegistrationCoach::STATUS_REGISTERED;
                }

                $executedTransactionResult = MeetRegistration::executePayment(
                    $calculatedFees,
                    $chosenMethod,
                    $registration,
                    $host,
                    $registrant,
                    $onetimeach
                );
                $transaction = $executedTransactionResult['transaction']; /** @var MeetTransaction $transaction */
                $athleteStatus = $executedTransactionResult['athlete_status'];
                $coachStatus = $executedTransactionResult['coach_status'];
                $calculatedFees = $executedTransactionResult['calculated_fees'];
                $paymentMethodString = $executedTransactionResult['payment_method_string'];
                $result['message'] = $executedTransactionResult['message'];
            }
            #endregion

            $auditEvent = [
                'registration' => [],
                'athletes' => [],
                'specialists' => [],
                'coaches' => [],
                'scratch' => $txScratch,
            ];
            $last_transaction = MeetTransaction::where('meet_registration_id', $registration->id)->orderBy('created_at', 'desc')->first();
            foreach ($tx['athletes'] as $ra) { /** @var RegistrationAthlete $ra */
                if (in_array($ra->id, $shouldGoIntoWaitlist)) {
                    $ra->in_waitlist = true;
                    $ra->transaction()->associate($waitlistTransaction);
                    $ra->status = RegistrationAthlete::STATUS_PENDING_NON_RESERVED;
                } else {
                    if($transaction)
                    {
                        $ra->transaction()->associate($transaction);
                    }
                    else
                    {
                        $ra->transaction()->associate($last_transaction);
                    }
                    // $ra->transaction()->associate($transaction);
                    $ra->status = $athleteStatus;
                }
                $ra->save();

                $a = $ra->toArray();
                unset($a['transaction']);
                $auditEvent['athletes'][] = $a;
            }
           
            foreach ($tx['coaches'] as $rc) { /** @var RegistrationCoach $rc */
                if ($transaction !== null) {
                    $rc->in_waitlist = false;
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

            foreach ($reservations as $r) { /** @var USAGReservation $r */
                $r->status = USAGReservation::RESERVATION_STATUS_MERGED;
                $r->save();

                AuditEvent::usagReservationProcessed(request()->_managed_account, auth()->user(), $r);
            }

            $registration->status = $registrationStatus;
            $registration->save();

            $registrationArray = $registration->toArray();

            $number_of["athletes"] = count($tx['athletes']);
            $number_of["coaches"] = count($tx['coaches']);
            $number_of["specialists"] = isset($specialistCount) ? $specialistCount : 0;

            unset($registrationArray['athletes']);
            unset($registrationArray['specialists']);
            unset($registrationArray['coaches']);
            $auditEvent['registration'] = $registrationArray;

            if ($newRegistration) {
                AuditEvent::registrationCreated(
                    request()->_managed_account, auth()->user(), $registration, $auditEvent
                );
                $meetEntryReport = $meet->registrantMeetEntryAndStoreReport($meet->id, $gym);
                Mail::to($gym->user->email)->send(new GymRegisteredMailable(
                    $meet,
                    $gym,
                    $registration,
                    $gymSummary,
                    $paymentMethodString,
                    $transaction !== null,
                    $waitlistTransaction !== null,
                    $sanction,
                    $meetEntryReport
                ));

                if($enable_travel_arrangements)
                    Mail::to(env('MAIL_TRAVEL_ADDRESS'))->cc("hello@allgymnastics.com")->send(new TransportHelpMailable($meet, $gym, $number_of));

            } else {
                AuditEvent::registrationUpdated(
                    request()->_managed_account, auth()->user(), $registration, $auditEvent
                );
                $meetEntryReport = $meet->registrantMeetEntryAndStoreReport($meet->id, $gym);
                Mail::to($gym->user->email)->send(new GymRegistrationUpdatedMailable(
                    $meet,
                    $gym,
                    $registration,
                    $gymSummary,
                    $paymentMethodString,
                    $transaction !== null,
                    $waitlistTransaction !== null,
                    $sanction,
                    $meetEntryReport
                ));
            }


            if(isset($prev_deposit) && $prev_deposit != null)
            {
                $prev_deposit->is_used = true;
                $prev_deposit->save();
            }
            if($changes_fees > 0 && !$is_credit_amount_new)
            {
                $credit_row->credit_amount += $changes_fees;
                $credit_row->save();
            }
            if($credit_used > 0)
            {
                $credit_row->used_credit_amount += $credit_used;
                $credit_row->save();
            }
            $credit_row->save();

            DB::commit();
            return $registration;
        } catch (Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
