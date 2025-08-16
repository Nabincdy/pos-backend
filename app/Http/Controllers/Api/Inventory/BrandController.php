<?php

namespace App\Http\Controllers\Api\Inventory;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Inventory\Brand\StoreBrandRequest;
use App\Http\Requests\Api\Inventory\Brand\UpdateBrandRequest;
use App\Http\Resources\Inventory\BrandResource;
use App\Models\Inventory\Brand;

class BrandController extends Controller
{
    public function index()
    {
        $this->checkAuthorization('brand_access');

        return BrandResource::collection(Brand::all());
    }

    public function store(StoreBrandRequest $request)
    {
        $this->checkAuthorization('brand_create');

        $brand = Brand::create($request->validated());

        return response()->json([
            'data' => new BrandResource($brand),
            'message' => 'Brand Added Successfully',
        ], 201);
    }

    public function show(Brand $brand)
    {
        $this->checkAuthorization('brand_access');

        return new BrandResource($brand);
    }

    public function update(UpdateBrandRequest $request, Brand $brand)
    {
        $this->checkAuthorization('brand_edit');

        if ($request->hasFile('logo') && $brand->logo) {
            $this->deleteFile($brand->logo);
        }

        $brand->update($request->validated());

        return response()->json([
            'data' => new BrandResource($brand),
            'message' => 'Brand Updated Successfully',
        ]);
    }

    public function destroy(Brand $brand)
    {
        $this->checkAuthorization('brand_delete');

        if ($brand->logo) {
            $this->deleteFile($brand->logo);
        }

        $brand->delete();

        return response()->json([
            'data' => '',
            'message' => 'Brand Deleted Successfully',
        ]);
    }
}
