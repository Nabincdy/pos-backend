<?php

namespace App\Http\Controllers\Api\Account;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Account\Ledger\StoreLedgerRequest;
use App\Http\Requests\Api\Account\Ledger\UpdateLedgerRequest;
use App\Http\Resources\Account\LedgerResource;
use App\Models\Account\Ledger;

class LedgerController extends Controller
{
    public function index()
    {
        $this->checkAuthorization('ledger_access');

        $ledgers = Ledger::with('ledgerGroup', 'subLedgers')->whereNull('ledger_id')->get();

        return LedgerResource::collection($ledgers);
    }

    public function store(StoreLedgerRequest $request)
    {
        $this->checkAuthorization('ledger_create');

        $ledger = Ledger::create($request->validated());

        return response()->json([
            'data' => new LedgerResource($ledger->load('ledgerGroup')),
            'message' => 'Ledger Created Successfully',
        ], 201);
    }

    public function show(Ledger $ledger)
    {
        $this->checkAuthorization('ledger_access');

        return new LedgerResource($ledger->load('ledgerGroup'));
    }

    public function update(UpdateLedgerRequest $request, Ledger $ledger)
    {
        $this->checkAuthorization('ledger_edit');

        if ($ledger->auto_generated) {
            return response()->json([
                'message' => 'Auto generated ledger can not be deleted',
            ], 400);
        }

        $ledger->update($request->validated());

        return response()->json([
            'data' => new LedgerResource($ledger->load('ledgerGroup')),
            'message' => 'Ledger Updated Successfully',
        ]);
    }

    public function destroy(Ledger $ledger)
    {
        $this->checkAuthorization('ledger_delete');

        if ($ledger->auto_generated) {
            return response()->json([
                'message' => 'Auto generated ledger can not be deleted',
            ], 400);
        }

        $ledger->subLedgers()->delete();
        $ledger->delete();

        return response()->json([
            'data' => '',
            'message' => 'Ledger Deleted Successfully',
        ]);
    }

    public function updateStatus(Ledger $ledger)
    {
        $this->checkAuthorization('ledger_edit');

        if ($ledger->auto_generated) {
            return response()->json([
                'message' => 'Auto generated ledger can not be updated',
            ], 400);
        }

        $ledger->update([
            'status' => ! $ledger->status,
        ]);

        return response()->json([
            'status' => $ledger->status,
            'message' => 'Status Updated Successfully',
        ]);
    }
}
