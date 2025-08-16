<?php

namespace App\Http\Controllers\Api\Account;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Account\AccountOpeningBalance\StoreAccountOpeningBalanceRequest;
use App\Http\Requests\Api\Account\AccountOpeningBalance\UpdateAccountOpeningBalanceRequest;
use App\Http\Resources\Account\AccountOpeningBalanceResource;
use App\Models\Account\AccountOpeningBalance;
use App\Models\Setting\FiscalYear;

class AccountOpeningBalanceController extends Controller
{
    public function index()
    {
        $this->checkAuthorization('accountOpeningBalance_access');

        return AccountOpeningBalanceResource::collection(AccountOpeningBalance::with('ledger')->filterData()->get());
    }

    public function store(StoreAccountOpeningBalanceRequest $request)
    {
        $this->checkAuthorization('accountOpeningBalance_create');
        $accountOpeningBalance = AccountOpeningBalance::create($request->validated() + [
            'fiscal_year_id' => FiscalYear::where('is_running', 1)->first()->id,
            'dr_amount' => $request->input('amount_type') === 'Dr' ? $request->input('amount') : 0,
            'cr_amount' => $request->input('amount_type') === 'Cr' ? $request->input('amount') : 0,
        ]);

        return response()->json([
            'data' => new AccountOpeningBalanceResource($accountOpeningBalance->load('ledger')),
            'message' => 'Opening Balance Added Successfully',
        ], 201);
    }

    public function show(AccountOpeningBalance $accountOpeningBalance)
    {
        $this->checkAuthorization('accountOpeningBalance_access');

        return AccountOpeningBalanceResource::make($accountOpeningBalance->load('ledger'));
    }

    public function update(UpdateAccountOpeningBalanceRequest $request, AccountOpeningBalance $accountOpeningBalance)
    {
        $this->checkAuthorization('accountOpeningBalance_edit');

        $accountOpeningBalance->update($request->validated() + [
            'dr_amount' => $request->input('amount_type') === 'Dr' ? $request->input('amount') : 0,
            'cr_amount' => $request->input('amount_type') === 'Cr' ? $request->input('amount') : 0,
        ]);

        return response()->json([
            'data' => new AccountOpeningBalanceResource($accountOpeningBalance->load('ledger')),
            'message' => 'Opening Balance Updated Successfully',
        ]);
    }

    public function destroy(AccountOpeningBalance $accountOpeningBalance)
    {
        $this->checkAuthorization('accountOpeningBalance_delete');

        $accountOpeningBalance->delete();

        return response()->json([
            'data' => '',
            'message' => 'Opening Balance Deleted Successfully',
        ]);
    }
}
