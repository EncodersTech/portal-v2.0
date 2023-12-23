<?php

namespace App\Http\Controllers\Api;

use App\Helper;
use App\Models\Athlete;
use App\Models\AthleteLevel;
use App\Models\AuditEvent;
use App\Models\ClothingSize;
use App\Services\USAIGCService;
use DateTime;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Exceptions\CustomBaseException;
use App\Models\AthleteSpecialistEvents;
use App\Models\SanctioningBody;
use Illuminate\Support\Facades\Validator;

class AthleteController extends BaseApiController
{
    public function athleteLevelList()
    {
        try {
            return $this->success(['levels' => Helper::getStructuredLevelList()]);
        } catch(CustomBaseException $e) {
            throw $e;
        } catch(\Throwable $e) {
            Log::warning(self::class . '@athleteLevelList : ' . $e->getMessage(), [
                'Throwable' => $e
            ]);
            return $this->error([
                'message' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function bodiesList()
    {
        try {
            $bodies = SanctioningBody::all();
            return $this->success(['bodies' => $bodies]);
        } catch(CustomBaseException $e) {
            throw $e;
        } catch(\Throwable $e) {
            Log::warning(self::class . '@bodiesList : ' . $e->getMessage(), [
                'Throwable' => $e
            ]);
            return $this->error([
                'message' => 'Something went wrong while fetching sanctioning bodies list.'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function specialistEvents()
    {
        try {
            $events = AthleteSpecialistEvents::with([
                'sanctioning_body' => function ($q) {
                    $q->exclude(['created_at', 'updated_at']);
                }
            ])->exclude(['created_at', 'updated_at'])->get();

            return $this->success(['events' => $events]);
        } catch(CustomBaseException $e) {
            throw $e;
        } catch(\Throwable $e) {
            Log::warning(self::class . '@specialistEvents : ' . $e->getMessage(), [
                'Throwable' => $e
            ]);
            return $this->error([
                'message' => 'Something went wrong while fetching specialist evnets list.'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function athleteList(Request $request, string $gym)
    {
        try {
            $gym = $request->_managed_account->retrieveGym($gym);
            $athletes = $gym->athletes()->with([
                'tshirt',
                'leo',
                'usag_level', 'usag_level.sanctioning_body', 'usag_level.level_category',
                'usaigc_level', 'usaigc_level.sanctioning_body', 'usaigc_level.level_category',
                'aau_level', 'aau_level.sanctioning_body', 'aau_level.level_category',
                'nga_level', 'nga_level.sanctioning_body', 'nga_level.level_category'
            ])->orderBy('first_name', 'ASC')->orderBy('last_name', 'ASC')->get();

            return $this->success(['athletes' => $athletes]);
        } catch(CustomBaseException $e) {
            throw $e;
        } catch(\Throwable $e) {
            Log::warning(self::class . '@athleteList : ' . $e->getMessage(), [
                'Gym' => $gym,
                'Throwable' => $e
            ]);
            return $this->error([
                'message' => 'Something went wrong while fetching athletes.'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function athleteRemove(Request $request, string $gym, string $athlete)
    {
        try {
            $gym = $request->_managed_account->retrieveGym($gym);
            $gym->removeAthlete($athlete);
            return $this->success();
        } catch(CustomBaseException $e) {
            throw $e;
        } catch(\Throwable $e) {
            Log::warning(self::class . '@athleteRemove : ' . $e->getMessage(), [
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
            $athletes = $gym->failed_athlete_imports()->with([
                'usag_level', 'usag_level.sanctioning_body', 'usag_level.level_category',
                'usaigc_level', 'usaigc_level.sanctioning_body', 'usaigc_level.level_category',
                'aau_level', 'aau_level.sanctioning_body', 'aau_level.level_category'
            ])->orderBy('created_at', 'DESC')->get();

            return $this->success(['failed_imports' => $athletes]);
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
            $gym->removeFailedAthleteImport($faulty);
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
    public function athletesImports($gymId, Request $request): JsonResponse
    {
        try {
            DB::beginTransaction();
            $athletes = [];
            $gym = $request->_managed_account->retrieveGym($gymId);
            foreach ($request->all() as $attr) {
                Validator::make($attr, Athlete::CREATE_RULES)->validate();
                $dob = new DateTime($attr['dob']);
                $tshirtSize = null;
                $leoSize = null;

                /*if ($dob > now())
                    throw new CustomBaseException('Invalid birth date.', '-1');*/

                if (isset($attr['tshirt_size_id'])) {
                    $tshirtSize = ClothingSize::find($attr['tshirt_size_id']);
                    if (($tshirtSize == null) || $tshirtSize->chart->is_leo) {
                        return $this->error(['message' => 'No such T-Shirt size.'], 69);
                    }
                    $tshirtSize = $tshirtSize->id;
                }

                if (isset($attr['leo_size_id'])) {
                    $leoSize = ClothingSize::find($attr['leo_size_id']);
                    if (($leoSize == null) || !$leoSize->chart->is_leo) {
                        return $this->error(['message' => 'No such Leotard size.'], 69);
                    }
                    $leoSize = $leoSize->id;
                }

                $athlete = [
                    'first_name' => Helper::title($attr['first_name']),
                    'last_name' => Helper::title($attr['last_name']),
                    'gender' => $attr['gender'],
                    'dob' => $dob,
                    'is_us_citizen' => isset($attr['is_us_citizen']),

                    'tshirt_size_id' => $tshirtSize,
                    'leo_size_id' => $leoSize
                ];

                if (isset($attr['usag_no'])) {
                    $duplicate = $gym->athletes()->where('usag_no', $attr['usag_no'])->first();
                    if ($duplicate !== null) {
                        return $this->error([
                            'message' => 'There is already an athlete with USAG No '.$attr['usag_no'].' in this gym.'
                        ]);
                    }

                    $level = AthleteLevel::find($attr['usag_level_id']);
                    if ($level == null) {
                        return $this->error(['message' => 'No such USAG level.']);
                    }

                    if ((!$level->level_category->male && ($attr['gender'] == 'male')) ||
                        (!$level->level_category->female && ($attr['gender'] == 'female'))) {
                        return $this->error(['message' => 'Invalid Gender / USAG Level combination'], -1);
                    }

                    $athlete += [
                        'usag_no' => $attr['usag_no'],
                        'usag_level_id' => $level->id,
                        'usag_active' => isset($attr['usag_active'])
                    ];
                }

                if (isset($attr['usaigc_no'])) {
                    $duplicate = $gym->athletes()->where('usaigc_no', $attr['usaigc_no'])->first();
                    if ($duplicate !== null) {
                        return $this->error([
                            'message' => 'There is already an athlete with USAIGC No '.$attr['usaigc_no'].' in this gym.'
                        ]);
                    }

                    $level = AthleteLevel::find($attr['usaigc_level_id']);
                    if ($level == null) {
                        return $this->error(['message' => 'No such USAIGC level.']);
                    }

                    if ((!$level->level_category->male && ($attr['gender'] == 'male')) ||
                        (!$level->level_category->female && ($attr['gender'] == 'female'))) {
                        return $this->error(['message' => 'Invalid Gender / USAIGC Level combination']);
                    }

                    $athlete += [
                        'usaigc_no' => $attr['usaigc_no'],
                        'usaigc_level_id' => $level->id,
                        'usaigc_active' => isset($attr['usaigc_active'])
                    ];
                }

                if (isset($attr['aau_no'])) {
                    $duplicate = $gym->athletes()->where('aau_no', $attr['aau_no'])->first();
                    if ($duplicate !== null) {
                        return $this->error([
                            'message' => 'There is already an athlete with AAU No '.
                                $attr['aau_no'].' in this gym.'
                        ]);
                    }

                    $level = AthleteLevel::find($attr['aau_level_id']);
                    if ($level == null) {
                        return $this->error(['message' => 'No such AAU level.']);
                    }

                    if ((!$level->level_category->male && ($attr['gender'] == 'male')) ||
                        (!$level->level_category->female && ($attr['gender'] == 'female'))) {
                        return $this->error(['message' => 'Invalid Gender / AAU Level combination'], -1);
                    }

                    $athlete += [
                        'aau_no' => $attr['aau_no'],
                        'aau_level_id' => $level->id,
                        'aau_active' => isset($attr['aau_active']),
                    ];
                }

                if (isset($attr['nga_no'])) {
                    $duplicate = $gym->athletes()->where('nga_no', $attr['nga_no'])->first();
                    if ($duplicate !== null) {
                        return $this->error(['message' => 'There is already an athlete with NGA No '.$attr['nga_no'].' in this gym.']);
                    }

                    $level = AthleteLevel::find($attr['nga_level_id']);
                    if ($level == null) {
                        return $this->error(['message' => 'No such NGA level.']);
                    }

                    if ((!$level->level_category->male && ($attr['gender'] == 'male')) ||
                        (!$level->level_category->female && ($attr['gender'] == 'female'))) {
                        return $this->error(['message' => 'Invalid Gender / NGA Level combination'], -1);
                    }

                    $athlete += [
                        'nga_no' => $attr['nga_no'],
                        'nga_level_id' => $level->id,
                        'nga_active' => isset($attr['nga_active']),
                    ];
                }

                $athlete = $gym->athletes()->create($athlete);

                AuditEvent::athleteCreated($request->_managed_account, auth()->user(), $athlete);
                if ($athlete->usaigc_no) {
                    $usaigcService = resolve(USAIGCService::class);
                    /** @var USAIGCService $usaigcService */
                    $verificationResult = $usaigcService->verifyAthlete(($athlete));

                    if ($verificationResult !== true) {
                        $athlete->usaigc_no = null;
                        $athlete->usaigc_level_id = null;
                        $athlete->usaigc_active = false;
                        $athlete->save();
                        $athletes[] = $athlete;
                    }
                } else {
                    $athletes[] = $athlete;
                }
            }
            DB::commit();
            return $this->success(['athletes' => $athletes]);
        } catch (CustomBaseException $e) {
            DB::rollBack();
            return $this->error(['message' => $e->getMessage()]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return $this->error(['message' => $e->getMessage()]);
        }
    }
}
