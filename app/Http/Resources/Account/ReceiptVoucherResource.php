<?php

namespace App\Http\Resources\Account;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;
use NumberFormatter;

class ReceiptVoucherResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id ?? '',
            'fiscal_year_id' => $this->fiscal_year_id ?? '',
            'receipt_no' => $this->receipt_no ?? '',
            'receipt_method' => $this->receipt_method ?? '',
            'receipt_date' => $this->receipt_date ?? '',
            'created_by' => $this->whenLoaded('createUser', function () {
                return $this->createUser->name ?? '';
            }),
            'is_cancelled' => $this->is_cancelled ?? '',
            'cancelled_reason' => $this->cancelled_reason ?? '',
            'remarks' => $this->remarks ?? '',
            'amount' => $this->whenLoaded('journal', function () {
                return $this->journal->journal_particulars_sum_dr_amount ?? 0;
            }),
            'amount_in_words' => $this->whenLoaded('journal', function () {
                return Str::headline($this->numberFormatter($this->journal->journal_particulars_sum_dr_amount ?? 0));
            }),
            'journalParticulars' => $this->when(request()->routeIs('admin.account.receiptVoucher.show'), function () {
                return JournalParticularResource::collection($this->journal->journalParticulars ?? collect());
            }),
        ];
    }

    public function numberFormatter($digit): bool|string
    {
        $format = new \NumberFormatter('en', NumberFormatter::SPELLOUT);

        return $format->format($digit);
    }
}
