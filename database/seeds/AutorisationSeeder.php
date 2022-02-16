<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\User;
use App\Autorisation;
class AutorisationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $user = User::all();
        foreach($user as $index){
            $auth = new Autorisation;
            $auth->user_id = $index->id;
            switch ($index->role) {
                case 1:
                    $auth->product_update = false;
                    $auth->stock_update = false;
                    $auth->charge_update = false;
                    $auth->corbeille = false;
                    break;
                case 2:
                    $auth->charge_list = false;
                    $auth->charge_add = false;
                    $auth->charge_update = false;
                    $auth->dashboard = false;
                    $auth->corbeille = false;
                    break;
            }
            $auth->save();
        }
    }
}
