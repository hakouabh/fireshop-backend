<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use App\Product;
use App\Stock;
use App\ProductType;
use App\Customer;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\ProductImport;
use Illuminate\Support\Facades\Auth;
use DB;
use Log;
use App\Exports\ProductExport;
use Illuminate\Support\Facades\Storage;

use Illuminate\Validation\Rule;

class ProductController extends Controller
{
    public function __construct(){
        $this->content = array();
    }

    public function export(Request $request){

        return Excel::download(new ProductExport($request), 'Produits.xlsx');

    }

    public function storeImage(Request $request){
        $product = Product::where('company_id', Auth::user()->company->id)
                            ->find($request->id);

        if($request->hasFile('image')){
            if(!Storage::exists('public/products/'.$request->id.'/')) {
            
                Storage::makeDirectory('public/products/'.$request->id.'/'); //creates directory
            
            }    
            $ext = $request->file('image')->getClientOriginalExtension();
            $filename = time().".".$ext;
            // Get all files in a directory
            $files =   Storage::allFiles('public/products/'.$request->id.'/');

            // Delete Files
            Storage::delete($files);
            $path = Storage::putFileAs('public/products/'.$request->id.'/', $request->file('image'), $filename); 
            $product->update([
                'image' => config('app.url').Storage::disk('local')->url($path),
            ]);
        }
    }

    public function getStock(Request $request){
        $product = Stock::with('product.type')
                    ->where('product_id',$request->product_id)
                    ->where('company_id', Auth::user()->company->id)->orderBy('created_at','desc')->paginate($request->perpage);
        return response()->json($product, 200);
    }
    public function findStock($id){
        $stock = Stock::with('product')->where('id',$id)->where('company_id', Auth::user()->company->id)->first();
        return response()->json($stock, 200);
    }

    public function updateStock(Request $request){
        Log::alert("message");

        $stock = Stock::where('id',$request->id)->where('company_id', Auth::user()->company->id)->first();
        $product = Product::where('company_id', Auth::user()->company->id)->where('id', $stock->product_id)->firstOrFail();
        $product->decrement('stock',$stock->quantity);

        $stock->update([
            'quantity' => $request->quantity,
            'cost' => $request->cost,
            'selling_price' => $request->selling_price,
            'initial_quantity' => $request->quantity,
        ]);

        $product->update([
            'selling_price' => $request->input('selling_price'),
            'cost' => $request->input('cost'),
        ]);
        $product->increment('stock',$request->input('quantity'));
        return response()->json($stock, 200);
    }
    public function get(Request $request){
        $product = Product::with('type','sold')
                            ->where('company_id', Auth::user()->company->id);
        
        if($request->has('filter')){
            $params = (object) json_decode($request->filter, true);
            
            if(is_array($params->type)){
                $params->type = (object) $params->type;
            }
            if(is_array($params->date_range)){
                $params->date_range = (object) $params->date_range;
            }
            if($params->name){
                $product = $product->where('name', 'like', '%' . $params->name . '%');
            }
            if($params->sku){
                $product = $product->where('sku', 'like', '%' . $params->sku . '%');
            }
            if(is_object($params->type) && $params->type->id){
                $product = $product->where('type_id', $params->type->id);
            }
            if(is_object($params->date_range) && ($params->date_range->from && $params->date_range->to)){
                $product = $product->whereBetween('created_at', [
                    Carbon::createFromFormat('Y-m-d', $params->date_range->from)->format('Y-m-d')." 00:00:00",
                    Carbon::createFromFormat('Y-m-d', $params->date_range->to)->format('Y-m-d')." 23:59:59"
                ]);
            }
        }

        if($this->content['data'] = $product->paginate($request->perpage)){
          $this->content['status'] = 200;
          return response()->json($this->content, $this->content['status'], [], JSON_NUMERIC_CHECK);
        }

        $this->content['error'] = "Server Error";
        $this->content['status'] = 500;

        return response()->json($this->content, $this->content['status'], [], JSON_NUMERIC_CHECK);
    }

