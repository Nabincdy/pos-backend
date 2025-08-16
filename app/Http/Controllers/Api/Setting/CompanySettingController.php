<?php

namespace App\Http\Controllers\Api\Setting;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Setting\UpdateCompanySettingRequest;
use App\Http\Resources\Setting\CompanySettingResource;
use App\Models\Setting\CompanySetting;
use Illuminate\Support\Facades\Cache;

class CompanySettingController extends Controller
{
    public function index(): CompanySettingResource
    {
        $this->checkAuthorization('companySetting_access');

        return new CompanySettingResource(companySetting());
    }

    public function update(UpdateCompanySettingRequest $request, CompanySetting $companySetting)
    {
        $this->checkAuthorization('companySetting_edit');

        if ($request->hasFile('logo') && $companySetting->logo) {
            $this->deleteFile($companySetting->logo);
        }
        $companySetting->update($request->validated() + $request->validated()['code_prefixes']);

        Cache::forget('company_setting');

        return response()->json([
            'data' => new CompanySettingResource($companySetting),
            'message' => 'Company Setting Updated Successfully',
        ]);
    }
}
