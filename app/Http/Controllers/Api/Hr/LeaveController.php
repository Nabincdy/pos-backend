<?php

namespace App\Http\Controllers\Api\Hr;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Hr\Leave\StoreLeaveRequest;
use App\Http\Requests\Api\Hr\Leave\UpdateLeaveRequest;
use App\Http\Resources\Hr\LeaveResource;
use App\Models\Hr\Leave;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class LeaveController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $this->checkAuthorization('leave_access');

        return LeaveResource::collection(Leave::with('leaveType', 'employee')->get());
    }

    public function store(StoreLeaveRequest $request): JsonResponse
    {
        $this->checkAuthorization('leave_create');
        $leave = Leave::create($request->validated() + [
            'submit_user_id' => auth()->id(),
        ]);

        return response()->json([
            'data' => new LeaveResource($leave),
            'message' => 'Leave added successfully',
        ], 201);
    }

    public function show(Leave $leave): LeaveResource
    {
        $this->checkAuthorization('leave_access');

        return LeaveResource::make($leave);
    }

    public function update(UpdateLeaveRequest $request, Leave $leave): JsonResponse
    {
        $this->checkAuthorization('leave_edit');
        $leave->update($request->validated());

        return response()->json([
            'data' => new LeaveResource($leave),
            'message' => 'Leave updated successfully',
        ]);
    }

    public function destroy(Leave $leave): JsonResponse
    {
        $this->checkAuthorization('leave_delete');
        $leave->delete();

        return response()->json([
            'data' => '',
            'message' => 'Leave deleted successfully',
        ], 200);
    }
}
