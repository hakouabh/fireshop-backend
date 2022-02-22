<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Plan;
class PlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $plan = Plan::create(
            [
                'title' => 'Free',
                'text' => '15-days trial',
                'lifetime' => 60 * 60 * 24 * 15,
                'price' => 0,
                'status' => 1
            ]
        );
    }
}
