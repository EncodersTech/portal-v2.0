<?php

namespace App\Repositories;

use App\Models\LevelMeet;
use App\Models\Meet;
use App\Models\MeetRegistration;
use App\Models\SanctioningBody;
use App\Models\State;
use App\Models\USAGSanction;
use Barryvdh\Snappy\Facades\SnappyPdf as PDF;
use Barryvdh\Snappy\PdfWrapper;
use Carbon\Carbon;
use Illuminate\Support\Str;
use App\Models\MeetTransaction;
/**
 * Class MeetRepository
 */
class MeetRepository
{
    public function getMeetData()
    {
        $data['sanction_body'] = SanctioningBody::SANCTION_BODY;
        $data['states'] = State::pluck('name', 'id');
        $data['status'] = Meet::REGISTRATION_STATUS;

        return $data;
    }

    public function getMeetDashboardData($meet)
    {
        $data = null;
        $data['total_earn'] = 0;
        $data['total_ath'] = 0;
        $data['total_coa'] = 0;
        $data['allgym_fees'] = 0;

        $registrations = $meet->registrations()->with(['athletes', 'coaches'])->where('status', MeetRegistration::STATUS_REGISTERED)->withCount(['athletes', 'coaches'])->get();
        $data['total_gym'] = MeetRegistration::where('meet_id', $meet->id)->count('gym_id');
        foreach ($registrations as $i => $registration) {
            $total_earn = 0;
            foreach($registration->transactions as $transaction) {
                if($transaction->status != MeetTransaction::STATUS_COMPLETED){
                    continue;
                }
                $breakdown = $transaction->breakdown;
                // dd($breakdown);
                $handling = $breakdown['host']['handling'] + $breakdown['gym']['handling'];
                $processor = $breakdown['host']['processor'] + $breakdown['gym']['processor'];
                $data['allgym_fees'] += $handling + $processor;
                $total_earn += $breakdown['host']['total'];
            }
            $data['total_ath'] += $registration->athletes_count;
            $data['total_coa'] += $registration->coaches_count;
            // $data['total_earn'] += $registration->transactions->sum('total');
            $data['total_earn'] += $total_earn;
        }

        $data['team_allow'] = LevelMeet::whereIn('meet_id', [$meet->id])->whereIn('allow_teams', [true])->count();

        return $data;
    }

    public function generateAdminMeetsReport(): PdfWrapper
    {
        $meetLists = Meet::with(['gym.user', 'levels', 'meetCategories', 'venue_state'])->get();

        $data = [
            'meetLists' => $meetLists,
        ];

        return PDF::loadView('admin.meets.PDF.meet_lists', $data);
        /** @var PdfWrapper $pdf */
    }

    public function getFormattedDate($start_date, $end_date)
    {
        $start = Carbon::parse($start_date)->toDateString();
        $end = Carbon::parse($end_date)->toDateString();
        $startDate = Carbon::createFromFormat('Y-m-d', $start);
        $endDate = Carbon::createFromFormat('Y-m-d', $end);

        $dateArray = [];

        while ($startDate <= $endDate) {
            $dateArray['dateArr'][] = $startDate->copy()->format('Y-m-d');
            $startDate->addDay();
        }

        return $dateArray;
    }

    public function getMeetLineChartData($meet)
    {
        $meetStartData = $meet->registration_start_date;
        $meetEndDate = ($meet->allow_late_registration) ? $meet->late_registration_end_date : $meet->registration_end_date;

        $dateArray = $this->getFormattedDate($meetStartData, $meetEndDate);

        $registrations = $meet->registrations()->with(['athletes', 'coaches'])->where('status', MeetRegistration::STATUS_REGISTERED)->get();

        $data = [];
        $athleteArr = [];
        $coachArr = [];
        $dateArr = [];
        $gymArr = [];

        foreach ($dateArray['dateArr'] as $i => $cDate) {
            $athleteCount = 0;
            $coachCount = 0;
            $gymCount = 0;
            if ($cDate <= now()->format('Y-m-d')) { //show only start date to today data, If the end date is higher than today's date, the continuum will continue to come.
                foreach ($registrations as $registration) {
                    foreach ($registration->athletes as $j => $athlete) {
                        $a_regi_date = trim(substr($athlete->created_at, 0, 10));
                        if ($cDate == $a_regi_date) {
                            $athleteCount++;
                        }
                    }

                    foreach ($registration->coaches as $j => $coach) {
                        $c_regi_date = trim(substr($coach->created_at, 0, 10));
                        if ($cDate == $c_regi_date) {
                            $coachCount++;
                        }
                    }

                    $regi_date = trim(substr($registration->created_at, 0, 10));
                    if ($cDate == $regi_date) {
                        $gymCount++;
                    }
                }
                $athleteArr[] = $athleteCount;
                $coachArr[] = $coachCount;
                $dateArr[] = $cDate;
                $gymArr[] = $gymCount;
            }

        }

        $data['athletes'] = $athleteArr;
        $data['coaches'] = $coachArr;
        $data['gyms'] = $gymArr;
        $data['dates'] = $dateArr;

        return $data;
    }

