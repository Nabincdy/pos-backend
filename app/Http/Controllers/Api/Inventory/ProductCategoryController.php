<?php

namespace App\Http\Controllers\Api\Inventory;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Inventory\ProductCategory\StoreProductCategoryRequest;
use App\Http\Requests\Api\Inventory\ProductCategory\UpdateProductCategoryRequest;
use App\Http\Resources\Inventory\ProductCategoryResource;
use App\Models\Inventory\ProductCategory;
use Illuminate\Support\Str;

class ProductCategoryController extends Controller
{
    public function productCategoryCode()
    {
        return companySetting()->product_category.Str::padLeft(ProductCategory::max('id') + 1, 3, 0);
    }

    public function index()
    {
        $this->checkAuthorization('productCategory_access');

        $productCategories = ProductCategory::with('productCategories')->whereNull('product_category_id')->get();

        return ProductCategoryResource::collection($productCategories);
    }

    public function store(StoreProductCategoryRequest $request)
    {
        $this->checkAuthorization('productCategory_create');

        $productCategory = ProductCategory::create($request->validated());

        return response()->json([
            'data' => new ProductCategoryResource($productCategory),
            'message' => 'Category Added Successfully',
        ], 201);
    }

    public function show(ProductCategory $productCategory)
    {
        $this->checkAuthorization('productCategory_access');

        return new ProductCategoryResource($productCategory);
    }

    public function update(UpdateProductCategoryRequest $request, ProductCategory $productCategory)
    {
        $this->checkAuthorization('productCategory_edit');

        if ($request->hasFile('image') && $productCategory->image) {
            $this->deleteFile($productCategory->image);
        }

        $productCategory->update($request->validated());

        return response()->json([
            'data' => new ProductCategoryResource($productCategory),
            'message' => 'Category Updated Successfully',
        ]);
    }

    public function destroy(ProductCategory $productCategory)
    {
        $this->checkAuthorization('productCategory_delete');

        $productCategory->productCategories()->delete();
        if ($productCategory->image) {
            $this->deleteFile($productCategory->image);
        }
        $productCategory->delete();

        return response()->json([
            'data' => '',
            'message' => 'Category Deleted Successfully',
        ]);
    }
}
