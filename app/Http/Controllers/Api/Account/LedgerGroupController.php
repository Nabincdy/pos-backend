<?php

namespace App\Http\Controllers\Api\Account;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Account\LedgerGroup\StoreLedgerGroupRequest;
use App\Http\Requests\Api\Account\LedgerGroup\UpdateLedgerGroupRequest;
use App\Http\Resources\Account\LedgerGroupResource;
use App\Models\Account\LedgerGroup;

class LedgerGroupController extends Controller
{
    public function index()
    {
        $this->checkAuthorization('ledgerGroup_access');

        $ledgerGroups = LedgerGroup::with('ledgerGroup')->orderBy('account_head_id')->get();

        return LedgerGroupResource::collection($ledgerGroups);
    }

    public function store(StoreLedgerGroupRequest $request)
    {
        $this->checkAuthorization('ledgerGroup_create');

        $ledgerGroup = LedgerGroup::create($request->validated());

        return response()->json([
            'data' => new LedgerGroupResource($ledgerGroup->load('ledgerGroup')),
            'message' => 'Ledger Group Added Successfully',
        ], 201);
    }

    public function show(LedgerGroup $ledgerGroup)
    {
        $this->checkAuthorization('ledgerGroup_access');

        return new LedgerGroupResource($ledgerGroup->load('ledgerGroup'));
    }

    public function update(UpdateLedgerGroupRequest $request, LedgerGroup $ledgerGroup)
    {
        $this->checkAuthorization('ledgerGroup_edit');

        if ($ledgerGroup->auto_generated) {
            return response()->json([
                'message' => 'Auto generated ledger group can not be updated',
            ], 400);
        }

        $ledgerGroup->update($request->validated());

        return response()->json([
            'data' => LedgerGroupResource::make($ledgerGroup),
            'message' => 'Ledger Group Updated Successfully',
        ]);
    }

    public function destroy(LedgerGroup $ledgerGroup)
    {
        $this->checkAuthorization('ledgerGroup_delete');

        if ($ledgerGroup->auto_generated) {
            return response()->json([
                'message' => 'Auto generated ledger group can not be deleted',
            ], 400);
        }

        $ledgerGroup->ledgerGroups()->delete();
        $ledgerGroup->delete();

        return response()->json([
            'data' => '',
            'message' => 'Ledger Group Deleted Successfully',
        ]);
    }

    public function updateStatus(LedgerGroup $ledgerGroup)
    {
        $this->checkAuthorization('ledgerGroup_edit');

        if ($ledgerGroup->auto_generated) {
            return response()->json([
                'message' => 'Auto generated ledger group can not be updated',
            ], 400);
        }

        $ledgerGroup->update([
            'status' => ! $ledgerGroup->status,
        ]);

        return response()->json([
            'status' => $ledgerGroup->status,
            'message' => 'Status Updated Successfully',
        ]);
    }
}
