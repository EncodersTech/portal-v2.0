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

class NGAService {
    public const API_KEY = 'ALLGYM_4LL2N2UUXC';
    public const API_BASE_PROD = 'admin.nationalgym.org/';
    // http://admin.nationalgym.org/Api.Club.Athletes?ApiKey=ALLGYM_4LL2N2UUXC&NGAClubNum=***CLUBNUMBER***&Discipline=AW
    public const API_DATE_FORMAT = 'n/j/Y';

    private $apiBase = 'http://' . self::API_BASE_PROD;
    private $guzzle = null; /** @var Guzzle $guzzle */

    public function __construct(bool $useDev = false) {
        try {
            // trackthis => 
            // $this->apiBase = 'https://' . ($useDev ? self::API_BASE_DEV : self::API_BASE_PROD) . self::API_PATH;
            $this->apiBase = 'http://' . self::API_BASE_PROD; // this line was written to get the usaigc.com
            $this->guzzle = new Guzzle([
                'base_uri' => $this->apiBase,
                'timeout'  => config('app.ext_api_timeout', 15),
            ]);
        } catch (\Throwable $e) {
            logger()->error(self::class . '::__construct() : ' . $e->getMessage());
            throw new CustomBaseException('Something went wrong with the NGA server.',-1);
        }
    }

    public function getClub(string $clubId)
    {
        $clubId = 'N'.preg_replace('/[^0-9]/', '', $clubId);
        // $clubId = $clubId[0] == 'N' ? $clubId : 'N'.$clubId;
        // http://admin.nationalgym.org/Api.Club.Athletes?ApiKey=ALLGYM_4LL2N2UUXC&NGAClubNum=N101020&Discipline=AW
        $path = $this->apiBase. 'Api.Club.Athletes?ApiKey=' . self::API_KEY . '&NGAClubNum='. $clubId .'&Discipline=AW';
        $path_m = $this->apiBase. 'Api.Club.Athletes?ApiKey=' . self::API_KEY . '&NGAClubNum='. $clubId .'&Discipline=AM';
        $result = [];
        try {
            $responseJSON = (string) $this->guzzle->request('GET', $path)->getBody();
            $responseJSON_m = (string) $this->guzzle->request('GET', $path_m)->getBody();

            $response = json_decode($responseJSON, true);
            $response_m = json_decode($responseJSON_m, true);

            if (($response === null && $response_m === null) || (isset($response['Result']) && $response['Result'] == "Invalid Request" && isset($response_m['Result']) && $response_m['Result'] == "Invalid Request"))
                throw new \Exception("Wrong response\n" . $response . " \n " . $response_m);
            if (isset($response['error']) && isset($response_m['error']))
                throw new \Exception("Error\n" . $response['message'] . " \n " . $response_m['message']);
            if(isset($response['results']))
                $result = array_merge($result,$response['results'][0]['result']['row']);
            if(isset($response_m['results']))
            $result = array_merge($result,$response_m['results'][0]['result']['row']);

            return $result;
        } catch (\Throwable $e) {
            logger()->error(self::class . '::getClub() : ' . $e->getMessage());
            throw new CustomBaseException('Something went wrong with the NGA server import. Please contact Admin.',-1);
            // throw new CustomBaseException( $result,-1);
        }
    }
    public function getCoach(string $clubId)
    {
        $clubId = 'N'.preg_replace('/[^0-9]/', '', $clubId);
        // $clubId = $clubId[0] == 'N' ? $clubId : 'N'.$clubId;

        // http://admin.nationalgym.org/Api.Club.Coaches?ApiKey=ALLGYM_4LL2N2UUXC&NGAClubNum=N101058
        $path = $this->apiBase. 'Api.Club.Coaches?ApiKey=' . self::API_KEY . '&NGAClubNum='. $clubId;
        $result = [];
        try {
            $responseJSON = (string) $this->guzzle->request('GET', $path)->getBody();

            $response = json_decode($responseJSON, true);

            if ($response === null  || (isset($response['Result']) && $response['Result'] == "Invalid Request"))
                throw new \Exception("Wrong response\n" . $response);
            if (isset($response['error']))
                throw new \Exception("Error\n" . $response['message']);
            if(isset($response['results']))
                $result = $response['results'][0]['result']['row'];

            return $result;
        } catch (\Throwable $e) {
            logger()->error(self::class . '::getCoach() : ' . $e->getMessage());
            throw new CustomBaseException('Something went wrong with the NGA server import. Please contact Admin.',-1);
            // throw new CustomBaseException( $result,-1);
        }
    }

