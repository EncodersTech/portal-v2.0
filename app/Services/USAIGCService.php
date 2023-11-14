<?php

namespace App\Services;

use App\Exceptions\CustomBaseException;
use App\Helper;
use App\Models\Athlete;
use App\Models\MeetRegistration;
use App\Models\RegistrationAthlete;
use App\Models\RegistrationAthleteVerification;
use App\Models\RegistrationSpecialist;
use App\Models\RegistrationSpecialistEvent;
use GuzzleHttp\Client as Guzzle;

class USAIGCService {
    public const API_BASE_DEV = 'igcdev.com';
    public const API_BASE_PROD = 'usaigc.com';
    public const API_PATH = 'app/API/V1/';

    public const API_DATE_FORMAT = 'n/j/Y';

    private $apiBase = 'https://' . self::API_BASE_DEV . self::API_PATH;
    private $guzzle = null; /** @var Guzzle $guzzle */

    public function __construct(bool $useDev = false) {
        try {
            // trackthis => 
            // $this->apiBase = 'https://' . ($useDev ? self::API_BASE_DEV : self::API_BASE_PROD) . self::API_PATH;
            $this->apiBase = 'https://' . self::API_BASE_PROD . self::API_PATH; // this line was written to get the usaigc.com
            $this->guzzle = new Guzzle([
                'base_uri' => $this->apiBase,
                'timeout'  => config('app.ext_api_timeout', 15),
            ]);
        } catch (\Throwable $e) {
            logger()->error(self::class . '::__construct() : ' . $e->getMessage());
            throw new CustomBaseException('Something went wrong with the USAIGC server.',-1);
        }
    }

    public function getClub(string $clubId)
    {
        $clubId = str_replace("IGC","",$clubId);
        $path = 'getClub?ID=IGC' . $clubId;
        try {
            $responseJSON = (string) $this->guzzle->request('GET', $path)->getBody();

            $response = json_decode($responseJSON, true);
            if ($response === null)
                throw new \Exception("Wrong response\n" . $response);

            return $response;
        } catch (\Throwable $e) {
            logger()->error(self::class . '::getClub() : ' . $e->getMessage());
            throw new CustomBaseException('Something went wrong with the USAIGC server import.',-1);
        }
    }
    public function getCoach(string $clubId)
    {
        $clubId = str_replace("IGC","",$clubId);
        $path = 'getClubCoach?ID=IGC' . $clubId;
        try {
            $responseJSON = (string) $this->guzzle->request('GET', $path)->getBody();

            $response = json_decode($responseJSON, true);
            if ($response === null)
                throw new \Exception("Wrong response\n" . $response);

            return $response;
        } catch (\Throwable $e) {
            logger()->error(self::class . '::getClub() : ' . $e->getMessage());
            throw new CustomBaseException('Something went wrong with the USAIGC server import.',-1);
        }
    }
    public function setSanction($igcno, $url)
    {
        $path = 'setSanctionUrl/'.$igcno.'?data='.$url;
        try {
            $responseJSON = (string) $this->guzzle->request('GET', $path)->getBody();

            $response = json_decode($responseJSON, true);
            if ($response === null)
                throw new \Exception("Wrong response\n" . $response);

            return $response;
        } catch (\Throwable $e) {
            logger()->error(self::class . '::setSanction() : ' . $e->getMessage());
            throw new CustomBaseException('Something went wrong with the USAIGC server import.',-1);
        }
    }
    public function verifyAthlete($athlete, bool $throw = false)
    {
        $path = 'getAthleteStatus?ID=IGC';
        try {
            $issues = [];
            $isValid = false;
            $response = null;

            if (!(
                ($athlete instanceof Athlete) ||
                ($athlete instanceof RegistrationAthlete) ||
                ($athlete instanceof RegistrationSpecialist)
            ))
                throw new CustomBaseException("Invalid athlete.", -1);

            $memberpath = $path . $athlete->usaigc_no;

            $responseJSON = (string) $this->guzzle->request('GET', $memberpath)->getBody();
            $response = json_decode($responseJSON, true);

            if ($response === null) {
                $issues[] = "USAIGC servers returned an invalid response" . $response;
            } else if (count($response) != 1) {
                $issues[] = "Invalid USAIGC number '". $athlete->usaigc_no . "'";
            } else {
                $response = $response[0];
                //when athlete update, that time compare only Last name and DOB so other field not compare - 24-4-21
//                if ($athlete->first_name != trim($response['FIRSTNAME']))
//                    $issues[] = 'First name mismatch, local is `' . $athlete->first_name . '`, remote is `' . $response['FIRSTNAME'] . '`';

                if ($athlete->last_name != trim($response['LASTNAME']))
                    $issues[] = 'Last name mismatch, local is `' . $athlete->last_name . '`, remote is `' . $response['LASTNAME'] . '`';

//                if ($athlete->last_name != $response['LASTNAME'])
//                    $issues[] = 'Last name mismatch, local is `' . $athlete->last_name . '`, remote is `' . $response['LASTNAME'] . '`';

//                $gender = strtolower($response['GENDER']);
//                if ($athlete->gender != $gender)
//                    $issues[] = 'Last name mismatch, local is `' . $athlete->last_name . '`, remote is `' . $gender . '`';

                $dob = \DateTime::createFromFormat(self::API_DATE_FORMAT, $response['DOB']);
                if (($dob === null) || ($dob === false)) {
                    $issues[] = 'Invalid date value `' . $response['DOB'] . '`';
                } else {
                    $dob = $dob->setTime(0, 0);
                    if ($athlete->dob != $dob)
                        $issues[] = 'DoB mismatch, local is `' . $athlete->dob->format(Helper::AMERICAN_SHORT_DATE) . '`, remote is `' . $dob->format(Helper::AMERICAN_SHORT_DATE) . '`';
                }

//                $ApiActive = ($response['STATUS'] == 'active');
//                if ($athlete->usaigc_active !== $ApiActive)
//                    $issues[] = 'USAIGC membership active mistmatch, local is `'
//                                . ($athlete->usaigc_active ? '' : 'in') . 'active` remote is `'
//                                .  ($ApiActive ? '' : 'in') . 'active`.';

                if (count($issues) < 1)
                    $isValid = true;
            }

            return $isValid ? true : $issues;
        } catch (\Throwable $e) {
            logger()->error(self::class . '::verifyAthlete() : ' . $e->getMessage());
            if ($throw)
                throw new CustomBaseException('Something went wrong with the USAIGC verification.',-1);
            return ['Something went wrong with the USAIGC verification'];
        }
    }

