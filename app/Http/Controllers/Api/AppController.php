<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Exceptions\CustomBaseException;
use App\Models\Setting;

class AppController extends BaseApiController
{
    public function withdrawalFees()
    {
        try {
            return Setting::withdrawalFees();
        } catch (\Throwable $th) {
            throw new CustomBaseException('Something went wrong while loading withdrawal fees', -1, $th);
        }
    }
}
