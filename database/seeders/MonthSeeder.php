<?php

namespace Database\Seeders;

use App\Models\Setting\Month;
use Illuminate\Database\Seeder;

class MonthSeeder extends Seeder
{
    public function run()
    {
        $months = [
            ['name' => 'Baishak', 'month' => '01'],
            ['name' => 'Jestha', 'month' => '02'],
            ['name' => 'Ashad', 'month' => '03'],
            ['name' => 'Shrawan', 'month' => '04'],
            ['name' => 'Bhadra', 'month' => '05'],
            ['name' => 'Ashwin', 'month' => '06'],
            ['name' => 'Kartik', 'month' => '07'],
            ['name' => 'Mansir', 'month' => '08'],
            ['name' => 'Poush', 'month' => '09'],
            ['name' => 'Magh', 'month' => '10'],
            ['name' => 'Falgun', 'month' => '11'],
            ['name' => 'Chaitra', 'month' => '12'],
        ];

        foreach ($months as $month) {
            Month::create($month);
        }
    }
}
