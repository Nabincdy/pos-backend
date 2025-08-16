<?php

namespace App\Http\Controllers\Api\Inventory;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Inventory\Purchase\StorePurchaseRequest;
use App\Http\Resources\Inventory\PurchaseResource;
use App\Services\Inventory\PurchaseService;
use Illuminate\Http\Request;

class PurchaseController extends Controller
{
    public function __construct(public PurchaseService $purchaseService)
    {
    }

    public function purchaseCode()
    {
        return $this->purchaseService->getPurchaseCode();
    }

    public function allPurchases(Request $request)
    {
        $this->checkAuthorization('purchase_access');

        $purchases = $this->purchaseService->allPurchases($request);

        return PurchaseResource::collection($purchases);
    }

    public function index(Request $request)
    {
        $this->checkAuthorization('purchase_access');

        $purchases = $this->purchaseService->purchaseList($request);

        return PurchaseResource::collection($purchases);
    }

    public function store(StorePurchaseRequest $request)
    {
        $this->checkAuthorization('purchase_create');

        $purchase = $this->purchaseService->storePurchase($request);

        return response()->json([
            'data' => new PurchaseResource($purchase),
            'message' => 'Purchase Created Successfully',
        ], 201);
    }

    public function show($id)
    {
        $this->checkAuthorization('purchase_access');

        return PurchaseResource::make($this->purchaseService->showPurchase($id));
    }

    public function destroy(Request $request, $id)
    {
        $this->checkAuthorization('purchase_delete');

        $request->validate([
            'cancel_reason' => ['required', 'max:255'],
        ]);

        $this->purchaseService->deletePurchase($request, $id);

        return response()->json([
            'data' => '',
            'message' => 'Purchase Cancelled Successfully',
        ]);
    }
}
