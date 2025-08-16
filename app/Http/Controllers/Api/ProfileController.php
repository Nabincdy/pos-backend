<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\UpdatePasswordRequest;
use App\Http\Requests\Api\UpdateProfileRequest;
use App\Http\Resources\ProfileResource;

class ProfileController extends Controller
{
    public function profile(): ProfileResource
    {
        return ProfileResource::make(auth()->user());
    }

    public function updateProfile(UpdateProfileRequest $request)
    {
        if ($request->hasFile('photo') && auth()->user()->photo) {
            $this->deleteFile(auth()->user()->photo);
        }

        auth()->user()->update($request->validated());

        return response()->json([
            'data' => ProfileResource::make(auth()->user()),
            'message' => 'Profile Updated Successfully',
        ]);
    }

    public function updatePassword(UpdatePasswordRequest $request)
    {
        auth()->user()->update($request->validated());

        return response([
            'data' => '',
            'message' => 'Password Changed Successfully',
        ]);
    }
}
