<?php

namespace App\Services;

use App\Exceptions\CustomBaseException;
use App\Helper;
use App\Mail\Host\USAG\USAGSanctionReceivedMailable;
use App\Mail\USAG\USAGLevelIssue;
use App\Mail\Registrant\USAG\USAGReservationReceivedMailable;
use App\Models\AuditEvent;
use App\Models\ErrorCodeCategory;
use App\Models\Gym;
use App\Models\LevelCategory;
use App\Models\MeetRegistration;
use App\Models\RegistrationAthlete;
use App\Models\RegistrationAthleteVerification;
use App\Models\RegistrationCoach;
use App\Models\RegistrationCoachVerification;
use App\Models\USAGReservation;
use App\Models\USAGSanction;
use App\Models\AthleteLevel;
use App\Models\SanctioningBody;
use GuzzleHttp\Client as Guzzle;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class USAGService {
    public const API_BASE_DEV = 'test.usagym.org';
    public const API_BASE_PROD = 'api.usagym.org';
    public const API_PATH = '/v4/';
    // public const API_PATH = '/v4/';

    public const WEBHOOK_TYPE_SANCTION = 'Sanction';
    public const WEBHOOK_TYPE_RESERVATION = 'Reservation';
    public const WEBHOOK_TYPE_CLUB = 'Club';

    public const WEBHOOK_ACTION_ADD = 'Add';
    public const WEBHOOK_ACTION_UPDATE = 'Update';
    public const WEBHOOK_ACTION_DELETE = 'Delete';
    public const WEBHOOK_ACTION_CHANGE_VENDOR = 'ChangeVendor';
    public const WEBHOOK_ACTION_SCRATCH = 'Scratch';

    public const WEBHOOK_VALID_ACTIONS = [
        self::WEBHOOK_TYPE_SANCTION => [
            self::WEBHOOK_ACTION_ADD,
            self::WEBHOOK_ACTION_UPDATE,
            self::WEBHOOK_ACTION_DELETE,
            self::WEBHOOK_ACTION_CHANGE_VENDOR,
        ],
        self::WEBHOOK_TYPE_CLUB => [
            self::WEBHOOK_ACTION_ADD,
            self::WEBHOOK_ACTION_UPDATE,
        ],
        self::WEBHOOK_TYPE_RESERVATION => [
            self::WEBHOOK_ACTION_ADD,
            self::WEBHOOK_ACTION_UPDATE,
            self::WEBHOOK_ACTION_SCRATCH,
        ],
    ];
  
    private $apiBase = 'https://' . self::API_BASE_DEV . self::API_PATH;

    private $webhookKey = null;

    private $guzzle = null; /** @var Guzzle $guzzle */

    public function __construct(bool $useDev = false, string $webhookKey = null) {
        try {
            $useDev = false;
            if ($webhookKey !== null)
                $this->webhookKey = $webhookKey;

            $this->apiBase = 'https://' . ($useDev ? self::API_BASE_DEV : self::API_BASE_PROD) . self::API_PATH;
            $this->guzzle = new Guzzle([
                'auth' => ['meetvip', 'b9f614e3-ee2a-45ee-9f53-3740b5565e38'],
                'base_uri' => $this->apiBase,
                'timeout'  => config('app.ext_api_timeout', 15),
            ]);
        } catch (\Throwable $e) {
            logger()->error(self::class . '::__construct() : ' . $e->getMessage());
            throw new CustomBaseException('Something went wrong with the USAG server.',-1);
        }
    }

    public function authorize(Request $request) {
        if ($this->webhookKey !== null) {
            $token = $request->bearerToken();
            if ($token !== $this->webhookKey)
                throw new CustomBaseException('USAG authorization failed.', Response::HTTP_UNAUTHORIZED);
        }

        return true;
    }
    public function verifyAthlete($athlete, bool $throw = false)
    {
        $isValid = false;
        $athlete_no = $athlete->usag_no;
        // print_r($athlete->gym->usag_membership); die();
        $sanction = $athlete->gym->usag_membership;
        $path = 'sanction/'. $sanction .'/verification/athlete?people=';
        try{
            if ($athlete != null) {
                $issues = [];
                $results = [
                    'name' => $athlete->fullName(),
                    'gender' => $athlete->gender,
                    'dob' => $athlete->dob->format(Helper::AMERICAN_SHORT_DATE),
                    'number' => $athlete->usag_no,
                    'us_citizen' => $athlete->is_us_citizen,
                ];

                $memberpath = $this->apiBase.$path . $athlete_no;

                $responseJSON = (string) $this->guzzle->request('GET', $memberpath)->getBody();
                // print_r($responseJSON); die();
                $response = json_decode($responseJSON, true);
                if ($response === null) {
                    $issues["general_issues"] = "USAG servers returned an invalid response\n" . $response;
                } else if ($response['status'] != 'success') {
                    $issues["general_issues"] = "Invalid USAG sanction number '". $sanction . "' or athlete number '". $number . "'";
                } else {
                    $response = $response['data']['members'][0];

                    $elligible = ($response['Eligible'] == 1);
                    if (!$elligible) {
                        $issues["general_issues"] = $response['IneligibleReason'];
                    } else {
                        if ($athlete->first_name != $response['FirstName'])
                        $issues["general_issues"] = 'First name mismatch, local is `' . $athlete->first_name . '`, remote is `' . $response['FirstName'] . '`';

                        if ($athlete->last_name != $response['LastName'])
                            $issues["general_issues"] = 'Last name mismatch, local is `' . $athlete->last_name . '`, remote is `' . $response['LastName'] . '`';

                        $dob = \DateTime::createFromFormat('n/j/y', $response['DOB']);
                        if (($dob === null) || ($dob === false)) {
                            $issues["general_issues"] = 'Invalid date value `' . $response['DOB'] . '`';
                        } else {
                            $dob = $dob->setTime(0, 0);
                            if ($athlete->dob != $dob)
                                $issues["general_issues"] = 'DoB mismatch, local is `' . $athlete->dob->format(Helper::AMERICAN_SHORT_DATE) . '`, remote is `' . $dob->format(Helper::AMERICAN_SHORT_DATE) . '`';
                        }

                        $api_us_citizen = ($response['USCitizen'] == 'Yes');
                        if ($athlete->is_us_citizen != $api_us_citizen)
                            $issues["general_issues"] = 'US Citizen mismatch, local is `' . $athlete->is_us_citizen . '`, remote is `' . $api_us_citizen . '`';

                        if (count($issues) < 1)
                            $isValid = true;
                    }
                }                        
            } else {
                $results = [];
                $issues[] = 'There is no athlete with USAG number ' . $number . ' in local database.';
            }
            // print_r($issues); die();
            // return $results;
            return $isValid ? true : $issues;
        }catch (\Throwable $e) {
            if($e->getCode() == 403)
            {
                $response = json_decode($e->getResponse()->getBody()->getContents(), true);
                if(isset($response['message']))
                {
                    $response = $response['message'];
                    throw new CustomBaseException($response, $e->getCode());
                }
            }
            logger()->error(self::class . '::verifyAthletes() : ' . $e->getMessage());
            throw new CustomBaseException('Something went wrong with the USAG verification.'. $e->getMessage(),-1);
        }

    }
    public function verifyAthletes(RegistrationAthleteVerification $verification) : array
    {
        try {
            $registration = $verification->meet_registration; /** @var MeetRegistration $registration */
            $results = [];

            foreach ($verification->athletes as $sanction => $data) {
                $discipline = $data['discipline'];
                $numbers = $data['numbers'];
                // $path = 'sanctions/' . $sanction . '/verification/a/' . $discipline . '/';
                $path = 'sanction/'. $sanction .'/verification/athlete?people=';

                if (count($numbers) < 1)
                    continue;

                foreach ($numbers as $number) {
                    $issues = [];
                    $isValid = false;

                    $athlete = $registration->athletes()
                                            ->where('status', '!=', RegistrationAthlete::STATUS_SCRATCHED)
                                            ->where('usag_no', $number)->first(); /** @var RegistrationAthlete $athlete */
                    if ($athlete != null) {

                        $results[$number] = [
                            'name' => $athlete->fullName(),
                            'gender' => $athlete->gender,
                            'dob' => $athlete->dob->format(Helper::AMERICAN_SHORT_DATE),
                            'number' => $athlete->usag_no,
                            'us_citizen' => $athlete->is_us_citizen,
                        ];

                        $memberpath = $path . $number;

                        $responseJSON = (string) $this->guzzle->request('GET', $memberpath)->getBody();
                        $response = json_decode($responseJSON, true);
                        if ($response === null) {
                            $issues[] = "USAG servers returned an invalid response\n" . $response;
                        } else if ($response['status'] != 'success') {
                            $issues[] = "Invalid USAG sanction number '". $sanction . "' or athlete number '". $number . "'";
                        } else {
                            $response = $response['data']['members'][0];

                            $elligible = ($response['Eligible'] == 1);
                            if (!$elligible) {
                                $issues[] = $response['IneligibleReason'];
                            } else {
                                if ($athlete->first_name != $response['FirstName'])
                                $issues[] = 'First name mismatch, local is `' . $athlete->first_name . '`, remote is `' . $response['FirstName'] . '`';
    
                                if ($athlete->last_name != $response['LastName'])
                                    $issues[] = 'Last name mismatch, local is `' . $athlete->last_name . '`, remote is `' . $response['LastName'] . '`';
    
                                $dob = \DateTime::createFromFormat('n/j/y', $response['DOB']);
                                if (($dob === null) || ($dob === false)) {
                                    $issues[] = 'Invalid date value `' . $response['DOB'] . '`';
                                } else {
                                    $dob = $dob->setTime(0, 0);
                                    if ($athlete->dob != $dob)
                                        $issues[] = 'DoB mismatch, local is `' . $athlete->dob->format(Helper::AMERICAN_SHORT_DATE) . '`, remote is `' . $dob->format(Helper::AMERICAN_SHORT_DATE) . '`';
                                }
    
                                $api_us_citizen = ($response['USCitizen'] == 'Yes');
                                if ($athlete->is_us_citizen != $api_us_citizen)
                                    $issues[] = 'US Citizen mismatch, local is `' . $athlete->is_us_citizen . '`, remote is `' . $api_us_citizen . '`';
    
                                if (count($issues) < 1)
                                    $isValid = true;
                            }
                        }                        
                    } else {
                        $results[$number] = [];
                        $issues[] = 'There is no athlete with USAG number ' . $number . ' in local database.';
                    }

                    $results[$number] += [
                        'valid' => $isValid,
                        'issues' => $issues
                    ];
                }
            }
            
            return $results;
        } catch (\Throwable $e) {
            logger()->error(self::class . '::verifyAthletes() : ' . $e->getMessage());
            throw new CustomBaseException('Something went wrong with the USAG verification.',-1);
        }
    }
    public function verifyCoach($coach , bool $throw = false)
    {
        // https://api.usagym.org/v4/sanction/58025/verification/coach?people=412333
        $isValid = false;
        $coach_no = $coach->usag_no;
        // print_r($athlete->gym->usag_membership); die();
        $sanction = $coach->gym->usag_membership;
        $path = 'sanction/'. $sanction .'/verification/coach?people='.$coach_no;
        try{
            $issues['general_issues'] = array();
            $valid = false;
            $responseJSON = (string) $this->guzzle->request('GET', $path)->getBody();
            $response = json_decode($responseJSON, true);
            if ($response === null) {
                $issues["general_issues"][] = "USAG servers returned an invalid response" . $response;
            } else if ($response['status'] != 'success') {
                $issues["general_issues"][] = "Invalid USAG sanction number '". $sanction . "' or coach number '". $number . "'";
            } else {
                $response = $response['data']['members'][0];

                $elligible = ($response['Eligible'] == 1);
                if (!$elligible) {
                    $issues["general_issues"][] = $response['IneligibleReason'];
                } else {
                    if ($coach->first_name != $response['FirstName'])
                    $issues["general_issues"][] = 'First name mismatch, local is `' . $coach->first_name . '`, remote is `' . $response['FirstName'] . '`';

                    if ($coach->last_name != $response['LastName'])
                        $issues["general_issues"][] = 'Last name mismatch, local is `' . $coach->last_name . '`, remote is `' . $response['LastName'] . '`';
                    
                    if (count($issues["general_issues"]) < 1)
                        $isValid = true;
                }
            }
            return $isValid ? true : $issues;
        }catch (\Throwable $e) {
            logger()->error(self::class . '::verifyCoaches() : ' . $e->getMessage());
            throw new CustomBaseException('Something went wrong with the USAG verification.' . $e->getMessage(),-1);
        }
    }
    public function verifyCoaches(RegistrationCoachVerification $verification) : array
    {
        try {
            $registration = $verification->meet_registration; /** @var MeetRegistration $registration */
            $results = [];

            $numbers = $verification->coaches['numbers'];
            $sanctions = $verification->coaches['sanctions'];

            foreach ($numbers as $number) {
                $issues = [];
                $valid = false;
                
                $coach = $registration->coaches()
                                        ->where('status', '!=', RegistrationCoach::STATUS_SCRATCHED)
                                        ->where('usag_no', $number)->first(); /** @var RegistrationCoach $coach */
                
                if ($coach != null) {
                    $results[$number] = [
                        'name' => $coach->fullName(),
                        'gender' => $coach->gender,
                        'dob' => $coach->dob->format(Helper::AMERICAN_SHORT_DATE),
                        'number' => $coach->usag_no,
                    ];

                    $valid = [];

                    foreach ($sanctions as $sanction => $discipline) {
                        // https://api.usagym.org/v4/sanctions/87945/verification/c/w/2137605

                        // https://usagym.org/app/api/v4/sanction/sanctionID/verification/memberType
                        $path = 'sanction/' . $sanction . '/verification/coach?people='.$number;
                        
                        $responseJSON = (string) $this->guzzle->request('GET', $path)->getBody();
                        $response = json_decode($responseJSON, true);
                        
                        $valid[$sanction] = false;
                        $issues[$sanction] = [];

                        if ($response === null) {
                            $issues[$sanction][] = "USAG servers returned an invalid response" . $response;
                        } else if ($response['status'] != 'success') {
                            $issues[$sanction][] = "Invalid USAG sanction number '". $sanction . "' or coach number '". $number . "'";
                        } else {
                            $response = $response['data']['members'][0];

                            $elligible = ($response['Eligible'] == 1);
                            if (!$elligible) {
                                $issues[$sanction][] = $response['IneligibleReason'];
                            } else {
                                if ($coach->first_name != $response['FirstName'])
                                $issues[$sanction][] = 'First name mismatch, local is `' . $coach->first_name . '`, remote is `' . $response['FirstName'] . '`';

                                if ($coach->last_name != $response['LastName'])
                                    $issues[$sanction][] = 'Last name mismatch, local is `' . $coach->last_name . '`, remote is `' . $response['LastName'] . '`';

                                if (count($issues[$sanction]) < 1)
                                    $valid[$sanction] = true;
                            }
                        }
                    }                    
                } else {
                    $results[$number] = [];
                    $issues['global'] = 'There is no coach with USAG number ' . $number . ' in local database.';
                }

                $results[$number] += [
                    'valid' => $valid,
                    'issues' => $issues
                ];
            }

            return $results;
        } catch (\Throwable $e) {
            logger()->error(self::class . '::verifyCoaches() : ' . $e->getMessage());
            throw new CustomBaseException('Something went wrong with the USAG verification.',-1);
        }
    }

    public function AddSanction($payload, Carbon $timestamp) {
        DB::beginTransaction();
        try {    
            $now = now(); /** @var Carbon $now */
            $notifStage = 0;
            $data = $payload->Sanction;

            if (!isset($data->OrganizationID))
                throw new CustomBaseException('Missing organization number', Response::HTTP_BAD_REQUEST);

            if (!isset($data->DisciplineType))
                throw new CustomBaseException('Missing discipline type', Response::HTTP_BAD_REQUEST);

            $category = strtolower($data->DisciplineType);
            if (!key_exists($category, USAGSanction::SANCTION_SUPPORTED_CATEGORIES))
                return false;

            $host = Gym::where('is_archived', false)
                        ->where('usag_membership', $data->OrganizationID)
                        ->first(); /** @var Gym $host */

            $contactName = null;
            $contactEmail = null;
            $unassigned = ($host === null);

            $sanction = USAGSanction::where('number', $data->SanctionID)
                                    ->where('action', USAGSanction::SANCTION_ACTION_ADD)
                                    ->first();  /** @var USAGSanction $sanction */
            if ($sanction !== null) {
                if (!$unassigned && ($sanction->gym !== null)) {                    
                    if ($sanction->gym->id !== $host->id) {
                        throw new CustomBaseException(
                            'A sanction with ID ' . $data->SanctionID . ' is already assigned to another club on Allgymnastics. Please contact Allgymnastics support.',
                            Response::HTTP_BAD_REQUEST
                        );
                    }
                }                

                switch ($sanction->status) {
                    case USAGSanction::SANCTION_STATUS_PENDING:
                    case USAGSanction::SANCTION_STATUS_UNASSIGNED:
                        throw new CustomBaseException(
                            'A pending or unassigned sanction with ID ' . $data->SanctionID . ' already exists on Allgymnastics',
                            Response::HTTP_BAD_REQUEST
                        );
                        break;

                    case USAGSanction::SANCTION_STATUS_DISMISSED:
                        $sanction->delete();
                        break;

                    case USAGSanction::SANCTION_STATUS_MERGED:
                        throw new CustomBaseException(
                            'A sanction with ID ' . $data->SanctionID . ' was already processed on Allgymnastics',
                            Response::HTTP_BAD_REQUEST
                        );
                        break;
                }
            }

            if ($unassigned) {
                // Sanction is going to be unassigned
                if (!Str::is('*@*.*', $data->MeetDirectorEmail)) {
                    throw new CustomBaseException(
                        'Invalid contact email "' . $data->MeetDirectorEmail . '".',
                        Response::HTTP_BAD_REQUEST
                    );
                }
                $contactName = $data->MeetDirectorName;
                $contactEmail = $data->MeetDirectorEmail;                    
            } else {
                $contactName = $host->user->fullName();
                $contactEmail = $host->user->email;
            }
            $data_to_send = array(
                'gym_id' => (!$unassigned ? $host->id : null),
                'gym_usag_no' => $data->OrganizationID,
                'usag_sanction_id' => $data->SanctionID,
                'action' => 3, // usag sanction add mail action
                'contact_name' => $contactName,
                'contact_email' => strtolower($contactEmail),
            );
            $this->checkForExistingLevelsInSanction($payload, $data_to_send);

            $sanction = [
                'gym_id' => (!$unassigned ? $host->id : null),
                'gym_usag_no' => $data->OrganizationID,
                'number' => $data->SanctionID,
                'level_category_id' => USAGSanction::SANCTION_SUPPORTED_CATEGORIES[$category],
                'action' => USAGSanction::SANCTION_ACTION_ADD,
                'payload' => $payload,
                'status' => ($unassigned ? USAGSanction::SANCTION_STATUS_UNASSIGNED : USAGSanction::SANCTION_STATUS_PENDING),
                'contact_name' => $contactName,
                'contact_email' => strtolower($contactEmail),
                'usag_meet_name' => $data->Name,
                'timestamp' => $timestamp,
                'notification_stage' => $notifStage,
                'next_notification_on' => $now->addDays(USAGSanction::SANCTION_NOTIFICATION_STAGES[$notifStage]),
            ];
            $sanction = USAGSanction::create($sanction); /** @var USAGSanction $sanction */

            AuditEvent::usagSanctionReceived($sanction);

            Mail::to($sanction->contact_email)
                ->send(new USAGSanctionReceivedMailable($sanction));

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }        
    }

    public function UpdateSanction($payload, Carbon $timestamp, $action = USAGSanction::SANCTION_ACTION_UPDATE) {
        DB::beginTransaction();
        try {    
            $now = now(); /** @var Carbon $now */
            $notifStage = 0;
            $data = $payload->Sanction;
                        
            $parent = USAGSanction::where('number', $data->SanctionID)
                                    ->where('action', USAGSanction::SANCTION_ACTION_ADD)
                                    ->first();  /** @var USAGSanction $parent */
            if ($parent !== null) {
                $unassigned = ($parent->gym_id === null);

                $data_to_send = array(
                    'gym_id' => $parent->gym_id,
                    'gym_usag_no' => $parent->gym_usag_no,
                    'usag_sanction_id' => $data->SanctionID,
                    'action' => 4, // usag sanction update mail action
                    'contact_name' => $parent->contact_name,
                    'contact_email' => strtolower($parent->contact_email),
                );
                $this->checkForExistingLevelsInSanction($payload, $data_to_send);

                $sanction = [
                    'number' => $parent->number,
                    'gym_id' => $parent->gym_id,
                    'gym_usag_no' => $parent->gym_usag_no,
                    'meet_id' => $parent->meet_id,
                    'level_category_id' => $parent->level_category_id,
                    'action' => $action,
                    'payload' => $payload,
                    'status' => ($unassigned ? USAGSanction::SANCTION_STATUS_UNASSIGNED : USAGSanction::SANCTION_STATUS_PENDING),
                    'contact_name' => $parent->contact_name,
                    'contact_email' => $parent->contact_email,
                    'usag_meet_name' => $parent->usag_meet_name,
                    'timestamp' => $timestamp,
                    'notification_stage' => $notifStage,
                    'next_notification_on' => $now->addDays(USAGSanction::SANCTION_NOTIFICATION_STAGES[$notifStage]),
                ];                

                $sanction = $parent->children()->create($sanction); /** @var USAGSanction $sanction */

                AuditEvent::usagSanctionReceived($sanction);

                Mail::to($sanction->contact_email)
                    ->send(new USAGSanctionReceivedMailable($sanction));
            } else {
                throw new CustomBaseException(
                    'Received a sanction update for an non-existing sanction',
                    Response::HTTP_BAD_REQUEST
                );
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }        
    }

    public function AddReservation($payload, Carbon $timestamp) {
        DB::beginTransaction();
        try {    
            $now = now(); /** @var Carbon $now */
            $notifStage = 0;
            $data = $payload->Reservation;

            if (!isset($data->ClubUSAGID))
                throw new CustomBaseException('Missing club number', Response::HTTP_BAD_REQUEST);

            $gym = Gym::where('is_archived', false)
                ->where('usag_membership', $data->ClubUSAGID)
                ->first(); /** @var Gym $gym */

            $contactName = null;
            $contactEmail = null;
            $unassigned = ($gym === null);

            $sanction = USAGSanction::where('action', USAGSanction::SANCTION_ACTION_ADD)
                                    ->where('number', $data->SanctionID)
                                    ->first();            
            if ($sanction === null) {
                throw new CustomBaseException(
                    'Received a reservation for an non-existing sanction',
                    Response::HTTP_BAD_REQUEST
                );
            }

            if (!$unassigned) { // If matched a gym, check for a previous reservation and avoid duplicates
                $reservation = USAGReservation::where(function (Builder $q0) use ($gym, $data) {                
                    $q0->where('gym_id', $gym->id)
                        ->orWhere('gym_usag_no', $data->ClubUSAGID);
                })->wherehas('usag_sanction', function (Builder $q) use ($data) {
                    $q->where('number', $data->SanctionID);
                })->where('action', USAGReservation::RESERVATION_ACTION_ADD)
                ->first();  /** @var USAGReservation $reservation */
                
                if ($reservation !== null) {
                    switch ($reservation->status) {
                        case USAGSanction::SANCTION_STATUS_UNASSIGNED:
                        case USAGReservation::RESERVATION_STATUS_PENDING:
                            throw new CustomBaseException(
                                'A reservation for this club and sanction with ID ' . $data->SanctionID . ' already exists on Allgymnastics',
                                Response::HTTP_BAD_REQUEST
                            );
                            break;

                        case USAGReservation::RESERVATION_STATUS_DISMISSED:
                            $reservation->delete();
                            break;

                        case USAGReservation::RESERVATION_STATUS_MERGED:
                            throw new CustomBaseException(
                                'A reservation for this club and sanction with ID ' . $data->SanctionID . ' was already processed on Allgymnastics',
                                Response::HTTP_BAD_REQUEST
                            );
                            break;
                    }
                }
            }

            if ($unassigned) {
                // Reservation is going to be unassigned
                if (!Str::is('*@*.*', $data->ClubContactEmail)) {
                    throw new CustomBaseException(
                        'Invalid contact email "' . $data->ClubContactEmail . '".',
                        Response::HTTP_BAD_REQUEST
                    );
                }
                $contactName = $data->ClubContact;
                $contactEmail = $data->ClubContactEmail;                    
            } else {
                $contactName = $gym->user->fullName();
                $contactEmail = $gym->user->email;
            }
            $data_to_send = array(
                'gym_id' => (!$unassigned ? $gym->id : null),
                'gym_usag_no' => $data->ClubUSAGID,
                'usag_sanction_id' => $sanction->id,
                'action' => USAGReservation::RESERVATION_ACTION_ADD,
                'contact_name' => $contactName,
                'contact_email' => strtolower($contactEmail),
            );
            $this->checkForExistingLevels($payload, $data_to_send);
            $reservation = [
                'gym_id' => (!$unassigned ? $gym->id : null),
                'gym_usag_no' => $data->ClubUSAGID,
                'usag_sanction_id' => $sanction->id,
                'action' => USAGReservation::RESERVATION_ACTION_ADD,
                'payload' => $payload,
                'status' => ($unassigned ? USAGReservation::RESERVATION_STATUS_UNASSIGNED : USAGReservation::RESERVATION_STATUS_PENDING),
                'contact_name' => $contactName,
                'contact_email' => strtolower($contactEmail),
                'timestamp' => $timestamp,
                'notification_stage' => $notifStage,
                'next_notification_on' => $now->addDays(USAGReservation::RESERVATION_NOTIFICATION_STAGES[$notifStage]),
            ];
            $reservation = USAGReservation::create($reservation); /** @var USAGReservation $reservation */

            AuditEvent::usagReservationReceived($reservation);

            Mail::to($reservation->contact_email)
                ->send(new USAGReservationReceivedMailable($reservation));

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }        
    }

    public function UpdateReservation($payload, Carbon $timestamp) {
        DB::beginTransaction();
        try {    
            $now = now(); /** @var Carbon $now */
            $notifStage = 0;
            $data = $payload->Reservation;

            if (!isset($data->ClubUSAGID))
                throw new CustomBaseException('Missing club number', Response::HTTP_BAD_REQUEST);

            $gym = Gym::where('is_archived', false)
                ->where('usag_membership', $data->ClubUSAGID)
                ->first(); /** @var Gym $gym */

            $unassigned = ($gym === null);

            $parent = USAGReservation::where(function (Builder $q0) use ($unassigned, $gym, $data) {
                if ($unassigned)
                    $q0->where(DB::raw('gym_usag_no::TEXT'), $data->ClubUSAGID);
                else
                    $q0->where('gym_id', $gym->id);
            })->wherehas('usag_sanction', function (Builder $q) use ($data) {
                $q->where('number', $data->SanctionID);
            })->where('action', USAGReservation::RESERVATION_ACTION_ADD)
            ->first();  /** @var USAGReservation $reservation */

            /*
            dump([
                'unassigned' => $unassigned,
                'gym' => ($unassigned ? null : [
                    'id' => $gym->id,
                    'name' => $gym->name,
                    'usag_membership' => $gym->usag_membership,
                ]),
                'data' => ($parent === null ? null : [
                    'parent.id' => $parent->id,
                    
                    'parent.gym.name' => $parent->gym->name,
                    'parent.payload.ClubName' => $parent->payload['Reservation']['ClubName'],
                    'current.payload.ClubName' => $data->ClubName,

                    'parent.gym_usag_no' => $parent->id,
                    'parent.gym.usag_membership' => $parent->gym->usag_membership,
                    'parent.payload.ClubUSAGID' => $parent->payload['Reservation']['ClubUSAGID'],
                    'current.payload.ClubUSAGID' => $data->ClubUSAGID,

                    'parent.usag_sanction.number' => $parent->usag_sanction->number,
                    'parent.payload.SanctionID' => $parent->payload['Reservation']['SanctionID'],
                    'current.payload.SanctionID' => $data->SanctionID,
                ]),
                'sql' => [
                    'parent' => $parentSql,
                ]
            ]);
            throw new CustomBaseException('test', -1);
            */
      
            if ($parent !== null) {       
                $data_to_send = array(
                    'gym_id' => $parent->gym_id,
                    'gym_usag_no' => $parent->gym_usag_no,
                    'usag_sanction_id' => $parent->usag_sanction->id,
                    'action' => USAGReservation::RESERVATION_ACTION_UPDATE,
                    'contact_name' => $parent->contact_name,
                    'contact_email' => strtolower($parent->contact_email),
                );
                $this->checkForExistingLevels($payload, $data_to_send);         
                $reservation = [
                    'gym_id' => $parent->gym_id,
                    'gym_usag_no' => $parent->gym_usag_no,
                    'usag_sanction_id' => $parent->usag_sanction->id,
                    'action' => USAGReservation::RESERVATION_ACTION_UPDATE,
                    'payload' => $payload,
                    'status' => ($unassigned ? USAGReservation::RESERVATION_STATUS_UNASSIGNED : USAGReservation::RESERVATION_STATUS_PENDING),
                    'contact_name' => $parent->contact_name,
                    'contact_email' => $parent->contact_email,
                    'timestamp' => $timestamp,
                    'notification_stage' => $notifStage,
                    'next_notification_on' => $now->addDays(USAGSanction::SANCTION_NOTIFICATION_STAGES[$notifStage]),
                ];                

                $reservation = $parent->children()->create($reservation); /** @var USAGReservation $reservation */

                AuditEvent::usagReservationReceived($reservation);

                Mail::to($reservation->contact_email)
                    ->send(new USAGReservationReceivedMailable($reservation));
            } else {
                throw new CustomBaseException(
                    'Received a reservation update for an non-existing reservation',
                    Response::HTTP_BAD_REQUEST
                );
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }        
    }
    public function checkForExistingLevelsInSanction($payload, $data_to_send)
    {   
        if(typeof($payload) == 'object')
        {
            $payload = json_decode(json_encode($payload), true); // convert object to array
        }
        $athlete_levels = $payload['Sanction']['Levels']['Add'];
        $gender = $payload['Sanction']['DisciplineType'];
        $dff_level_male = [];
        $dff_level_female = [];
        if($gender == 'Women')
        {
            $data = AthleteLevel::select('code')->whereIn('code', $athlete_levels)
            ->where('sanctioning_body_id', SanctioningBody::USAG)
            ->where('level_category_id',LevelCategory::GYMNASTICS_WOMEN)
            ->get()->pluck('code')->toArray();
            $dff_level_female = array_diff($athlete_levels, $data);
        }
        else
        {
            $data = AthleteLevel::select('code')->whereIn('code', $athlete_levels)
            ->where('sanctioning_body_id', SanctioningBody::USAG)
            ->where('level_category_id',LevelCategory::GYMNASTICS_MEN)
            ->get()->pluck('code')->toArray();
            $dff_level_male = array_diff($athlete_levels, $data);
        }

        if(count($dff_level_female) == 0 && count($dff_level_male) == 0)
        {
            echo 'all done';
            // everything is ok and levels are found in our db. No need to do anything
        }
        else
        {
            $level_need['male'] = [];
            $level_need['female'] = [];

            foreach ($dff_level_female as $key => $value) {
                $level_need['female'][] = $value;
            }
            foreach ($dff_level_male as $key => $value) {
                $level_need['male'][] = $value;
            }
            Mail::to(env('MAIL_ADMIN_ADDRESS'))->cc("zawad.sharif93@gmail.com")
                ->send(new USAGLevelIssue($level_need, $data_to_send));
        }
    }
    public function checkForExistingLevels($payload, $data_to_send) // $payload, $data_to_send
    {
        if(typeof($payload) == 'object')
        {
            $payload = json_decode(json_encode($payload), true); // convert object to array
        }
        $athlete_levels = $payload['Reservation']['Details']['Gymnasts']['Add'];
        $levels['male'] = [];
        $levels['female'] = [];
        foreach ($athlete_levels as $key => $value) {
            if($value['Gender'] == 'female')
                $levels['female'][] = $value['Level'];
            else
                $levels['male'][] = $value['Level'];
        }
        $levels_male = array_unique($levels['male']);
        $levels_female = array_unique($levels['female']);
        if(count($levels_male) > 0)
        {
            $data = AthleteLevel::select('code')->whereIn('code', $levels_male)
            ->where('sanctioning_body_id', SanctioningBody::USAG)
            ->where('level_category_id',LevelCategory::GYMNASTICS_MEN)
            ->get()->pluck('code')->toArray();
            $dff_level_male = array_diff($levels_male, $data);
        }
        if(count($levels_female) > 0)
        {
            $data = AthleteLevel::select('code')->whereIn('code', $levels_female)
            ->where('sanctioning_body_id', SanctioningBody::USAG)
            ->where('level_category_id',LevelCategory::GYMNASTICS_WOMEN)
            ->get()->pluck('code')->toArray();
            $dff_level_female = array_diff($levels_female, $data);
        }
        if(count($dff_level_female) == 0 && count($dff_level_male) == 0)
        {
            // everything is ok and levels are found in our db. No need to do anything
        }
        else
        {
            $level_need['male'] = [];
            $level_need['female'] = [];

            foreach ($dff_level_female as $key => $value) {
                $level_need['female'][] = $value;
            }
            foreach ($dff_level_male as $key => $value) {
                $level_need['male'][] = $value;
            }
            Mail::to(env('MAIL_ADMIN_ADDRESS'))->cc("zawad.sharif93@gmail.com")->send(new USAGLevelIssue($level_need, $data_to_send));
        }
    }
}