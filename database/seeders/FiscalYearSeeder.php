<?php

namespace Database\Seeders;

use App\Models\Setting\FiscalYear;
use Illuminate\Database\Seeder;

class FiscalYearSeeder extends Seeder
{
    public function run()
    {
        $fiscalYears = [
            ['year' => '2079', 'year_title' => '2079/80', 'start_date' => '2079-04-01', 'end_date' => '2080-03-31', 'is_running' => 1],
            ['year' => '2080', 'year_title' => '2080/81', 'start_date' => '2080-04-01', 'end_date' => '2081-03-32'],
            ['year' => '2081', 'year_title' => '2081/82', 'start_date' => '2081-04-01', 'end_date' => '2082-03-31'],
        ];

        foreach ($fiscalYears as $fiscalYear) {
            FiscalYear::create($fiscalYear);
        }
    }
}
