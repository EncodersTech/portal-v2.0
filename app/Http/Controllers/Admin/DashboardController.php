<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\AppBaseController;
use App\Models\User;
use App\Models\AthleteLevel;
use App\Models\SanctioningBody;
use App\Models\LevelCategory;
use App\Repositories\DashboardRepository;
use Illuminate\Http\Request;
use App\Services\USAGService;
use App\Services\IntellipayService;
use App\Models\UserBalanceTransaction;
use Illuminate\Support\Facades\DB;
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
    public function usagLevel()
    {
        $data = [];
        $data['usag_levels'] = AthleteLevel::where('sanctioning_body_id', SanctioningBody::USAG)->orderBy('id','ASC')->get();
        $data['label_categories'] = LevelCategory::get();
        $data['page'] = 'usag_level';
        return view('admin.error_report.index')->with($data);
    }
    public function errorNotice()
    {
        $data = [];
        $data['log_errors'] = $this->getLoggedError();
        $data['usag_levels'] = AthleteLevel::where('sanctioning_body_id', SanctioningBody::USAG)->orderBy('id','ASC')->get();
        $data['label_categories'] = LevelCategory::get();
        $data['page'] = 'error_notice';
        return view('admin.error_report.index')->with($data);
    }
    public function onetimeach_report_postdddd(Request $request)
    {
        dd($request);
    }
    public function balance_adjustment()
    {
        $data = [];
        $data['page'] = 'balance_adjustment';
        return view('admin.balance.index')->with($data);
    }
    public function get_user(Request $request)
    {
        $email = $request->email;
        $user = User::where('email',$email)->first();
        return response()->json(['status' => 'success', 'user' => $user]);
    }
    public function adjust_balance(Request $request)
    {
        $email = $request->email;
        $amount = $request->amount;
        $description = $request->description;
        $user = User::where('email',$email)->first();
        if($user)
        {
            DB::beginTransaction();

            $user->cleared_balance = $user->cleared_balance + $amount;
            
            $user->balance_transactions()->create([
                'processor_id' => null,
                'total' => $amount,
                'description' => $description,
                'clears_on' => now(),
                'created_at' => now(),
                'updated_at' => now(),
                'type' => UserBalanceTransaction::BALANCE_TRANSACTION_TYPE_ADMIN,
                'status' => UserBalanceTransaction::BALANCE_TRANSACTION_STATUS_CLEARED
            ]);
            $user->save();
            DB::commit();
            return response()->json(['status' => 'success', 'message' => 'Balance Adjusted Successfully']);
        }
    }
    public function onetimeach_report()
    {
        $from_date = date('m/d/Y',strtotime('-1 month'));
        $to_date = date('m/d/Y');
        if(isset($_POST['from_date']) && isset($_POST['to_date']))
        {
            $from_date = $_POST['from_date'];
            $to_date = $_POST['to_date'];
        }
        $data = [];
        $intellipay = resolve(IntellipayService::class);
        $data['items'] = $intellipay->get_ach_payment_list($from_date,$to_date);
        // dd($data['items']);
        $data['label_categories'] = LevelCategory::get();
        $data['page'] = 'onetimeach_report';
        $data['from_date'] = $from_date;
        $data['to_date'] = $to_date;
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
    public function getLoggedError()
    {
        $env = env('APP_ENV', 'local');
        $env = 'production';
        $s = 's';
        // $s = $env == 'production' ? '' : 's';
        $logFiles = glob(storage_path('logs/laravel-www-data-*.log'));
        $data_merged = [];
        foreach($logFiles as $logFile)
        {
            $filename = basename($logFile);
            $data = [
                'id' => random_int(1, 1000000),
                'name' => $filename,
                'last_modified' =>  \Datetime::createFromFormat('U', filemtime($logFile))->format('Y-m-d H:i:s'),
                'date' => preg_match('/\d{4}-\d{2}-\d{2}/', $logFile, $dates) ? $dates[0] : date('Y-m-d', filemtime($logFile))
            ];
            $result = [];
            $file = file_get_contents($logFile);

            $pattern = '/\[\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}\] \w+\.ERROR:\s.*?(?=\[\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}\] \w+\.\w+:|$)/s';
            $date_pattern = '/\[\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}\] \w+\.ERROR:/s';
            preg_match_all($pattern, $file, $matches);
            foreach ($matches as $key => $value) {
                foreach ($value as $k => $v) {
                    $temp = substr($v, 0, strpos($v, '[stacktrace]'));
                    if(trim($temp) != "")
                    {
                        $result[$key][$k]['heading'] = $temp;
                        $result[$key][$k]['details'] = $v;
                    }
                    else
                    {
                        $result[$key][$k]['heading'] = $v;
                        $result[$key][$k]['details'] = $v;
                    }
                }
            }

            $matches = [];
            $pattern = '/\[\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}\] \w+\.INFO:\s.*?(?=\[\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}\] \w+\.\w+:|$)/s';
            $date_pattern = '/\[\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}\] \w+\.INFO:/s';
            preg_match_all($pattern, $file, $matches);
            foreach ($matches as $key => $value) {
                foreach ($value as $k => $v) {
                    $temp = substr($v, 0, strpos($v, '[stacktrace]'));
                    if(trim($temp) != "")
                    {
                        $result[$key][$k]['heading'] = $temp;
                        $result[$key][$k]['details'] = $v;
                    }
                    else
                    {
                        preg_match_all($date_pattern, $v, $matches_string);
                        $temp = str_replace($matches_string[0],'',$v);
                        $result[$key][$k]['heading'] = $temp;
                        $result[$key][$k]['details'] = $v;
                    }
                }
            }
            if(count($result) > 0)
            {
                $data['errors'] = $result[0];
                $data_merged[] = $data;
            }
            
        }
        return $data_merged;
    }
    public function usag_check()
    {
        $usag = new USAGService();
        $usag->checkForExistingLevelsInSanction();
        // $usag->checkForExistingLevels();
    }
    public function reports_dashboard()
    {
        if(isset($_GET['start_date']) && isset($_GET['end_date']))
        {
            $start_date = $_GET['start_date'];
            $end_date = $_GET['end_date'];
        }
        else
        {
            $start_date = date('Y-m-d',strtotime('-12 month'));
            $end_date = date('Y-m-d');
        }
        
        $user = resolve(User::class);

        $data = [];
        $data['start_date'] = $start_date;
        $data['end_date'] = $end_date;

        $start_date = date('Y-m-d h:i:s',strtotime($start_date));
        $end_date = date('Y-m-d h:i:s',strtotime($end_date));

        $data['user_statistics'] = $user->get_user_statistics($start_date,$end_date);
        $data['transaction_statistics'] = $user->get_transaction_method_sum($start_date,$end_date);
        $data['user_balance_statistics'] = $user->get_user_balance_sum($start_date,$end_date);
        $data['athlete_statistics'] = $user->get_athlete_count_per_gym();
        $data['coach_statistics'] = $user->get_coach_count_per_gym();
        $data['meet_registration_statistics'] = $user->meet_registration_report($start_date,$end_date);
        $data['gym_registration_statistics'] = $user->gym_registration_report($start_date,$end_date);
        
        // dd($data['meet_registration_statistics']);
        return view('admin.dashboard.report', $data);
    }

}
