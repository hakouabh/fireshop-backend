<?php

namespace App\Imports;

use App\Stock;
use App\Product;
use App\ProductType;
use Maatwebsite\Excel\Concerns\ToCollection;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Log;

class ProductImport implements ToCollection, WithHeadingRow
{
    
    public function collection(Collection $rows)
    {
        foreach ($rows as $row) 
        {
            $find = ProductType::where('name', 'like', '%' . $row['type'] . '%')->first();
            if(!$find){
                $type = new ProductType();
                $type->name = $row['type'];
                $type->save();
                $id = $type->id;
            }else{
                $id = $find->id; 
            }
            $product = Product::create([
                'company_id' => Auth::user()->company->id,
                'site_id' => Auth::user()->site->id,
                'name' => $row['name'],
                'sku' => $row['sku'],
                'type_id' =>$id,
                'stock' => $row['stock'],
                'cost' => $row['cost'],
                'selling_price' => $row['selling_price'],
            ]);
            // integration des sites
            Stock::create([
                'company_id' => Auth::user()->company->id,
                'product_id' => $product->id,
                'site_id' => $product->site_id,
                'quantity' => $row['stock'],
                'initial_quantity' => $row['stock'],
                'cost' => $row['cost'],
                'selling_price' => $row['selling_price'],
            ]);
        }
    }
}
