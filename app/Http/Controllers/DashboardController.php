<?php

namespace App\Http\Controllers;

use App\Exceptions\CustomBaseException;
use App\Exports\GymsExport;
use App\Exports\SanctionLevelsExport;
use App\Models\Conversation;
use App\Models\Gym;
use App\Models\MeetRegistration;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use function foo\func;
use Maatwebsite\Excel\Facades\Excel;

class DashboardController extends AppBaseController
{

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index(Request $request)
    {
        $managed = $request->_managed_account;
        /** @var User $managed */
        $showSanctionNotifications = $managed->isCurrentUser() || $managed->pivot->can_manage_gyms;
        $popupnotifications = DB::table('popnotificaitons')
                                ->where('validity', '>=', Carbon::now())
                                ->where('status', 1)
                                ->orderBy('id', 'desc')->get();

        $has_popup = false;
        foreach ($popupnotifications as $popupnotification) {
            if($popupnotification->selected_users == null)
            {
                $has_popup = true;
                DB::table('popnotificaitons')->where('id', $popupnotification->id)
                ->update(
                    ['selected_users' => json_encode(array($managed->id => 1))
                ]);
            }
            else
            {
                $selected_users = json_decode($popupnotification->selected_users, true);
                if(!array_key_exists($managed->id, $selected_users) || $selected_users[$managed->id] == 0)
                {
                    $has_popup = true;
                    $selected_users[$managed->id] = 1;
                    DB::table('popnotificaitons')->where('id', $popupnotification->id)
                    ->update(
                        ['selected_users' => json_encode($selected_users)
                    ]);
                }
            }
        }

        return view('dashboard', [
            '_managed' => $managed,
            'current_page' => 'dashboard',
            'showSanctionNotifications' => $showSanctionNotifications,
            'generalNotifications' => $popupnotifications,
            'has_popup' => $has_popup
        ]);
    }

    public function browseMeets()
    {
        return view('browse', [
            'current_page' => 'browse-meets'
        ]);
    }
    public function privacyPolicy() {
        // echo "Hello";
        return view('auth.privacy_policy');
    }

    /**
     * @throws CustomBaseException
     */
    public function sanctionLevelExportExcel(Request $request)
    {
//        $this->authenticate($request);
        return Excel::download(new SanctionLevelsExport(), 'sanctionLevels-'.time().'.xlsx');
    }

    /**
     * @param Request $request
     * @return BinaryFileResponse
     */
    public function gymExportExcel(Request $request)
    {
        return Excel::download(new GymsExport(), 'gyms-'.time().'.xlsx');
    }
}
