<?php

namespace App\Http\Controllers\Api;

use App\Helper;
use App\Models\AuditEvent;
use App\Models\ClothingSize;
use App\Models\Coach;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Exceptions\CustomBaseException;
use Illuminate\Support\Facades\Validator;

class CoachController extends BaseApiController
{
    public function coachList(Request $request, string $gym)
    {
        try {
            $gym = $request->_managed_account->retrieveGym($gym);
            $coaches = $gym->coaches()->with(['tshirt'])
                ->orderBy('first_name', 'ASC')->orderBy('last_name', 'ASC')->get();

            return $this->success(['coaches' => $coaches]);
        } catch(CustomBaseException $e) {
            throw $e;
        } catch(\Throwable $e) {
            Log::warning(self::class . '@coachList : ' . $e->getMessage(), [
                'Gym' => $gym,
                'Throwable' => $e
            ]);
            return $this->error([
                'message' => 'Something went wrong while fetching athletes.'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function coachRemove(Request $request, string $gym, string $coach)
    {
        try {
            $gym = $request->_managed_account->retrieveGym($gym);
            $gym->removeCoach($coach);
            return $this->success();
        } catch(CustomBaseException $e) {
            throw $e;
        } catch(\Throwable $e) {
            Log::warning(self::class . '@coachRemove : ' . $e->getMessage(), [
                'Gym' => $gym,
                'Throwable' => $e
            ]);
            return $this->error([
                'message' => 'Something went wrong while removing athlete.'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function failedImports(Request $request, string $gym)
    {
        try {
            $gym = $request->_managed_account->retrieveGym($gym);
            $coaches = $gym->failed_coach_imports()->orderBy('created_at', 'DESC')->get();

            return $this->success(['failed_imports' => $coaches]);
        } catch(CustomBaseException $e) {
            throw $e;
        } catch(\Throwable $e) {
            Log::warning(self::class . '@failedImports : ' . $e->getMessage(), [
                'Gym' => $gym,
                'Throwable' => $e
            ]);
            return $this->error([
                'message' => 'Something went wrong while fetching failed imports.'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function faultyImportRemove(Request $request, string $gym, string $faulty)
    {
        try {
            $gym = $request->_managed_account->retrieveGym($gym);
            $gym->removeFailedCoachImport($faulty);
            return $this->success();
        } catch(CustomBaseException $e) {
            throw $e;
        } catch(\Throwable $e) {
            Log::warning(self::class . '@faultyImportRemove : ' . $e->getMessage(), [
                'Gym' => $gym,
                'Throwable' => $e
            ]);
            return $this->error([
                'message' => 'Something went wrong while removing athlete.'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @param $gymId
     * @param  Request  $request
     *
     * @return JsonResponse
     */
    public function coachesImports($gymId, Request $request): JsonResponse
    {
        try {
            DB::beginTransaction();
            $coaches = [];
            $gym = $request->_managed_account->retrieveGym($gymId);
            foreach ($request->all() as $attr) {
                Validator::make($attr, Coach::CREATE_RULES)->validate();
                $dob = new \DateTime($attr['dob']);
                $tshirtSize = null;

                /*if ($dob > now())
                    throw new CustomBaseException('Invalid birth date.', '-1');*/

                if (isset($attr['tshirt_size_id'])) {
                    $tshirtSize = ClothingSize::find($attr['tshirt_size_id']);
                    if (($tshirtSize == null) || $tshirtSize->chart->is_leo)
                        return $this->error(['message' => 'No such T-Shirt size.']);
                    $tshirtSize= $tshirtSize->id;
                }

                $coach = [
                    'first_name' => Helper::title($attr['first_name']),
                    'last_name' => Helper::title($attr['last_name']),
                    'gender' => $attr['gender'],
                    'dob' => $dob,
                    'tshirt_size_id' => $tshirtSize,
                ];

                if (isset($attr['usag_no'])) {
                    $duplicate = $gym->coaches()->where('usag_no', $attr['usag_no'])->first();
                    if ($duplicate !== null)
                        return $this->error(['message' => 'There is already a coach with USAG No ' .
                            $attr['usag_no'] . ' in this gym.']);

                    $coach += [
                        'usag_no' => $attr['usag_no'],
                        'usag_active' => isset($attr['usag_active']),
                        'usag_expiry' => isset($attr['usag_expiry']) ? new \DateTime($attr['usag_expiry']) : null,
                        'usag_safety_expiry' => isset($attr['usag_safety_expiry']) ? new \DateTime($attr['usag_safety_expiry']) : null,
                        'usag_safesport_expiry' => isset($attr['usag_safesport_expiry']) ? new \DateTime($attr['usag_safesport_expiry']) : null,
                        'usag_background_expiry' => isset($attr['usag_background_expiry']) ? new \DateTime($attr['usag_background_expiry']) : null,
                        'usag_u100_certification' => isset($attr['usag_u100_certification'])
                    ];
                }
                if (isset($attr['usaigc_active'])) {
                    $coach += [
                        'usaigc_active' => $attr['usaigc_active']
                    ];
                }
                if (isset($attr['usaigc_no'])) {
                    $duplicate = $gym->coaches()->where('usaigc_no', $attr['usaigc_no'])->first();
                    if ($duplicate !== null)
                        return $this->error(['message' => 'There is already a coach with USAIGC No ' .
                            $attr['usaigc_no'] . ' in this gym.']);

                    $coach += [
                        'usaigc_no' => $attr['usaigc_no'],
                        'usaigc_background_check' => isset($attr['usaigc_background_check'])
                    ];
                }

                if (isset($attr['aau_no'])) {
                    $duplicate = $gym->coaches()->where('aau_no', $attr['aau_no'])->first();
                    if ($duplicate !== null)
                        return $this->error(['message' => 'There is already a coach with AAU No ' .
                            $attr['aau_no'] . ' in this gym.']);

                    $coach += [
                        'aau_no' => $attr['aau_no']
                    ];
                }

                if (isset($attr['nga_no'])) {
                    $duplicate = $gym->coaches()->where('nga_no', $attr['nga_no'])->first();
                    if ($duplicate !== null)
                        return $this->error(['message' => 'There is already a coach with NGA No ' .
                            $attr['nga_no'] . ' in this gym.']);

                    $coach += [
                        'nga_no' => $attr['nga_no']
                    ];
                }

                $coach = $gym->coaches()->create($coach);
                AuditEvent::coachCreated($request->_managed_account, auth()->user(), $coach);

                $coaches[] = $coach;
            }

            DB::commit();
            return $this->success(['coaches' => $coaches]);
        } catch (CustomBaseException $e) {
            DB::rollBack();
            return $this->error(['message' => $e->getMessage()]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return $this->error(['message' => $e->getMessage()]);
        }
    }
}
