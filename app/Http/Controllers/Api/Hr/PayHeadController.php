<?php

namespace App\Http\Controllers\Api\Hr;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Hr\PayHead\StorePayHeadRequest;
use App\Http\Requests\Api\Hr\PayHead\UpdatePayHeadRequest;
use App\Http\Resources\Hr\PayHeadResource;
use App\Models\Hr\PayHead;

class PayHeadController extends Controller
{
    public function index()
    {
        $this->checkAuthorization('payHead_access');

        return PayHeadResource::collection(PayHead::with('tax')->get());
    }

    public function store(StorePayHeadRequest $request)
    {
        $this->checkAuthorization('payHead_create');

        $payHead = PayHead::create($request->validated());

        return response()->json([
            'data' => new PayHeadResource($payHead->load('tax')),
            'message' => 'Pay Head Added Successfully',
        ], 201);
    }

    public function show(PayHead $payHead)
    {
        $this->checkAuthorization('payHead_access');

        return new PayHeadResource($payHead->load('tax'));
    }

    public function update(UpdatePayHeadRequest $request, PayHead $payHead)
    {
        $this->checkAuthorization('payHead_edit');

        $payHead->update($request->validated());

        return response()->json([
            'data' => new PayHeadResource($payHead->load('tax')),
            'message' => 'Pay Head Updated Successfully',
        ]);
    }

    public function destroy(PayHead $payHead)
    {
        $this->checkAuthorization('payHead_delete');

        $payHead->delete();

        return response()->json([
            'data' => '',
            'message' => 'Pay Head Deleted Successfully',
        ]);
    }
}
