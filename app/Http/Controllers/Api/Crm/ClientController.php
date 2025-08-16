<?php

namespace App\Http\Controllers\Api\Crm;

use App\Exports\Crm\ClientExport;
use App\Exports\Crm\ClientSampleExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Crm\Client\ImportClientRequest;
use App\Http\Requests\Api\Crm\Client\StoreClientRequest;
use App\Http\Requests\Api\Crm\Client\UpdateClientRequest;
use App\Http\Resources\Crm\ClientResource;
use App\Imports\Crm\ClientImport;
use App\Models\Account\Ledger;
use App\Models\Crm\Client;
use App\Traits\SmsTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;

class ClientController extends Controller
{
    use SmsTrait;

    public function clientCode()
    {
        return companySetting()->client.Str::padLeft(Client::max('id') + 1, 3, 0);
    }

    public function index()
    {
        $this->checkAuthorization('client_access');

        $clients = Client::with('clientGroup', 'company')->get();

        return ClientResource::collection($clients);
    }

    public function store(StoreClientRequest $request)
    {
        $this->checkAuthorization('client_create');

        if (empty(\accountSetting()->client_ledger_group_id)) {
            abort(400, 'Client ledger group not mapped in account setting');
        }

        $client = DB::transaction(function () use ($request) {
            $ledger = Ledger::create([
                'ledger_group_id' => \accountSetting()->client_ledger_group_id,
                'ledger_name' => $request->input('name'),
                'code' => $request->input('code'),
                'category' => 'Client',
                'phone' => $request->input('phone'),
                'email' => $request->input('email'),
                'pan_no' => $request->input('pan_no'),
                'address' => $request->input('address'),
                'auto_generated' => true,
            ]);

            return Client::create($request->validated() + [
                'ledger_id' => $ledger->id,
            ]);
        });

        return response()->json([
            'data' => new ClientResource($client->load('clientGroup', 'company')),
            'message' => 'Client Added Successfully',
        ], 201);
    }

    public function show(Client $client)
    {
        $this->checkAuthorization('client_access');

        return new ClientResource($client->load('clientGroup', 'company'));
    }

    public function update(UpdateClientRequest $request, Client $client)
    {
        $this->checkAuthorization('client_edit');

        if (empty(\accountSetting()->client_ledger_group_id)) {
            abort(400, 'Client ledger group not mapped in account setting');
        }

        DB::transaction(function () use ($request, $client) {
            if ($request->hasFile('profile_photo') && $client->profile_photo) {
                $this->deleteFile($client->profile_photo);
            }
            $client->update($request->validated());

            $client->ledger()->update([
                'ledger_group_id' => \accountSetting()->client_ledger_group_id,
                'ledger_name' => $request->input('name'),
                'code' => $request->input('code'),
                'category' => 'Supplier',
                'phone' => $request->input('phone'),
                'email' => $request->input('email'),
                'pan_no' => $request->input('pan_no'),
                'address' => $request->input('address'),
            ]);
        });

        return response()->json([
            'data' => new ClientResource($client->load('clientGroup', 'company')),
            'message' => 'Client Updated Successfully',
        ]);
    }

    public function destroy(Client $client)
    {
        $this->checkAuthorization('client_delete');

        if ($client->profile_photo) {
            $this->deleteFile($client->profile_photo);
        }
        $client->ledger()->delete();
        $client->delete();

        return response()->json([
            'data' => '',
            'message' => 'Client Deleted Successfully',
        ]);
    }

    public function downloadSample()
    {
        return Excel::download(new ClientSampleExport(), 'client_entry_format.xlsx');
    }

    public function import(ImportClientRequest $request)
    {
        $this->checkAuthorization('client_create');

        Excel::import(new ClientImport($request), $request->file('excel_file'));

        return response()->json([
            'data' => '',
            'message' => 'Clients Imported Successfully',
        ]);
    }

    public function export()
    {
        $this->checkAuthorization('client_access');

        $clients = Client::with('clientGroup', 'company')->get();

        return Excel::download(new ClientExport($clients), 'clients.xlsx');
    }

    public function clientDueReport()
    {
        $this->checkAuthorization('client_access');

        $clients = Client::with(['ledger.sales' => function ($query) {
            $query->with('saleParticulars');
            $query->withSum(['receiptRecords' => function ($q) {
                $q->where('is_cancelled', 0);
            }], 'amount');
            $query->where('is_cancelled', 0);
        }])->get()->map(function ($client) {
            $sales_amount_sum = $client->ledger?->sales->reduce(function ($sum, $sale) {
                return $sum + $sale->saleParticulars->sum('total_amount');
            }, 0);

            return [
                'ledger_id' => $client->ledger_id,
                'code' => $client->code,
                'name' => $client->name,
                'phone' => $client->phone,
                'due_amount' => $sales_amount_sum - ($client->ledger?->sales->sum('receipt_records_sum_amount') ?? 0),
            ];
        });

        return $clients;
    }

    public function sendPaymentReminderSms(Request $request)
    {
        $request->validate([
            'clients' => ['required', 'array'],
            'clients.*.name' => ['required', 'string', 'max:255'],
            'clients.*.phone' => ['required'],
            'clients.*.due_amount' => ['required', 'gt:0', 'numeric'],
        ]);

        foreach ($request->input('clients') as $client) {
            $this->sendTextSMS($client['phone'], $this->paymentReminderMessage($client['name'], $client['due_amount']), $client['name']);
        }

        return response()->json([
            'data' => '',
            'message' => 'Payment Reminder Sms Sent Successfully',
        ]);
    }

    private function paymentReminderMessage($client_name, $amount): string
    {
        return "Dear $client_name, Your due amount is Rs. $amount.Please pay on time. ".companySetting()->company_name;
    }
}
