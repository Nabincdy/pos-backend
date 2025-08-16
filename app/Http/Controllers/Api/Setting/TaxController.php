<?php

namespace App\Http\Controllers\Api\Setting;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Setting\Tax\StoreTaxRequest;
use App\Http\Requests\Api\Setting\Tax\UpdateTaxRequest;
use App\Http\Resources\Setting\TaxResource;
use App\Models\Account\Ledger;
use App\Models\Setting\Tax;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TaxController extends Controller
{
    public function taxCode()
    {
        return companySetting()->tax.Str::padLeft(Tax::max('id') + 1, 3, 0);
    }

    public function index(): AnonymousResourceCollection
    {
        $this->checkAuthorization('tax_access');

        return TaxResource::collection(Tax::all());
    }

    public function store(StoreTaxRequest $request): JsonResponse
    {
        $this->checkAuthorization('tax_create');

        if (empty(\accountSetting()->tax_ledger_group_id)) {
            abort(400, 'Tax ledger group not mapped in account setting');
        }

        $tax = DB::transaction(function () use ($request) {
            $ledger = Ledger::create([
                'ledger_group_id' => \accountSetting()->tax_ledger_group_id,
                'ledger_name' => $request->input('name'),
                'code' => $request->input('code'),
                'auto_generated' => true,
            ]);

            return Tax::create($request->validated() + [
                'ledger_id' => $ledger->id,
            ]);
        });

        return response()->json([
            'data' => new TaxResource($tax),
            'message' => 'Tax Added Successfully',
        ], 201);
    }

    public function show(Tax $tax): TaxResource
    {
        $this->checkAuthorization('tax_access');

        return TaxResource::make($tax);
    }

    public function update(UpdateTaxRequest $request, Tax $tax): JsonResponse
    {
        $this->checkAuthorization('tax_edit');

        if (empty(\accountSetting()->tax_ledger_group_id)) {
            abort(400, 'Tax ledger group not mapped in account setting');
        }

        DB::transaction(function () use ($request, $tax) {
            $tax->update($request->validated());

            $tax->ledger()->update([
                'ledger_group_id' => \accountSetting()->tax_ledger_group_id,
                'ledger_name' => $request->input('name'),
                'code' => $request->input('code'),
            ]);
        });

        return response()->json([
            'data' => new TaxResource($tax),
            'message' => 'Tax Updated Successfully',
        ]);
    }

    public function destroy(Tax $tax): JsonResponse
    {
        $this->checkAuthorization('tax_delete');

        $tax->ledger()->delete();
        $tax->delete();

        return response()->json([
            'data' => '',
            'message' => 'Tax Deleted Successfully',
        ]);
    }
}
