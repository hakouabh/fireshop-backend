<?php

use Illuminate\Database\Seeder;
use App\Stock;
use App\Product;
class StockSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $products = Product::get();
        foreach($products as $product){
            $stock = Stock::where('product_id',$product->id)->first();
            if(!$stock){
                Stock::create([
                    'quantity' => $product->stock,
                    'initial_quantity' => $product->stock,
                    'cost' => $product->cost,
                    'selling_price' => $product->selling_price,
                    'product_id' => $product->id,
                    'company_id' => $product->company_id
                  ]);
            }
        }
    }
}
