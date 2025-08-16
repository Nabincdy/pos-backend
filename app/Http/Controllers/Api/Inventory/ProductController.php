<?php

namespace App\Http\Controllers\Api\Inventory;

use App\Exports\Inventory\ProductExport;
use App\Exports\Inventory\ProductSampleExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Inventory\Product\StoreProductRequest;
use App\Http\Requests\Api\Inventory\Product\UpdateProductRequest;
use App\Http\Resources\Inventory\ProductResource;
use App\Http\Resources\Inventory\WarehouseResource;
use App\Imports\Inventory\ProductImport;
use App\Models\Inventory\Product;
use App\Models\Inventory\ProductStock;
use App\Models\Inventory\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;

class ProductController extends Controller
{
    public function productCode()
    {
        return companySetting()->product . Str::padLeft(Product::max('id') + 1, 3, 0);
    }

    public function allProducts(Request $request)
    {
        $this->checkAuthorization('product_access');

        $products = Product::withSum(['productStocks' => function ($query) use ($request) {
            $query->filterData($request->all());
        }], 'quantity')
            ->with('productCategory', 'unit')
            ->filterData($request->all())
            ->get();

        return ProductResource::collection($products);
    }

    public function warehouseWiseProductStocks(Request $request)
    {
        $this->checkAuthorization('product_access');

        $warehouses = Warehouse::withSum(['productStocks' => function ($query) use ($request) {
            $query->filterData($request->all());
        }], 'quantity')->with(['warehouses' => function ($query) use ($request) {
            $query->withSum(['productStocks' => function ($q) use ($request) {
                $q->filterData($request->all());
            }], 'quantity');
        }])
            ->whereNull('warehouse_id')
            ->get();

        return WarehouseResource::collection($warehouses);
    }

    public function outOfStockProducts()
    {
        $this->checkAuthorization('product_access');

        $products = Product::withSum(['productStocks' => function ($query) {
            $query->filterData();
        }], 'quantity')
            ->whereHas('productStocks', function ($query) {
                $query->filterData();
            })
            ->with('unit')
            ->get()->filter(function ($product) {
                return $product->product_stocks_sum_quantity < $product->reorder_quantity;
            });

        return ProductResource::collection($products);
    }

    public function index(Request $request)
    {
        $this->checkAuthorization('product_access');

        $products = Product::withSum(['productStocks' => function ($query) {
            $query->filterData();
        }], 'quantity')
            ->with('productCategory', 'unit')
            ->filterData($request->all())
            ->paginate($request->limit ?? 10);

        return ProductResource::collection($products);
    }

    public function store(StoreProductRequest $request)
    {
        $this->checkAuthorization('product_create');

        $product = Product::create($request->validated());

        return response()->json([
            'data' => new ProductResource(
                $product->loadSum('productStocks', 'quantity')->load('productCategory', 'unit')
            ),
            'message' => 'Product Added Successfully',
        ], 201);
    }

    public function show(Product $product)
    {
        $this->checkAuthorization('product_access');

        $product->loadSum(['productStocks' => function ($query) {
            $query->filterData();
        }], 'quantity')
            ->load('productCategory', 'unit');

        return new ProductResource($product->load('productCategory'));
    }

    public function update(UpdateProductRequest $request, Product $product)
    {
        $this->checkAuthorization('product_edit');

        if ($request->hasFile('image') && $product->image) {
            $this->deleteFile($product->image);
        }

        $product->update($request->validated());

        return response()->json([
            'data' => new ProductResource($product->load('productCategory', 'unit')),
            'message' => 'Product Updated Successfully',
        ]);
    }

    public function destroy(Product $product)
    {
        $this->checkAuthorization('product_delete');

        if ($product->image) {
            $this->deleteFile($product->image);
        }
        $product->delete();

        return response()->json([
            'data' => '',
            'message' => 'Product Deleted Successfully',
        ]);
    }

    public function downloadSample()
    {
        return Excel::download(new ProductSampleExport(), 'product_entry_format.xlsx');
    }

    public function import(Request $request)
    {
        $this->checkAuthorization('product_create');

        $request->validate([
            'excel_file' => ['required', 'file', 'mimes:xlsx,txt'],
        ]);

        Excel::import(new ProductImport($request), $request->file('excel_file'));

        return response()->json([
            'data' => '',
            'message' => 'Products Imported Successfully',
        ]);
    }

    public function export()
    {
        $this->checkAuthorization('product_access');

        $products = Product::with('productCategory', 'unit', 'brand', 'purchaseTax', 'salesTax')->get();

        return Excel::download(new ProductExport($products), 'products.xlsx');
    }

    public function productStockBatches(Product $product)
    {
        $productStocks = ProductStock::whereNotNull('batch_no')->filterData(['product_id' => $product->id])->get();
        $batchNumbers = ProductStock::whereNotNull('batch_no')->filterData(['product_id' => $product->id])->distinct('batch_no')->pluck('batch_no');

        $productStockBatches = collect([]);

        foreach ($batchNumbers as $batchNumber) {
            $productBatchStock = $productStocks->where('batch_no', $batchNumber);
            if ($productBatchStock->sum('quantity') > 0) {
                $productStockBatches->push([
                    'batch_no' => $batchNumber,
                    'expiry_date' => $productBatchStock->last()->expiry_date,
                    'en_expiry_date' => $productBatchStock->last()->en_expiry_date,
                    'stock_quantity' => $productBatchStock->sum('quantity'),
                ]);
            }
        }

        return $productStockBatches;
    }
}
