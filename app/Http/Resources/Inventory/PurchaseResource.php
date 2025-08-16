<?php

namespace App\Http\Resources\Inventory;

use App\Http\Resources\Account\LedgerResource;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;
use NumberFormatter;

class PurchaseResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id ?? '',
            'invoice_no' => $this->invoice_no ?? '',
            'purchase_date' => $this->purchase_date ?? '',
            'en_purchase_date' => $this->en_purchase_date ?? '',
            'payment_type' => $this->payment_type ?? '',
            'supplier' => LedgerResource::make($this->whenLoaded('supplierLedger')),
            'created_by' => $this->whenLoaded('createUser', function () {
                return $this->createUser->name ?? '';
            }),
            'is_cancelled' => $this->is_cancelled ?? false,
            'cancelled_reason' => $this->cancelled_reason ?? '',
            'cancelled_by' => $this->cancel_user_id ?? '',
            'remarks' => $this->remarks ?? '',
            'total_amount' => round($this->purchaseParticulars->sum('total_amount'), 2),
            'purchase_summary' => [
                'sub_total_amount' => round($this->purchaseParticulars->sum('sub_total'), 2),
                'tax_amount' => round($this->purchaseParticulars->sum('purchase_tax_amount'), 2),
                'discount_amount' => round($this->purchaseParticulars->sum('discount_amount'), 2),
                'paid_amount' => round($this->payment_records_sum_paid_amount ?? 0, 2),
                'due_amount' => round($this->calculateDueAmount(), 2),
                'amount_in_words' => Str::headline($this->numberFormatter(round($this->purchaseParticulars->sum('total_amount'), 2))),
            ],
            'payment_status' => $this->paymentStatus(),
            $this->mergeWhen(request()->routeIs('admin.inventory.purchase.show'), [
                'supplier_ledger_id' => $this->supplier_ledger_id ?? '',
                'purchaseParticulars' => PurchaseParticularResource::collection($this->whenLoaded('purchaseParticulars')),
            ]),
        ];
    }

    private function calculateDueAmount(): float
    {
        return (float) ($this->purchaseParticulars->sum('total_amount')) - ($this->payment_records_sum_paid_amount ?? 0);
    }

    private function paymentStatus(): string
    {
        if ($this->calculateDueAmount() == 0) {
            return 'Paid';
        } elseif ($this->payment_records_sum_paid_amount > 0) {
            return 'Partially Paid';
        } else {
            return 'UnPaid';
        }
    }

    public function numberFormatter($digit): bool|string
    {
        $format = new \NumberFormatter('en', NumberFormatter::SPELLOUT);

        return $format->format($digit);
    }
}
