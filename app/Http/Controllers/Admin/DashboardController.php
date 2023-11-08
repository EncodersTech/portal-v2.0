<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\AppBaseController;
use App\Models\User;
use App\Models\AthleteLevel;
use App\Models\SanctioningBody;
use App\Models\LevelCategory;
use App\Repositories\DashboardRepository;
use Illuminate\Http\Request;

class DashboardController extends AppBaseController
{
    /**
     * @var DashboardRepository
     */
    private $dashboardRepository;

    public function __construct(DashboardRepository $dashboardRepository)
    {
        $this->dashboardRepository = $dashboardRepository;
    }

    public function index()
    {
        $data = $this->dashboardRepository->getDashboardData();

        return view('admin.dashboard.index')->with($data);
    }

    public function pendingWithdrawalBalance()
    {
        $data['users'] = User::where('cleared_balance','>',0)->where('cleared_balance','!=',0)->get();

        return view('admin.reports.pending_withdrawal_balance.index')->with($data);
    }

    public function printPendingWithdrawalBalance()
    {
        $pdf = $this->dashboardRepository->pendingWithdrawalBalanceReport()->setPaper('a4')
            ->setOption('margin-top', '10mm')
            ->setOption('margin-bottom', '10mm')
            ->setOption('footer-html', view('PDF.host.meet.reports.header_footer.common_footer')->render());

        return $pdf->stream('pending_withdrawal_balace_report.'.time().'.pdf');
    }
    public function errorNotice()
    {
        $data = [];
        $data['usag_levels'] = AthleteLevel::where('sanctioning_body_id', SanctioningBody::USAG)->orderBy('id','ASC')->get();
        $data['label_categories'] = LevelCategory::get();
        return view('admin.error_report.index')->with($data);
    }
    public function usagLevelsUpdate(Request $request)
    {
        try{
            
            $request->validate([
                'code' => 'required',
                'name' => 'required',
                'abbrebiation' => 'required',
                'is_disabled' => 'required',
            ]);
            $data = $request->all();
            $level = AthleteLevel::find($data['id']);
            $level->code = $data['code'];
            $level->name = $data['name'];
            $level->abbreviation = $data['abbrebiation'];
            $level->is_disabled = $data['is_disabled'];
            $level->save();
            $response = array(
                'status' => 200,
                'message' => 'USAG Level Updated Successfully',
                'data' => $level,
            );
            return response()->json($response, 200);
        }
        catch(\Exception $e){
            $response = array(
                'status' => 500,
                'message' => 'Something went wrong : ' . $e->getMessage(),
                'data' => $e->getMessage(),
            );
            return response()->json($response, 500);
        }
    }
    public function usagLevelsAdd(Request $request)
    {
        try{


            $data = $request->all();
            // validate the data
            $request->validate([
                'code' => 'required',
                'name' => 'required',
                'abbrebiation' => 'required',
                'is_disabled' => 'required',
                'label_category' => 'required',
            ]);
            $maxID = AthleteLevel::where('sanctioning_body_id', SanctioningBody::USAG)->where('level_category_id',$data['label_category'])->max('id');
            $maxID = $maxID + 1;

            // check if this ID already exists
            $id_exist = AthleteLevel::where('id',$maxID)->first();
            if($id_exist)
            {
                $maxID = AthleteLevel::max('id') + 4000 + 1;
            }
            // check if code already exists
            $code = AthleteLevel::where('sanctioning_body_id', SanctioningBody::USAG)->where('level_category_id',$data['label_category'])->where('code',$data['code'])->first();
            if($code)
            {
                $response = array(
                    'status' => 500,
                    'message' => 'Level Code already exists, it should be unique',
                    'data' => $code,
                );
                return response()->json($response , 500);
            }

            $level = new AthleteLevel();
            $level->id = $maxID;
            $level->sanctioning_body_id = SanctioningBody::USAG;
            $level->code = $data['code'];
            $level->name = $data['name'];
            $level->abbreviation = $data['abbrebiation'];
            $level->is_disabled = $data['is_disabled'];
            $level->level_category_id = $data['label_category'];
            $level->save();
            $response = array(
                'status' => 200,
                'message' => 'USAG Level Added Successfully',
                'data' => $level,
            );
            return response()->json($response, 200);
        }
        catch(\Exception $e){
            $response = array(
                'status' => 500,
                'message' => 'Something went wrong : '. $e->getMessage(),
                'data' => $e->getMessage(),
            );
            return response()->json($response , 500);
        }
    }
}
