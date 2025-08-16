<?php

namespace App\Exports\Inventory;

use App\Models\Inventory\Brand;
use App\Models\Inventory\ProductCategory;
use App\Models\Inventory\Unit;
use App\Models\Setting\Tax;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;

class ProductSampleExport implements WithHeadings, ShouldAutoSize, WithEvents
{
    protected array $selects;

    protected int $row_count;

    protected int $column_count;

    public function __construct()
    {
        $this->selects = [  //selects should have column_name and options
            ['columns_name' => 'A', 'options' => ProductCategory::pluck('name')->toArray()],
            ['columns_name' => 'G', 'options' => Unit::pluck('name')->toArray()],
            ['columns_name' => 'H', 'options' => Brand::pluck('name')->toArray()],
            ['columns_name' => 'J', 'options' => Tax::pluck('name')->toArray()],
            ['columns_name' => 'L', 'options' => Tax::pluck('name')->toArray()],
        ];
        $this->row_count = 800;
        $this->column_count = 12;
    }

    public function headings(): array
    {
        return [
            'product_category',
            'name',
            'code',
            'sku',
            'reorder_quantity',
            'barcode',
            'unit',
            'brand',
            'purchase_rate',
            'purchase_tax',
            'sales_rate',
            'sales_tax',
            'description',
        ];
    }

    public function registerEvents(): array
    {
        return [
            // handle by a closure.
            AfterSheet::class => function (AfterSheet $event) {
                $row_count = $this->row_count;
                $column_count = $this->column_count;
                foreach ($this->selects as $select) {
                    $drop_column = $select['columns_name'];
                    $options = $select['options'];
                    // set dropdown list for first data row
                    $validation = $event->sheet->getCell("{$drop_column}2")->getDataValidation();
                    $validation->setType(DataValidation::TYPE_LIST);
                    $validation->setErrorStyle(DataValidation::STYLE_INFORMATION);
                    $validation->setAllowBlank(false);
                    $validation->setShowInputMessage(true);
                    $validation->setShowErrorMessage(true);
                    $validation->setShowDropDown(true);
                    $validation->setErrorTitle('Input error');
                    $validation->setError('Value is not in list.');
                    $validation->setPromptTitle('Pick from list');
                    $validation->setPrompt('Please pick a value from the drop-down list.');
                    $validation->setFormula1(sprintf('"%s"', implode(',', $options)));

                    // clone validation to remaining rows
                    for ($i = 3; $i <= $row_count; $i++) {
                        $event->sheet->getCell("{$drop_column}{$i}")->setDataValidation(clone $validation);
                    }
                    // set columns to autosize
                    for ($i = 1; $i <= $column_count; $i++) {
                        $column = Coordinate::stringFromColumnIndex($i);
                        $event->sheet->getColumnDimension($column)->setAutoSize(true);
                    }
                }
            },
        ];
    }
}
