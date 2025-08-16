<?php

namespace App\Http\Controllers\Api\Setting;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Setting\Bank\StoreBankRequest;
use App\Http\Requests\Api\Setting\Bank\UpdateBankRequest;
use App\Http\Resources\Setting\BankResource;
use App\Models\Account\Ledger;
use App\Models\Setting\Bank;
use Illuminate\Support\Facades\DB;

class BankController extends Controller
{
    public function index()
    {
        $this->checkAuthorization('bank_access');

        $banks = Bank::with('bankAccounts')->get();

        return BankResource::collection($banks);
    }

    public function store(StoreBankRequest $request)
    {
        $this->checkAuthorization('bank_create');

        if (empty(\accountSetting()->bank_ledger_group_id)) {
            abort(400, 'Bank Account ledger group not mapped in account setting');
        }

        $bank = DB::transaction(function () use ($request) {
            $ledger = Ledger::create([
                'ledger_group_id' => \accountSetting()->bank_ledger_group_id,
                'ledger_name' => $request->input('name'),
                'code' => $request->input('code'),
                'category' => 'Bank',
                'auto_generated' => true,
            ]);

            return Bank::create($request->validated() + [
                'ledger_id' => $ledger->id,
            ]);
        });

        return response()->json([
            'data' => new BankResource($bank),
            'message' => 'Bank Added Successfully',
        ], 201);
    }

    public function show(Bank $bank)
    {
        $this->checkAuthorization('bank_access');

        return BankResource::make($bank);
    }

    public function update(UpdateBankRequest $request, Bank $bank)
    {
        $this->checkAuthorization('bank_edit');

        if (empty(\accountSetting()->bank_ledger_group_id)) {
            abort(400, 'Bank Account ledger group not mapped in account setting');
        }

        DB::transaction(function () use ($request, $bank) {
            $bank->update($request->validated());

            $bank->ledger()->update([
                'ledger_group_id' => \accountSetting()->bank_ledger_group_id,
                'ledger_name' => $request->input('name'),
                'code' => $request->input('code'),
            ]);
        });

        return response()->json([
            'data' => new BankResource($bank),
            'message' => 'Bank Updated Successfully',
        ]);
    }

    public function destroy(Bank $bank)
    {
        $this->checkAuthorization('bank_delete');

        $bank->ledger()->delete();
        $bank->bankAccounts()->delete();
        $bank->delete();

        return response()->json([
            'data' => '',
            'message' => 'Bank Deleted Successfully',
        ]);
    }
}