    public function getMeetBarChartData($meet)
    {
        $meetStartData = $meet->registration_start_date;
        $meetEndDate = ($meet->allow_late_registration) ? $meet->late_registration_end_date : $meet->registration_end_date;

        $dateArray = $this->getFormattedDate($meetStartData, $meetEndDate);

        $registrations = $meet->registrations()->with(['athletes', 'coaches'])->where('status', MeetRegistration::STATUS_REGISTERED)->get();

        $data = [];
        $dollarArr = [];
        $dateArr = [];

        foreach ($dateArray['dateArr'] as $i => $cDate) {
            $dollarCount = 0;
            if ($cDate <= now()->format('Y-m-d')) {
                foreach ($registrations as $registration) {
                    foreach ($registration->transactions as $j => $transaction) {
                        $a_regi_date = trim(substr($transaction->created_at, 0, 10));
                        if ($cDate == $a_regi_date) {
                            $dollarCount += $transaction->total;
                        }
                    }
                }
                $dollarArr[] = $dollarCount;
                $dateArr[] = $cDate;
            }
        }

        $data['earnedAmount'] = $dollarArr;
        $data['dates'] = $dateArr;

        return $data;
    }

    public function getMeetPieChartData($meet)
    {
//       $registrations = $meet->registrations()->with(['athletes' => function (HasMany $q) {
//           $q->with(['registration_level' => function(BelongsTo $q){
//               $q->with(['level' => function(BelongsTo $q){
//                   $q->withCount('sanctioning_body');
//               }]);
//           }]);
//       }])->get();

        $registrations = $meet->registrations()->with(['athletes'])->where('status', MeetRegistration::STATUS_REGISTERED)->get();

        $usagCount = 0;
        $usaigcCount = 0;
        $ngaCount = 0;
        $aauCount = 0;

        foreach ($registrations as $registration) {
            foreach ($registration->athletes as $athlete) {
                $bodyId = $athlete->registration_level->level->sanctioning_body_id;
                if ($bodyId == SanctioningBody::USAG) {
                    $usagCount++;
                } elseif ($bodyId == SanctioningBody::USAIGC) {
                    $usaigcCount++;
                } elseif ($bodyId == SanctioningBody::AAU) {
                    $aauCount++;
                } else {
                    $ngaCount++;
                }
            }
        }

        $data['labels'] = ['USAG', 'USAIGC', 'AAU', 'NGA'];
        $data['dataPoints'] = [$usagCount, $usaigcCount, $aauCount, $ngaCount];

        return $data;
    }

    public function getMeetUsagSanction($sanctions, $gym, $meet)
    {
        $pendingRe = [];
        $mergeRe = [];
        foreach ($sanctions as $sanction) {

            if ($sanction->id == $gym->id) {
                foreach ($sanction->usag_sanctions->sortByDesc('timestamp') as $key => $usag_sanction) {
                    if ($usag_sanction->meet_id == $meet->id) {
                        if ($usag_sanction->status == USAGSanction::SANCTION_STATUS_PENDING) {
                            $pendingRe[$usag_sanction->number] = $usag_sanction;
                        } else {
                            $mergeRe[] = $usag_sanction;
                        }
                    }
                }
            }
            break;
        }

        return array_merge($pendingRe, $mergeRe);
    }


