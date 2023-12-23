<?php

namespace App\Http\Controllers\Admin;

use App\Exports\AdminMeetsExport;
use App\Http\Controllers\AppBaseController;
use App\Models\Meet;
use App\Queries\FeaturedMeetDataTable;
use App\Queries\MeetDataTable;
use App\Queries\MeetGymsDataTable;
use App\Repositories\MeetRepository;
use Exception;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Yajra\DataTables\DataTables;
use Maatwebsite\Excel\Facades\Excel;

class MeetController extends AppBaseController
{
    /**
     * @var MeetRepository
     */
    private $meetRepository;

    public function __construct(MeetRepository $meetRepository)
    {
        $this->meetRepository = $meetRepository;
    }

    public function index(Request $request)
    {
        if ($request->ajax()) {
            return DataTables::of((new MeetDataTable())->get($request->only(['sanction_type','from_date','to_date','state','status'])))->make(true);
        }

        $data = $this->meetRepository->getMeetData();

        return view('admin.meets.index')->with($data);
    }

    public function meetDashboard(Meet $meet)
    {
        
        $meet = Meet::with('gym')->findOrFail($meet->id);
        $data = $this->meetRepository->getMeetDashboardData($meet);

        return view('admin.meets.dashboard.index', compact('meet'))->with($data);
    }

    public function getMeetGyms(Request $request)
    {
        if ($request->ajax()) {
            return DataTables::of((new MeetGymsDataTable())->get($request->only(['meet_id'])))->make(true);
        }
    }

    public function meetExportExcel()
    {
        return Excel::download(new AdminMeetsExport(), 'adminMeets-'.time().'.xlsx');
    }

    public function meetExportPDF()
    {
        $pdf = $this->meetRepository->generateAdminMeetsReport()->setPaper('a4', 'landscape')
            ->setOption('margin-top', '10mm')
            ->setOption('margin-bottom', '10mm')
            ->setOption('footer-html', view('PDF.host.meet.reports.header_footer.common_footer')->render());

        return $pdf->stream('adminUsers.'.time().'.pdf');
    }

    public function meetFeatured(Meet $meet)
    {
        /** @var Meet $meet */
        $meet = Meet::find($meet->id);
        if (!$meet) {
            return $this->sendError('Meet not found.');
        }

        $meet->is_featured = !$meet->is_featured;
        $meet->save();

        return $this->sendSuccess('Meet featured successfully.');
    }

    public function updateHandlingFee($id, Request $request)
    {
        $input = $request->all();
        $meet = Meet::find($id);
        if(!$meet){
            return $this->sendError('Meet not found.');
        }

        $meet->update(['custom_handling_fee' => $input['custom_handling_fee'] ]);

        return $this->sendSuccess('Handling fee updated successfully.');
    }

    /**
     * @param  Request  $request
     *
     * @return Application|Factory|View
     * @throws Exception
     */
    public function featuredMeets(Request $request)
    {
        if ($request->ajax()) {
            return DataTables::of((new FeaturedMeetDataTable())->get())->make(true);
        }

        return view('admin.featured_meets.index');
    }
}