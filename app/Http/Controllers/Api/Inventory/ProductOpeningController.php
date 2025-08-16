<?php

namespace App\Http\Controllers\Api\Inventory;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Inventory\ProductOpening\StoreProductOpeningRequest;
use App\Http\Requests\Api\Inventory\ProductOpening\UpdateProductOpeningRequest;
use App\Http\Resources\Inventory\ProductOpeningResource;
use App\Models\Inventory\ProductOpening;
use App\Models\Setting\FiscalYear;
use Illuminate\Support\Facades\DB;

class ProductOpeningController extends Controller
{
    public function index()
    {
        $this->checkAuthorization('productOpening_access');

        return ProductOpeningResource::collection(ProductOpening::with('product', 'warehouse')->filter()->get());
    }

    public function store(StoreProductOpeningRequest $request)
    {
        $this->checkAuthorization('productOpening_create');

        $productDetailData = DB::transaction(function () use ($request) {
            $productDetail = ProductOpening::create($request->validated() + [
                'fiscal_year_id' => FiscalYear::where('is_running', 1)->first()->id,
                'user_id' => auth()->id(),
            ]);
            $productDetail->productStock()->create([
                'fiscal_year_id' => runningFiscalYear()->id,
                'product_id' => $request->input('product_id'),
                'unit_id' => $request->input('unit_id'),
                'warehouse_id' => $request->input('warehouse_id'),
                'quantity' => $request->input('quantity'),
                'rate' => $request->input('rate'),
                'date' => $request->input('opening_date'),
                'en_date' => $request->input('en_opening_date'),
                'batch_no' => $request->input('batch_no'),
                'expiry_date' => $request->input('expiry_date'),
                'en_expiry_date' => $request->input('en_expiry_date'),
            ]);

            return $productDetail;
        });

        return response()->json([
            'data' => new ProductOpeningResource($productDetailData->load('product', 'warehouse')),
            'message' => 'Product Opening Added Successfully',
        ], 201);
    }

    public function show(ProductOpening $productOpening)
    {
        $this->checkAuthorization('productOpening_access');

        return ProductOpeningResource::make($productOpening->load('warehouse', 'product'));
    }

    public function update(UpdateProductOpeningRequest $request, ProductOpening $productOpening)
    {
        $this->checkAuthorization('productOpening_edit');

        DB::transaction(function () use ($request, $productOpening) {
            $productOpening->update($request->validated());
            $productOpening->productStock()->update([
                'product_id' => $request->input('product_id'),
                'unit_id' => $request->input('unit_id'),
                'warehouse_id' => $request->input('warehouse_id'),
                'quantity' => $request->input('quantity'),
                'rate' => $request->input('rate'),
                'date' => $request->input('opening_date'),
                'en_date' => $request->input('en_opening_date'),
                'batch_no' => $request->input('batch_no'),
                'expiry_date' => $request->input('expiry_date'),
                'en_expiry_date' => $request->input('en_expiry_date'),
            ]);

            return $productOpening;
        });

        return response()->json([
            'data' => new ProductOpeningResource($productOpening->load('warehouse', 'product')),
            'message' => 'Product Opening Updated Successfully',
        ]);
    }

    public function destroy(ProductOpening $productOpening)
    {
        $this->checkAuthorization('productOpening_delete');
        $productOpening->productStock()->delete();
        $productOpening->delete();

        return response()->json([
            'data' => '',
            'message' => 'Product Opening Deleted Successfully',
        ], 200);
    }
}
