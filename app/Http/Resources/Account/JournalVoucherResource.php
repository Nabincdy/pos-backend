<?php

namespace App\Http\Resources\Account;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;
use NumberFormatter;

class JournalVoucherResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id ?? '',
            'fiscal_year_id' => $this->fiscal_year_id ?? '',
            'voucher_no' => $this->voucher_no ?? '',
            'voucher_date' => $this->voucher_date ?? '',
            'create_user_id' => $this->create_user_id ?? '',
            'created_by' => $this->whenLoaded('createUser', function () {
                return $this->createUser->name ?? '';
            }),
            'amount' => $this->whenLoaded('journal', function () {
                return $this->journal->journal_particulars_sum_dr_amount ?? 0;
            }),
            'amount_in_words' => $this->whenLoaded('journal', function () {
                return Str::headline($this->numberFormatter($this->journal->journal_particulars_sum_dr_amount ?? 0));
            }),
            'is_cancelled' => $this->is_cancelled ?? '',
            'cancelled_reason' => $this->cancelled_reason ?? '',
            'cancel_user_id' => $this->cancel_user_id ?? '',
            'remarks' => $this->remarks ?? '',
            'journalParticulars' => $this->when(request()->routeIs('admin.account.journalVoucher.show'), function () {
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
