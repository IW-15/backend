<?php

namespace Database\Seeders;

use App\Models\OutletRevenue as ModelsOutletRevenue;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class OutletRevenue extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $ranges = [
            ['label' => 'Rp0 - Rp5JT',],
            ['label' => 'Rp5JT - Rp10JT',],
            ['label' => 'Rp10JT - Rp20JT',],
            ['label' => 'Rp20JT - Rp50JT',],
            ['label' => 'Rp50JT - Rp100JT',],
            ['label' => 'Rp100JT - Rp250JT',],
            ['label' => 'Rp250JT - Rp500JT',],
            ['label' => 'Rp500JT - Rp1M',],
            ['label' => 'Rp1M - Rp2M',],
            ['label' => 'Di atas Rp2M',]
        ];

        ModelsOutletRevenue::insert($ranges);
    }
}
