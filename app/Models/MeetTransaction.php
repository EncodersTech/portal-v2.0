<?php

namespace App\Models;

use App\Exceptions\CustomBaseException;
use Illuminate\Database\Eloquent\Model;
use App\Traits\Excludable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class MeetTransaction extends Model
{
    use Excludable;

    protected $casts = [
        'breakdown' => 'json',
    ];

    protected $guarded = ['id'];

    public const PAYMENT_METHOD_CC = 1;
    public const PAYMENT_METHOD_PAYPAL = 2;
    public const PAYMENT_METHOD_ACH = 3;
    public const PAYMENT_METHOD_CHECK = 4;
    public const PAYMENT_METHOD_BALANCE = 5;
    public const PAYMENT_METHOD_ONETIMEACH = 6;

    public const PAYMENT_METHOD_STRINGS = [
        self::PAYMENT_METHOD_CC => 'Credit Card',
        self::PAYMENT_METHOD_PAYPAL => 'PayPal',
        self::PAYMENT_METHOD_ACH => 'ACH',
        self::PAYMENT_METHOD_CHECK => 'Mailed Check',
        self::PAYMENT_METHOD_BALANCE => 'Allgymnastics.com Balance',
        self::PAYMENT_METHOD_ONETIMEACH => 'One Time ACH'
    ];

    public const STATUS_PENDING = 1;
    public const STATUS_COMPLETED = 2;
    public const STATUS_CANCELED = 3;
    public const STATUS_FAILED = 4;

    public const STATUS_WAITLIST_PENDING = 5;
    public const STATUS_WAITLIST_CONFIRMED = 6;

    public const TYPE_PAYMENT = 1;
    public const TYPE_REFUND = 2;

    public function meet_registration()
    {
        return $this->belongsTo(MeetRegistration::class);
    }

    public function athletes()
    {
        return $this->hasMany(RegistrationAthlete::class, 'transaction_id');
    }

    public function specialist_events()
    {
        return $this->hasMany(RegistrationSpecialistEvent::class, 'transaction_id');
    }

    public function coaches()
    {
        return $this->hasMany(RegistrationCoach::class, 'transaction_id');
    }

    public function host_balance_transaction()
    {
        return $this->morphOne(UserBalanceTransaction::class, 'related');
    }

    public function host_check_confirmation_transaction()
    {
        return $this->hasOne(CheckConfirmationTransaction::class, 'transaction_id');
    }
    public function chargeWaitlistedTransaction()
    {
        $r = $this->meet_registration; /** @var MeetRegistration $r */
        $m = $r->meet; /** @var Meet $m */

        $levels = [];
        try{
            $athletes = $this->athletes;
            $snapshot = [
                'registration' => [
                    'late_fee' => ($r->was_late) ? $r->late_fee : 0
                ],
                'levels' => []
            ];
            foreach ($athletes as $a) {
                $l = $a->registration_level;
                if (!key_exists($l->id, $snapshot['levels'])) {
                    $snapshot['levels'][$l->id] = [
                        'team_fee' => ($l->has_team && !$l->is_waitlist_team_paid) ? $l->team_fee : 0,
                        'team_late_fee' => ($l->was_late && !$l->is_waitlist_team_paid) ? $l->team_late_fee : 0
                    ];
                }
                $snapshot['levels'][$l->id]['athletes'][$a->id] = [
                    'fee' => $l->registration_fee,
                    'late_fee' => ($a->was_late) ? $l->late_registration_fee : 0
                ];
            }

            $specialist_events = $this->specialist_events;
            foreach ($specialist_events as $se) { /** @var RegistrationSpecialistEvent $se */
                $s = $se->specialist; /** @var RegistrationSpecialist $s */
                $l = $s->registration_level; /** @var LevelRegistration $l */

                if (!key_exists($l->id, $snapshot['levels'])) {
                    $snapshot['levels'][$l->id] = [
                        'team_fee' => ($l->has_team && !$l->is_waitlist_team_paid) ? $l->team_fee : 0,
                        'team_late_fee' => ($l->was_late && !$l->is_waitlist_team_paid) ? $l->team_late_fee : 0
                    ];
                }

                $snapshot['levels'][$l->id]['specialists'][$s->id] = [
                        'fee' => $l->specialist_registration_fee,
                        'late_fee' => ($se->was_late) ? $l->specialist_late_registration_fee : 0,
                ];
            }
            return $snapshot;
        }
        catch(Exception $e){
            throw new CustomBaseException('Error while charging waitlisted transaction', $e);
        }
    }
    public function calculateWaitlistTotal($snapshot)
    {
        $total = $snapshot['registration']['late_fee'];
        $level_team_fees = [];
        foreach ($snapshot['levels'] as $key => $level) {
            $total += $level['team_fee'] + $level['team_late_fee'];
            $level_team_fees[$key] = 
            [
                'fee' => $level['team_fee'],
                'late' => $level['team_late_fee']
            ];
            if(isset($level['athletes']))
                foreach ($level['athletes'] as $athlete) {
                    $total += $athlete['fee'] + $athlete['late_fee'];
                }
            if(isset($level['specialists']))
                foreach ($level['specialists'] as $specialist) {
                    $total += $specialist['fee'] + $specialist['late_fee'];
                }
        }
        return [
            'level_team_fees' => $level_team_fees,
            'registration_late_fee' => $snapshot['registration']['late_fee'],
            'subtotal' => $total
        ];
    }
    public function reapplyFees(bool $dryRun = false) {
        $r = $this->meet_registration; /** @var MeetRegistration $r */
        $m = $r->meet; /** @var Meet $m */

        $levels = [];
        try {
            $snapshot = [
                'registration' => [
                    'old' => [
                        'was_late' => $r->was_late,
                        'late_fee' => $r->late_fee,
                        'late_refund' => $r->late_refund,
                    ],
                    'new' => [],
                ],
                'levels' => [],
            ];

            $athletes = $this->athletes;
            foreach ($athletes as $a) { /** @var RegistrationAthlete $a */
                $l = $a->registration_level; /** @var LevelRegistration $l */

                if (!key_exists($l->id, $snapshot['levels'])) {
                    $levels[] = $l;

                    $snapshot['levels'][$l->id] = [
                        'old' => [
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
                }

                if (!$a->in_waitlist) {
                    $snapshot['levels'][$l->id]['athletes'][] = [
                        'old' => [
                            'was_late' => $a->was_late,
                            'fee' => $a->fee,
                            'late_fee' => $a->late_fee,
                            'refund' => $a->refund,
                            'late_refund' => $a->late_refund,
                        ],
                        'new' => [
                            'was_late' => $a->was_late,
                            'fee' => $a->fee,
                            'late_fee' => $a->late_fee,
                            'refund' => $a->refund,
                            'late_refund' => $a->late_refund,
                        ],
                    ];
                }
            }

            $specialist_events = $this->specialist_events;
            foreach ($specialist_events as $se) { /** @var RegistrationSpecialistEvent $se */
                $s = $se->specialist; /** @var RegistrationSpecialist $s */
                $l = $s->registration_level; /** @var LevelRegistration $l */

                if (!key_exists($l->id, $snapshot['levels'])) {
                    $levels[] = $l;

                    $snapshot['levels'][$l->id] = [
                        'old' => [
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
                }

                if (!key_exists($s->id, $snapshot['levels'][$l->id]['specialists']))
                    $snapshot['levels'][$l->id]['specialists'][$s->id] = [];

                if (!$se->in_waitlist) {
                    $snapshot['levels'][$l->id]['specialists'][$s->id][] = [
                        'old' => [
                            'was_late' => $se->was_late,
                            'fee' => $se->fee,
                            'late_fee' => $se->late_fee,
                            'refund' => $se->refund,
                            'late_refund' => $se->late_refund,
                        ],
                        'new' => [
                            'was_late' => $se->was_late,
                            'fee' => $se->fee,
                            'late_fee' => $se->late_fee,
                            'refund' => $se->refund,
                            'late_refund' => $se->late_refund,
                        ],
                    ];
                }
            }

            $r->fresh();
            $hasAthletes = false;
            foreach ($r->levels as $al) { /** @var AthleteLevel $al */
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

                if (!$dryRun)
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
                if ($r->was_late) {
                    if (($r->late_fee - $r->late_refund) != $m->late_registration_fee)
                        $r->late_fee += $m->late_registration_fee - ($r->late_fee - $r->late_refund);
                } else {
                    // clear fees
                    if (($r->late_fee - $r->late_refund) != 0)
                        $r->late_refund = $r->late_fee;
                }
            } else {
                // clear the fees
                $r->was_late = false;
                if (($r->late_fee - $r->late_refund) != 0)
                    $r->late_refund = $r->late_fee;
            }
            if (!$dryRun)
                $r->save();

            $snapshot['registration']['new'] = [
                'was_late' => $r->was_late,
                'late_fee' => $r->late_fee,
                'late_refund' => $r->late_refund,
            ];

            return $snapshot;
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function calculatedTotal(array $snapshot) {
        $r = $this->meet_registration; /** @var MeetRegistration $r */
        $m = $r->meet; /** @var Meet $m */

        try {
            $r = $this->meet_registration; /** @var MeetRegistration $r */
            return $r->calculateRegistrationTotal($snapshot);
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function methodName()
    {
        return self::PAYMENT_METHOD_STRINGS[$this->method];
    }
}
