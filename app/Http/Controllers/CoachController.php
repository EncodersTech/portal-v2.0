<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\ClothingSizeChart;
use App\Helper;
use App\Models\Coach;
use App\Models\FailedCoachImport;

use Illuminate\Support\Facades\Session;
use App\Exceptions\CustomBaseException;
use App\Services\NGAService;
use App\Services\USAGService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
class CoachController extends Controller
{
    public function index(Request $request, string $gym)
    {
        $gym = $request->_managed_account->retrieveGym($gym);
        $coaches = $gym->coaches;

        return view('coach.list', [
            'current_page' => 'gym-' . $gym->id,
            'gym' => $gym,
            'coaches' => $coaches
        ]);
    }

    public function create(Request $request, string $gym)
    {
        $gym = $request->_managed_account->retrieveGym($gym);
        $tshirtChart = ClothingSizeChart::where('is_default', true)->where('is_leo', false)->first();

        return view('coach.create', [
            'current_page' => 'gym-' . $gym->id,
            'gym' => $gym,
            'tshirt_chart' => $tshirtChart,
            'bodies' => Helper::getStructuredLevelList()
        ]);
    }

    public function store(Request $request, string $gym) {
        $attr = $request->validate(Coach::CREATE_RULES);
        $gym = $request->_managed_account->retrieveGym($gym); /** @var \App\Models\Gym $gym */
        DB::beginTransaction();
        try {
            $coach = $gym->createCoach($attr);
            $errText = '';
            $status = 'success';
            if(isset($attr['usag_no']))
            {
                $usagService = resolve(USAGService::class); /** @var USAGCService $usaigcService */
                $verificationResultUsag = $usagService->verifyCoach(($coach));
                $errText = '';
                $status = 'success';
        
                if($verificationResultUsag !== true)
                {
                    if(isset($verificationResultUsag["general_issues"]))
                    {
                        $coach->usag_no = null;
                        $status = 'warning';
                        $errText = 'But USAG verification failed and USAG membership number has not been saved';
                    }
                }
            }
            if(isset($attr['nga_no']))
            {
                $attr['nga_no'] = 'N'.preg_replace('/[^0-9]/', '', $attr['nga_no']);
                $ngaService = resolve(NGAService::class); /** @var USAIGCService $usaigcService */
                $verificationResultNga = $ngaService->verifyCoach(($coach));      
                if($verificationResultNga !== true)
                {
                    // unset($attr['nga_no']);
                    $coach->nga_no = null;
                    $status = 'warning';
                    $errText = 'But NGA verification failed and NGA membership number has not been saved';
                }
            }
            $coach->save();
            DB::commit();
            return redirect(route('gyms.coaches.index', ['gym' => $gym]))
            ->with($status, 'Coach "' . $coach->fullName() . '" successfully created. ' . $errText);
        }
        catch(CustomBaseException $e) {
            DB::rollBack();
            return back()->with('error', $e->getMessage());
        } catch(\Throwable $e) {
            DB::rollBack();
            Log::channel('slack-warning')->warning($e);
            return back()->with('error', 'An error occured.');
        }
    }

    public function show(Request $request, string $gym, string $coach)
    {
        $gym = $request->_managed_account->retrieveGym($gym);
        
        $coach = $gym->coaches()->find($coach);
        if ($coach == null)
            throw new CustomBaseException('No such coache.', -1);

        return view('coach.view', [
            'current_page' => 'gym-' . $gym->id,
            'gym' => $gym,
            'coach' => $coach,
        ]);
    }

    public function edit(Request $request, string $gym, string $coach)
    {
        $gym = $request->_managed_account->retrieveGym($gym);
        
        $coach = $gym->coaches()->find($coach);
        if ($coach == null)
            throw new CustomBaseException('No such coache.', -1);

        $tshirtChart = ClothingSizeChart::where('is_default', true)->where('is_leo', false)->first();
        return view('coach.edit', [
            'current_page' => 'gym-' . $gym->id,
            'gym' => $gym,
            'coach' => $coach,
            'tshirt_chart' => $tshirtChart,
            'bodies' => Helper::getStructuredLevelList()
        ]);
    }
    