    public function deleted(Request $request){
        $products = Product::with('type')
                            ->where('company_id', Auth::user()->company->id)
                            ->onlyTrashed()
                            ->paginate($request->perpage);

        return response()->json($products, 200);                      
    }

    public function restore($id){
        $products = Product::onlyTrashed()->where('company_id', Auth::user()->company->id)->find($id);
        $products->restore();                      
    }

    public function find(Request $request){
        $product = Product::with('type', 'sold')
                            ->where('company_id', Auth::user()->company->id)
                            ->find($request->id);

        if($this->content['data'] = $product){
          $this->content['status'] = 200;
          return response()->json($this->content, $this->content['status'], [], JSON_NUMERIC_CHECK);
        }

        $this->content['error'] = "Server Error";
        $this->content['status'] = 500;

        return response()->json($this->content, $this->content['status'], [], JSON_NUMERIC_CHECK);
    }

    public function customers(Request $request){
        $product_id = $request->id;
        $customers = Customer::whereHas('order', function($query) use ($product_id) {
                                $query->whereHas('order_detail', function($query) use ($product_id) {
                                    $query->where('product_id', $product_id);
                                });
                            })->get();

        if($this->content['data'] = $customers){
          $this->content['status'] = 200;
          return response()->json($this->content, $this->content['status'], [], JSON_NUMERIC_CHECK);
        }

        $this->content['error'] = "Server Error";
        $this->content['status'] = 500;

        return response()->json($this->content, $this->content['status'], [], JSON_NUMERIC_CHECK);
    }

    public function addProduct(Request $request){

        $request->validate([
            'import_file' => 'required|file|mimes:xls,xlsx',
          ]);
        $path = $request->file('import_file');
        $data = Excel::import(new ProductImport, $path);
    }
    public function add(Request $request)
    { 
        $validateData = $request->validate([
            'name' => 'required|min:3',
            'type' => 'required',
            'stock' => 'required|integer|min:0',
            'cost' => 'required|numeric|min:0',
            'selling_price' => 'required|numeric|min:0'
        ]);
        if($request->sku){
            $validateData = $request->validate([
                'sku' => [Rule::unique('products')->where(function ($query) use ($request) {
                    return $query->where('site_id', Auth::user()->site->id);
                })],
            ]);
        }
        $product = Product::create([
          'sku' => $request->input('sku'),
          'name' => $request->input('name'),
          'description' => $request->input('description'),
          'type_id' => ($request->input('type.id')) ? $request->input('type.id') : ProductType::create(['name' => $request->input('type')])->id,
          'stock' => $request->input('stock'),
          'cost' => $request->input('cost'),
          'site_id' => ($request->input('site_id') ? $request->input('site_id') : Auth::user()->site->id),
          'selling_price' => $request->input('selling_price'),
          'company_id' => Auth::user()->company->id
        ]);
        // integration des sites
        $stock = Stock::create([
            'quantity' => $request->input('stock'),
            'initial_quantity' => $product->stock,
            'cost' => $request->input('cost'),
            'selling_price' => $request->input('selling_price'),
            'product_id' => $product->id,
            'company_id' => Auth::user()->company->id,
            'site_id' => Auth::user()->site->id
          ]);

        if($this->content['data'] = Product::with('type')->where('company_id', Auth::user()->company->id)->get()){
          $this->content['status'] = 200;
          return response()->json($this->content, $this->content['status'], [], JSON_NUMERIC_CHECK);
        }

        $this->content['error'] = "Server Error";
        $this->content['status'] = 500;

        return response()->json($this->content, $this->content['status'], [], JSON_NUMERIC_CHECK);
    }

