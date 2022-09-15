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

        return view('dashboard', [
            '_managed' => $managed,
            'current_page' => 'dashboard',
            'showSanctionNotifications' => $showSanctionNotifications
        ]);
    }

    public function browseMeets()
    {
        return view('browse', [
            'current_page' => 'browse-meets'
        ]);
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
