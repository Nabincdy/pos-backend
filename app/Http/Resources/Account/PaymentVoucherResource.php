<?php

namespace App\Http\Resources\Account;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;
use NumberFormatter;

class PaymentVoucherResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id ?? '',
            'voucher_no' => $this->voucher_no ?? '',
            'payment_date' => $this->payment_date ?? '',
            'payment_method' => $this->payment_method ?? '',
            'created_by' => $this->whenLoaded('createUser', function () {
                return $this->createUser->name ?? '';
            }),
            'is_cancelled' => $this->is_cancelled ?? '',
            'cancelled_reason' => $this->cancelled_reason ?? '',
            'remarks' => $this->remarks ?? '',
            'amount' => $this->whenLoaded('journal', function () {
                return $this->journal->journal_particulars_sum_cr_amount ?? 0;
            }),
            'amount_in_words' => $this->whenLoaded('journal', function () {
                return Str::headline($this->numberFormatter($this->journal->journal_particulars_sum_cr_amount ?? 0));
            }),
            'journalParticulars' => $this->when(request()->routeIs('admin.account.paymentVoucher.show'), function () {
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
