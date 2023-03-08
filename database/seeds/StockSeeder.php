<?php
namespace Database\Seeders;

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
                // integration des sites
                Stock::create([
                    'quantity' => $product->stock,
                    'initial_quantity' => $product->stock,
                    'cost' => $product->cost,
                    'selling_price' => $product->selling_price,
                    'product_id' => $product->id,
                    'site_id' => $product->site_id,
                    'site_id' => $product->site_id,
                    'company_id' => $product->company_id
                  ]);
            }
        }
    }
}
