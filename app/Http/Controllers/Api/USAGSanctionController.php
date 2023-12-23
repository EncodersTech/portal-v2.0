<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Exceptions\CustomBaseException;
use App\Helper;
use App\Models\Gym;
use App\Models\Meet;
use App\Models\USAGSanction;
use Illuminate\Support\Facades\Log;

class USAGSanctionController extends BaseApiController
{
    public function merge(Request $request, string $gym, string $sanction) {
        $meet = null;
        $data = null;
        $result = [];
        try {
            $gym = $request->_managed_account->retrieveGym($gym); /** @var Gym $gym */
            $meet = $request->input('meet');
            $data = $request->input('data');
            $result = USAGSanction::merge($gym, $sanction, $meet, $data, $request->input('meet_data_switches')); /** @var Meet $meet */
            
            return $this->success([
                'message' => 'success',
                'url' => route('gyms.meets.details', [
                    'meet' => $result->id,
                ]),
            ]);
        } catch(CustomBaseException $e) {
            throw $e;
        } catch(\Throwable $e) {
            Log::warning(self::class . '@merge : ' . $e->getMessage(), [
                'Gym' => $gym,
                'Sanction' => $sanction,
                'Meet' => $meet,
                'Data' => $data,
                'Throwable' => $e
            ]);
            return $this->error([
                'message' => 'Something went wrong while merging this sanction.'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }        
    }
}
