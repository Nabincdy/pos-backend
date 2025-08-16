<?php

namespace App\Http\Controllers\Api\Inventory;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Inventory\Unit\StoreUnitRequest;
use App\Http\Requests\Api\Inventory\Unit\UpdateUnitRequest;
use App\Http\Resources\Inventory\UnitResource;
use App\Models\Inventory\Unit;

class UnitController extends Controller
{
    public function index()
    {
        $this->checkAuthorization('unit_access');

        return UnitResource::collection(Unit::all());
    }

    public function store(StoreUnitRequest $request)
    {
        $this->checkAuthorization('unit_create');

        $unit = Unit::create($request->validated());

        return response()->json([
            'data' => new UnitResource($unit),
            'message' => 'Unit Added Successfully',
        ], 201);
    }

    public function show(Unit $unit)
    {
        $this->checkAuthorization('unit_access');

        return new UnitResource($unit);
    }

    public function update(UpdateUnitRequest $request, Unit $unit)
    {
        $this->checkAuthorization('unit_edit');

        $unit->update($request->validated());

        return response()->json([
            'data' => new UnitResource($unit),
            'message' => 'Unit Updated Successfully',
        ]);
    }

    public function destroy(Unit $unit)
    {
        $this->checkAuthorization('unit_delete');

        $unit->delete();

        return response()->json([
            'data' => '',
            'message' => 'Unit Deleted Successfully',
        ]);
    }
}
