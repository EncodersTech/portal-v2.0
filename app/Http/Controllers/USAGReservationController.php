<?php

namespace App\Http\Controllers;

use App\Models\Gym;
use Illuminate\Http\Request;
use App\Exceptions\CustomBaseException;
use App\Models\USAGReservation;
use App\Models\USAGSanction;
use Throwable;

class USAGReservationController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request, string $gym, string $sanction)
    {
        try {
            $gym = $request->_managed_account->retrieveGym($gym); /** @var Gym $gym */

            $sanction = USAGSanction::where('number', $sanction)
                                ->where('action', USAGSanction::SANCTION_ACTION_ADD)
                                ->where('status', USAGSanction::SANCTION_STATUS_MERGED)
                                ->first(); /** @var USAGSanction $sanction */
            if ($sanction === null)
                throw new CustomBaseException('Could not retrieve the sanction for this reservation', -1);

            $late = $sanction->meet->isLAte();

            $result = json_encode(USAGReservation::calculateFinalState($gym, $sanction->number));
            if ($result === false)
                throw new CustomBaseException('JSON encoding failed.', -1);

            //return $result;
            return view('reservation.usag.details', [
                'current_page' => 'gym-' . $gym->id,
                'gym' => $gym,
                'sanction' => $sanction,
                'state' => $result,
                'late' => $late,
            ]);
        } catch (CustomBaseException $e) {
            throw $e;
        } catch (Throwable $e) {
            throw new CustomBaseException('Something went wrong while fetching reservation details (server error)', -1);
        }        
    }
}
