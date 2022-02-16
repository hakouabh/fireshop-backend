<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Product;

class CustomerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        for ($i = 0; $i < 100; $i++) {

        factory(App\Charge::class)->create();
        }

    }
}