    public function verifyAthletes(RegistrationAthleteVerification $verification) : array
    {
        $path = 'getAthleteStatus?ID=IGC';
        try {
            $registration = $verification->meet_registration; /** @var MeetRegistration $registration */
            $results = [];

            if (count($verification->athletes) < 1)
                throw new CustomBaseException("There are no entrants to be verified.", -1);

            foreach ($verification->athletes as $number) {
                $issues = [];
                $isValid = false;
                $response = null;

                $athlete = $registration->athletes()
                                        ->where('status', '!=', RegistrationAthlete::STATUS_SCRATCHED)
                                        ->where('usaigc_no', $number)->first(); /** @var RegistrationAthlete $athlete */

                if ($athlete == null) {
                    $athlete = $registration->specialists()
                                    ->where('usaigc_no', $number)->first(); /** @var RegistrationSpecialist $athlete */
                }

                if ($athlete != null) {
                    if (($athlete instanceof RegistrationSpecialist) && ($athlete->status() == RegistrationSpecialistEvent::STATUS_SPECIALIST_SCRATCHED))
                        continue;

                    $results[$number] = [
                        'name' => $athlete->fullName(),
                        'gender' => $athlete->gender,
                        'dob' => $athlete->dob->format(Helper::AMERICAN_SHORT_DATE),
                        'number' => $athlete->usaigc_no,
                        'us_citizen' => $athlete->is_us_citizen,
                    ];

                    $memberpath = $path . $number;
                    $responseJSON = (string) $this->guzzle->request('GET', $memberpath)->getBody();
                    $response = json_decode($responseJSON, true);

                    if ($response === null) {
                        $issues[] = "USAIGC servers returned an invalid response" . $response;
                    } else if (count($response) != 1) {
                        $issues[] = "Invalid USAIGC number '". $number . "'";
                    } else {
                        $response = $response[0];
                        if ($athlete->first_name != $response['FIRSTNAME'])
                            $issues[] = 'First name mismatch, local is `' . $athlete->first_name . '`, remote is `' . $response['FIRSTNAME'] . '`';

                        if ($athlete->last_name != $response['LASTNAME'])
                            $issues[] = 'Last name mismatch, local is `' . $athlete->last_name . '`, remote is `' . $response['LASTNAME'] . '`';

                        $gender = strtolower($response['GENDER']);
                        if ($athlete->gender != $gender)
                            $issues[] = 'Last name mismatch, local is `' . $athlete->last_name . '`, remote is `' . $gender . '`';

                        $dob = \DateTime::createFromFormat(self::API_DATE_FORMAT, $response['DOB']);
                        if (($dob === null) || ($dob === false)) {
                            $issues[] = 'Invalid date value `' . $response['DOB'] . '`';
                        } else {
                            $dob = $dob->setTime(0, 0);
                            if ($athlete->dob != $dob)
                                $issues[] = 'DoB mismatch, local is `' . $athlete->dob->format(Helper::AMERICAN_SHORT_DATE) . '`, remote is `' . $dob->format(Helper::AMERICAN_SHORT_DATE) . '`';
                        }

                        if ($response['STATUS'] != 'active')
                            $issues[] = 'This athlete\'s USAIGC membership is not active';


                        if (count($issues) < 1)
                            $isValid = true;
                    }
                } else {
                    $results[$number] = [];
                    $issues[] = 'There is no athlete with USAIGC number ' . $number . ' in local database.';
                }

                $results[$number] += [
                    'valid' => $isValid,
                    'issues' => $issues,
                ];
            }

            return $results;
        } catch (\Throwable $e) {
            logger()->error(self::class . '::verifyAthletes() : ' . $e->getMessage());
            throw new CustomBaseException('Something went wrong with the USAIGC verification.',-1);
        }
    }
}