    public function printCheckSendingDetails($meetId, $gymId): PdfWrapper
    {
        $meetRegistration = MeetRegistration::with(['meet', 'gym', 'transactions' => function ($q) {
            $q->orderByDesc('created_at')->first();
        }])->where('meet_id', $meetId)->where('gym_id', $gymId)->first();


        $data = [
            'meetRe' => $meetRegistration,
        ];

        return PDF::loadView('registration.PDF.check_sending_details', $data);
        /** @var PdfWrapper $pdf */
    }

    public function getSummaryData($meet)
    {
        $data = $this->getMeetDashboardData($meet);

        $athArr = [];
        $coachArr = [];
        $gymArr = [];

        $registrations = $meet->registrations()->with(['athletes', 'coaches', 'gym'])->get();

        $indexArray = [];
        $finalSortingArray = [];
        $genderArray = [];
        foreach ($registrations as $key => $registration) {

            //athlete summary
            foreach ($registration->athletes as $athlete) {
                $lName = Str::after($athlete->registration_level->level->name, 'Level');

                $gender = ($athlete->registration_level->level->level_category->female) ? 'Female' : 'Male';
                $levelName = $athlete->registration_level->level->sanctioning_body->initialism . '(Gymnastics) : ' . $athlete->registration_level->level->name . ' - ' . $gender;
                $indexArray[$levelName] = isset($indexArray[$levelName]) ? $indexArray[$levelName] + 1 : 1;
                $athArr[$levelName] = $indexArray[$levelName];
                $genderArray[$levelName] = $gender;

                $finalSortingArray[$levelName] = $lName;
            }

            //gym summary
            $gymArr[$key]['gym'] = $registration['gym'];

            //coach summary
            $gymArr[$key]['coach'] = [];
            foreach ($registration->coaches as $coach) {
                $gymName = $registration['gym']->name;
                $indexArray[$gymName] = isset($indexArray[$gymName]) ? $indexArray[$gymName] + 1 : 1;
                $coachArr[$key]['gym'][$registration['gym']->name] = $indexArray[$gymName];

                if (isset($gymArr[$key]['coach']) && (! in_array($coach->first_name.' '.$coach->last_name,
                        $gymArr[$key]['coach']))) {
                    $gymArr[$key]['coach'][] = $coach->first_name.' '.$coach->last_name;
                    $coachArr[$key]['coach'][] = $coach->first_name.' '.$coach->last_name;
                }
            }
        }

        $levels = [];
        $sortedArray = collect($genderArray)->sortBy(function ($key, $record) use (&$levels) {
            if (Str::contains($record, 'Level')) {
                $levels[$key]['levels'][] = $record;
            } else {
                $levels[$key]['other'][] = $record;
            }
            if ($key == 'Female') {
                return -1;
            }

            return 0;
        });

        //prepare female data
        $femaleData = [];
        $final1 = [];
        $array1 = [];
        if(isset($levels['Female'])) {
            if(isset($levels['Female']['levels'])) {
                foreach ($levels['Female']['levels'] as $level) {
                    $femaleData['Female']['levels'][$finalSortingArray[$level]] = $level;
                }
                ksort($femaleData['Female']['levels']);
                $array1 = $femaleData['Female']['levels'];
            }

            $finalArray1 = array_merge($array1, isset($levels['Female']['other']) ? $levels['Female']['other'] : []);
            $index = 1;
            foreach ($finalArray1 as $item) {
                $final1[$index] = $item;
                $index++;
            }
        }

        //prepare male data
        $maleData = [];
        $final2 = [];
        $array2 = [];
        if(isset($levels['Male'])){
            if(isset($levels['Male']['levels'])) {
                foreach ($levels['Male']['levels'] as $level) {
                    $maleData['Male']['levels'][$finalSortingArray[$level]] = $level;
                }
                ksort($maleData['Male']['levels']);
                $array2 = $maleData['Male']['levels'];
            }
            $finalArray2 = array_merge($array2, isset($levels['Male']['other']) ? $levels['Male']['other'] : []);
            $index = 1;
            foreach ($finalArray2 as $item) {
                $final2[$index] = $item;
                $index++;
            }
        }

        $finalArray = array_merge($final1, $final2);

        $result = [];
        foreach ($finalArray as $key => $value) {
            $result[$value] = $athArr[$value];
        }

        $data['athleteLevelArr'] = $result;
        $data['coachSummaryArr'] = $coachArr;
        $data['gymSummaryArr'] = $gymArr;

        return $data;
    }
}
