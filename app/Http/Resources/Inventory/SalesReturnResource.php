<?php

namespace App\Http\Resources\Inventory;

use App\Http\Resources\Account\LedgerResource;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;
use NumberFormatter;

class SalesReturnResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id ?? '',
            'invoice_no' => $this->invoice_no ?? '',
            'return_date' => $this->return_date ?? '',
            'client' => LedgerResource::make($this->whenLoaded('clientLedger')),
            'created_by' => $this->whenLoaded('createUser', function () {
                return $this->createUser->name ?? '';
            }),
            'remarks' => $this->remarks ?? '',
            'total_amount' => round($this->salesReturnParticulars->sum('total_amount'), 2),
            'return_summary' => [
                'sub_total_amount' => round($this->salesReturnParticulars->sum('sub_total'), 2),
                'tax_amount' => round($this->salesReturnParticulars->sum('sales_tax_amount'), 2),
                'discount_amount' => round($this->salesReturnParticulars->sum('discount_amount'), 2),
                'amount_in_words' => Str::headline($this->numberFormatter(round($this->salesReturnParticulars->sum('total_amount'), 2))),
            ],
            $this->mergeWhen(request()->routeIs('admin.inventory.salesReturn.show'), [
                'client_ledger_id' => $this->client_ledger_id ?? '',
                'salesReturnParticulars' => SalesReturnParticularResource::collection($this->whenLoaded('salesReturnParticulars')),
            ]),
        ];
    }

    private function numberFormatter($digit): bool|string
    {
        $format = new \NumberFormatter('en', NumberFormatter::SPELLOUT);

        return $format->format($digit);
    }
}
