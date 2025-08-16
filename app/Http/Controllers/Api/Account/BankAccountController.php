<?php

namespace App\Http\Controllers\Api\Account;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Account\BankAccount\StoreBankAccountRequest;
use App\Http\Requests\Api\Account\BankAccount\UpdateBankAccountRequest;
use App\Http\Resources\Account\BankAccountResource;
use App\Models\Account\BankAccount;
use App\Models\Account\Ledger;
use App\Models\Setting\Bank;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class BankAccountController extends Controller
{
    public function bankAccountCode()
    {
        return companySetting()->bank_account.Str::padLeft(BankAccount::max('id') + 1, 3, 0);
    }

    public function index()
    {
        $this->checkAuthorization('bankAccount_access');

        $bankAccounts = BankAccount::with('bank')->get();

        return BankAccountResource::collection($bankAccounts);
    }

    public function store(StoreBankAccountRequest $request)
    {
        $this->checkAuthorization('bankAccount_create');

        $bankAccount = DB::transaction(function () use ($request) {
            $bank = Bank::with('ledger')->find($request->input('bank_id'));
            $ledger = Ledger::create([
                'ledger_group_id' => $bank->ledger->ledger_group_id ?? null,
                'ledger_id' => $bank->ledger_id ?? null,
                'ledger_name' => $request->input('account_name'),
                'code' => $request->input('code'),
                'category' => 'Bank Account',
                'auto_generated' => true,
            ]);
            if ($request->input('opening_balance') > 0) {
                $ledger->accountOpeningBalances()->create([
                    'fiscal_year_id' => runningFiscalYear()->id,
                    'dr_amount' => $request->input('amount_type') == 'Dr' ? $request->input('opening_balance') : 0,
                    'cr_amount' => $request->input('amount_type') == 'Cr' ? $request->input('opening_balance') : 0,
                ]);
            }

            return BankAccount::create($request->validated() + [
                'ledger_id' => $ledger->id,
            ]);
        });

        return response()->json([
            'data' => new BankAccountResource($bankAccount->load('bank')),
            'message' => 'Bank Account Added Successfully',
        ], 201);
    }

    public function show(BankAccount $bankAccount)
    {
        $this->checkAuthorization('bankAccount_access');

        return BankAccountResource::make($bankAccount->load('bank'));
    }

    public function update(UpdateBankAccountRequest $request, BankAccount $bankAccount)
    {
        $this->checkAuthorization('bankAccount_edit');

        DB::transaction(function () use ($request, $bankAccount) {
            $bank = Bank::with('ledger')->find($request->input('bank_id'));

            $bankAccount->update($request->validated());

            $bankAccount->ledger()->update([
                'ledger_group_id' => $bank->ledger->ledger_group_id ?? null,
                'ledger_id' => $bank->ledger_id ?? null,
                'ledger_name' => $request->input('account_name'),
                'code' => $request->input('code'),
            ]);
        });

        return response()->json([
            'data' => new BankAccountResource($bankAccount->load('bank')),
            'message' => 'Bank Account Updated Successfully',
        ]);
    }

    public function destroy(BankAccount $bankAccount)
    {
        $this->checkAuthorization('bankAccount_delete');

        $bankAccount->ledger()->delete();
        $bankAccount->delete();

        return response()->json([
            'data' => '',
            'message' => 'Bank Account Deleted Successfully',
        ]);
    }
}
