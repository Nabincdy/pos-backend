<?php

namespace App\Http\Resources\Inventory;

use App\Http\Resources\Account\LedgerResource;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;
use NumberFormatter;

class QuotationResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id ?? '',
            'invoice_no' => $this->invoice_no ?? '',
            'quotation_date' => $this->quotation_date ?? '',
            'client' => LedgerResource::make($this->whenLoaded('clientLedger')),
            'created_by' => $this->whenLoaded('createUser', function () {
                return $this->createUser->name ?? '';
            }),
            'is_converted_to_sale' => $this->is_converted_to_sale ?? false,
            'remarks' => $this->remarks ?? '',
            'total_amount' => round($this->quotationParticulars->sum('total_amount'), 2),
            'quotation_summary' => [
                'sub_total_amount' => round($this->quotationParticulars->sum('sub_total'), 2),
                'tax_amount' => round($this->quotationParticulars->sum('sales_tax_amount'), 2),
                'discount_amount' => round($this->quotationParticulars->sum('discount_amount'), 2),
                'amount_in_words' => Str::headline($this->numberFormatter(round($this->quotationParticulars->sum('total_amount'), 2))),
            ],
            $this->mergeWhen(request()->routeIs('admin.inventory.quotation.show'), [
                'client_ledger_id' => $this->client_ledger_id ?? '',
                'quotationParticulars' => QuotationParticularResource::collection($this->whenLoaded('quotationParticulars')),
            ]),
        ];
    }

    public function numberFormatter($digit): bool|string
    {
        $format = new \NumberFormatter('en', NumberFormatter::SPELLOUT);

        return $format->format($digit);
    }
}
