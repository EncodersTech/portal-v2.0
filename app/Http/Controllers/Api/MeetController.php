<?php

namespace App\Http\Controllers\Api;

use App\Models\Gym;
use App\Models\Deposit;
use App\Models\MeetAdmission;
use App\Models\MeetSubscription;
use Illuminate\Http\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use App\Exceptions\CustomBaseException;
use Illuminate\Validation\ValidationException;
use App\Models\State;
use Illuminate\Support\Facades\DB;
use App\Models\SanctioningBody;
use App\Models\Meet;
use App\Helper;
use App\Jobs\ProcessEntrantVerificationRequest;
use App\Mail\Host\TransactionCompletedMailable as HostTransactionCompletedMailable;
use App\Mail\Registrant\WaitlistConfirmedMailable;
use App\Mail\Registrant\WaitlistRejectedMailable;
use App\Mail\Registrant\TransactionCompletedMailable;
use App\Mail\Registrant\TransactionFailedMailable;
use App\Mail\Registrant\DepositCompleteMailable;
use App\Models\AuditEvent;
use App\Models\LevelCategory;
use App\Models\MeetRegistration;
use App\Models\MeetReport;
use App\Models\MeetTransaction;
use App\Models\RegistrationAthlete;
use App\Models\RegistrationAthleteVerification;
use App\Models\RegistrationCoach;
use App\Models\RegistrationSpecialist;
use App\Models\RegistrationSpecialistEvent;
use App\Models\Setting;
use App\Models\User;
use App\Models\UserBalanceTransaction;
use App\Services\StripeService;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class MeetController extends BaseApiController
{
    public function meetList(Request $request, string $gym)
    {
        return $this->_meet_list($request, $gym);
    }

    public function activeMeetList(Request $request, string $gym)
    {
        return $this->_meet_list($request, $gym, false);
    }

    public function archivedMeetList(Request $request, string $gym)
    {
        return $this->_meet_list($request, $gym, true);
    }

    private function _meet_list(Request $request, string $gym, ?bool $archived = null)
    {
        try {
            $gym = $request->_managed_account->gyms()->where('id', $gym)->first();
            if ($gym == null)
                return $this->error(['message' => 'There is no such gym in ' . ($request->_managed_account->isCurrentUser() ? 'your' : $request->_managed_account->fullName() . '\'s') . ' account'], 400);


            if (!$archived && $gym->is_archived)
                return $this->error([
                    'message' => 'You cannot edit archived gyms'
                ], 400);


            $meets = $gym->meets()->with([
                'tshirt_chart',
                'leo_chart',
                'venue_state',
                'admissions',
                'categories',
            ])->orderBy('updated_at', 'DESC');

            if ($archived !== null)
                $meets = $meets->where('is_archived', $archived);

            $meets = $meets->get();

            foreach ($meets as $meet) { /** @var Meet $meet */
                $meet->can_be_edited = $meet->canBeEdited();
                $meet->can_be_deleted = $meet->canBeDeleted();
                $meet->registration_status = $meet->registrationStatus();
            }

            $can_create =  ($request->_managed_account->isCurrentUser() || $request->_managed_account->pivot->can_create_meet);
            $can_edit = ($request->_managed_account->isCurrentUser() || $request->_managed_account->pivot->can_edit_meet);

            return $this->success([
                'meets' => $meets,
                'permissions' => [
                    'create' => $can_create,
                    'edit' => $can_edit
                ]
            ]);
        } catch(CustomBaseException $e) {
            throw $e;
        } catch(\Throwable $e) {
            Log::warning(self::class . '@_meet_list : ' . $e->getMessage(), [
                'Gym' => $gym,
                'archived' => $archived,
                'Throwable' => $e
            ]);
            return $this->error([
                'message' => 'Something went wrong while fetching meets.'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    
    public function close(Request $request, string $gym, string $meet)
    {
        $gym = $request->_managed_account->retrieveGym($gym); /** @var Gym $gym */
        $meet = $gym->retrieveMeet($meet); /** @var Meet $meet */
        if ($meet->closeMeet())
            return $this->success([
                'success' =>' Your meet is closed.',
            ]);
        else
            return $this->error([
                'message' => 'There was an error while closing your meet.'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
    }
    public function participatingGyms(string $meetId)
    {
        $meet = Meet::select('show_participate_clubs')->where('id',$meetId)->first();
        if($meet['show_participate_clubs'])
        {
            $gym = DB::table('meet_registrations as mr')
            ->join('gyms', 'mr.gym_id', '=', 'gyms.id')
            ->select('gyms.name')
            ->where('mr.meet_id', $meetId)
            ->get();
            return $this->success(['gym' => $gym]);
        }
        else
        {
            return $this->error([
                'message' => 'Meet host does not allow showing participating gyms.'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    public function statesList()
    {
        try {
            $states = State::exclude(['created_at', 'updated_at'])->orderBy('name', 'asc')->get();
            return $this->success(['states' => $states]);
        } catch(CustomBaseException $e) {
            throw $e;
        } catch(\Throwable $e) {

            Log::warning(self::class . '@statesList : ' . $e->getMessage(), [
                'Throwable' => $e
            ]);
            return $this->error([
                'message' => 'Something went wrong while fetching states list.'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function meet(Request $request, string $meet)
    {
        try {
            if (!Helper::isInteger($meet))
                throw new CustomBaseException('Invalid meet id', -1);

            return $this->meets($request, (int) $meet);
        } catch(CustomBaseException $e) {
            throw $e;
        } catch(\Throwable $e) {
            Log::warning(self::class . '@statesList : ' . $e->getMessage(), [
                'Throwable' => $e
            ]);
            return $this->error([
                'message' => 'Something went wrong while fetching meet details.'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function meets(Request $request, int $meet = null) {
        $filters = null;
        try {
            $browseMeetsRules = [
                'page' => ['sometimes', 'nullable', 'integer', 'min:1'],
                'limit' => ['sometimes', 'nullable', 'integer', 'min:1'],
                'state' => ['sometimes', 'nullable', 'string', 'size:2'],
                'status' => ['sometimes', 'nullable', 'string'],
                'from' => ['sometimes', 'nullable', 'date_format:m/d/Y',],
                'to' => ['sometimes', 'nullable', 'date_format:m/d/Y',],
                'usag' => ['sometimes', 'nullable', 'boolean'],
                'usaigc' => ['sometimes', 'nullable', 'boolean'],
                'aau' => ['sometimes', 'nullable', 'boolean'],
                'nga' => ['sometimes', 'nullable', 'boolean'],
                'name' =>  ['sometimes', 'nullable', 'string', 'max:255'],
                'open' => ['sometimes', 'nullable', 'boolean']
            ];

            $filters = $request->validate($browseMeetsRules);
            $filters['enddate'] = "07/07/2021";
            $page = (isset($filters['page']) ? (int) $filters['page'] : 1);
            $limit = (isset($filters['limit']) ? (int) $filters['limit'] : null);
            $state = (isset($filters['state']) ? $filters['state'] : null);
            $status = (isset($filters['status']) ? $filters['status'] : null);
            $from = (isset($filters['from']) ? new \DateTime($filters['from']) : null);
            $to = (isset($filters['to']) ? new \DateTime($filters['to']) : null);
            $usag = (isset($filters['usag']) && $filters['usag'] ? true : false);
            $usaigc = (isset($filters['usaigc']) && $filters['usaigc'] ? true : false);
            $aau = (isset($filters['aau']) && $filters['aau'] ? true : false);
            $nga = (isset($filters['nga']) && $filters['nga'] ? true : false);
            $name = (isset($filters['name']) ? $filters['name'] : null);
            $enddate = (isset($filters['enddate']) ? new \DateTime($filters['enddate']) : null);
            $open = (isset($filters['open']) && $filters['open'] ? true : false);

            $query = Meet::with([
                'gym' => function ($q) {
                    $q->exclude([
                        'user_id', 'handling_fee_override', 'cc_fee_override',
                        'paypal_fee_override', 'ach_fee_override', 'check_fee_override',
                        'is_archived', 'created_at', 'updated_at'
                    ]);
                },
                'gym.state' => function ($q) {
                    $q->exclude(['created_at', 'updated_at']);
                },
                'gym.country' => function ($q) {
                    $q->exclude(['created_at', 'updated_at']);
                },
                'tshirt_chart' => function ($q) {
                    $q->exclude(['is_leo', 'is_default', 'is_disabled', 'created_at', 'updated_at'])
                        ->where('is_disabled', false);
                },
                'tshirt_chart.sizes' => function ($q) {
                    $q->exclude(['is_disabled'])
                        ->where('is_disabled', false);
                },
                'leo_chart' => function ($q) {
                    $q->exclude(['is_leo', 'is_default', 'is_disabled', 'created_at', 'updated_at'])
                        ->where('is_disabled', false);
                },
                'leo_chart.sizes' => function ($q) {
                    $q->exclude(['is_disabled'])
                        ->where('is_disabled', false);
                },
                'venue_state' => function ($q) {
                    $q->exclude(['created_at', 'updated_at']);
                },
                'admissions' => function ($q) {
                    $q->exclude(['created_at', 'updated_at']);
                },
                'levels' => function ($q) {
                    $q->exclude(['created_at', 'updated_at'])
                        ->where('is_disabled', false);
                },
                'competition_format' => function ($q) {
                    $q->exclude(['created_at', 'updated_at']);
                }
            ])->with(['categories', 'levels.sanctioning_body', 'levels.level_category'])
                ->where('is_published', true)
                ->where('end_date', '>', $enddate)
                ->where('is_archived', false);

            if ($meet !== null)
                $query->where('id', $meet);
            if ($query->first()->show_participate_clubs == true) {
                $query->with('registrations');
            }
            if ($state !== null) {
                $state = State::where('code', $state)->first();
                /** @var \App\Models\State $state */
                if ($state == null)
                    throw new CustomBaseException('No such state code.', '-1');

                $query->where('venue_state_id' ,$state->id);
            }

            if ($status !== null) {
                $now = now()->setTime(0, 0);

                if ($status == Meet::REGISTRATION_STATUS_CLOSED) {
                    $query->where(function ($q) use ($status, $now) {
                        $q->where('registration_end_date', '<=', $now);
                    });
                }

                if ($status == Meet::REGISTRATION_STATUS_OPEN) {
                    $query->where(function ($q) use ($now) {
                        $q->where(function ($q2) use ($now) {
                            $q2->where('allow_late_registration', true)
                                ->where('late_registration_end_date', '>=', $now)
                                ->where('late_registration_start_date', '<=', $now);
                        });

                        $q->orWhere(function ($q2) use ($now) {
                            $q2->where('registration_end_date', '>=', $now)
                                ->where('registration_start_date', '<=', $now);
                        });
                    });
                }

                if ($status == Meet::REGISTRATION_STATUS_LATE) {
                    $query->where(function ($q) use ($now) {
                        $q->where('allow_late_registration', true)
                            ->where('late_registration_end_date', '>=', $now)
                            ->where('late_registration_start_date', '<=', $now);
                    });
                }

                if ($status == Meet::REGISTRATION_STATUS_OPENING_SOON) {
                    $query->where(function ($q) use ($now) {
                        $q->where('registration_end_date', '>=', $now)
                            ->where('registration_start_date', '>=', $now);
                    });
                }
            }

            if ($from !== null)
                $query->where('start_date', '>=', $from);

            if ($to !== null)
                $query->where('end_date', '<=', $to);

            if ($usag || $usaigc || $aau || $nga) {
                $subquery = ' in (
                    SELECT sanctioning_body_id
                    FROM category_meet
                    WHERE meet_id = id
                )';

                $query->where(function ($q) use ($usag, $usaigc, $aau, $nga, $subquery) {
                    if ($usag)
                        $q->orWhereRaw(SanctioningBody::USAG . $subquery);

                    if ($usaigc)
                        $q->orWhereRaw(SanctioningBody::USAIGC . $subquery);

                    if ($aau)
                        $q->orWhereRaw(SanctioningBody::AAU . $subquery);

                    if ($nga)
                        $q->orWhereRaw(SanctioningBody::NGA . $subquery);
                });
            }

            if ($name !== null)
                $query->where('name', 'ILIKE', '%' . strtolower($name) . '%');

           if ($open) {
               $now = now()->setTime(0, 0);

               $query->where(function ($q) use ($now) {
                   $q->where(function ($q2) use ($now) {
                       $q2->where('allow_late_registration', true)
                           ->where('late_registration_end_date', '>=', $now)
                           ->where('late_registration_start_date', '<=', $now);
                   });

                   $q->orWhere(function ($q2) use ($now) {
                       $q2->where('registration_end_date', '>=', $now)
                           ->where('registration_start_date', '<=', $now);
                   });
               });
           }
            if ($meet === null) {
                $countQuery = clone $query;
                $countQuery->select(DB::raw('count(meets.id) as meet_count'));
                $count = $countQuery->first()->meet_count;
            } else {
                $count = 1;
            }

            if ($limit !== null)
                $query->limit($limit)->offset(($page - 1) * $limit);
            if($enddate != null)
                $query->where('end_date','>=',$enddate);
            $meets = $query->orderBy('is_featured', 'DESC')
                ->orderBy('start_date', 'DESC')
                ->get()
                ->makeHidden([
                    'gym_id', 'tshirt_size_chart_id', 'leo_size_chart_id', 'mso_meet_id',
                    'venue_state_id', 'meet_competition_format_id', 'is_published' , 'is_archived',
                    'handling_fee_override', 'cc_fee_override',
                    'paypal_fee_override', 'ach_fee_override', 'check_fee_override',
                    'created_at', 'updated_at'
                ]);

            $meets = $meets->map(function ($m) use ($meet) {
                    $m->registration_status = $m->registrationStatus();

                    if ($meet !== null) {
                        $m->is_waitlist = $m->isWaitList();
                        $m->used_slots = $m->getUsedSlots();
                    }

                    $m->admissions->map(function ($admission) {
                        $admission->amount = $admission->type == MeetAdmission::TYPE_TBD ? 'TBD' : $admission->amount ;
                    });

                    $m->editing_abilities = $m->editingAbilities();
                    $m->schedule_url = (isset($m->schedule)) ? route('file.download', $m->id) : null;
                return $m;
            });
            $meetsList = [];
            if(count($meets) > 1)
            {
                foreach ($meets as $key => $value) {
                    if($value->registration_status != Meet::REGISTRATION_STATUS_CLOSED)
                    {
                        $meetsList[] = $value;
                    }
                }
            }
            return $this->success([
                'total' => $count,
                'page' => $page,
                'limit' => $limit,
                // 'meets' => $meets,
                'meets' => count($meets) > 1 ? $meetsList : $meets,
            ]);
        } catch(ValidationException $e) {
            throw $e;
        } catch(CustomBaseException $e) {
            throw $e;
        } catch(\Throwable $e) {
            if (config('app.debug'))
                throw $e;

            Log::warning(self::class . '@meets : ' . $e->getMessage(), [
                'filters' => $filters
            ]);
            return $this->error([
                'message' => 'Something went wrong while retreiving meets.'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function meetApi(Request $request, string $meet)
    {
        try {
            if (!Helper::isInteger($meet))
                throw new CustomBaseException('Invalid meet id', -1);

            return $this->meetsApi($request, (int) $meet);
        } catch(CustomBaseException $e) {
            throw $e;
        } catch(\Throwable $e) {
            Log::warning(self::class . '@statesList : ' . $e->getMessage(), [
                'Throwable' => $e
            ]);
            return $this->error([
                'message' => 'Something went wrong while fetching meet details.'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function meetsApi (Request $request, int $meet = null) {
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
                'name' =>  ['sometimes', 'nullable', 'string', 'max:255'],
                'open' => ['sometimes', 'nullable', 'boolean'],
                'status' => ['sometimes', 'nullable', 'integer'],
            ];

            $filters = $request->validate($browseMeetsRules);

            $page = (isset($filters['page']) ? (int) $filters['page'] : 1);
            $limit = (isset($filters['limit']) ? (int) $filters['limit'] : null);
            $state = (isset($filters['state']) ? $filters['state'] : null);
            $from = (isset($filters['from']) ? new \DateTime($filters['from']) : null);
            $to = (isset($filters['to']) ? new \DateTime($filters['to']) : null);
            $usag = (isset($filters['usag']) && $filters['usag'] ? true : false);
            $usaigc = (isset($filters['usaigc']) && $filters['usaigc'] ? true : false);
            $aau = (isset($filters['aau']) && $filters['aau'] ? true : false);
            $nga = (isset($filters['nga']) && $filters['nga'] ? true : false);
            $name = (isset($filters['name']) ? $filters['name'] : null);
            $open = (isset($filters['open']) && $filters['open'] ? true : false);
            $status = (isset($filters['status']) ? $filters['status'] : null);

            if ($meet !== null) {
                $query = Meet::with([
                    'gym' => function ($q) {
                        $q->exclude([
                            'user_id', 'handling_fee_override', 'cc_fee_override',
                            'paypal_fee_override', 'ach_fee_override', 'check_fee_override',
                            'is_archived', 'created_at', 'updated_at'
                        ]);
                    },
                    'gym.state' => function ($q) {
                        $q->exclude(['created_at', 'updated_at']);
                    },
                    'gym.country' => function ($q) {
                        $q->exclude(['created_at', 'updated_at']);
                    },
                    'tshirt_chart' => function ($q) {
                        $q->exclude(['is_leo', 'is_default', 'is_disabled', 'created_at', 'updated_at'])
                            ->where('is_disabled', false);
                    },
                    'tshirt_chart.sizes' => function ($q) {
                        $q->exclude(['is_disabled'])
                            ->where('is_disabled', false);
                    },
                    'leo_chart' => function ($q) {
                        $q->exclude(['is_leo', 'is_default', 'is_disabled', 'created_at', 'updated_at'])
                            ->where('is_disabled', false);
                    },
                    'leo_chart.sizes' => function ($q) {
                        $q->exclude(['is_disabled'])
                            ->where('is_disabled', false);
                    },
                    'venue_state' => function ($q) {
                        $q->exclude(['created_at', 'updated_at']);
                    },
                    'admissions' => function ($q) {
                        $q->exclude(['created_at', 'updated_at']);
                    },
                    'levels' => function ($q) {
                        $q->exclude(['created_at', 'updated_at'])
                            ->where('is_disabled', false);
                    },
                    'competition_format' => function ($q) {
                        $q->exclude(['created_at', 'updated_at']);
                    }
                ])->with(['categories', 'levels.sanctioning_body', 'levels.level_category'])
                    ->where('is_published', true)
                    ->where('is_archived', false);

                $query->where('id', $meet);

                if ($query->first()->show_participate_clubs == true) {
                    $query->with('registrations.gym');
                }
            } else{
                $query = Meet::with(['gym','venue_state'])->where('is_published', true)
                    ->where('is_archived', false);
            }

            if ($state !== null) {
                $state = State::where('code', $state)->first();
                /** @var \App\Models\State $state */
                if ($state == null)
                    throw new CustomBaseException('No such state code.', '-1');

                $query->where('venue_state_id' ,$state->id);
            }

            if ($from !== null)
                $query->where('start_date', '>=', $from);

            if ($to !== null)
                $query->where('end_date', '<=', $to);

            if ($usag || $usaigc || $aau || $nga) {
                $subquery = ' in (
                    SELECT sanctioning_body_id
                    FROM category_meet
                    WHERE meet_id = id
                )';

                $query->where(function ($q) use ($usag, $usaigc, $aau, $nga, $subquery) {
                    if ($usag)
                        $q->orWhereRaw(SanctioningBody::USAG . $subquery);

                    if ($usaigc)
                        $q->orWhereRaw(SanctioningBody::USAIGC . $subquery);

                    if ($aau)
                        $q->orWhereRaw(SanctioningBody::AAU . $subquery);

                    if ($nga)
                        $q->orWhereRaw(SanctioningBody::NGA . $subquery);
                });
            }

            if ($name !== null)
                $query->where('name', 'ILIKE', '%' . strtolower($name) . '%');

            if ($open) {
                $now = now()->setTime(0, 0);

                $query->where(function ($q) use ($now) {
                    $q->where(function ($q2) use ($now) {
                        $q2->where('allow_late_registration', true)
                            ->where('late_registration_end_date', '>=', $now)
                            ->where('late_registration_start_date', '<=', $now);
                    });

                    $q->orWhere(function ($q2) use ($now) {
                        $q2->where('registration_end_date', '>=', $now)
                            ->where('registration_start_date', '<=', $now);
                    });
                });
            }

//            if ($this->allow_late_registration) {
//                if ($now > $this->late_registration_end_date) {
//                    return self::REGISTRATION_STATUS_CLOSED;
//                } elseif ($now >= $this->late_registration_start_date) {
//                    return self::REGISTRATION_STATUS_LATE;
//                }
//            }
//
//            if ($now > $this->registration_end_date) {
//                return self::REGISTRATION_STATUS_CLOSED;
//            } elseif ($now >= $this->registration_start_date) {
//                return self::REGISTRATION_STATUS_OPEN;
//            } else {
//                return self::REGISTRATION_STATUS_OPENING_SOON;
//            }

            if ($status !== null) {
                $now = now()->setTime(0, 0);
                if ($status == Meet::REGISTRATION_STATUS_OPEN) {

                    $query->where(function ($q) use ($now) {
                        $q->where(function ($q2) use ($now) {
                            $q2->where('allow_late_registration', true)
                                ->where('late_registration_end_date', '>=', $now)
                                ->where('late_registration_start_date', '>=', $now);
                        });

                        $q->orWhere(function ($q2) use ($now) {
                            $q2->where('registration_end_date', '>=', $now)
                                ->where('registration_start_date', '<=', $now);
                        });
                    });

                }else if ($status == Meet::REGISTRATION_STATUS_LATE){

                    $query->where(function ($q) use ($now) {
                        $q->where(function ($q2) use ($now) {
                            $q2->where('allow_late_registration', true)
                                ->where('late_registration_end_date', '>=', $now)
                                ->where('late_registration_start_date', '<=', $now);
                        });
                    });

                }else if ($status == Meet::REGISTRATION_STATUS_OPENING_SOON){

                    $query->where(function ($q) use ($now) {
                        $q->where('registration_start_date', '>', $now);
                    });

                }else if ($status == Meet::REGISTRATION_STATUS_CLOSED){

                    $query->where(function ($q) use ($now) {
                        $q->where(function ($q2) use ($now) {
                            $q2->where('allow_late_registration', true)
                                ->where('late_registration_end_date', '<', $now);
                        });

                        $q->orWhere(function ($q2) use ($now) {
                            $q2->where('registration_end_date', '<', $now);
                        });
                    });

                }
            }

            if ($meet === null) {
                $countQuery = clone $query;
                $countQuery->select(DB::raw('count(meets.id) as meet_count'));
                $count = $countQuery->first()->meet_count;
            } else {
                $count = 1;
            }

            if ($limit !== null)
                $query->limit($limit)->offset(($page - 1) * $limit);

            $meets = $query->orderBy('is_featured', 'DESC')
                ->orderBy('start_date', 'DESC')
                ->get()
                ->makeHidden([
                    'gym_id', 'tshirt_size_chart_id', 'leo_size_chart_id', 'mso_meet_id',
                    'venue_state_id', 'meet_competition_format_id', 'is_published' , 'is_archived',
                    'handling_fee_override', 'cc_fee_override',
                    'paypal_fee_override', 'ach_fee_override', 'check_fee_override',
                    'created_at', 'updated_at'
                ]);

            if ($meet !== null){

                $meets = $meets->first();

                $meet_start = strtotime($meets->start_date);
                $meet_end = strtotime($meets->end_date);
                $today = time();
                if($today < $meet_start) // upcoming
                    $sts = 3;
                else if($today >= $meet_start && $today < $meet_end) // active
                    $sts = 2;
                else if($today >= $meet_end) //past
                    $sts = 1;

                $meets = [
                    'meetId' => $meets->id,
                    'mso_id' => $meets->mso_meet_id,
                    'meetHost' => $meets->gym->name,
                    'meetName' => $meets->name,
                    'organizationName' => $meets->gym->name,
                    'meetDescription' => $meets->description,
                    'meetAddress1' => $meets->venue_addr_1,
                    'meetAddress2' => $meets->venue_addr_2,
                    'meetCity' => $meets->venue_city,
                    'meetStateName' => $meets->venue_state->name,
                    'meetZipCode' => $meets->venue_zipcode,
                    'meetSchedule' => [
                        'meetStartDate' => $meets->start_date,
                        'meetEndDate' => $meets->end_date,
                        'meetRegistrationStartDate' => $meets->registration_start_date,
                        'meetRegistrationEndDate' => $meets->registration_end_date,
                        'meetRegistrationScratchEndDate' => $meets->registration_scratch_end_date,
                    ],
                    'schedule_url' => (isset($meets->schedule)) ? route('file.download', $meets->id) : null,
                    'isMeetSubscribed' => 1,
                    'meetOrigin' => 1,
                    'meetStatus' =>  $sts, // $meets->registrationStatus(),
                    'meetWebsite' => $meets->website,
                    'venueWebsite' => $meets->venue_website,
                    'equipment' => $meets->equipement,
                    'lateRegistrationEndDate' => $meets->late_registration_end_date,
                    'lateRegistrationFee' => $meets->late_registration_fee,
                    'lateRegistrationStartDate' => $meets->late_registration_start_date,
                    'profile' => $meets->profile_picture,
                    'primaryContactEmail' => $meets->primary_contact_email,
                    'primaryContactFax' => $meets->primary_contact_fax,
                    'primaryContactFirstName' => $meets->primary_contact_first_name,
                    'primaryContactLastName' => $meets->primary_contact_last_name,
                    'primaryContactPhone' => $meets->primary_contact_phone,
                    'schedule' => $meets->schedule,
                    'schedule_url' => route('file.download', $meets),
                    'secondaryContactEmail' => $meets->secondary_contact_email,
                    'secondaryContactFax' => $meets->secondary_contact_fax,
                    'secondaryContactFirstName' => $meets->secondary_contact_first_name,
                    'secondaryContactLastName' => $meets->secondary_contact_last_name,
                    'secondaryContactPhone' => $meets->secondary_contact_phone,
                    'specialAnnoucements' => $meets->special_annoucements,
                    'sanctionBodies' => $meets->sanction_bodies,
                    'showParticipateClubs' => $meets->show_participate_clubs,
                    'teamFormat' => $meets->team_format,
                    'isWaitlist' => $meets->isWaitList(),
                    'usedSlots' => $meets->getUsedSlots(),
                    'editingAbilities' => $meets->editingAbilities(),
                    'admissions' => $meets->admissions->map(function ($admission) {
                        $admission->amount = $admission->type == MeetAdmission::TYPE_TBD ? 'TBD' : $admission->amount ;
                    }),
                    'categories' => $meets->categories,
                    'competitionFormat' => $meets->competition_format,
                    'gym' => $meets->gym,
                    'levels' => $meets->levels,
                    'tshirtChart' => $meets->tshirt_chart,
                    'leoChart' => $meets->leo_chart,
                    'registeredCubs' => ($meets->show_participate_clubs == true) ? $meets->registrations->map(function ($item){
                        return $item->gym;
                    }) : null,
                ];

            }else {
                $meets = $meets->map(function ($item) use ($count){
                    $meet_start = strtotime($item->start_date);
                    $meet_end = strtotime($item->end_date);
                    $today = time();

                    if($today < $meet_start) // upcoming
                        $sts = 3;
                    else if($today >= $meet_start && $today < $meet_end) // active
                        $sts = 2;
                    else if($today >= $meet_end) //past
                        $sts = 1;
                    $data = [
                        'meetId' => $item->id,
                        'mso_id' => $item->mso_meet_id,
                        'meetHost' => $item->gym->name,
                        'meetName' => $item->name,
                        'organizationName' => $item->gym->name,
                        'meetAddress1' => $item->venue_addr_1,
                        'meetAddress2' => $item->venue_addr_2,
                        'meetCity' => $item->venue_city,
                        'meetStateName' => $item->venue_state->name,
                        'meetZipCode' => $item->venue_zipcode,
                        'meetDescription' => $item->description,
                        'profile' => $item->profile_picture,
                        'meetSchedule' => [
                            'meetStartDate' => $item->start_date,
                            'meetEndDate' => $item->end_date,
                            'meetRegistrationStartDate' => $item->registration_start_date,
                            'meetRegistrationEndDate' => $item->registration_end_date,
                            'meetRegistrationScratchEndDate' => $item->registration_scratch_end_date,
                        ],
                        'schedule_url' => (isset($item->schedule)) ? route('file.download', $item->id) : null,
                        'isMeetSubscribed' => 1,
                        'meetOrigin' => 1,
                        'meetStatus' => $sts //$item->registrationStatus(),
                    ];
                    return $data;
                });
            }

            return $this->success([
                'total' => $count,
                'page' => $page,
                'limit' => $limit,
                'meets' => $meets,
            ]);
        } catch(ValidationException $e) {
            throw $e;
        } catch(CustomBaseException $e) {
            throw $e;
        } catch(\Throwable $e) {
            if (config('app.debug'))
                throw $e;

            Log::warning(self::class . '@meets : ' . $e->getMessage(), [
                'filters' => $filters
            ]);
            return $this->error([
                'message' => 'Something went wrong while retreiving meets.'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }


    public function removeMeet(Request $request, string $gym, string $meet)
    {
        try {
            $gym = $request->_managed_account->retrieveGym($gym);
            $gym->removeMeet($meet);
            return $this->success();
        } catch(CustomBaseException $e) {
            throw $e;
        } catch(\Throwable $e) {
            Log::warning(self::class . '@removeMeet : ' . $e->getMessage(), [
                'Gym' => $gym,
                'Meet' => $meet,
                'Throwable' => $e
            ]);
            return $this->error([
                'message' => 'Something went wrong while removing meet.'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function hostMeetDetails(Request $request, string $gym, string $meet)
    {
        try {
            $gym = $request->_managed_account->retrieveGym($gym); /** @var Gym $gym */
            $meet = $gym->retrieveMeet($meet); /** @var Meet $meet */

            if (!$meet->is_published)
                throw new CustomBaseException("This meet is not published yet.", -1);

            $registrations = $meet->registrations()->with([
                'gym' => function ($q) {
                    $q->select([
                        'id', 'user_id', 'name', 'short_name', 'profile_picture', 'office_phone',
                        'website'
                    ]);
                },
                'gym.user' => function ($q) {
                    $q->select([
                        'id', 'first_name', 'last_name', 'email'
                    ]);
                },
                'levels' => function ($q) {
                    $q->with([
                        'sanctioning_body' => function ($q) {
                            $q->exclude(['created_at', 'updated_at']);
                        },
                        'level_category' => function ($q) {
                            $q->exclude(['created_at', 'updated_at']);
                        },
                    ]);
                },
                'athletes' => function ($q) {
                    $q->select([
                        'id', 'meet_registration_id', 'transaction_id', 'level_registration_id',
                        'first_name', 'last_name', 'gender', 'dob', 'is_us_citizen', 'tshirt_size_id',
                        'leo_size_id', 'usag_no', 'usag_active', 'usaigc_no', 'usaigc_active',
                        'aau_no', 'aau_active', 'nga_no', 'nga_active', 'was_late', 'in_waitlist', 'fee', 'late_fee', 'refund',
                        'late_refund', 'status', 'created_at', 'updated_at',
                    ])->with([
                        'tshirt' => function ($q) {
                            $q->exclude(['is_disabled']);
                        },
                        'leo' => function ($q) {
                            $q->exclude(['is_disabled']);
                        },
                        'registration_level' => function ($q) {
                            $q->select(['id', 'level_id'])
                                ->with([
                                    'level' => function ($q) {
                                        $q->select(['id', 'sanctioning_body_id', 'level_category_id']);
                                    },
                                ]);
                        }
                    ])->orderBy('first_name', 'ASC')
                        ->orderBy('last_name', 'ASC');
                },
                'specialists' => function ($q) {
                    $q->select([
                        'id', 'meet_registration_id', 'level_registration_id', 'first_name',
                        'last_name', 'gender', 'dob', 'is_us_citizen', 'tshirt_size_id',
                        'leo_size_id', 'usag_no', 'usag_active', 'usaigc_no', 'usaigc_active',
                        'aau_no', 'aau_active', 'nga_no', 'nga_active', 'created_at', 'updated_at',
                    ])->with([
                        'tshirt' => function ($q) {
                            $q->exclude(['is_disabled']);
                        },
                        'leo' => function ($q) {
                            $q->exclude(['is_disabled']);
                        },
                        'registration_level' => function ($q) {
                            $q->select(['id', 'level_id'])
                                ->with([
                                    'level' => function ($q) {
                                        $q->select(['id', 'sanctioning_body_id', 'level_category_id']);
                                    },
                                ]);
                        },
                        'events' => function ($q) {
                        },
                    ])->orderBy('first_name', 'ASC')
                        ->orderBy('last_name', 'ASC');
                },
                'coaches' => function ($q) {
                    $q->select([
                        'id', 'meet_registration_id', 'first_name', 'last_name', 'gender', 'dob',
                        'tshirt_size_id', 'usag_no', 'usag_active', 'usag_expiry',
                        'usag_safety_expiry', 'usag_safesport_expiry', 'usag_background_expiry',
                        'usag_u100_certification', 'usaigc_no', 'usaigc_background_check',
                        'aau_no', 'nga_no', 'was_late', 'in_waitlist', 'status', 'created_at', 'updated_at',
                        'transaction_id'
                    ])->with([
                        'tshirt' => function ($q) {
                            $q->exclude(['is_disabled']);
                        }
                    ])->orderBy('first_name', 'ASC')
                        ->orderBy('last_name', 'ASC');
                },
                'transactions' => function ($q) {
                    $q->select([
                        'id', 'meet_registration_id', 'processor_id',
                        'breakdown', 'method', 'status', 'created_at', 'updated_at', 'is_deposit', 'is_deposit_sattle'
                    ]);
                },
                'athlete_verifications' => function ($q) {
                    $q->select([
                        'id', 'meet_registration_id', 'sanctioning_body_id', 'results', 'status'
                    ]);
                },
                'coach_verifications' => function ($q) {
                    $q->select([
                        'id', 'meet_registration_id', 'sanctioning_body_id', 'results', 'status'
                    ]);
                },
            ])
                ->where('status', '!=', MeetRegistration::STATUS_CANCELED)
                ->get()
                ->makeHidden([
                    'gym_id', 'meet_id', 'handling_fee_override', 'cc_fee_override',
                    'paypal_fee_override', 'ach_fee_override', 'check_fee_override',
                ]);

            foreach ($registrations as $i => $registration) { /** @var MeetRegistration $registration */
                $specialists = $registration->specialists;
                foreach ($specialists as $j => $specialist) { /** @var RegistrationSpecialist $specialist */
                    $specialists[$j]->status = $specialist->status();
                    $specialists[$j]->has_pending_events = $specialist->hasPendingEvents();
                }
                $registrations[$i]->specialists = $specialists;
            }
            $users_info = DB::select("select users.id, users.email from gyms join users on gyms.user_id = users.id");
            $getAllGym = Gym::where('user_id', '!=',  $request->_managed_account->id)->get();
            $user_i = [];
            foreach ($users_info as $k) {
                $user_i[$k->id] = $k->email;
            }
            $newgym = [];
            foreach ($getAllGym as $k) {
                $getAllGym_2 = $k;
                if(isset($user_i[$k['user_id']]))
                {
                    $getAllGym_2['email'] = $user_i[$k['user_id']];
                }
                else
                {
                    $getAllGym_2['email'] = 'no mail';
                }
                $newgym[] = $getAllGym_2;
            }
            $depositGym =  $meet->deposit()->orderBy('id', 'desc')->get();
            $result = [
                'registrations' => $registrations,
                'allgym' => $newgym,
                'depositGym' => $depositGym
            ];

            return $this->success($result);
        } catch(CustomBaseException $e) {
            throw $e;
        } catch(\Throwable $e) {
            Log::warning(self::class . '@hostMeetDetails : ' . $e->getMessage(), [
                'Gym' => $gym,
                'Meet' => $meet,
                'Throwable' => $e
            ]);
            return $this->error([
                'message' => 'Something went wrong while fetching meet details.'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    private function generateRandomString($length = 8) {
        $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }
    public function createDeposit(Request $request) 
    {
        DB::beginTransaction();
        try{
            $k = json_decode($request->depositVar);
            $deposit = new Deposit;
            $deposit->meet_id = $k->meetId;
            $deposit->gym_id = $k->gymId;
            $deposit->amount = $k->amount;
            $deposit->token_id = $this->generateRandomString();
            $deposit->save();
            DB::commit();

            $gym = DB::table('gyms')
            ->join('users', 'users.id', '=', 'gyms.user_id')
            ->select('users.first_name','users.last_name','users.email', 'gyms.name')
            ->where('gyms.id', $k->gymId)
            ->first();
            
            $deposit->gymDetails = $gym;
            $deposit->edit = array(
                'update' => false,
                'gym'   => false,
                'amount' => false
            );
            Mail::to($gym->email)->send(new DepositCompleteMailable($deposit));
            return $this->success([
                'msg' => "Deposit Added Successfully"
            ]);
        }
        catch(CustomBaseException $e) {
            DB::rollBack();
                $msg = $e->getMessage();
                $msg .= '. Deposit Add Failed, Please Contect Admin';
                throw new CustomBaseException($msg, -1, $e);
            }
    }
    public function disableDeposit(Request $request)
    {
        try{
            DB::beginTransaction();
            $deposit = Deposit::where('id',$request->depositId)->first();
            $deposit->is_enable = false;
            $deposit->save();
            DB::commit();
            return $this->success([
                'msg' => "Deposit Disabled Successfully"
            ]);
        }catch(CustomBaseException $e)
        {
            DB::rollBack();
            $msg = $e->getMessage();
            $msg .= '. Deposit Disable Failed, Please Contect Admin';
            throw new CustomBaseException($msg, -1, $e);
        }
        
    }
    public function enableDeposit(Request $request)
    {
        try{
            DB::beginTransaction();
            $deposit = Deposit::where('id',$request->depositId)->first();
            $deposit->is_enable = true;
            $deposit->save();
            DB::commit();
            return $this->success([
                'msg' => "Deposit Disabled Successfully"
            ]);
        }catch(CustomBaseException $e)
        {
            DB::rollBack();
            $msg = $e->getMessage();
            $msg .= '. Deposit Disable Failed, Please Contect Admin';
            throw new CustomBaseException($msg, -1, $e);
        }
        
    }
    public function editDeposit(Request $request)
    {
        $prev_d = json_decode($request->deposit);
        try{
            DB::beginTransaction();
            $update_gym = false;
            $update_amount = true;
            $deposit = Deposit::where('id',$prev_d->id)->first();
            if($deposit->gym_id != $prev_d->gym_id)
            {
                $update_gym = true;
            }
            if($deposit->amount != $prev_d->amount)
            {
                $update_amount = true;
            }
            $deposit->gym_id = $prev_d->gym_id;
            $deposit->amount = $prev_d->amount;
            $deposit->updated_at = now();
            $deposit->save();
            DB::commit();

            $gym = DB::table('gyms')
            ->join('users', 'users.id', '=', 'gyms.user_id')
            ->select('users.first_name','users.last_name','users.email', 'gyms.name')
            ->where('gyms.id', $prev_d->gym_id)
            ->first();
            
            $deposit->gymDetails = $gym;
            $deposit->edit = array(
                'update' => true,
                'gym'   => $update_gym,
                'amount' => $update_amount
            );
            Mail::to($gym->email)->send(new DepositCompleteMailable($deposit));

            return $this->success([
                'msg' => "Deposit Updated Successfully"
            ]);
        }catch(CustomBaseException $e)
        {
            DB::rollBack();
            $msg = $e->getMessage();
            $msg .= '. Deposit Update Failed, Please Contect Admin';
            throw new CustomBaseException($msg, -1, $e);
        }
    }

    public function hostConfirmCheck(Request $request, string $gym, string $meet,
        string $registration, string $check ,string $card )
    {
        DB::beginTransaction();
        try {
            $stripeTransaction = null;
            $ignoreCheckCharge = config('app.ignore_check_charge');
            $host = $request->_managed_account; /** @var User $user */
            $gym = $host->retrieveGym($gym); /** @var Gym $gym */
            $meet = $gym->retrieveMeet($meet); /** @var Meet $meet */

            $registration = $meet->registrations()
                ->where('status', '!=', MeetRegistration::STATUS_CANCELED)
                ->find($registration); /** @var MeetRegistration $registration */
            if ($registration == null)
                throw new CustomBaseException("No such registration", -1);

            $check = $registration->transactions()
                ->where('method', MeetTransaction::PAYMENT_METHOD_CHECK)
                ->where('status', MeetTransaction::STATUS_PENDING)
                ->find($check); /** @var MeetTransaction $check */
            if ($check == null)
                throw new CustomBaseException("No such pending check", -1);

            // $clientFee = $request->validate(['amount' => ['required', 'numeric']]);
            // $processor_fee1 = ($clientFee['amount'] * $meet->cc_fee()) / 100;
            // $clientFee = $clientFee['amount']+$processor_fee1;
            
            // if (!Helper::isFloat($clientFee))
            //     throw new CustomBaseException("Invalid handling fee amount.", -1);
            // $clientFee = (float) $clientFee;

            // if($check->is_deposit)
            // {
            //     $fee = $check->breakdown['host']['deposit_handling'] + $check->breakdown['gym']['deposit_handling'];
            //     $processor_fee2 = ( $fee * $meet->cc_fee()) / 100;
            //     $fee +=  $processor_fee2;
            // }
            // else
            // {
            //     $fee = $check->breakdown['host']['handling'] + $check->breakdown['gym']['handling'];
            //     $processor_fee2 = ( $fee * $meet->cc_fee()) / 100;
            //     $fee +=  $processor_fee2;
            // }
            

            // $update_host_processor_fees = $check->breakdown;
            // $update_host_processor_fees["host"]["total"] -= $processor_fee2;
            // $update_host_processor_fees["host"]["processor"] = $processor_fee2;
            // if($check->is_deposit)
            // {
            //     $update_host_processor_fees["host"]["deposit_total"] -= $processor_fee2;
            // }
            // $check->breakdown = $update_host_processor_fees;
            // // exit();
            // // die();
            // // return $this->error([
            // //              'message' => 'Something went wrong while confirming check.'
            // //          ], Response::HTTP_INTERNAL_SERVER_ERROR);

            // if ($fee != $clientFee)
            //     throw new CustomBaseException("Handling fee amount mismatch.", -1);

            foreach ($check->athletes as $athlete) { /** @var RegistrationAthlete $athlete */
                if ($athlete->status != RegistrationAthlete::STATUS_PENDING_NON_RESERVED)
                    throw new CustomBaseException('Invalid athlete status');
                $athlete->status = RegistrationAthlete::STATUS_REGISTERED;
                $athlete->save();
            }

            $events = $check->specialist_events;
            foreach ($events as $event) { /** @var RegistrationSpecialistEvent $event */
                if ($event->status != RegistrationSpecialistEvent::STATUS_SPECIALIST_PENDING)
                    throw new CustomBaseException('Invalid specialist status');

                $event->status = RegistrationSpecialistEvent::STATUS_SPECIALIST_REGISTERED;
                $event->save();
            }

            foreach ($check->coaches as $coach) { /** @var RegistrationCoach $coach */
                if ($coach->status != RegistrationCoach::STATUS_PENDING_NON_RESERVED)
                    throw new CustomBaseException('Invalid coach status');
                $coach->status = RegistrationCoach::STATUS_REGISTERED;
                $coach->save();
            }

            $check->status = MeetTransaction::STATUS_COMPLETED;
            $check->save();

            // if (!$ignoreCheckCharge) {
            //     $stripeTransaction = StripeService::createCharge(
            //         $host->stripe_customer_id,
            //         $card,
            //         $fee,
            //         'USD',
            //         '',
            //         [
            //             'registration' => $registration->id,
            //             'transaction' => $check->id,
                            // 'gym' => $gym->name,
                            // 'meet' => $meet->name,
            //         ]
            //     );
            // }

            // $description = 'Check Confirmation — Handling Fee from ' . $registration->gym->name .
            //     '\'s registration in ' . $meet->name;

            // $checkConfirmationTransaction = $check->host_check_confirmation_transaction()->create([
            //     'user_id' => $host->id,
            //     'processor_id' => ($ignoreCheckCharge ? 'AG-FAKE-' . Helper::uniqueId() : $stripeTransaction->id),
            //     'total' => ($ignoreCheckCharge ? 0 : -$fee),
            //     'description' =>  $description,
            // ]);

            // AuditEvent::checkAccepted(
            //     request()->_managed_account, auth()->user(), $check, $checkConfirmationTransaction
            // );

            // $checkConfirmationTransaction->save();

            DB::commit();

            try {

                Mail::to($check->meet_registration->gym->user->email)
                    ->send(new TransactionCompletedMailable($check));

                Mail::to($host->email)
                    ->send(new HostTransactionCompletedMailable($check));
            } catch (\Throwable $e) {
                Log::warning(self::class . '@hostConfirmCheck (Mail) : ' . $e->getMessage(), [
                    'Throwable' => $e
                ]);
            }

            return $this->success([
                'transaction' => 'true'
                // 'transaction' => $checkConfirmationTransaction
            ]);
        } catch(CustomBaseException $e) {
            DB::rollBack();
            // if ($stripeTransaction != null) {
            //     $msg = $e->getMessage();
            //     $msg .= '. Payment failed. A charge might have been placed on your payment method,' .
            //         ' we tried to cancel it. If the charge appears on your payment method,' .
            //         ' please contact us.';
            //     throw new CustomBaseException($msg, -1, $e);
            // }
            throw $e;
        } catch(\Throwable $e) {
            DB::rollBack();
            Log::warning(self::class . '@hostConfirmCheck : ' . $e->getMessage(), [
                'Throwable' => $e
            ]);
            // if ($stripeTransaction != null) {
            //     $msg = $e->getMessage();
            //     $msg .= '. Payment failed. A charge might have been placed on your payment method,' .
            //         ' we tried to cancel it. If the charge appears on your payment method,' .
            //         ' please contact us.';
            //     throw new CustomBaseException($msg, -1, $e);
            // }
            return $this->error([
                'message' => 'Something went wrong while confirming check.'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function hostRejectCheck(Request $request, string $gym, string $meet,
        string $registration, string $check)
    {
        DB::beginTransaction();
        try {
            $host = $request->_managed_account; /** @var User $user */
            $gym = $host->retrieveGym($gym); /** @var Gym $gym */
            $meet = $gym->retrieveMeet($meet); /** @var Meet $meet */

            $registration = $meet->registrations()
                ->where('status', '!=', MeetRegistration::STATUS_CANCELED)
                ->find($registration); /** @var MeetRegistration $registration */
            if ($registration == null)
                throw new CustomBaseException("No such registration", -1);

            $check = $registration->transactions()
                ->where('method', MeetTransaction::PAYMENT_METHOD_CHECK)
                ->where('status', MeetTransaction::STATUS_PENDING)
                ->find($check); /** @var MeetTransaction $check */
            if ($check == null)
                throw new CustomBaseException("No such pending check", -1);

            $registrationLateFee = $check->breakdown['registration_late_fee'];
            $registration->late_fee -= $registrationLateFee;
            $registration->save();

            $levelTeamFees = $check->breakdown['level_team_fees'];
            if (count($levelTeamFees) > 0) {
                $levelIds = array_keys($levelTeamFees);
                $levels = $registration->levels()->wherePivotIn('id', $levelIds)->get();
                foreach ($levels as $l) { /** @var AthleteLevel $l */
                    $l->pivot->team_fee -= $levelTeamFees[$l->pivot->id]['fee'];
                    $l->pivot->team_late_fee -= $levelTeamFees[$l->pivot->id]['late'];
                    $l->pivot->save();
                }
            }

            $check->status = MeetTransaction::STATUS_CANCELED;
            $check->save();

            AuditEvent::checkRejected(request()->_managed_account, auth()->user(), $check);

            Mail::to($check->meet_registration->gym->user->email)
                ->send(new TransactionFailedMailable($check));

            DB::commit();
            return $this->success();
        } catch(CustomBaseException $e) {
            DB::rollBack();
            throw $e;
        } catch(\Throwable $e) {
            DB::rollBack();
            Log::warning(self::class . '@hostRejectCheck : ' . $e->getMessage(), [
                'Throwable' => $e
            ]);
            return $this->error([
                'message' => 'Something went wrong while rejecting check.'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function hostConfirmWaitlistEntry(Request $request, string $gym, string $meet,
        string $registration, string $transaction)
    {
        DB::beginTransaction();
        try {
            $host = $request->_managed_account; /** @var User $user */
            $gym = $host->retrieveGym($gym); /** @var Gym $gym */
            $meet = $gym->retrieveMeet($meet); /** @var Meet $meet */

            $registration = $meet->registrations()
                ->where('status', MeetRegistration::STATUS_REGISTERED)
                ->find($registration); /** @var MeetRegistration $registration */
            if ($registration == null)
                throw new CustomBaseException("No such registration", -1);

            $transaction = $registration->transactions()
                ->where('status', MeetTransaction::STATUS_WAITLIST_PENDING)
                ->find($transaction); /** @var MeetTransaction $transaction */
            if ($transaction == null)
                throw new CustomBaseException("No such transaction", -1);

            $transaction->status = MeetTransaction::STATUS_WAITLIST_CONFIRMED;
            $transaction->save();

            AuditEvent::waitlistConfirmed(
                request()->_managed_account, auth()->user(), $transaction
            );

            Mail::to($registration->gym->user->email)
                ->send(new WaitlistConfirmedMailable($transaction));

            // TODO : Mail to host

            DB::commit();

            return $this->success();
        } catch(CustomBaseException $e) {
            DB::rollBack();
            throw $e;
        } catch(\Throwable $e) {
            DB::rollBack();
            Log::warning(self::class . '@hostConfirmWaitlistEntry : ' . $e->getMessage(), [
                'Throwable' => $e
            ]);
            return $this->error([
                'message' => 'Something went wrong while confirming registration.'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function hostRejectWaitlistEntry(Request $request, string $gym, string $meet,
        string $registration, string $transaction)
    {
        DB::beginTransaction();
        try {
            $host = $request->_managed_account; /** @var User $user */
            $gym = $host->retrieveGym($gym); /** @var Gym $gym */
            $meet = $gym->retrieveMeet($meet); /** @var Meet $meet */

            $registration = $meet->registrations()
                ->where('status', MeetRegistration::STATUS_REGISTERED)
                ->find($registration); /** @var MeetRegistration $registration */
            if ($registration == null)
                throw new CustomBaseException("No such registration", -1);

            $transaction = $registration->transactions()
                ->whereIn('status', [
                    MeetTransaction::STATUS_WAITLIST_PENDING,
                    MeetTransaction::STATUS_WAITLIST_CONFIRMED,
                ])->find($transaction); /** @var MeetTransaction $transaction */
            if ($transaction == null)
                throw new CustomBaseException("No such transaction", -1);

            AuditEvent::waitlistRejected(
                request()->_managed_account, auth()->user(), $transaction
            );

            $transaction->delete();

            Mail::to($registration->gym->user->email)
                ->send(new WaitlistRejectedMailable($registration));

            // TODO : Mail to host

            DB::commit();

            return $this->success([
                'registration' => $registration
            ]);
        } catch(CustomBaseException $e) {
            DB::rollBack();
            throw $e;
        } catch(\Throwable $e) {
            DB::rollBack();
            Log::warning(self::class . '@hostRejectWaitlistEntry : ' . $e->getMessage(), [
                'Throwable' => $e
            ]);
            return $this->error([
                'message' => 'Something went wrong while rejecting registration.'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function hostVerifyEntrants(Request $request, string $gym, string $meet, string $registration) {
        DB::beginTransaction();
        try {
            $host = $request->_managed_account; /** @var User $user */
            $gym = $host->retrieveGym($gym); /** @var Gym $gym */
            $meet = $gym->retrieveMeet($meet); /** @var Meet $meet */

            $registration = $meet->registrations()
                ->where('status', '!=', MeetRegistration::STATUS_CANCELED)
                ->find($registration); /** @var MeetRegistration $registration */
            if ($registration == null)
                throw new CustomBaseException("No such registration", -1);

            $body = $request->input('body');
            $type = $request->input('type');

            $entrants = null;
            $specialists = null;
            $entrantVerifications = null;
            $filter = null;
            $status = null;
            $categories = null;

            switch ($body) {
                case SanctioningBody::USAG:
                    $categories = $meet->categories()
                        ->wherePivot('sanctioning_body_id', $body)
                        ->whereRaw('"category_meet"."sanction_no" IS NOT NULL')
                        ->get();
                    if ($categories->count() < 1)
                        throw new CustomBaseException("There are no active USAG sanctions in this meet to verify against.", -1);

                    $filter = 'usag_no';

                    switch ($type) {
                        case 'athletes':
                            $entrantVerifications = $registration->athlete_verifications();
                            $status = RegistrationAthlete::STATUS_SCRATCHED;
                            break;

                        case 'coaches':
                            $entrants = $registration->coaches();
                            $entrantVerifications = $registration->coach_verifications();
                            $status = RegistrationCoach::STATUS_SCRATCHED;
                            break;

                        default:
                            throw new CustomBaseException("Invalid type.", -1);
                            break;
                    }

                    break;

                case SanctioningBody::USAIGC:
                    if ($type != 'athletes')
                        throw new CustomBaseException("Invalid type.", -1);

                    $entrants = $registration->athletes()->with('registration_level.level')
                        ->whereHas('registration_level.level', function ($q) use ($body) {
                            $q->where('sanctioning_body_id', $body);
                        });

                    $specialists = $registration->specialists()->with('registration_level.level')
                        ->whereHas('registration_level.level', function ($q) use ($body) {
                            $q->where('sanctioning_body_id', $body);
                        });
                    $entrantVerifications = $registration->athlete_verifications();
                    $status = RegistrationAthlete::STATUS_SCRATCHED;
                    $filter = 'usaigc_no';
                    break;

                default:
                    throw new CustomBaseException("Invalid sanctioning body", -1);
            }

            $verification = $entrantVerifications->where('sanctioning_body_id', $body)
                ->first();

            if ($verification == null) {
                $verification = $entrantVerifications->create([
                    'sanctioning_body_id' => $body,
                    $type => [],
                    'status' => RegistrationAthleteVerification::VERIFICATION_PROCESSING
                ]);
            } else {
                if ($verification->status == RegistrationAthleteVerification::VERIFICATION_PROCESSING)
                    throw new CustomBaseException("There already a matching verification processing.", -1);
            }
            /** @var RegistrationAthleteVerification $verification */

            $entrantNumbers = [];
            if ($body == SanctioningBody::USAG) {
                if ($type == 'athletes') {
                    $hasEntrants = false;
                    foreach ($categories as $category) {
                        $entrants = $registration->athletes()->with('registration_level.level')
                            ->whereHas('registration_level.level', function ($q) use ($body, $category) {
                                $q->where('sanctioning_body_id', $body)
                                    ->where('level_category_id', $category->pivot->level_category_id);
                            })->where('status', '!=', $status)->whereNotNull($filter)->get();

                        if ($entrants->count() < 1)
                            continue;

                        $discipline = null;
                        switch ($category->id) {
                            case LevelCategory::GYMNASTICS_MEN:
                                $discipline = 'm';
                                break;

                            case LevelCategory::GYMNASTICS_WOMEN:
                                $discipline = 'w';
                                break;

                            default:
                                break;
                        }

                        $hasEntrants = true;
                        $entrantNumbers[$category->pivot->sanction_no] = [
                            'discipline' => $discipline,
                            'numbers' => $entrants->pluck($filter)->toArray()
                        ];
                    }

                    if (!$hasEntrants)
                        throw new CustomBaseException("There are no athletes to be verified.", -1);
                } else {
                    $entrants = $entrants->where('status', '!=', $status)->whereNotNull($filter)
                        ->get()->pluck($filter)->toArray();

                    $sanctions = [];
                    foreach ($categories as $category) {
                        switch ($category->id) {
                            case LevelCategory::GYMNASTICS_MEN:
                                $sanctions[$category->pivot->sanction_no] = 'm';
                                break;

                            case LevelCategory::GYMNASTICS_WOMEN:
                                $sanctions[$category->pivot->sanction_no] = 'w';
                                break;

                            default:
                                break;
                        }
                    }

                    $entrantNumbers = [
                        'sanctions' => $sanctions,
                        'numbers' => $entrants,
                    ];

                    if (count($entrants) < 1)
                        throw new CustomBaseException("There are no coaches to be verified.", -1);
                }
            } else {
                $entrantNumbers = $entrants->where('status', '!=', $status)->whereNotNull($filter)
                    ->get()->pluck($filter)->toArray();

                if ($specialists !== null) {
                    $specialists = $specialists->whereNotNull($filter)
                        ->get()->filter(function ($item) {
                            /** @var RegistrationSpecialist $item */
                            return ($item->status() != RegistrationSpecialistEvent::STATUS_SPECIALIST_SCRATCHED);
                        })->pluck($filter)->toArray();

                    $entrantNumbers = array_merge($entrantNumbers, $specialists);
                }

                $entrantNumbers = array_unique($entrantNumbers, SORT_STRING);
                if (count($entrantNumbers) < 1)
                    throw new CustomBaseException("There are no athletes to be verified.", -1);
            }

            $verification->update([
                $type => $entrantNumbers,
                'results' => []
            ]);
            $verification->save();

            DB::commit();

            ProcessEntrantVerificationRequest::dispatch($type, $verification);

            return $this->success([
                'message' => 'Verification initiated.',
                'verification' => $verification->makeHidden([
                    'meet_registration_id',
                    'sanctioning_body_id',
                    'athletes',
                    'created_at',
                    'updated_at',
                ])
            ]);
        } catch(CustomBaseException $e) {
            DB::rollBack();
            throw $e;
        } catch(\Throwable $e) {
            DB::rollBack();
            Log::warning(self::class . '@hostVerifyEntrants : ' . $e->getMessage(), [
                'Throwable' => $e
            ]);
            return $this->error([
                'message' => 'Something went wrong while running the verification.'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function getGymList(Request $request,Meet $meet)
    {
        $user = $request->_managed_account; /** @var User $user */
        $gymIds = MeetRegistration::where('meet_id','=',$meet->id)->pluck('gym_id')->toArray();
        $gyms = [];

        foreach ($gymIds as $gym) {
            $gyms[] = Gym::with(['state','country'])->find($gym);
        }

        return $this->success([
            'gyms' => $gyms,
        ]);
    }

    public function meetSubscribe(Request $request){
        $input = $request->all();
        try {
            $meetExists = Meet::where('id',$input['meet_id'])->exists();
            if (!$meetExists){
                return $this->error([
                    'message' => 'Meet does not exists.'
                ], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
            $userExists = User::where('id',$input['user_id'])->exists();
            if (!$userExists){
                return $this->error([
                    'message' => 'User does not exists.'
                ], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
            $subscriptionExist = MeetSubscription::where('meet_id',$input['meet_id'])->where('user_id',$input['user_id'])->exists();
            if ($subscriptionExist){
                return $this->error([
                    'message' => 'This user already subscribed meet.'
                ], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
            MeetSubscription::create($input);
            $meet = Meet::find($input['meet_id']);
            Helper::addNotification('Meet subscribed','You are subscribed to '.$meet->name,$input['user_id']);

            return $this->success([
                'message' => 'Meet subscribed successfully',
            ]);
        } catch(CustomBaseException $e) {
            throw $e;
        } catch(\Throwable $e) {
            Log::warning(self::class . '@statesList : ' . $e->getMessage(), [
                'Throwable' => $e
            ]);
            return $this->error([
                'message' => 'Something went wrong while fetching subscribe meet.'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function meetUnSubscribe(Request $request){
        $input = $request->all();
        try {
            MeetSubscription::where('meet_id',$input['meet_id'])->where('user_id',$input['user_id'])->delete();
            $meet = Meet::find($input['meet_id']);

            Helper::addNotification('Meet unsubscribed','You are unsubscribed from '.$meet->name,$input['user_id']);

            return $this->success([
                'message' => 'Meet unsubscribed successfully',
            ]);
        } catch(CustomBaseException $e) {
            throw $e;
        } catch(\Throwable $e) {
            Log::warning(self::class . '@statesList : ' . $e->getMessage(), [
                'Throwable' => $e
            ]);
            return $this->error([
                'message' => 'Something went wrong while fetching unsubscribe meet.'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function subscribedMeets(Request $request,int $user = null){
        $input = $request->all();
        try {
            $browseMeetsRules = [
                'page' => ['sometimes', 'nullable', 'integer', 'min:1'],
                'limit' => ['sometimes', 'nullable', 'integer', 'min:1'],
            ];

            $filters = $request->validate($browseMeetsRules);

            $page = (isset($filters['page']) ? (int) $filters['page'] : 1);
            $limit = (isset($filters['limit']) ? (int) $filters['limit'] : null);
            $meetIds = MeetSubscription::toBase();
            if ($user != null){
                $meetIds->where('user_id','=',$user);
            }

            $query = Meet::with(['gym','venue_state'])->whereIn('id',$meetIds->pluck('meet_id')->toArray())->where('is_published', true)
                ->where('is_archived', false);
            $countQuery = clone $query;
            $countQuery->select(DB::raw('count(meets.id) as meet_count'));
            $count = $countQuery->first()->meet_count;

            if ($limit !== null)
                $query->limit($limit)->offset(($page - 1) * $limit);

            $meets = $query->orderBy('is_featured', 'DESC')
                ->orderBy('start_date', 'DESC')
                ->get();

            $meets = $meets->map(function ($item) use ($count){
                $data = [
                    'meetId' => $item->id,
                    'mso_id' => $item->mso_meet_id,
                    'meetHost' => $item->gym->name,
                    'meetName' => $item->name,
                    'organizationName' => $item->gym->name,
                    'meetAddress1' => $item->venue_addr_1,
                    'meetAddress2' => $item->venue_addr_2,
                    'meetCity' => $item->venue_city,
                    'meetStateName' => $item->venue_state->name,
                    'meetZipCode' => $item->venue_zipcode,
                    'meetSchedule' => [
                        'meetStartDate' => $item->start_date,
                        'meetEndDate' => $item->end_date,
                        'meetRegistrationStartDate' => $item->registration_start_date,
                        'meetRegistrationEndDate' => $item->registration_end_date,
                        'meetRegistrationScratchEndDate' => $item->registration_scratch_end_date,
                    ],
                    'isMeetSubscribed' => 1,
                    'meetOrigin' => 1,
                    'meetStatus' => $item->registrationStatus(),
                ];
                return $data;
            });

            return $this->success([
                'total' => $count,
                'page' => $page,
                'limit' => $limit,
                'meets' => $meets,
            ]);
        } catch(CustomBaseException $e) {
            throw $e;
        } catch(\Throwable $e) {
            Log::warning(self::class . '@statesList : ' . $e->getMessage(), [
                'Throwable' => $e
            ]);
            return $this->error([
                'message' => 'Something went wrong while fetching unsubscribe meet.'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @param $meetId
     *
     * @return BinaryFileResponse
     */
    public function download($meetId): BinaryFileResponse
    {
        $file_name = substr(json_decode(Meet::where('id', $meetId)->first()['schedule'])->path,9);
        $path = storage_path().'/'.'app'.'/public/'.$file_name;

        return response()->download($path);
    }
    public function getUSAIGCAthleteCount($igc_no)
    {
        $number = DB::SELECT("SELECT count(ra.*) as total_athlete
        FROM category_meet as cm 
        JOIN meets as m on m.id = cm.meet_id 
        join meet_registrations as mr on mr.meet_id = m.id 
        join level_registration as lr on lr.meet_registration_id = mr.id 
        join athlete_levels as al on al.id = lr.level_id 
        join registration_athletes as ra on ra.level_registration_id = lr.id 
        where cm.sanctioning_body_id = 2 and al.sanctioning_body_id = 2 and cm.sanction_no = 'IGC02665'");

        return $this->success([
            'total' => $number[0]->total_athlete
        ]);
    }
}
