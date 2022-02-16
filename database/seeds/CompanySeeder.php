<?php

use Illuminate\Database\Seeder;
use App\Company;
use Carbon\Carbon;
class CompanySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // factory(App\Company::class)->create();
        Company::where('expire_at', null)->update(['expire_at' => Carbon::now()->addMonth()]);
    }
}
