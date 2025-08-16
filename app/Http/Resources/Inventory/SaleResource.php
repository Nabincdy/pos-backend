<?php

namespace App\Http\Resources\Inventory;

use App\Http\Resources\Account\LedgerResource;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;
use NumberFormatter;

class SaleResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id ?? '',
            'invoice_no' => $this->invoice_no ?? '',
            'sales_date' => $this->sales_date ?? '',
            'en_sales_date' => $this->en_sales_date ?? '',
            'payment_type' => $this->payment_type ?? '',
            'client' => LedgerResource::make($this->whenLoaded('clientLedger')),
            'created_by' => $this->whenLoaded('createUser', function () {
                return $this->createUser->name ?? '';
            }),
            'is_cancelled' => $this->is_cancelled ?? false,
            'cancelled_reason' => $this->cancelled_reason ?? '',
            'cancelled_by' => $this->cancel_user_id ?? '',
            'remarks' => $this->remarks ?? '',
            'total_amount' => round($this->saleParticulars->sum('total_amount'), 2),
            'sales_summary' => [
                'sub_total_amount' => round($this->saleParticulars->sum('sub_total'), 2),
                'tax_amount' => round($this->saleParticulars->sum('sales_tax_amount'), 2),
                'discount_amount' => round($this->saleParticulars->sum('discount_amount'), 2),
                'paid_amount' => round($this->receipt_records_sum_amount ?? 0, 2),
                'due_amount' => round($this->calculateDueAmount(), 2),
                'amount_in_words' => Str::headline($this->numberFormatter(round($this->saleParticulars->sum('total_amount'), 2))),
            ],
            'payment_status' => $this->paymentStatus(),
            $this->mergeWhen(request()->routeIs('admin.inventory.sale.show'), [
                'client_ledger_id' => $this->client_ledger_id ?? '',
                'saleParticulars' => SaleParticularResource::collection($this->whenLoaded('saleParticulars')),
            ]),
        ];
    }

    private function calculateDueAmount(): float
    {
        return (float) ($this->saleParticulars->sum('total_amount')) - ($this->receipt_records_sum_amount ?? 0);
    }

    private function paymentStatus(): string
    {
        if ($this->calculateDueAmount() == 0) {
            return 'Paid';
        } elseif ($this->receipt_records_sum_amount > 0) {
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