    public function update(Request $request)
    {
        $validateData = $request->validate([
            'name' => 'required|min:3',
            'type' => 'required',
        ]);
        $product = Product::with('type')
                            ->where('company_id', Auth::user()->company->id)
                            ->find($request->id);
        
        if($request->input('sku') && $product->sku != $request->input('sku')){
            $validateData = $request->validate([
                'sku' => [Rule::unique('products')->where(function ($query) use ($request) {
                    return $query->where('company_id', Auth::user()->company->id);
                })]
            ]);
        }
        $product->update([
          'name' => $request->input('name'),
          'sku' => $request->input('sku'),
          'description' => $request->input('description'),
          'type_id' => ($request->input('type.id')) ? $request->input('type.id') : ProductType::create(['name' => $request->input('type')])->id,
          'image' => $request->input('image')
        ]);

        if($this->content['data'] = Product::with('type')->where('company_id', Auth::user()->company->id)->find($request->id)){
          $this->content['status'] = 200;
          return response()->json($this->content, $this->content['status'], [], JSON_NUMERIC_CHECK);
        }

        $this->content['error'] = "Server Error";
        $this->content['status'] = 500;

        return response()->json($this->content, $this->content['status'], [], JSON_NUMERIC_CHECK);
    }

    public function delete(Request $request)
    {
        $product = Product::with('type','stocks')
                            ->where('company_id', Auth::user()->company->id)
                            ->find($request->id);
        
        $product->delete();

        if(!$this->content['data'] = Product::with('type')->where('company_id', Auth::user()->company->id)->find($request->id)){
          $this->content['status'] = 200;
          return response()->json($this->content, $this->content['status'], [], JSON_NUMERIC_CHECK);
        }

        $this->content['error'] = "Server Error";
        $this->content['status'] = 500;

        return response()->json($this->content, $this->content['status'], [], JSON_NUMERIC_CHECK);
    }

    public function deleteStock(Request $request)
    {
        $stock = Stock::where('company_id', Auth::user()->company->id)
                            ->find($request->id);
        $product = Product::where('company_id', Auth::user()->company->id)->find($stock->product_id);
        if($stock->is_defect == false)
            $product->decrement('stock', $stock->quantity);
        
        $stock->delete();

        if(!$this->content['data'] = Stock::where('company_id', Auth::user()->company->id)->find($request->id)){
          $this->content['status'] = 200;
          return response()->json($this->content, $this->content['status'], [], JSON_NUMERIC_CHECK);
        }

        $this->content['error'] = "Server Error";
        $this->content['status'] = 500;

        return response()->json($this->content, $this->content['status'], [], JSON_NUMERIC_CHECK);
    }

    public function stock(Request $request){
        $validateData = $request->validate([
            'quantity' => 'required|integer|min:0',
            'product' => 'required',
            'cost' => 'required|numeric|min:0',
            'selling_price' => 'required|numeric|min:0',
            'is_defect' => 'required'
        ]);
        $params = (object) $request->product; 

        $product = Product::where('company_id', Auth::user()->company->id)->where('id', $params->id)->firstOrFail();
        // integration des sites
        Stock::create([
            'quantity' => $request->input('quantity'),
            'initial_quantity' => $request->input('quantity'),
            'cost' => $request->input('cost'),
            'selling_price' => $request->input('selling_price'),
            'product_id' => $product->id,
            'site_id' => $product->site_id,
            'is_defect' => $request->input('is_defect'),
            'company_id' => Auth::user()->company->id
          ]);
        if ($request->input('is_defect') == false){
            $product->update([
                'selling_price' => $request->input('selling_price'),
                'cost' => $request->input('cost'),
            ]);
            $product->increment('stock',$request->input('quantity'));

        }else if($request->input('is_defect') == true ){
            $product->decrement('stock',$request->input('quantity')); 

        }
        if ($request->input('update_price') == true){
            Stock::where('company_id', Auth::user()->company->id)->where('product_id', $product->id)
            ->update([
                'selling_price' => $request->input('selling_price')
            ]);
        }  
        return response()->json($product, 200);
    }
}
