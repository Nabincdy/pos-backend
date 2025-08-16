<?php

namespace App\Http\Controllers\Api\UserManagement;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\UserManagement\User\StoreUserRequest;
use App\Http\Requests\Api\UserManagement\User\UpdateUserRequest;
use App\Http\Resources\UserManagement\UserResource;
use App\Models\User;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class UserController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $this->checkAuthorization('user_access');

        return UserResource::collection(User::with('role')->whereNot('id', auth()->id())->get());
    }

    public function store(StoreUserRequest $request): JsonResponse
    {
        $this->checkAuthorization('user_create');

        $user = User::create($request->validated());

        return response()->json([
            'data' => new UserResource($user->load('role')),
            'message' => 'User Added Successfully',
        ], 201);
    }

    public function show(User $user): UserResource
    {
        $this->checkAuthorization('user_access');

        return UserResource::make($user->load('role'));
    }

    public function update(UpdateUserRequest $request, User $user): JsonResponse
    {
        $this->checkAuthorization('user_edit');

        if ($request->hasFile('photo') && $user->photo) {
            $this->deleteFile($user->photo);
        }

        $user->update($request->validated());

        return response()->json([
            'data' => new UserResource($user->load('role')),
            'message' => 'User Updated successfully',
        ]);
    }

    public function destroy(User $user): JsonResponse
    {
        $this->checkAuthorization('user_delete');

        if ($user->photo) {
            $this->deleteFile($user->photo);
        }

        $user->delete();

        return response()->json([
            'data' => '',
            'message' => 'User Deleted Successfully',
        ]);
    }

    public function updateUserStatus(User $user): Response|Application|ResponseFactory
    {
        $this->checkAuthorization('user_edit');

        $user->update([
            'status_at' => empty($user->status_at) ? now() : null,
        ]);

        return response([
            'status_at' => $user->status_at,
            'message' => 'User status updated successfully',
        ]);
    }
}
