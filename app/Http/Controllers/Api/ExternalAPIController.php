<?php

namespace App\Http\Controllers\Api;

use App\Exports\SanctionLevelsExport;
use App\Models\AthleteLevel;
use App\Models\Gym;
use App\Models\LevelCategory;
use App\Models\USAGSanction;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use App\Exceptions\CustomBaseException;
use App\Helper;
use App\Models\CategoryMeet;
use App\Models\ClothingSize;
use App\Models\ClothingSizeChart;
use App\Models\Country;
use App\Models\Meet;
use App\Models\MeetRegistration;
use App\Models\RegistrationAthlete;
use App\Models\RegistrationCoach;
use App\Models\RegistrationSpecialist;
use App\Models\RegistrationSpecialistEvent;
use App\Models\SanctioningBody;
use App\Models\State;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

use Illuminate\Foundation\Auth\ResetsPasswords;

class ExternalAPIController extends BaseApiController
{
    public const API_ERROR_RESSOURCE_NOT_FOUND = 404;
    public const API_ERROR_INVALID_VALUE = 400;

    private function authenticate(Request $request)
    {
        $configuredKeys = [
            config('app.api_ps_key'),
            config('app.frontend_key'),
        ];

        $incomingKey = $request->header('key') ? $request->header('key') : null;

        if (!in_array($incomingKey, $configuredKeys)) {
            \Log::info('Invalid API key');
            throw new CustomBaseException('Invalid API key', Response::HTTP_UNAUTHORIZED);
        }
    }

