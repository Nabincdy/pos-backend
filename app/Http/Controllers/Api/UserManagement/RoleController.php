<?php

namespace App\Http\Controllers\Api\UserManagement;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\UserManagement\Role\StoreRoleRequest;
use App\Http\Requests\Api\UserManagement\Role\UpdateRoleRequest;
use App\Http\Resources\UserManagement\RoleResource;
use App\Models\Role;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;

class RoleController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $this->checkAuthorization('role_access');

        return RoleResource::collection(Role::with('permissions')->get());
    }

    public function store(StoreRoleRequest $request): JsonResponse
    {
        $this->checkAuthorization('role_create');

        $roleData = DB::transaction(function () use ($request) {
            $role = Role::create($request->validated());
            $role->permissions()->attach($request->input('permissions'));

            return $role;
        });

        return response()->json([
            'data' => new RoleResource($roleData->load('permissions')),
            'message' => 'Role added successfully',
        ], 201);
    }

    public function show(Role $role): RoleResource
    {
        $this->checkAuthorization('role_access');

        return RoleResource::make($role->load('permissions'));
    }

    public function update(UpdateRoleRequest $request, Role $role): JsonResponse
    {
        $this->checkAuthorization('role_edit');

        $role = DB::transaction(function () use ($request, $role) {
            $role->update($request->validated());
            $role->permissions()->sync($request->input('permissions'));

            return $role;
        });

        return response()->json([
            'data' => new RoleResource($role->load('permissions')),
            'message' => 'Role Updated successfully',
        ]);
    }

    public function destroy(Role $role): JsonResponse
    {
        $this->checkAuthorization('role_delete');

        $role->permissions()->detach();
        $role->delete();

        return response()->json([
            'data' => '',
            'message' => 'Role deleted successfully',
        ]);
    }
}
