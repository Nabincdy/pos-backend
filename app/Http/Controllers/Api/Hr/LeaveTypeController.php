<?php

namespace App\Http\Controllers\Api\Hr;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Hr\LeaveType\StoreLeaveTypeRequest;
use App\Http\Requests\Api\Hr\LeaveType\UpdateLeaveTypeRequest;
use App\Http\Resources\Hr\LeaveTypeResource;
use App\Models\Hr\LeaveType;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class LeaveTypeController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $this->checkAuthorization('leaveType_access');

        return LeaveTypeResource::collection(LeaveType::all());
    }

    public function store(StoreLeaveTypeRequest $request): JsonResponse
    {
        $this->checkAuthorization('leaveType_create');
        $leaveType = LeaveType::create($request->validated());

        return response()->json([
            'data' => new LeaveTypeResource($leaveType),
            'message' => 'Leave type added successfully',
        ], 201);
    }

    public function show(LeaveType $leaveType): LeaveTypeResource
    {
        $this->checkAuthorization('leaveType_access');

        return LeaveTypeResource::make($leaveType);
    }

    public function update(UpdateLeaveTypeRequest $request, LeaveType $leaveType): JsonResponse
    {
        $this->checkAuthorization('leaveType_edit');
        $leaveType->update($request->validated());

        return response()->json([
            'data' => new LeaveTypeResource($leaveType),
            'message' => 'Leave type updated successfully',
        ]);
    }

    public function destroy(LeaveType $leaveType): JsonResponse
    {
        $this->checkAuthorization('leaveType_delete');
        $leaveType->delete();

        return response()->json([
            'data' => '',
            'message' => 'Leave type deleted successfully',
        ], 200);
    }
}