    public function verifyAthlete($athlete, bool $throw = false)
    {
        return true;
        // http://admin.nationalgym.org/Api.Athlete?ApiKey=ALLGYM_4LL2N2UUXC&NGANumber=N112303
        $athleteNga = 'N'.preg_replace('/[^0-9]/', '', $athlete->nga_no);

        $path = $this->apiBase. 'Api.Athlete?ApiKey=' . self::API_KEY . '&NGANumber='. $athleteNga;
        try {
            $issues = [];
            $isValid = false;
            $response = null;
            $ngaRawAthlete = null;

            if (!(
                ($athlete instanceof Athlete) ||
                ($athlete instanceof RegistrationAthlete) ||
                ($athlete instanceof RegistrationSpecialist)
            ))
                throw new CustomBaseException("Invalid athlete.", -1);

            $responseJSON = (string) $this->guzzle->request('GET', $path)->getBody();
            $response = json_decode($responseJSON, true);
            
            if ($response === null) {
                $issues["general_issues"][] = "NGA servers returned an invalid response" . $response;
            } else if (count($response['results'][0]['result']['row']) != 1) {
                $issues["general_issues"][] = "Invalid NGA number '". $athlete->nga_no . "'";
            } else {
                $response = $response['results'][0]['result']['row'][0];
                $ngaRawAthlete = $response;
                if ($athlete->last_name != trim($response['LastName']))
                    $issues["general_issues"][] = 'Last name mismatch, local is `' . $athlete->last_name . '`, remote is `' . $response['LastName'] . '`';

                $dob = \DateTime::createFromFormat(self::API_DATE_FORMAT, $response['DOB']);
                if (($dob === null) || ($dob === false)) {
                    $issues["general_issues"][] = 'Invalid date value `' . $response['DOB'] . '`';
                } else {
                    $dob = $dob->setTime(0, 0);
                    if ($athlete->dob != $dob)
                        $issues["general_issues"][] = 'DoB mismatch, local is `' . $athlete->dob->format(Helper::AMERICAN_SHORT_DATE) . '`, remote is `' . $dob->format(Helper::AMERICAN_SHORT_DATE) . '`';
                }
                if (!isset($issues["general_issues"]) || count($issues["general_issues"]) < 1)
                    $isValid = true;
                else
                {
                    $issues["allgym"] = $athlete;
                    $issues["nga"] = $ngaRawAthlete;
                }
            }

            return $isValid ? true : $issues;
        } catch (\Throwable $e) {
            logger()->error(self::class . '::verifyAthlete() : ' . $e->getMessage());
            if ($throw)
                throw new CustomBaseException('Something went wrong with the NGA verification. '. $e->getMessage(),-1);
            return ['Something went wrong with the NGA verification ' . $e->getMessage()];
        }
    }
    public function verifyCoach($coach , bool $throw = false)
    {
        return true;
        // $coach = (object) $coachh;
        // http://admin.nationalgym.org/Api.Coach?ApiKey=ALLGYM_4LL2N2UUXC&NGANumber=N104012
        $coachNga = 'N'.preg_replace('/[^0-9]/', '', $coach->nga_no);

        $path = $this->apiBase. 'Api.Coach?ApiKey=' . self::API_KEY . '&NGANumber='. $coachNga;
        try {
            $issues = [];
            $isValid = false;
            $response = null;
            $ngaRawCoach = null;
            $responseJSON = (string) $this->guzzle->request('GET', $path)->getBody();
            $response = json_decode($responseJSON, true);
            if ($response === null) {
                $issues["general_issues"][] = "NGA servers returned an invalid response" . $response;
            } else if (count($response['results'][0]['result']['row']) != 1) {
                $issues["general_issues"][] = "Invalid NGA number '". $coach->nga_no . "'";
            } else {
                $response = $response['results'][0]['result']['row'][0];
                $ngaRawCoach = $response;
                if ($coach->last_name != trim($response['LastName']))
                    $issues["general_issues"][] = 'Last name mismatch, local is `' . $coach->last_name . '`, remote is `' . $response['LastName'] . '`';

                $dob = \DateTime::createFromFormat(self::API_DATE_FORMAT, $response['DOB']);
                $coachDob = \DateTime::createFromFormat('Y-m-d h:i:s', $coach->dob);

                if (($dob === null) || ($dob === false)) {
                    $issues["general_issues"][] = 'Invalid date value `' . $response['DOB'] . '`';
                } else {
                    $dob = $dob->setTime(0, 0);
                    $coachDob = $coachDob->setTime(0, 0);

                    if ($coachDob != $dob)
                        $issues["general_issues"][] = 'DoB mismatch, local is `' . $coachDob->format(Helper::AMERICAN_SHORT_DATE) . '`, remote is `' . $dob->format(Helper::AMERICAN_SHORT_DATE) . '`';
                }
                if (!isset($issues["general_issues"]) || count($issues["general_issues"]) < 1)
                    $isValid = true;
                else
                {
                    $issues["allgym"] = $coach;
                    $issues["nga"] = $ngaRawCoach;
                }
            }
            
            return $isValid ? true : $issues;
        } catch (\Throwable $e) {
            logger()->error(self::class . '::verifyCoach() : ' . $e->getMessage());
            if ($throw)
                throw new CustomBaseException('Something went wrong with the NGA verification.',-1);
            return ['Something went wrong with the NGA verification'];
        }
    }
}