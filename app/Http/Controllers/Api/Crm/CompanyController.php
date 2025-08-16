<?php

namespace App\Http\Controllers\Api\Crm;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Crm\Company\StoreCompanyRequest;
use App\Http\Requests\Api\Crm\Company\UpdateCompanyRequest;
use App\Http\Resources\Crm\CompanyResource;
use App\Models\Crm\Company;
use Illuminate\Support\Str;

class CompanyController extends Controller
{
    public function companyCode()
    {
        return companySetting()->company.Str::padLeft(Company::max('id') + 1, 3, 0);
    }

    public function index()
    {
        $this->checkAuthorization('company_access');

        return CompanyResource::collection(Company::all());
    }

    public function store(StoreCompanyRequest $request)
    {
        $this->checkAuthorization('company_create');

        $company = Company::create($request->validated());

        return response()->json([
            'data' => new CompanyResource($company),
            'message' => 'Company Added Successfully',
        ], 201);
    }

    public function show(Company $company)
    {
        $this->checkAuthorization('company_access');

        return new CompanyResource($company);
    }

    public function update(UpdateCompanyRequest $request, Company $company)
    {
        $this->checkAuthorization('company_edit');

        if ($request->hasFile('logo') && $company->logo) {
            $this->deleteFile($company->logo);
        }

        $company->update($request->validated());

        return response()->json([
            'data' => new CompanyResource($company),
            'message' => 'Company Updated Successfully',
        ]);
    }

    public function destroy(Company $company)
    {
        $this->checkAuthorization('company_delete');

        if ($company->logo) {
            $this->deleteFile($company->logo);
        }
        $company->delete();

        return response()->json([
            'data' => '',
            'message' => 'Company Deleted Successfully',
        ]);
    }
}
