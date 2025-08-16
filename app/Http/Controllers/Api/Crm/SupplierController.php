<?php

namespace App\Http\Controllers\Api\Crm;

use App\Exports\Crm\SupplierExport;
use App\Exports\Crm\SupplierSampleExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Crm\Supplier\ImportSupplierRequest;
use App\Http\Requests\Api\Crm\Supplier\StoreSupplierRequest;
use App\Http\Requests\Api\Crm\Supplier\UpdateSupplierRequest;
use App\Http\Resources\Crm\SupplierResource;
use App\Imports\Crm\SupplierImport;
use App\Models\Account\Ledger;
use App\Models\Crm\Supplier;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;

class SupplierController extends Controller
{
    public function supplierCode()
    {
        return companySetting()->supplier.Str::padLeft(Supplier::max('id') + 1, 3, 0);
    }

    public function index()
    {
        $this->checkAuthorization('supplier_access');

        $suppliers = Supplier::with('company')->get();

        return SupplierResource::collection($suppliers);
    }

    public function store(StoreSupplierRequest $request)
    {
        $this->checkAuthorization('supplier_create');

        if (empty(\accountSetting()->supplier_ledger_group_id)) {
            abort(400, 'Supplier ledger group not mapped in account setting');
        }

        $supplier = DB::transaction(function () use ($request) {
            $ledger = Ledger::create([
                'ledger_group_id' => \accountSetting()->supplier_ledger_group_id,
                'ledger_name' => $request->input('supplier_name'),
                'code' => $request->input('code'),
                'category' => 'Supplier',
                'phone' => $request->input('phone'),
                'email' => $request->input('email'),
                'pan_no' => $request->input('pan_no'),
                'address' => $request->input('address'),
                'auto_generated' => true,
            ]);

            return Supplier::create($request->validated() + [
                'ledger_id' => $ledger->id,
            ]);
        });

        return response()->json([
            'data' => new SupplierResource($supplier->load('company')),
            'message' => 'Supplier Added Successfully',
        ], 201);
    }

    public function show(Supplier $supplier)
    {
        $this->checkAuthorization('supplier_access');

        return new SupplierResource($supplier->load('company'));
    }

    public function update(UpdateSupplierRequest $request, Supplier $supplier)
    {
        $this->checkAuthorization('supplier_edit');

        if (empty(\accountSetting()->supplier_ledger_group_id)) {
            abort(400, 'Supplier ledger group not mapped in account setting');
        }

        DB::transaction(function () use ($request, $supplier) {
            if ($request->hasFile('profile_photo') && $supplier->profile_photo) {
                $this->deleteFile($supplier->profile_photo);
            }

            $supplier->update($request->validated());

            $supplier->ledger()->update([
                'ledger_group_id' => \accountSetting()->ledger_group_id,
                'ledger_name' => $request->input('supplier_name'),
                'code' => $request->input('code'),
                'category' => 'Supplier',
                'phone' => $request->input('phone'),
                'email' => $request->input('email'),
                'pan_no' => $request->input('pan_no'),
                'address' => $request->input('address'),
            ]);
        });

        return response()->json([
            'data' => new SupplierResource($supplier->load('company')),
            'message' => 'Supplier Updated Successfully',
        ]);
    }

    public function destroy(Supplier $supplier)
    {
        $this->checkAuthorization('supplier_delete');

        if ($supplier->profile_photo) {
            $this->deleteFile($supplier->profile_photo);
        }

        $supplier->ledger()->delete();
        $supplier->delete();

        return response()->json([
            'data' => '',
            'message' => 'Supplier Deleted Successfully',
        ]);
    }

    public function downloadSample()
    {
        return Excel::download(new SupplierSampleExport(), 'supplier_entry_format.xlsx');
    }

    public function import(ImportSupplierRequest $request)
    {
        $this->checkAuthorization('supplier_create');

        Excel::import(new SupplierImport($request), $request->file('excel_file'));

        return response()->json([
            'data' => '',
            'message' => 'Supplier Imported Successfully',
        ]);
    }

    public function export()
    {
        $this->checkAuthorization('supplier_access');

        $suppliers = Supplier::with('company')->get();

        return Excel::download(new SupplierExport($suppliers), 'suppliers.xlsx');
    }
}
