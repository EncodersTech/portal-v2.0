<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\AppBaseController;
use App\Models\USAGReservation;
use App\Repositories\USAGReservationRepository;
use Illuminate\Http\Request;

class USAGReservationController extends AppBaseController
{
    /**
     * @var USAGReservationRepository
     */
    private $USAGReservationRepo;

    public function __construct(USAGReservationRepository $USAGReservationRepo)
    {
        $this->USAGReservationRepo = $USAGReservationRepo;
    }

    public function index()
    {
        $usagReservations = USAGReservation::with(['level_category', 'gym', 'usag_sanction.level_category', 'parent', 'children'])
            ->orderByDesc('created_at')
            ->where('status', USAGReservation::RESERVATION_STATUS_PENDING)
            ->orWhere('status', USAGReservation::RESERVATION_STATUS_HIDE)
            ->paginate(12);

        return view('admin.usag_reservation.index', compact('usagReservations'));
    }

    public function searchUsagReservation(Request $request)
    {
        $input = $request->all();
        $usagReservations = $this->USAGReservationRepo->searchUsagReser($input['searchData']);

        $results =  view('admin.usag_reservation.usag_reservation', compact('usagReservations'))->render();

        return $this->sendResponse($results, 'Usag Reservation search data successfully.');
    }

    public function usagReservationHide($id)
    {
        $usagReservation = USAGReservation::find($id);

        if (!$usagReservation) {
            return $this->sendError('USAG Reservation not found.');
        }

        $usagReservation->status = ($usagReservation->status == USAGReservation::RESERVATION_STATUS_PENDING)
            ? USAGReservation::RESERVATION_STATUS_HIDE
            : USAGReservation::RESERVATION_STATUS_PENDING;
        $usagReservation->save();

        return $this->sendSuccess('USAG Reservation updated successfully.');
    }

    public function usagReservationDestroy($id)
    {
        $usagReservation = USAGReservation::find($id);

        if (!$usagReservation) {
            return $this->sendError('USAG Reservation not found.');
        }

        $usagReservation->status = USAGReservation::RESERVATION_STATUS_DELETE;
        $usagReservation->save();

        return $this->sendSuccess('USAG Reservation Deleted successfully.');
    }
}
