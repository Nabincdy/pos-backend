<?php

namespace App\Http\Controllers\Api\Inventory;

use App\Http\Controllers\Controller;
use App\Http\Resources\Inventory\ProductStockResource;
use App\Http\Resources\Inventory\Report\CategoryStockSummaryResource;
use App\Http\Resources\Inventory\Report\ProductStockSummaryResource;
use App\Http\Resources\Inventory\Report\ProductWisePurchaseResource;
use App\Http\Resources\Inventory\Report\ProductWiseSalesResource;
use App\Http\Resources\Inventory\Report\PurchaseSummaryResource;
use App\Http\Resources\Inventory\Report\SalesSummaryResource;
use App\Models\Inventory\Product;
use App\Models\Inventory\ProductCategory;
use App\Models\Inventory\ProductOpening;
use App\Models\Inventory\ProductStock;
use App\Models\Inventory\Purchase;
use App\Models\Inventory\PurchaseReturn;
use App\Models\Inventory\Sale;
use App\Models\Inventory\SalesReturn;
use App\Models\Inventory\StockAdjustment;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class InventoryReportController extends Controller
{
    public function stockSummaryCategoryWise(Request $request)
    {
        $productCategories = ProductCategory::with([
            'products' => function ($query) use ($request) {
                $query->with(['productStocks' => function ($query) use ($request) {
                    $query->filterData($request->all());
                }]);
            },
            'productCategories.products' => function ($query) use ($request) {
                $query->with(['productStocks' => function ($query) use ($request) {
                    $query->filterData($request->all());
                }]);
            },
        ])
            ->whereNull('product_category_id')
            ->get()->map(function ($cat) {
                if (count($cat->productCategories) > 0) {
                    foreach ($cat->productCategories as $sub_cat) {
                        $this->mapStockQuantities($sub_cat);
                    }
                } else {
                    $this->mapStockQuantities($cat);
                }

                return $cat;
            });

        return CategoryStockSummaryResource::collection($productCategories);
    }

    public function productStockSummary(Request $request, ProductCategory $productCategory)
    {
        $productCategory->load(['products.productStocks' => function ($query) use ($request) {
            $query->filterData($request->all());
        }]);

        return ProductStockSummaryResource::collection($productCategory->products);
    }

    public function productLedger(Request $request, Product $product)
    {
        $validated = $request->validate([
            'from_date' => ['required'],
            'to_date' => ['required', 'after_or_equal:from_date'],
        ]);

        $product->load(['productStocks' => function ($query) use ($validated) {
            $query->filterData($validated);
        }]);

        return response()->json([
            'data' => ProductStockResource::collection($product->productStocks->where('model_type', '!=', ProductOpening::class)),
            'opening_stock' => [
                'rate' => $product->productStocks->where('model_type', ProductOpening::class)->sum('rate'),
                'quantity' => $product->productStocks->where('model_type', ProductOpening::class)->sum('quantity'),
                'amount' => $product->productStocks->where('model_type', ProductOpening::class)->sum('amount'),
            ],
        ]);
    }

    public function expiryProducts(Request $request)
    {
        $productStocks = ProductStock::with('product')->where(function ($query) use ($request) {
            $request->input('expired_type') == 'Upcoming' ? $query->whereDate('en_expiry_date', '>', now()->toDateString()) : $query->whereDate('en_expiry_date', '<=', now()->toDateString());
        })->filterData($request->all())->get();

        $batchNumbers = $productStocks->unique(function ($item) {
            return $item['product_id'] . '-' . $item['batch_no'];
        })->values();

        $productStockBatches = collect([]);

        foreach ($batchNumbers as $batch) {
            $productBatchStock = $productStocks->where('product_id', $batch->product_id)->where('batch_no', $batch->batch_no);
            if ($productBatchStock->sum('quantity') > 0) {
                $productStockBatches->push([
                    'product_code' => $productBatchStock->last()?->product->code ?? '',
                    'product_name' => $productBatchStock->last()?->product->name ?? '',
                    'batch_no' => $batch->batch_no,
                    'expiry_date' => $productBatchStock->last()->expiry_date,
                    'en_expiry_date' => $productBatchStock->last()->en_expiry_date,
                    'stock_quantity' => $productBatchStock->sum('quantity'),
                ]);
            }
        }

        return $productStockBatches;
    }

    public function productWisePurchase(Request $request)
    {
        $validated = $request->validate([
            'from_date' => ['required'],
            'to_date' => ['required', 'after_or_equal:from_date'],
            'product_id' => ['nullable', Rule::exists('products', 'id')->withoutTrashed()],
        ]);

        $productWisePurchases = Product::where(function ($query) use ($validated) {
            if (! empty($validated['product_id'])) {
                $query->where('id', $validated['product_id']);
            }
        })
            ->with(['purchaseParticulars' => function ($query) use ($validated) {
                $query->with('purchase.supplierLedger');
                $query->filterData($validated);
            }])
            ->get();

        return ProductWisePurchaseResource::collection($productWisePurchases);
    }

    public function purchaseSummary(Request $request)
    {
        $validated = $request->validate([
            'from_date' => ['required'],
            'to_date' => ['required', 'after_or_equal:from_date'],
            'supplier_ledger_id' => ['nullable', Rule::exists('ledgers', 'id')->withoutTrashed()],
        ]);

        $purchases = Purchase::with('supplierLedger', 'purchaseParticulars.product', 'purchaseParticulars.unit')
            ->withSum(['paymentRecords' => function ($query) {
                $query->where('is_cancelled', 0);
            }], 'paid_amount')
            ->filterData($validated)
            ->get();

        return PurchaseSummaryResource::collection($purchases);
    }

    public function productWiseSales(Request $request)
    {
        $validated = $request->validate([
            'from_date' => ['required'],
            'to_date' => ['required', 'after_or_equal:from_date'],
            'product_id' => ['nullable', Rule::exists('products', 'id')->withoutTrashed()],
        ]);

        $productWiseSales = Product::where(function ($query) use ($validated) {
            if (! empty($validated['product_id'])) {
                $query->where('id', $validated['product_id']);
            }
        })
            ->with(['saleParticulars' => function ($query) use ($validated) {
                $query->with('sale.clientLedger');
                $query->filterData($validated);
            }])
            ->get();

        return ProductWiseSalesResource::collection($productWiseSales);
    }

    public function salesSummary(Request $request)
    {
        $validated = $request->validate([
            'from_date' => ['required'],
            'to_date' => ['required', 'after_or_equal:from_date'],
            'client_ledger_id' => ['nullable', Rule::exists('ledgers', 'id')->withoutTrashed()],
        ]);

        $sales = Sale::with('clientLedger', 'saleParticulars.product', 'saleParticulars.unit')
            ->withSum(['receiptRecords' => function ($query) {
                $query->where('is_cancelled', 0);
            }], 'amount')
            ->filterData($validated)
            ->get();

        return SalesSummaryResource::collection($sales);
    }

    public function mapStockQuantities(mixed $sub_cat): void
    {
        foreach ($sub_cat->products as $item) {
            $item->opening_quantity_sum = $item->productStocks->where('model_type', ProductOpening::class)->sum('quantity');
            $item->purchase_quantity_sum = $item->productStocks->where('model_type', Purchase::class)->sum('quantity');
            $item->purchase_return_quantity_sum = $item->productStocks->where('model_type', PurchaseReturn::class)->sum('quantity');
            $item->sales_quantity_sum = $item->productStocks->where('model_type', Sale::class)->sum('quantity');
            $item->sales_return_quantity_sum = $item->productStocks->where('model_type', SalesReturn::class)->sum('quantity');
            $item->stock_adjustment_quantity_sum = $item->productStocks->where('model_type', StockAdjustment::class)->sum('quantity');
            $item->stock_quantity_sum = $item->productStocks->sum('quantity');
            $item->amount_sum = $item->productStocks->sum('amount');
        }
    }
}
