<?php

namespace App\Services\Inventory;

use App\Contracts\Inventory\PurchaseInterface;
use App\Http\Requests\Api\Inventory\Purchase\StorePurchaseRequest;
use Illuminate\Http\Request;

class PurchaseService
{
    protected PurchaseInterface $purchaseRepository;

    public function __construct(PurchaseInterface $purchaseRepository)
    {
        $this->purchaseRepository = $purchaseRepository;
    }

    public function getPurchaseCode(): string
    {
        return $this->purchaseRepository->generatePurchaseCode();
    }

    public function allPurchases(Request $request)
    {
        return $this->purchaseRepository->all($request)
            ->filter(function ($purchase) use ($request) {
                if ($request->purchase_payment_status == 'due') {
                    return ($purchase->purchaseParticulars->sum('total_amount') - $purchase->payment_records_sum_paid_amount) > 0;
                }

                return true;
            });
    }

    public function purchaseList(Request $request)
    {
        return $this->purchaseRepository->list($request);
    }

    public function storePurchase(StorePurchaseRequest $request)
    {
        return $this->purchaseRepository->store($request->validated());
    }

    public function showPurchase($id)
    {
        return $this->purchaseRepository->show($id);
    }

    public function deletePurchase(Request $request, $id): void
    {
        $this->purchaseRepository->destroy($request, $id);
    }
}
