<?php

namespace App\Http\Controllers\Api\Crm;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Crm\ClientGroup\StoreClientGroupRequest;
use App\Http\Requests\Api\Crm\ClientGroup\UpdateClientGroupRequest;
use App\Http\Resources\Crm\ClientGroupResource;
use App\Models\Crm\ClientGroup;
use Illuminate\Support\Str;

class ClientGroupController extends Controller
{
    public function clientGroupCode()
    {
        return companySetting()->client_group.Str::padLeft(ClientGroup::max('id') + 1, 3, 0);
    }

    public function index()
    {
        $this->checkAuthorization('clientGroup_access');

        return ClientGroupResource::collection(ClientGroup::all());
    }

    public function store(StoreClientGroupRequest $request)
    {
        $this->checkAuthorization('clientGroup_create');

        $clientGroup = ClientGroup::create($request->validated());

        return response()->json([
            'data' => new ClientGroupResource($clientGroup),
            'message' => 'Client Group Added Successfully',
        ], 201);
    }

    public function show(ClientGroup $clientGroup)
    {
        $this->checkAuthorization('clientGroup_access');

        return new ClientGroupResource($clientGroup);
    }

    public function update(UpdateClientGroupRequest $request, ClientGroup $clientGroup)
    {
        $this->checkAuthorization('clientGroup_edit');

        $clientGroup->update($request->validated());

        return response()->json([
            'data' => new ClientGroupResource($clientGroup),
            'message' => 'Client Group Updated Successfully',
        ]);
    }

    public function destroy(ClientGroup $clientGroup)
    {
        $this->checkAuthorization('clientGroup_delete');

        $clientGroup->delete();

        return response()->json([
            'data' => '',
            'message' => 'Client Group Deleted Successfully',
        ]);
    }
}
