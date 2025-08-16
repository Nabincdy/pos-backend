<?php

namespace App\Http\Controllers\Api\Inventory;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Inventory\Warehouse\StoreWarehouseRequest;
use App\Http\Requests\Api\Inventory\Warehouse\UpdateWarehouseRequest;
use App\Http\Resources\Inventory\WarehouseResource;
use App\Models\Inventory\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class WarehouseController extends Controller
{
    public function warehouseCode()
    {
        return companySetting()->warehouse.Str::padLeft(Warehouse::max('id') + 1, 3, 0);
    }

    public function index()
    {
        $this->checkAuthorization('warehouse_access');

        $warehouses = Warehouse::with('warehouses')->whereNull('warehouse_id')->get();

        return WarehouseResource::collection($warehouses);
    }

    public function store(StoreWarehouseRequest $request)
    {
        $this->checkAuthorization('warehouse_create');

        $warehouse = Warehouse::create($request->validated());

        return response()->json([
            'data' => new WarehouseResource($warehouse),
            'message' => 'Warehouse Added Successfully',
        ], 201);
    }

    public function show(Warehouse $warehouse)
    {
        $this->checkAuthorization('warehouse_access');

        return new WarehouseResource($warehouse);
    }

    public function update(UpdateWarehouseRequest $request, Warehouse $warehouse)
    {
        $this->checkAuthorization('warehouse_edit');

        $warehouse->update($request->validated());

        return response()->json([
            'data' => new WarehouseResource($warehouse),
            'message' => 'Warehouse Updated Successfully',
        ]);
    }

    public function destroy(Warehouse $warehouse)
    {
        $this->checkAuthorization('warehouse_delete');

        $warehouse->delete();

        return response()->json([
            'data' => '',
            'message' => 'Warehouse Deleted Successfully',
        ]);
    }

    public function stockWarehouses(Request $request)
    {
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
}
