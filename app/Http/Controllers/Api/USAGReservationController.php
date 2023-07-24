<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Exceptions\CustomBaseException;
use App\Models\Gym;
use App\Models\MeetRegistration;
use App\Models\USAGReservation;
use Illuminate\Support\Facades\Log;

class USAGReservationController extends BaseApiController
{
    public function merge(Request $request, string $gym, string $sanction) {
        $summary = null;
        $method = null;
        $useBalance = null;

        $result = null;
        try {
            $gym = $request->_managed_account->retrieveGym($gym); /** @var Gym $gym */
            $summary = $request->input('summary');
            $method = $request->input('method');
            $useBalance = boolval($request->input('use_balance'));
            $data = $request->input('data');
            $coupon = $request->input('coupon');
            $travel_arrangement = $request->input('enable_travel_arrangements');
            $result = USAGReservation::merge($gym, $sanction, $data, $summary, $method, $useBalance,$coupon,$travel_arrangement ); /** @var MeetRegistration $result */            
            
            return $this->success([
                'message' => 'Your reservation has been successfully processed.',
                'registration' => $result,
                'url' => route('gyms.registration', [
                    'gym' => $gym->id,
                    'registration' => $result->id,
                ]),
            ]);
        } catch(CustomBaseException $e) {
            throw $e;
        } catch(\Throwable $e) {
            Log::warning(self::class . '@merge : ' . $e->getMessage(), [
                'Gym' => $gym,
                'Sanction' => $sanction,
                'Summary' => $summary,
                'Method' => $method,
                'UseBalance' => $useBalance,
                'Throwable' => $e
            ]);
            return $this->error([
                'message' => 'Something went wrong while processing your reservation.'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }        
    }
}
