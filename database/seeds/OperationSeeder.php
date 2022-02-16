<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Stock;
use App\Product;
use App\Order;
use App\OrderDetail;
use App\Operation;

class OperationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        for ($i = 0; $i < 5; $i++) {
        $list_sku = ['310047381','81698103','100533889','4038389','26897995'];

        $product = Product::where('company_id','0a327e34-9361-45f7-8574-d99056d1c321')
            ->where('sku', $list_sku[$i])->firstOrFail();
        $product->decrement('stock',5); 

        $order = Order::create([
            'company_id' => '0a327e34-9361-45f7-8574-d99056d1c321'
        ]);

        $order_detail = OrderDetail::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'amount' => 3,
            'cost' => $product->cost,
            'total_order' => 3 * $product->selling_price
        ]);
    }

    }
}
