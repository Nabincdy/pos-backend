<?php

namespace App\Http\Controllers\Api\Hr;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Hr\Department\StoreDepartmentRequest;
use App\Http\Requests\Api\Hr\Department\UpdateDepartmentRequest;
use App\Http\Resources\Hr\DepartmentResource;
use App\Models\Hr\Department;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class DepartmentController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $this->checkAuthorization('department_access');

        return DepartmentResource::collection(Department::all());
    }

    public function store(StoreDepartmentRequest $request): JsonResponse
    {
        $this->checkAuthorization('department_create');
        $department = Department::create($request->validated());

        return response()->json([
            'data' => new DepartmentResource($department),
            'message' => 'Department Added Successfully',
        ], 201);
    }

    public function show(Department $department): DepartmentResource
    {
        $this->checkAuthorization('department_access');

        return DepartmentResource::make($department);
    }

    public function update(UpdateDepartmentRequest $request, Department $department): JsonResponse
    {
        $this->checkAuthorization('department_edit');

        $department->update($request->validated());

        return response()->json([
            'data' => new DepartmentResource($department),
            'message' => 'Department Updated Successfully',
        ]);
    }

    public function destroy(Department $department): JsonResponse
    {
        $this->checkAuthorization('department_delete');

        $department->delete();

        return response()->json([
            'data' => '',
            'message' => 'Department deleted successfully',
        ]);
    }
}
