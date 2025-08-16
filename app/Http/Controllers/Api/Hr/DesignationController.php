<?php

namespace App\Http\Controllers\Api\Hr;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Hr\Designation\StoreDesignationRequest;
use App\Http\Requests\Api\Hr\Designation\UpdateDesignationRequest;
use App\Http\Resources\Hr\DesignationResource;
use App\Models\Hr\Designation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class DesignationController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $this->checkAuthorization('designation_access');

        return DesignationResource::collection(Designation::all());
    }

    public function store(StoreDesignationRequest $request): JsonResponse
    {
        $this->checkAuthorization('designation_create');

        $designation = Designation::create($request->validated());

        return response()->json([
            'data' => new DesignationResource($designation),
            'message' => 'Designation Added Successfully',
        ], 201);
    }

    public function show(Designation $designation): DesignationResource
    {
        $this->checkAuthorization('designation_access');

        return DesignationResource::make($designation);
    }

    public function update(UpdateDesignationRequest $request, Designation $designation): JsonResponse
    {
        $this->checkAuthorization('designation_edit');

        $designation->update($request->validated());

        return response()->json([
            'data' => new DesignationResource($designation),
            'message' => 'Designation updated successfully',
        ]);
    }

    public function destroy(Designation $designation): JsonResponse
    {
        $this->checkAuthorization('designation_delete');

        $designation->delete();

        return response()->json([
            'data' => '',
            'message' => 'Designation deleted successfully',
        ], 200);
    }
}
