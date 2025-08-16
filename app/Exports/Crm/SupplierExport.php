<?php

namespace App\Exports\Crm;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class SupplierExport implements WithHeadings, FromCollection, WithMapping
{
    public function __construct(public Collection $suppliers)
    {
    }

    public function headings(): array
    {
        return [
            'supplier_name',
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
        return $this->suppliers;
    }

    public function map($supplier): array
    {
        // Modify or map the collection data here
        return [
            $supplier->supplier_name,
            $supplier->code,
            $supplier->phone,
            $supplier->email,
            $supplier->company->company_name ?? '',
            $supplier->pan_no,
            $supplier->address,
        ];
    }
}
