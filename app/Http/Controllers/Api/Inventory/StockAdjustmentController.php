<?php

namespace App\Http\Controllers\Api\Inventory;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Inventory\StockAdjustment\StoreStockAdjustmentRequest;
use App\Http\Resources\Inventory\StockAdjustmentResource;
use App\Models\Inventory\StockAdjustment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StockAdjustmentController extends Controller
{
    public function index()
    {
        $this->checkAuthorization('stockAdjustment_access');

        $stockAdjustments = StockAdjustment::with('createUser')->filterData()->latest('adjustment_date')->get();

        return StockAdjustmentResource::collection($stockAdjustments);
    }

    public function store(StoreStockAdjustmentRequest $request)
    {
        $this->checkAuthorization('stockAdjustment_create');

        $stockAdjustment = DB::transaction(function () use ($request) {
            $stockAdjustment = StockAdjustment::create($request->validated() + [
                'fiscal_year_id' => runningFiscalYear()->id,
                'create_user_id' => auth()->id(),
            ]);
            foreach ($request->validated()['productStocks'] as $particular) {
                $stockAdjustment->productStocks()->create([
                    'fiscal_year_id' => $stockAdjustment->fiscal_year_id,
                    'product_id' => $particular['product_id'],
                    'unit_id' => $particular['unit_id'],
                    'warehouse_id' => $particular['warehouse_id'],
                    'quantity' => $particular['type'] == 'Out' ? -$particular['quantity'] : $particular['quantity'],
                    'rate' => $particular['rate'],
                    'type' => $particular['type'],
                    'date' => $stockAdjustment->adjustment_date,
                    'remarks' => "Stock Adjustment-$stockAdjustment->reference_no",
                ]);
            }

            return $stockAdjustment;
        });

        return response()->json([
            'data' => StockAdjustmentResource::make($stockAdjustment->load('createUser')),
            'message' => 'Stock Adjustment Created Successfully',
        ], 201);
    }

    public function show(StockAdjustment $stockAdjustment)
    {
        $this->checkAuthorization('stockAdjustment_access');

        $stockAdjustment->load('createUser', 'productStocks.warehouse', 'productStocks.product', 'productStocks.unit');

        return StockAdjustmentResource::make($stockAdjustment);
    }

    public function update(Request $request, StockAdjustment $stockAdjustment)
    {
        $this->checkAuthorization('stockAdjustment_edit');
    }

    public function destroy(StockAdjustment $stockAdjustment)
    {
        $this->checkAuthorization('stockAdjustment_delete');

        $stockAdjustment->delete();
        $stockAdjustment->productStocks()->delete();

        return response()->json([
            'data' => '',
            'message' => 'Stock Adjustment Deleted Successfully',
        ]);
    }
}