    public function levels(Request $request) {

        $this->authenticate($request);

        try {
            return $this->success(['levels' => Helper::getStructuredLevelList()]);
        } catch(CustomBaseException $e) {
            throw $e;
        } catch(\Throwable $e) {
            Log::warning(self::class . '@levels : ' . $e->getMessage(), [
                'Throwable' => $e
            ]);
            return $this->error([
                'message' => 'Something went wrong while fetching levels list.'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function bodies(Request $request) {
        $this->authenticate($request);

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

    public function states (Request $request) {
        $this->authenticate($request);

        try {
            $states = State::exclude(['created_at', 'updated_at'])->get();
            return $this->success(['states' => $states]);
        } catch(CustomBaseException $e) {
            throw $e;
        } catch(\Throwable $e) {
            Log::warning(self::class . '@states : ' . $e->getMessage(), [
                'Throwable' => $e
            ]);
            return $this->error([
                'message' => 'Something went wrong while fetching states list.'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function countries (Request $request) {
        $this->authenticate($request);

        try {
            $states = Country::exclude(['created_at', 'updated_at'])->get();
            return $this->success(['countries' => $states]);
        } catch(CustomBaseException $e) {
            throw $e;
        } catch(\Throwable $e) {
            Log::warning(self::class . '@countries : ' . $e->getMessage(), [
                'Throwable' => $e
            ]);
            return $this->error([
                'message' => 'Something went wrong while fetching countries list.'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function meets (Request $request) {
        $this->authenticate($request);
        return (new MeetController)->meets($request);
    }

    public function meet (Request $request, string $meet) {
        $this->authenticate($request);
        return (new MeetController)->meet($request, $meet);
    }

    public function meetsApi (Request $request) {
       // $this->authenticate($request);
        return (new MeetController)->meetsApi($request);
    }

    public function meetApi (Request $request, string $meet) {
        $this->authenticate($request);
        return (new MeetController)->meetApi($request, $meet);
    }

    public function meetSubscribe(Request $request){
        $this->authenticate($request);
        return (new MeetController)->meetSubscribe($request);
    }

    public function meetUnSubscribe(Request $request){
        $this->authenticate($request);
        return (new MeetController)->meetUnSubscribe($request);
    }

    public function subscribedMeets(Request $request,string $user = null){
        $this->authenticate($request);
        return (new MeetController)->subscribedMeets($request,$user);
    }

    public function ps_meet (Request $request, string $meetId, string $body = null) {
        $this->authenticate($request);
        
        $useWith = $request->get('useWith', null);

        try {
            $meet = null; /** @var Meet $meet */
            switch ($body) {
                case null:
                    $meet = Meet::find($meetId);
                    if ($meet === null)
                        throw new CustomBaseException("No such meet", Response::HTTP_OK);

                    break;

                case SanctioningBody::NGA:
                case SanctioningBody::USAIGC:
                case SanctioningBody::AAU:
                case SanctioningBody::USAG:
                    if ($useWith == 'meet') {
                        $meet = Meet::find($meetId);
                        if ($meet === null) {
                            throw new CustomBaseException("No such meet", Response::HTTP_OK);
                        }
                        
                        break;
                    }
                    if($body == SanctioningBody::NGA && $meetId[0] == 'N')
                        $meetId = substr($meetId, 1);
                    $category = CategoryMeet::where('sanction_no', $meetId)
                        ->where('sanctioning_body_id', $body)
                        ->first(); /** @var CategoryMeet $category */
                    if ($category === null)
                        throw new CustomBaseException("No such meet", Response::HTTP_OK);

                    $meet = $category->meet;

                    break;

//                case SanctioningBody::USAIGC:
//                case SanctioningBody::AAU:
//                    throw new CustomBaseException("Unsupported sanctioning body type.", Response::HTTP_OK);
//                    break;

                default:
                    throw new CustomBaseException("Invalid sanctioning body type.", self::API_ERROR_INVALID_VALUE);
                    break;
            }

            $result = [
                'meet_id' => $meet->id,
                'meet_registration_status' => $meet->registrationStatus(),
            ];

            $tshirtRequired = ($meet->tshirt_size_chart_id !== null);
            $leoRequired = ($meet->leo_size_chart_id !== null);

            if ($tshirtRequired) {
                $sizes = [];
                foreach ($meet->tshirt_chart->sizes as $sz) { /** @var ClothingSize $sz */
                    $sizes[] = [
                        'id' => $sz->id,
                        'designation' => $sz->size,
                        'disabled' => $sz->is_disabled
                    ];
                }

                $result['tshirt_chart'] = [
                    'name' => $meet->tshirt_chart->name,
                    'sizes' => $sizes,
                ];
            }

            if ($leoRequired) {
                $sizes = [];
                foreach ($meet->leo_chart->sizes as $sz) { /** @var ClothingSize $sz */
                    $sizes[] = [
                        'id' => $sz->id,
                        'designation' => $sz->size,
                        'disabled' => $sz->is_disabled
                    ];
                }

                $result['leo_chart'] = [
                    'name' => $meet->leo_chart->name,
                    'sizes' => $sizes,
                ];
            }

            $registrations = $meet->registrations()
                //->where('status', '!=', MeetRegistration::STATUS_CANCELED)
                ->with(['gym', 'gym.user', 'gym.country'])
                ->get();

            $entries = [];
            foreach ($registrations as $r) { /** @var MeetRegistration $registrations */
                
                if (!empty($body)) {

                    $bodyTypeCount = $r->gym->registrations()->whereHas('activeLevels', function (\Illuminate\Database\Eloquent\Builder $query) use($body) {
                        $query->where('sanctioning_body_id', $body);
                    })->count();
                    
                    if ($bodyTypeCount <= 0) {
                        continue;
                    }
                }
                
                $entries[] = [
                    'club_id' => $r->gym->id,
                    'registration_id' => $r->id,
                    "name" => $r->gym->name,
                    "short_name" => $r->gym->short_name,
                    'country' => $r->gym->country->code,
                    'contact' => [
                        'first_name' => $r->gym->user->first_name,
                        'last_name' => $r->gym->user->last_name,
                        'email' => $r->gym->user->email,
                        'office_phone' => $r->gym->office_phone,
                        'mobile_phone' => $r->gym->mobile_phone,
                    ],
                    'memberships' => [
                        'usag' => $r->gym->usag_membership,
                        'usaigc' => $r->gym->usaigc_membership,
                        'aau' => $r->gym->aau_membership,
                        'nga' => $r->gym->nga_membership,
                    ],
                    'status' => $r->status,
                ];
            }

            $result['registration_count'] = count($entries);
            $result['registrations'] = $entries;

            return $result;
        } catch(CustomBaseException $e) {
            return $this->error([
                'message' => $e->getMessage(),
            ], $e->getCode());
        } catch(\Throwable $e) {
            Log::warning(self::class . '@ps_meet : ' . $e->getMessage(), [
                'Throwable' => $e
            ]);
            return $this->error([
                'message' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function ps_registration (Request $request, string $registrationId, string $filter = null) {
        $this->authenticate($request);

        $filterBody = $request->get('bodyType', null);
        try {
            $showAthletes = true;
            $showCoaches = true;
            $athletes = [];
            $coaches = [];
            $result = [];

            switch (strtolower($filter)) {
                case 'athletes':
                    $showCoaches = false;
                    break;

                case 'coaches':
                    $showAthletes = false;
                    break;

                case null:
                    break;

                default:
                    throw new CustomBaseException("Invalid filter value.", self::API_ERROR_INVALID_VALUE);
                    break;
            }

            $registration = MeetRegistration::where('id', $registrationId)
                //->where('status', MeetRegistration::STATUS_REGISTERED)
                ->first(); /** @var MeetRegistration $registration */

            if ($registration === null)
                throw new CustomBaseException("No such registration", Response::HTTP_OK);

            $result['club_id'] = $registration->gym->id;

            $meet = $registration->meet; /** @var Meet $meet */
            $tshirtRequired = ($meet->tshirt_size_chart_id !== null);
            $leoRequired = ($meet->leo_size_chart_id !== null);

            if ($showAthletes) {
                foreach ($registration->athletes as $a) { /** @var RegistrationAthlete $a */

                    $bodyID = $a->registration_level->level->sanctioning_body_id;
                    $body = isset(SanctioningBody::SANCTION_BODY[$bodyID]) ? SanctioningBody::SANCTION_BODY[$bodyID] : null;
                    
                    if (!empty($filterBody) && $filterBody != $bodyID) {
                        continue;
                    }
                    
                    $entry = [
                        'first_name' => $a->first_name,
                        'last_name' => $a->last_name,
                        'dob' => $a->dob->format(Helper::AMERICAN_SHORT_DATE),
                        'us_citizen' => $a->is_us_citizen,
                        'sanctioning_body_id' => $a->registration_level->level->sanctioning_body_id,
                        'body' => $body,
                        'category' => [
                            'id' => $a->registration_level->level->level_category->id,
                            'name' => $a->registration_level->level->level_category->name,
                            'men' => $a->registration_level->level->level_category->male,
                            'women' => $a->registration_level->level->level_category->female,
                        ],

                        'level' => [
                            'id' => $a->registration_level->level->id,
                            'name' => $a->registration_level->level->name,
                            'code' => (
                            $a->registration_level->level->sanctioning_body_id == SanctioningBody::USAG ?
                                $a->registration_level->level->code :
                                $a->registration_level->level->abbreviation
                            ),
                           'abbr' => $a->registration_level->level->abbreviation
                        ],
                        'team' => $a->registration_level->has_team,
                        'specialist' => false,
                        'status' => $a->status,
                    ];

                    switch ($a->registration_level->level->sanctioning_body_id) {
                        case SanctioningBody::USAG:
                            $entry['membership'] = $a->usag_no;
                            break;

                        case SanctioningBody::USAIGC:
                            $entry['membership'] = $a->usaigc_no;
                            break;

                        case SanctioningBody::AAU:
                            $entry['membership'] = $a->aau_no;
                            break;

                        case SanctioningBody::NGA:
                            $entry['membership'] = $a->nga_no;
                            break;
                    }

                    if ($tshirtRequired) {
                        $entry['tshirt'] = [
                            'size' => $a->tshirt->size,
                            'size_id' => $a->tshirt->id,
                        ];
                    }

                    if ($leoRequired) {
                        $entry['leo'] = [
                            'size' => $a->leo->size,
                            'size_id' => $a->leo->id,
                        ];
                    }

                    $entry['created_at'] = $a->created_at->toIso8601String();
                    $entry['updated_at'] = $a->updated_at->toIso8601String();

                    $athletes[] = $entry;
                }

                foreach ($registration->specialists as $s) { /** @var RegistrationSpecialist $s */

                    $bodyID = $s->registration_level->level->sanctioning_body_id;
                    $body = isset(SanctioningBody::SANCTION_BODY[$bodyID]) ? SanctioningBody::SANCTION_BODY[$bodyID] : null;

                    if (!empty($filterBody) && $filterBody != $bodyID) {
                        continue;
                    }

                    $entry = [
                        'first_name' => $s->first_name,
                        'last_name' => $s->last_name,
                        'dob' => $s->dob->format(Helper::AMERICAN_SHORT_DATE),
                        'us_citizen' => $s->is_us_citizen,
                        'body' => $body,
                        'sanctioning_body_id' => $s->registration_level->level->sanctioning_body_id,

                        'category' => [
                            'id' => $s->registration_level->level->level_category->id,
                            'name' => $s->registration_level->level->level_category->name,
                            'men' => $s->registration_level->level->level_category->male,
                            'women' => $s->registration_level->level->level_category->female,
                        ],

                        'level' => [
                            'id' => $s->registration_level->level->id,
                            'name' => $s->registration_level->level->name,
                            'code' => (
                            $s->registration_level->level->sanctioning_body_id == SanctioningBody::USAG ?
                                $s->registration_level->level->code :
                                $s->registration_level->level->abbreviation
                            ),
                            'abbr' => $s->registration_level->level->abbreviation
                        ],

                        'team' => $s->registration_level->has_team,
                        'specialist' => true,
                        'status' => $s->status(),
                    ];

                    foreach ($s->events as $evt) { /** @var RegistrationSpecialistEvent $evt */
                        if ($evt->status != RegistrationSpecialistEvent::STATUS_SPECIALIST_REGISTERED)
                            continue;

                        $entry['events'][] = [
                            'id' => $evt->id,
                            'event_id' => $evt->specialist_event->id,
                            'name' => $evt->specialist_event->name,
                            'abbreviation' => $evt->specialist_event->abbreviation,
                            'status' => $evt->status,
                            'created_at' => $evt->created_at->toIso8601String(),
                            'updated_at' => $evt->updated_at->toIso8601String(),
                        ];
                    }

                    switch ($s->registration_level->level->sanctioning_body_id) {
                        case SanctioningBody::USAG:
                            $entry['membership'] = $s->usag_no;
                            break;

                        case SanctioningBody::USAIGC:
                            $entry['membership'] = $s->usaigc_no;
                            break;

                        case SanctioningBody::AAU:
                            $entry['membership'] = $s->aau_no;
                            break;

                        case SanctioningBody::NGA:
                            $entry['membership'] = $s->nga_no;
                            break;
                    }

                    if ($tshirtRequired) {
                        $entry['tshirt'] = [
                            'size' => $s->tshirt->size,
                            'size_id' => $s->tshirt->id,
                        ];
                    }

                    if ($leoRequired) {
                        $entry['leo'] = [
                            'size' => $s->leo->size,
                            'size_id' => $s->leo->id,
                        ];
                    }

                    $entry['created_at'] = $s->created_at->toIso8601String();
                    $entry['updated_at'] = $s->updated_at->toIso8601String();

                    $athletes[] = $entry;
                }
                $result['athletes'] = $athletes;
            }

            if ($showCoaches) {
                foreach ($registration->coaches as $c) { /** @var RegistrationCoach $c */
                    if ($c->status != RegistrationCoach::STATUS_REGISTERED) {
                        continue;
                    }
                    
                    if (!empty($filterBody)) {
                        if ($filterBody == SanctioningBody::USAG && empty($c->usag_no)) {
                            continue;
                        }

                        if ($filterBody == SanctioningBody::USAIGC && empty($c->usaigc_no)) {
                            continue;
                        }

                        if ($filterBody == SanctioningBody::AAU && empty($c->aau_no)) {
                            continue;
                        }

                        if ($filterBody == SanctioningBody::NGA && empty($c->nga_no)) {
                            continue;
                        }
                    }

                    $entry = [
                        'first_name' => $c->first_name,
                        'last_name' => $c->last_name,
                        'dob' => $c->dob->format(Helper::AMERICAN_SHORT_DATE),
                        'memberships' => [
                            'usag' => $c->usag_no,
                            'usaigc' => $c->usaigc_no,
                            'aau' => $c->aau_no,
                            'nga' => $c->nga_no,
                        ],
                        'status' => $c->status,
                    ];


                    if ($tshirtRequired) {
                        $entry['tshirt'] = [
                            'size' => $c->tshirt->size,
                            'size_id' => $c->tshirt->id,
                        ];
                    }

                    $entry['created_at'] = $c->created_at;
                    $entry['updated_at'] = $c->updated_at;

                    $coaches[] = $entry;
                }

                $result['coaches'] = $coaches;
            }

            return $result;
        } catch(CustomBaseException $e) {
            return $this->error([
                'message' => $e->getMessage(),
            ], $e->getCode());
        } catch(\Throwable $e) {
            Log::warning(self::class . '@ps_registration : ' . $e->getMessage(), [
                'Throwable' => $e
            ]);
            return $this->error([
                'message' => 'Something went wrong while fetching registration data.'
            ], 200);
        }
    }
    
    public function getSanction(Request $request) {
//        $this->authenticate($request);
        if (empty($request->get('meet_id')) && empty($request->get('club_id'))) {
            return $this->error([
                'message' => 'Pass either meet_id or club_id'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
        
        $meetID = $request->get('meet_id');
        $gymID = $request->get('club_id');

        $response['sanctions'] = [];
        if (!empty($meetID)) {
            $meet = Meet::find($meetID);
            if ($meet === null) {
                throw new CustomBaseException("No such meet", Response::HTTP_OK);
            }
            
            $response['meet_id'] = $meetID;
            $response['club_id'] = $meet->gym_id;
            $categoryMeet = CategoryMeet::where('meet_id', $meetID)->get();
            
            $sanctions = [
                'usag' => null,
                'usaigc' => null,
                'aau' => null,
                'nga' => null,
            ];
            foreach ($categoryMeet as $item) {
                $sanctions[\Str::lower(SanctioningBody::SANCTION_BODY[$item->sanctioning_body_id])] = $item->sanction_no;
            }
            
            $response['sanctions'] = $sanctions;
        } else {
            $response['club_id'] = $gymID;
            $response['sanctions'] = USAGSanction::where('gym_id', $gymID)->pluck('number')->toArray();
            $response['sanctions'] = array_values(array_unique($response['sanctions']));
        }
        
        return $response;
    }

    /**
     * @throws CustomBaseException
     */
    public function proScoreMeet(Request $request, string $meetId, $sanction = null, string $body = null)
    {
        $this->authenticate($request);
        $input = $request->all();

        try {
            /** @var Meet $meet */
            $meet = null;
            switch ($body) {
                case null:
                    $meet = Meet::find($meetId);
                    if ($meet === null)
                        throw new CustomBaseException("No such meet", Response::HTTP_OK);

                    break;

                case SanctioningBody::NGA:
                case SanctioningBody::USAIGC:
                case SanctioningBody::AAU:
                case SanctioningBody::USAG:
                    /** @var CategoryMeet $category */
                    $category = CategoryMeet::where('sanction_no', $sanction)
                        ->where('sanctioning_body_id', $body)
                        ->first();
                    if ($category === null)
                        throw new CustomBaseException("No such meet", Response::HTTP_OK);

                    $meet = $category->meet;

                    break;

                default:
                    throw new CustomBaseException("Invalid sanctioning body type.", self::API_ERROR_INVALID_VALUE);
                    break;
            }

            $result = [
                'meet_id' => $meet->id,
            ];


            $registrations = $meet->registrations()
                ->with(['gym', 'gym.user', 'gym.country'])
                ->get();

            $entries = [];
            foreach ($registrations as $r) { /** @var MeetRegistration $registrations */
                $entries[] = [
                    'club_id' => $r->gym->id,
                    'registration_id' => $r->id,
                    "name" => $r->gym->name,
                    "short_name" => $r->gym->short_name,
                    'country' => $r->gym->country->code,
                    'contact' => [
                        'first_name' => $r->gym->user->first_name,
                        'last_name' => $r->gym->user->last_name,
                        'email' => $r->gym->user->email,
                        'office_phone' => $r->gym->office_phone,
                        'mobile_phone' => $r->gym->mobile_phone,
                    ],
                    'memberships' => [
                        'usag' => $r->gym->usag_membership,
                        'usaigc' => $r->gym->usaigc_membership,
                        'aau' => $r->gym->aau_membership,
                        'nga' => $r->gym->nga_membership,
                    ],
                ];
            }

            $result['registration_count'] = count($entries);
            $result['registrations'] = $entries;

            return $result;
        } catch(CustomBaseException $e) {
            return $this->error([
                'message' => $e->getMessage(),
            ], $e->getCode());
        } catch(\Throwable $e) {
            Log::warning(self::class . '@proScoreMeet : ' . $e->getMessage(), [
                'Throwable' => $e
            ]);
            return $this->error([
                'message' => 'Something went wrong while fetching meet data.'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @throws CustomBaseException
     */
    public function proScoreMeetAthletes(Request $request, string $gymID, $membershipID = null , string $sanction = null)
    {
        $this->authenticate($request);
        try {
            $gym = null;
            $athletes = [];
            $result = [];
            switch ($sanction) {
                case null:
                    $gym = Gym::find($gymID);
                    if ($gym === null)
                        throw new CustomBaseException("No such club", Response::HTTP_OK);

                    break;
                case SanctioningBody::NGA:
                    $gym = Gym::where('id', $gymID)
                        ->where('nga_membership', $membershipID)
                        ->first();
                    if ($gym === null)
                        throw new CustomBaseException("No such club", Response::HTTP_OK);
                    break;
                case SanctioningBody::USAIGC:
                    $gym = Gym::where('id', $gymID)
                        ->where('usaigc_membership', $membershipID)
                        ->first();
                    if ($gym === null)
                        throw new CustomBaseException("No such club", Response::HTTP_OK);
                    break;
                case SanctioningBody::AAU:
                    $gym = Gym::where('id', $gymID)
                        ->where('aau_membership', $membershipID)
                        ->first();
                    if ($gym === null)
                        throw new CustomBaseException("No such club", Response::HTTP_OK);
                    break;
                case SanctioningBody::USAG:
                    $gym = Gym::where('id', $gymID)
                        ->where('usag_membership', $membershipID)
                        ->first();
                    if ($gym === null)
                        throw new CustomBaseException("No such club", Response::HTTP_OK);
                    break;
                default:
                    throw new CustomBaseException("Invalid sanctioning body type.", self::API_ERROR_INVALID_VALUE);
                    break;
            }

            // get club athletes
            foreach ($gym->athletes as $a) {
                $entry = [
                    'athlete_id' => $a->id,
                    'first_name' => $a->first_name,
                    'last_name' => $a->last_name,
                    'dob' => $a->dob->format(Helper::AMERICAN_SHORT_DATE),
                ];

                switch ($sanction) {
                    case SanctioningBody::USAG:
                        if ($a->usag_level != null) {
                            $entry['level'] = [
                                'id' => $a->usag_level->id,
                                'name' => $a->usag_level->name,
                                //                                'code' => (
                                //                                $a->usag_level->sanctioning_body_id == SanctioningBody::USAG ?
                                //                                    $a->usag_level->code :
                                //                                    $a->usag_level->abbreviation
                                //                                ),
                                'abbr' => $a->usag_level->abbreviation
                            ];
                        }
                        break;

                    case SanctioningBody::USAIGC:
                        if ($a->usaigc_level != null) {
                            $entry['level'] = [
                                'id' => $a->usaigc_level->id,
                                'name' => $a->usaigc_level->name,
                                //                                'code' => (
                                //                                $a->usag_level->sanctioning_body_id == SanctioningBody::USAG ?
                                //                                    $a->usag_level->code :
                                //                                    $a->usag_level->abbreviation
                                //                                ),
                                'abbr' => $a->usag_level->abbreviation
                            ];
                        }
                        break;

                    case SanctioningBody::AAU:
                        if ($a->aau_level != null) {
                            $entry['level'] = [
                                'id' => $a->aau_level->id,
                                'name' => $a->aau_level->name,
                                //                                'code' => (
                                //                                $a->usag_level->sanctioning_body_id == SanctioningBody::USAG ?
                                //                                    $a->usag_level->code :
                                //                                    $a->usag_level->abbreviation
                                //                                ),
                                'abbr' => $a->usag_level->abbreviation
                            ];
                        }
                        break;

                    case SanctioningBody::NGA:
                        if ($a->nga_level != null) {
                            $entry['level'] = [
                                'id' => $a->nga_level->id,
                                'name' => $a->nga_level->name,
                                //                                'code' => (
                                //                                $a->usag_level->sanctioning_body_id == SanctioningBody::USAG ?
                                //                                    $a->usag_level->code :
                                //                                    $a->usag_level->abbreviation
                                //                                ),
                                'abbr' => $a->usag_level->abbreviation
                            ];
                        }
                        break;
                }

                switch ($sanction) {
                    case SanctioningBody::USAG:
                        $entry['membership'] = $a->usag_no;
                        break;

                    case SanctioningBody::USAIGC:
                        $entry['membership'] = $a->usaigc_no;
                        break;

                    case SanctioningBody::AAU:
                        $entry['membership'] = $a->aau_no;
                        break;

                    case SanctioningBody::NGA:
                        $entry['membership'] = $a->nga_no;
                        break;
                }

                $athletes[] = $entry;
            }

            $result = [
                'club_id' => $gym->id,
                'athletes' => $athletes,
            ];

            return $result;
        } catch(CustomBaseException $e) {
            return $this->error([
                'message' => $e->getMessage(),
            ], $e->getCode());
        } catch(\Throwable $e) {
            Log::warning(self::class . '@proScoreMeetAthletes : ' . $e->getMessage(), [
                'Throwable' => $e
            ]);
            return $this->error([
                'message' => 'Something went wrong while fetching meet data.'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }


    /**
     * @throws CustomBaseException
     */
    public function sanctionLevels(Request $request, $meet = null): \Illuminate\Http\JsonResponse
    {
        try {
            $this->authenticate($request);

            $bodies = SanctioningBody::all();
            $categories = LevelCategory::all();
            $levels = AthleteLevel::where('is_disabled', false)->orderBy('created_at', 'ASC')->get();
            $result = [];
            $data = [];
            $tmp = [];
            foreach ($bodies as $body) {
                $tmp[$body->id] = $body;
            }
            $bodies = $tmp;

            $tmp = [];
            foreach ($categories as $category) {
                $tmp[$category->id] = $category;
            }
            $categories = $tmp;

            foreach ($levels as $level) {
                /** @var AthleteLevel $level */
                $body = $bodies[$level->sanctioning_body_id];
                $category = $categories[$level->level_category_id];

                $result[$body->initialism][] = $level;

            }

            foreach($result as $sanction => $levels) {
                foreach ($levels as $level) {
                    $data[] = [
                        'sanction' => $sanction,
                        'sanction_level_name' => $level->name,
                        'allgym_name' => $level->abbreviation
                    ];
                }
            }

            return $this->success($data);
        } catch(CustomBaseException $e) {
            throw $e;
        } catch(\Throwable $e) {
            Log::warning(self::class . '@levels : ' . $e->getMessage(), [
                'Throwable' => $e
            ]);
            return $this->error([
                'message' => 'Something went wrong while fetching levels list.'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

}