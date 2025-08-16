<?php

namespace App\Http\Controllers\Api\Inventory;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Inventory\ReceiptRecord\StoreReceiptRecordRequest;
use App\Http\Resources\Inventory\ReceiptRecordResource;
use App\Models\Account\Ledger;
use App\Models\Inventory\ReceiptRecord;
use App\Traits\SmsTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ReceiptRecordController extends Controller
{
    use SmsTrait;

    public function receiptRecordCode()
    {
        return companySetting()->client_payment.Str::padLeft(ReceiptRecord::max('id') + 1, 3, 0);
    }

    public function allReceiptRecords(Request $request)
    {
        $this->checkAuthorization('receiptRecord_access');

        $receiptRecords = ReceiptRecord::with('clientLedger', 'createUser')
            ->filterData($request->all())
            ->latest('receipt_date')
            ->get();

        return ReceiptRecordResource::collection($receiptRecords);
    }

    public function index(Request $request)
    {
        $this->checkAuthorization('receiptRecord_access');

        $receiptRecords = ReceiptRecord::with('clientLedger', 'createUser')
            ->filterData($request->all())
            ->latest('receipt_date')
            ->paginate($request->limit ?? 10);

        return ReceiptRecordResource::collection($receiptRecords);
    }

    public function store(StoreReceiptRecordRequest $request)
    {
        $this->checkAuthorization('receiptRecord_create');

        $receiptRecords = collect();

        DB::transaction(function () use ($request, $receiptRecords) {
            foreach ($request->input('sales') as $sale) {
                if ($sale['amount'] > 0) {
                    $receiptRecord = ReceiptRecord::create([
                        'invoice_no' => $this->receiptRecordCode(),
                        'fiscal_year_id' => runningFiscalYear()->id,
                        'sale_id' => $sale['sale_id'],
                        'client_ledger_id' => $request->input('client_ledger_id'),
                        'payment_method' => $request->input('payment_method'),
                        'cash_bank_ledger_id' => $request->input('payment_method') == 'Bank' ? $request->input('bank_account_ledger_id') : \accountSetting()->cash_ledger_id,
                        'receipt_date' => $request->input('receipt_date'),
                        'amount' => $sale['amount'] ?? 0,
                        'remarks' => $request->input('remarks'),
                        'create_user_id' => auth()->id(),
                    ]);
                    $receiptRecords->push($receiptRecord);
                    //save to journal
                    $journal = $receiptRecord->journal()->create([
                        'fiscal_year_id' => $receiptRecord->fiscal_year_id,
                        'journal_no' => $receiptRecord->invoice_no,
                        'date' => $receiptRecord->receipt_date,
                        'user_id' => auth()->id(),
                        'remarks' => $receiptRecord->remarks,
                    ]);
                    //credit for account receivable/client ledger after payment
                    $journal->journalParticulars()->create([
                        'ledger_id' => $receiptRecord->client_ledger_id,
                        'date' => $journal->date,
                        'dr_amount' => 0,
                        'cr_amount' => $receiptRecord->amount,
                        'remarks' => 'By-'.($receiptRecord->cashBankLedger->ledger_name ?? ''),
                    ]);
                    //debit for cash/bank ledger
                    $journal->journalParticulars()->create([
                        'ledger_id' => $receiptRecord->cash_bank_ledger_id,
                        'date' => $journal->date,
                        'dr_amount' => $receiptRecord->amount,
                        'cr_amount' => 0,
                        'remarks' => 'By-'.($receiptRecord->clientLedger->ledger_name ?? ''),
                    ]);
                }
            }

            if ($request->input('send_sms')) {
                $client = $receiptRecords->first()->clientLedger ?? '';
                if ($client) {
                    $this->sendTextSMS($client->phone, $this->paymentReceivedMessage($client->ledger_name, $receiptRecords->sum('amount')), $client->ledger_name);
                }
            }
        });

        return response()->json([
            'data' => ReceiptRecordResource::collection($receiptRecords),
            'message' => 'Client Payment Added Successfully',
        ], 201);
    }

    public function show(ReceiptRecord $receiptRecord)
    {
        $this->checkAuthorization('receiptRecord_access');

        return ReceiptRecordResource::make($receiptRecord->load('clientLedger', 'createUser', 'cashBankLedger'));
    }

    public function update(Request $request, ReceiptRecord $receiptRecord)
    {
        //
    }

    public function destroy(Request $request, ReceiptRecord $receiptRecord)
    {
        $request->validate([
            'cancel_reason' => ['required', 'max:255'],
        ]);

        DB::transaction(function () use ($request, $receiptRecord) {
            $receiptRecord->journal()->update([
                'is_cancelled' => 1,
            ]);

            $receiptRecord->journal?->journalParticulars()->update([
                'is_cancelled' => 1,
            ]);
            $receiptRecord->update([
                'is_cancelled' => 1,
                'cancel_user_id' => auth()->id(),
                'cancelled_reason' => $request->cancel_reason,
            ]);
        });

        return response()->json([
            'data' => '',
            'message' => 'Client Payment Cancelled Successfully',
        ]);
    }

    private function paymentReceivedMessage($client_name, $amount): string
    {
        return "Dear $client_name, Your due amount Rs. $amount has been received.Thank You, ".companySetting()->company_name;
    }
}
