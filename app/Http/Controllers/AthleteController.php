<?php

namespace App\Http\Controllers;

use App\Models\Athlete;
use Illuminate\Http\Request;
use App\Helper;
use App\Models\ClothingSizeChart;
use App\Exceptions\CustomBaseException;
use Illuminate\Support\Facades\Session;
use App\Models\FailedAthleteImport;
use App\Models\Gym;
use App\Services\USAIGCService;
use App\Services\USAGService;
use App\Services\NGAService;
use Illuminate\Support\Facades\DB;

class AthleteController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request, string $gym)
    {
        $gym = $request->_managed_account->retrieveGym($gym);
        $athletes = $gym->athletes;

        return view('athlete.list', [
            'current_page' => 'gym-' . $gym->id,
            'gym' => $gym,
            'athletes' => $athletes
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request, string $gym)
    {
        $gym = $request->_managed_account->retrieveGym($gym);
        $tshirtChart = ClothingSizeChart::where('is_default', true)->where('is_leo', false)->first();
        $leoChart = ClothingSizeChart::where('is_default', true)->where('is_leo', true)->first();
        return view('athlete.create', [
            'current_page' => 'gym-' . $gym->id,
            'gym' => $gym,
            'tshirt_chart' => $tshirtChart,
            'leo_chart' => $leoChart,
            'bodies' => Helper::getStructuredLevelList()
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, string $gym)
    {
        $attr = $request->validate(Athlete::CREATE_RULES);
        $gym = $request->_managed_account->retrieveGym($gym); /** @var Gym $gym */

        DB::beginTransaction();
        try {
            if(isset($attr['nga_no']))
                $attr['nga_no'] = 'N'.preg_replace('/[^0-9]/', '', $attr['nga_no']);
            $athlete = $gym->createAthlete($attr);
            $errCount = 0;
            $errText = 'Athlete "' . $athlete->fullName() . '" successfully created, however ';
            if ($athlete->usag_no) {
                $usagService = resolve(USAGService::class); /** @var USAIGCService $usaigcService */
                $verificationResult = $usagService->verifyAthlete($athlete);                
                if ($verificationResult !== true) {
                    $athlete->usag_no = null;
                    $athlete->usag_level_id = null;
                    $athlete->usag_active = false;
                    $errCount += 1;
                    $errText .= 'USAG verification failed and USAG membership number has not been saved';
                }
            }
            if ($athlete->usaigc_no) {
                $usaigcService = resolve(USAIGCService::class); /** @var USAIGCService $usaigcService */
                $verificationResult = $usaigcService->verifyAthlete(($athlete));

                if ($verificationResult !== true) {
                    $athlete->usaigc_no = null;
                    $athlete->usaigc_level_id = null;
                    $athlete->usaigc_active = false;
                    $errCount += 1;
                    $errText .= 'USAIGC verification failed and USAIGC membership number has not been saved';
                }
            }
            if($athlete->nga_no)
            {
                $ngaService = resolve(NGAService::class); /** @var USAIGCService $usaigcService */
                $verificationResultNga = $ngaService->verifyAthlete(($athlete));
                if($verificationResultNga !== true)
                {
                    $athlete->nga_no = null;
                    $athlete->nga_level_id = null;
                    $athlete->nga_active = false;
                    $errCount += 1;
                    $extra = '';
                    if($errCount == 2)
                        $extra = " , and ";
                    $errText .= $extra.'NGA verification failed and NGA membership number has not been saved';
                }
            }
            if($errCount != 0)
            {
                
                $athlete->save();
                DB::commit();
                return redirect(route('gyms.athletes.edit', [
                    'gym' => $gym,
                    'athlete' => $athlete
                ]))->with('warning', $errText);
            
            }
            DB::commit();
        } catch(CustomBaseException $e) {
            DB::rollBack();
            return back()->with('error', $e->getMessage());
        } catch(\Throwable $e) {
            DB::rollBack();
            return back()->with('error', 'An error occured.');
        }

        return redirect(route('gyms.athletes.index', ['gym' => $gym]))
                ->with('success', 'Athlete "' . $athlete->fullName() . '" successfully created.');
    }

    public function show(Request $request, string $gym, string $athlete)
    {
        $gym = $request->_managed_account->retrieveGym($gym);

        $athlete = $gym->athletes()->find($athlete);
        if ($athlete == null)
            throw new CustomBaseException('No such athlete.', -1);

        return view('athlete.view', [
            'current_page' => 'gym-' . $gym->id,
            'gym' => $gym,
            'athlete' => $athlete,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Athlete  $athlete
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request, string $gym, string $athlete)
    {
        $gym = $request->_managed_account->retrieveGym($gym);

        $athlete = $gym->athletes()->find($athlete);
        if ($athlete == null)
            throw new CustomBaseException('No such athlete.', -1);

        $tshirtChart = ClothingSizeChart::where('is_default', true)->where('is_leo', false)->first();
        $leoChart = ClothingSizeChart::where('is_default', true)->where('is_leo', true)->first();
        return view('athlete.edit', [
            'current_page' => 'gym-' . $gym->id,
            'gym' => $gym,
            'athlete' => $athlete,
            'tshirt_chart' => $tshirtChart,
            'leo_chart' => $leoChart,
            'bodies' => Helper::getStructuredLevelList()
        ]);
    }

    /**
     * Update the specified resource in storage.
     *selftp\Request  $request
     * @param  \App\Models\Athlete  $athlete
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, string $gym, string $athlete)
    {
        $checkNGA = !isset($request['isUpdate']) ; 
        $attr = $request->validate(Athlete::UPDATE_RULES);
        $gym = $request->_managed_account->retrieveGym($gym);
        $athlete = $gym->athletes()->find($athlete);
        if ($athlete == null)
            throw new CustomBaseException('No such athlete.', -1);
            
        DB::beginTransaction();
        try {

            
            if(isset($attr['nga_no']))
                $attr['nga_no'] = 'N'.preg_replace('/[^0-9]/', '', $attr['nga_no']);

                // throw new CustomBaseException($athlete,1);
            // unset($attr['isUpdate']);
            $athlete->updateProfile($attr);
            if ($athlete->usag_no) {
                $usagService = resolve(USAGService::class); /** @var USAGService $usaigcService */
                $verificationResult = $usagService->verifyAthlete($athlete);                
                if ($verificationResult !== true) {
                    $errors = implode(', ', $verificationResult);
                    throw new CustomBaseException("Your changes have not been saved because they did not match the data provided by USAG servers for this membership: ".$errors,
                        1);
                }
            }
            if ($athlete->usaigc_no) {
                $usaigcService = resolve(USAIGCService::class); /** @var USAIGCService $usaigcService */
                $verificationResult = $usaigcService->verifyAthlete($athlete);                
                if ($verificationResult !== true) {
                    $errors = implode(', ', $verificationResult);
                    throw new CustomBaseException("Your changes have not been saved because they did not match the data provided by USAIGC servers for this membership: ".$errors,
                        1);
                }
            }
            if($athlete->nga_no && $checkNGA)
            {   
                $ngaService = resolve(NGAService::class); /** @var USAIGCService $usaigcService */
                $verificationResultNga = $ngaService->verifyAthlete(($athlete));
                if ($verificationResultNga !== true) {

                    if(isset($verificationResultNga["general_issues"]))
                    {
                        $erdata = $verificationResultNga["general_issues"];
                        if(isset($verificationResultNga["nga"]) && $verificationResultNga["nga"] != null)
                            Session::flash('ngaData', $verificationResultNga);
                    }
                    else
                        $erdata = $verificationResultNga;
                        
                    $errors = implode(', ', $erdata );
                    throw new CustomBaseException("Your changes have not been saved because they did not match the data provided by NGA servers for this membership: ".$errors,
                        1);
                }
            }
            DB::commit();
        } catch(CustomBaseException $e) {
            DB::rollBack();
            return back()->with('error', $e->getMessage(), 'data');
        } catch(\Throwable $e) {
            DB::rollBack();
            return back()->with('error', 'An error occured.' . $e->getMessage());
        }

        return back()->with('success', 'Athlete info updated.');
    }

    /**
     * Remove the specified resources from storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function batchRemove(Request $request, string $gym)
    {
        $selected = $request->validate([
            'selected_athletes_list' => ['required', 'string']
        ])['selected_athletes_list'];
        $gym = $request->_managed_account->retrieveGym($gym);
        $gym->removeAthleteBatch($selected);
        return back()->with('success', 'The selected athletes were removed');
    }

    public function batchRemoveFailed(Request $request, string $gym)
    {
        $selected = $request->validate([
            'selected_failed_athletes_list' => ['required', 'string']
        ])['selected_failed_athletes_list'];
        $gym = $request->_managed_account->retrieveGym($gym);
        $gym->removeFailedAthleteBatch($selected);
        return back()->with('success', 'The selected entries were removed');
    }

    public function import(Request $request, string $gym)
    {
        $attr = $request->validate(Athlete::getImportRules());
        $gym = $request->_managed_account->retrieveGym($gym);
        $result = $gym->importAthletes(
            $attr['method'], $attr['duplicates'], $attr['body'],
            isset($attr['csv_file']) ? $attr['csv_file'] : null,
            isset($attr['delimiter']) ? $attr['delimiter'] : ','
        );

        if (($result['imported'] > 0) || ($result['overwritten'] > 0) || ($result['ignored'] > 0)) {
            $request->session()->flash('success',
                $result['imported'] . ' new, ' .
                $result['overwritten'] . ' overwritten, ' .
                $result['ignored'] . ' ignored.'
            );
        }

        if ($result['failed'] > 0)
            $request->session()->flash('error', $result['failed'] . ' failed to import. Please check the "Failed Imports" tab.');

        return back()->with('info', 'Athlete import finished.');
    }

    public function storeFromFailedImport(Request $request, string $gym, string $failed)
    {
        $attr = $request->validate(Athlete::CREATE_RULES);
        $gym = $request->_managed_account->retrieveGym($gym);
        $failed = $gym->failed_athlete_imports()->find($failed);
        if ($failed == null)
            throw new CustomBaseException('No such import entry.', -1);

        $athlete = $gym->createAthleteFromFailedImport($attr, $failed);

        return redirect(route('gyms.athletes.index', ['gym' => $gym]))
            ->with('success', 'Athlete "' . $athlete->fullName() . '" successfully created.');
    }

    public function failedImportEdit(Request $request, string $gym, string $failed)
    {
        $gym = $request->_managed_account->retrieveGym($gym);
        $athlete = $gym->failed_athlete_imports()->find($failed);
        if ($athlete == null)
            throw new CustomBaseException('No such entry.', -1);

        $duplicate = null;
        if ($athlete->error_code == FailedAthleteImport::ERROR_CODE_DUPLICATE) {
            if ($athlete->usag_no != null)
                $duplicate = $gym->athletes()->where('usag_no', $athlete->usag_no)->first();

            if ($athlete->usaigc_no != null)
                $duplicate = $gym->athletes()->where('usaigc_no', $athlete->usaigc_no)->first();

            if ($athlete->aau_no != null)
                $duplicate = $gym->athletes()->where('aau_no', $athlete->aau_no)->first();
        }

        $tshirtChart = ClothingSizeChart::where('is_default', true)->where('is_leo', false)->first();
        $leoChart = ClothingSizeChart::where('is_default', true)->where('is_leo', true)->first();
        return view('athlete.faulty-edit', [
            'current_page' => 'gym-' . $gym->id,
            'gym' => $gym,
            'athlete' => $athlete,
            'duplicate' => $duplicate,
            'tshirt_chart' => $tshirtChart,
            'leo_chart' => $leoChart,
            'bodies' => Helper::getStructuredLevelList()
        ]);
    }

    public function failedImportUpdate(Request $request, string $gym, string $failed, string $duplicate)
    {
        $attr = $request->validate(Athlete::UPDATE_RULES);
        $gym = $request->_managed_account->retrieveGym($gym);
        $athlete = $gym->failed_athlete_imports()->find($failed);
        if ($athlete == null)
            throw new CustomBaseException('No such entry.', -1);

        $duplicate = $gym->athletes()->find($duplicate);
        if ($duplicate == null)
            throw new CustomBaseException('No such athlete (duplicate).', -1);

        $duplicate->overwriteProfile($attr, $athlete);

        return redirect(route('gyms.athletes.index', ['gym' => $gym]))->with('success', 'Athlete info overwritten.');
    }
}
