<?php

namespace App\Http\Controllers;

use App\Models\Gym;
use App\Models\Meet;
use Illuminate\Http\Request;
use App\Exceptions\CustomBaseException;
use App\Models\USAGSanction;
use Throwable;

class USAGSanctionController extends Controller
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
            $meetId = USAGSanction::where('number',$sanction)->first()->meet_id;

            if ($meetId){
               $meetName = Meet::where('id',$meetId)->first()->name;
            }

            $result = json_encode(USAGSanction::calculateFinalState($gym, $sanction, true));
            if ($result === false)
                throw new CustomBaseException('JSON encoding failed.', -1);

            return view('sanction.usag.details', [
                'current_page' => 'gym-' . $gym->id,
                'gym' => $gym,
                'sanction' => $sanction,
                'state' => $result,
                'meetName' => $meetName ?? '',
            ]);
        } catch (CustomBaseException $e) {
            throw $e;
        } catch (Throwable $e) {
            throw new CustomBaseException('Something went wrong while fetching sanction details (server error)', -1);
        }
    }
}
