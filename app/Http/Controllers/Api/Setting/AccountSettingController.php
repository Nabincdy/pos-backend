<?php

namespace App\Http\Controllers\Api\Setting;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Setting\UpdateAccountSettingRequest;
use App\Http\Resources\Setting\AccountSettingResource;
use App\Models\Setting\AccountSetting;
use Illuminate\Support\Facades\Cache;

class AccountSettingController extends Controller
{
    public function index()
    {
        $this->checkAuthorization('accountSetting_access');

        return new AccountSettingResource(\accountSetting());
    }

    public function update(UpdateAccountSettingRequest $request, AccountSetting $accountSetting)
    {
        $this->checkAuthorization('accountSetting_edit');

        $accountSetting->update($request->validated());

        Cache::forget('account_setting');

        return response()->json([
            'data' => new AccountSettingResource($accountSetting),
            'message' => 'Account Setting Updated Successfully',
        ]);
    }
}
