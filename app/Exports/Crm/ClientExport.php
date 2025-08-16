<?php

namespace App\Exports\Crm;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ClientExport implements WithHeadings, FromCollection, WithMapping
{
    public function __construct(public Collection $clients)
    {
    }

    public function headings(): array
    {
        return [
            'client_group',
            'name',
            'code',
            'phone',
            'email',
            'company',
            'pan_no',
            'address',
        ];
    }

    public function collection(): Collection
    {
        return $this->clients;
    }

    public function map($client): array
    {
        // Modify or map the collection data here
        return [
            $client->clientGroup->group_name ?? '',
            $client->name,
            $client->code,
            $client->phone,
            $client->email,
            $client->company->company_name ?? '',
            $client->pan_no,
            $client->address,
        ];
    }
}
