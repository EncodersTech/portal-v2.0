<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Exceptions\CustomBaseException;
use App\Models\Gym;
use Illuminate\Validation\ValidationException;
use App\Models\State;
use Illuminate\Support\Facades\DB;
use App\Models\SanctioningBody;
use App\Models\Meet;
use App\Models\MeetRegistration;
use Illuminate\Database\Eloquent\Builder;

class GymController extends BaseApiController
{
    public function index (Request $request, string $gym) {
        try {
            $gym = $request->_managed_account->retrieveGym($gym); /** @var Gym $gym */

            $gym = $gym->toArray();

            unset(
                $gym['handling_fee_override'],
                $gym['cc_fee_override'],
                $gym['paypal_fee_override'],
                $gym['ach_fee_override'],
                $gym['check_fee_override']
            );

            return $this->success([
                'gym' => $gym,
            ]);
        } catch(CustomBaseException $e) {
            throw $e;
        } catch(\Throwable $e) {
            if (config('app.debug'))
                throw $e;

            Log::warning(self::class . '@index : ' . $e->getMessage(), [
                'gym' => $gym
            ]);
            return $this->error([
                'message' => 'Something went wrong while retreiving gym details.'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function joinedMeets (Request $request, string $gym) {
        $filters = null;
        try {
            $gym = $request->_managed_account->retrieveGym($gym); /** @var Gym $gym */

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
                'status' => ['sometimes', 'nullable', 'integer'],
            ];

            $filters = $request->validate($browseMeetsRules);

            $page = (isset($filters['page']) ? (int) $filters['page'] : 1);
            $limit = (isset($filters['limit']) ? (int) $filters['limit'] : null);
            $status = (isset($filters['status']) ? (int) $filters['status'] : null);

            $query = $gym->registrations()
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

                if ($from !== null)
                    $q->where('start_date', '>=', $from);

                if ($to !== null)
                    $q->where('end_date', '<=', $to);

                if ($state !== null) {
                    $q->whereHas('venue_state', function (Builder $q2) use ($state) {
                        $q2->where('code', $state);
                    });
                }

                if ($name !== null)
                    $q->where('name', 'ILIKE', '%' . strtolower($name) . '%');

                if ($usag || $usaigc || $aau || $nga) {
                    $subquery = ' in (
                        SELECT sanctioning_body_id
                        FROM category_meet
                        WHERE meet_id = id
                    )';

                    $q->where(function ($q2) use ($usag, $usaigc, $aau, $nga, $subquery) {
                        if ($usag)
                            $q2->orWhereRaw(SanctioningBody::USAG . $subquery);

                        if ($usaigc)
                            $q2->orWhereRaw(SanctioningBody::USAIGC . $subquery);

                        if ($aau)
                            $q2->orWhereRaw(SanctioningBody::AAU . $subquery);

                        if ($nga)
                            $q2->orWhereRaw(SanctioningBody::NGA . $subquery);
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

            if (($status !== null)/* && ($status != MeetRegistration::STATUS_CANCELED)*/)
                $query->where('status', $status);

            $countQuery = clone $query;
            $countQuery->select(DB::raw('count(id) as registration_count'));
            $count = $countQuery->first()->registration_count;

            if ($limit !== null)
                $query->limit($limit)->offset(($page - 1) * $limit);

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

    public function gymInfo($gymId)
    {
        $gym = Gym::with(['state','country'])->find($gymId);

        return $this->success([
            'gym' => $gym,
        ]);
    }
}
