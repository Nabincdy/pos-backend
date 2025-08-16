<?php

namespace App\Http\Controllers\Api\Setting;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Setting\FiscalYear\StoreFiscalYearRequest;
use App\Http\Requests\Api\Setting\FiscalYear\UpdateFiscalYearRequest;
use App\Http\Resources\Setting\FiscalYearResource;
use App\Models\Setting\FiscalYear;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class FiscalYearController extends Controller
{
    public function runningFiscalYear(): FiscalYearResource
    {
        return new FiscalYearResource(runningFiscalYear());
    }

    public function index(): AnonymousResourceCollection
    {
        $this->checkAuthorization('fiscalYear_access');

        return FiscalYearResource::collection(FiscalYear::orderBy('start_date')->get());
    }

    public function store(StoreFiscalYearRequest $request)
    {
        $this->checkAuthorization('fiscalYear_create');

        $fiscalYear = FiscalYear::create($request->validated() + [
            'is_running' => FiscalYear::count() == 0,
        ]);

        Cache::forget('running_fiscal_year');

        return \response()->json([
            'data' => new FiscalYearResource($fiscalYear),
            'message' => 'Fiscal Year Added successfully',
        ], 201);
    }

    public function show(FiscalYear $fiscalYear): FiscalYearResource
    {
        $this->checkAuthorization('fiscalYear_access');

        return new FiscalYearResource($fiscalYear);
    }

    public function update(UpdateFiscalYearRequest $request, FiscalYear $fiscalYear)
    {
        $this->checkAuthorization('fiscalYear_edit');

        $fiscalYear->update($request->validated());

        Cache::forget('running_fiscal_year');

        return \response()->json([
            'data' => new FiscalYearResource($fiscalYear),
            'message' => 'Fiscal Year Updated Successfully',
        ]);
    }

    public function destroy(FiscalYear $fiscalYear)
    {
        $this->checkAuthorization('fiscalYear_delete');

        if ($fiscalYear->is_running) {
            return response()->json([
                'message' => 'Running Fiscal Year can not be deleted',
            ], 400);
        }
        $fiscalYear->delete();

        Cache::forget('running_fiscal_year');

        return \response()->json([
            'data' => '',
            'message' => 'Fiscal Year Deleted Successfully',
        ]);
    }

    public function updateStatus(FiscalYear $fiscalYear)
    {
        $this->checkAuthorization('fiscalYear_edit');

        if ($fiscalYear->is_running) {
            return response()->json([
                'message' => 'This Year is already running.',
            ], 400);
        }

        DB::transaction(function () use ($fiscalYear) {
            $fiscalYear->update([
                'is_running' => 1,
            ]);

            FiscalYear::whereNot('id', $fiscalYear->id)->where('is_running', 1)->update([
                'is_running' => 0,
            ]);
        });

        Cache::forget('running_fiscal_year');

        return response()->json([
            'data' => FiscalYearResource::collection(FiscalYear::all()),
            'message' => 'Fiscal Year Status Updated Successfully',
        ]);
    }
}
