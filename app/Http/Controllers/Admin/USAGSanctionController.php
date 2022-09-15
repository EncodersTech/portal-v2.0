<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\AppBaseController;
use App\Models\USAGSanction;
use App\Repositories\USAGSanctionRepository;
use Illuminate\Http\Request;

class USAGSanctionController extends AppBaseController
{
    /**
     * @var USAGSanctionRepository
     */
    private $USAGSanctionRepo;

    public function __construct(USAGSanctionRepository $USAGSanctionRepo)
    {
        $this->USAGSanctionRepo = $USAGSanctionRepo;
    }

    public function index()
    {
        $usagSanctions = USAGSanction::with(['gym', 'meet', 'level_category', 'parent', 'children'])
            ->orderByDesc('created_at')
            ->where('status', USAGSanction::SANCTION_STATUS_PENDING)
            ->orWhere('status', USAGSanction::SANCTION_STATUS_HIDE)
            ->paginate(12);

        return view('admin.usag_sanction.index', compact('usagSanctions'));
    }

    public function searchUsagSanction(Request $request)
    {
        $input = $request->all();
        $usagSanctions = $this->USAGSanctionRepo->searchUsagSanc($input['searchData']);

        $results =  view('admin.usag_sanction.usag_sanction', compact('usagSanctions'))->render();

        return $this->sendResponse($results, 'Usag Sanction search data successfully.');
    }

    public function usagSanctionHide($id)
    {
        $usagSanction = USAGSanction::find($id);

        if (!$usagSanction) {
            return $this->sendError('USAG Sanction not found.');
        }

        $usagSanction->status = ($usagSanction->status == USAGSanction::SANCTION_STATUS_PENDING)
            ? USAGSanction::SANCTION_STATUS_HIDE
            : USAGSanction::SANCTION_STATUS_PENDING;
        $usagSanction->save();

        return $this->sendSuccess('USAG Sanction updated successfully.');
    }

    public function usagSanctionDestroy($id)
    {
        $usagSanction = USAGSanction::find($id);

        if (!$usagSanction) {
            return $this->sendError('USAG Sanction not found.');
        }

        $usagSanction->status = USAGSanction::SANCTION_STATUS_DELETE;
        $usagSanction->save();

        return $this->sendSuccess('USAG Sanction Deleted successfully.');
    }
}
