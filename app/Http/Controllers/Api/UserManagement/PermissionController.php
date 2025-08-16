<?php

namespace App\Http\Controllers\Api\UserManagement;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;

class PermissionController extends Controller
{
    public function __invoke(): JsonResponse
    {
        $permissions = Permission::all()->map(function ($permission) {
            $array = explode('_', $permission->title);
            $last = array_pop($array);

            return [
                'id' => $permission->id,
                'name' => Str::headline(implode('', $array)),
                'title' => Str::headline($last),

            ];
        });

        return response()->json([
            'data' => $permissions->groupBy('name'),
            'permissions' => $permissions->pluck('id'),
        ]);
    }
}
