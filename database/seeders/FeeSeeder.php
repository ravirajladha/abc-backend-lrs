<?php

namespace Database\Seeders;

use App\Models\Fee;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class FeeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $fees = [
            [
                'amount' => 1000,
                'slash_amount' => 1200,
                'total_amount' => 1800,
                'referral_amount' => 200,
                'referrer_amount' => 100,
                'benefits' => 'Access to all courses and resources',
                'description' => 'This fee covers the entire course duration and provides access to all necessary materials.'
            ]];

        Fee::insert($fees);
    }
}
