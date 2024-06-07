<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\AppBaseController;
use App\Http\Requests\UpdateSettingRequest;
use App\Models\Setting;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class SettingController extends AppBaseController
{
    public function index()
    {
        $settings = Setting::pluck('value', 'key')->toArray();

        return view('admin.settings.index',compact('settings'));
    }

    public function update(UpdateSettingRequest $request)
    {
        $input = $request->all();
        $input['audit_enabled'] = isset($input['audit_enabled']) ? 1 : 0;
        $input['is_schedule_withdraw_enabled'] = isset($input['is_schedule_withdraw_enabled']) ? 1 : 0;
        $input['enabled_feature_meet_fee'] = isset($input['enabled_feature_meet_fee']) ? 1 : 0;
        $input['one_time_ach'] = isset($input['one_time_ach']) ? 1 : 0;
        $input['cc_gateway'] = isset($input['cc_gateway']) ? 1 : 0;

        DB::beginTransaction();
        try {

            $settingInputArray = Arr::except($input, ['_token']);

            foreach ($settingInputArray as $key => $value) {
                Setting::where('key', '=', $key)->first()->update(['value' => $value]);
            }

            DB::commit();
            return back()->with('success', 'Setting update successfully.');
        } catch(\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