    public function update(Request $request, string $gym, string $coach)
    {
        $checkNGA = !isset($request['isUpdate']) ; 
        $attr = $request->validate(Coach::UPDATE_RULES);
        $gym = $request->_managed_account->retrieveGym($gym); /** @var \App\Models\Gym $gym */
        $coach = $gym->coaches()->find($coach); /** @var \App\Models\Coach $coach */
        if ($coach == null)
            throw new CustomBaseException('No such coach.', -1);
        DB::beginTransaction();
        try{

            $coach->updateProfile($attr);
            if(isset($attr['usag_no']))
            {
                $usagService = resolve(USAGService::class); /** @var USAGCService $usaigcService */
                $verificationResultUsag = $usagService->verifyCoach(($coach));
                $errText = '';
                $status = 'success';
        
                if($verificationResultUsag !== true)
                {
                    if(isset($verificationResultUsag["general_issues"]))
                    {
                        $erdata = $verificationResultUsag["general_issues"];
                        $errors = implode(', ', $erdata );
                        throw new CustomBaseException('USAG Verification Failed, Coach is not updated.' . $errors, -1);
                    }
                }
            }
            if(isset($attr['nga_no'])  && $checkNGA)
            {
                $attr['nga_no'] = 'N'.preg_replace('/[^0-9]/', '', $attr['nga_no']);
                $ngaService = resolve(NGAService::class); /** @var USAIGCService $usaigcService */
                $verificationResultNga = $ngaService->verifyCoach(($coach));
                $errText = '';
                $status = 'success';
        
                if($verificationResultNga !== true)
                {
                    if(isset($verificationResultNga["general_issues"]))
                    {
                        $erdata = $verificationResultNga["general_issues"];
                        if(isset($verificationResultNga["nga"]) && $verificationResultNga["nga"] != null)
                            Session::flash('ngaCoachData', $verificationResultNga);
                    }
                    else
                        $erdata = $verificationResultNga;
                    $errors = implode(', ', $erdata );
                    throw new CustomBaseException('NGA Verification Failed, Coach is not updated.' . $errors, -1);
                }
            }
            DB::commit();
        } catch(CustomBaseException $e) {
            DB::rollBack();
            return back()->with('error', $e->getMessage(), 'data');
        } catch(\Throwable $e) {
            DB::rollBack();
            return back()->with('error', 'An error occured.' .$e->getMessage() . json_encode($verificationResultNga) );
        }

        return back()->with('success', 'Coach info updated.');

        
    }


    public function batchRemove(Request $request, string $gym)
    {
        $selected = $request->validate([
            'selected_coaches_list' => ['required', 'string']
        ])['selected_coaches_list'];
        $gym = $request->_managed_account->retrieveGym($gym);
        $gym->removeCoachBatch($selected);
        return back()->with('success', 'The selected coaches were removed');
    }

    public function import(Request $request, string $gym)
    {
        $attr = $request->validate(Coach::getImportRules());
        $gym = $request->_managed_account->retrieveGym($gym); /** @var \App\Models\Gym $gym */
        $result = $gym->importCoaches(
            $attr['method'], $attr['duplicates'], $attr['body'],
            isset($attr['csv_file']) ? $attr['csv_file'] : null,
            isset($attr['delimiter']) ? $attr['delimiter'] : ';'
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

        return back()->with('info', 'Coach import finished.');
    }

    public function storeFromFailedImport(Request $request, string $gym, string $failed)
    {
        $attr = $request->validate(Coach::CREATE_RULES);
        $gym = $request->_managed_account->retrieveGym($gym);
        $failed = $gym->failed_coach_imports()->find($failed);
        if ($failed == null)
            throw new CustomBaseException('No such import entry.', -1);

        $coach = $gym->createCoachFromFailedImport($attr, $failed);

        return redirect(route('gyms.coaches.index', ['gym' => $gym]))
            ->with('success', 'Coach "' . $coach->fullName() . '" successfully created.');
    }

    public function failedImportEdit(Request $request, string $gym, string $failed)
    {
        $gym = $request->_managed_account->retrieveGym($gym);
        $coach = $gym->failed_coach_imports()->find($failed);
        if ($coach == null)
            throw new CustomBaseException('No such entry.', -1);

        $duplicate = null;
        if ($coach->error_code == FailedCoachImport::ERROR_CODE_DUPLICATE) {
            if ($coach->usag_no != null)
                $duplicate = $gym->coaches()->where('usag_no', $coach->usag_no)->first();

            if ($coach->usaigc_no != null)
                $duplicate = $gym->coaches()->where('usaigc_no', $coach->usaigc_no)->first();

            if ($coach->aau_no != null)
                $duplicate = $gym->coaches()->where('aau_no', $coach->aau_no)->first();
        }

        $tshirtChart = ClothingSizeChart::where('is_default', true)->where('is_leo', false)->first();
        return view('coach.faulty-edit', [
            'current_page' => 'gym-' . $gym->id,
            'gym' => $gym,
            'coach' => $coach,
            'duplicate' => $duplicate,
            'tshirt_chart' => $tshirtChart,
            'bodies' => Helper::getStructuredLevelList()
        ]);
    }

    public function failedImportUpdate(Request $request, string $gym, string $failed, string $duplicate)
    {
        $attr = $request->validate(Coach::UPDATE_RULES);
        $gym = $request->_managed_account->retrieveGym($gym);
        $coach = $gym->failed_coach_imports()->find($failed);
        if ($coach == null)
            throw new CustomBaseException('No such entry.', -1);

        $duplicate = $gym->coaches()->find($duplicate);
        if ($duplicate == null)
            throw new CustomBaseException('No such coach (duplicate).', -1);

        $duplicate->overwriteProfile($attr, $coach);

        return redirect(route('gyms.coaches.index', ['gym' => $gym]))->with('success', 'Coach info overwritten.');
    }

    public function batchRemoveFailed(Request $request, string $gym)
    {
        $selected = $request->validate([
            'selected_failed_coaches_list' => ['required', 'string']
        ])['selected_failed_coaches_list'];
        $gym = $request->_managed_account->retrieveGym($gym);
        $gym->removeFailedCoachBatch($selected);
        return back()->with('success', 'The selected entries were removed');
    }
}
