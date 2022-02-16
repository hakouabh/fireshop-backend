<?php

namespace App\Http\Controllers\Api\V2;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Product;
use App\Stock;
use App\Order;
use Carbon\Carbon;
use App\OrderDetail;
use App\Operation;
use Log;
use DB;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    public function __construct(){
        $this->content = array();
    }

    public function add(Request $request){
        
        $product = Product::where('company_id', Auth::user()->company->id)->where('sku', $request->sku)->first();
        if(!$product){
            $product = Product::where('company_id', Auth::user()->company->id)->where('id', $request->sku)->firstOrFail();
        }
        $sell_price = $product->selling_price;
        $cost = $product->cost;
        $stock = Stock::where('company_id', Auth::user()->company->id)
                ->where('product_id',$product->id)
                ->where('quantity','>',0)
                ->orderBy('created_at','asc')->first();
        
        if($stock){
            $stock->decrement('quantity');
            $sell_price = $stock->selling_price;
            $cost = $stock->cost;
        }

        $check = Order::with('order_detail.product')
        ->whereHas('order_detail.product', function($q) use($product){
            $q->where('id', $product->id);
        })
        ->where('terminated', false)
        ->where('company_id', Auth::user()->company->id)->first();

        if($check){
            if($check->type == 1){
                $check->order_detail->increment('amount');
                $check->order_detail->update([ 
                    'total_order' => $sell_price + $check->order_detail->total_order ,
                    'cost' => ($cost + $check->order_detail->cost )
                ]);
    
                return response()->json($product, 200);
            }
        }else {

            $order = Order::create([
                'company_id' => Auth::user()->company->id,
                'user_id' => Auth::user()->id
            ]);

            $order_detail = OrderDetail::create([
                'order_id' => $order->id,
                'product_id' => $product->id,
                'amount' => 1,
                'cost' => $cost,
                'total_order' => $sell_price
            ]);
            return response()->json($product, 201);
        }
    }
    
    public function get(){
        $orders = Order::with('order_detail.product')
            ->where('terminated', false)
            ->where('company_id', Auth::user()->company->id)->orderby('created_at','desc')->get();

        return response()->json($orders, 200);    
    }

    public function delete($id){

        $order = Order::with('order_detail')->where('id', $id)
                ->where('company_id', Auth::user()->company->id)->first();
        
        $stocks = Stock::where('company_id', Auth::user()->company->id)
                ->where('product_id', $order->order_detail->product->id)->get();
               
        foreach($stocks as $stock){

            $stock->update(['quantity' => $stock->initial_quantity]);

        }

        $order->delete();
        return response()->json($order, 200);
    }
    public function Return($id){

        $order = Order::with('order_detail')->where('id', $id)
                ->where('company_id', Auth::user()->company->id)->first();
        
        $order->update(['type' => -1 * ($order->type)]);

        $order->order_detail->update([
            'total_order' => -1 * ($order->order_detail->total_order),
            'discount' => -1 * ($order->order_detail->discount),
            'amount' => -1 * ($order->order_detail->amount)
        ]);

        $stock = Stock::where('product_id',$order->order_detail->product->id)->where('initial_quantity','>=',DB::raw('quantity'))
                ->orderBy('created_at','asc')
                ->where('company_id', Auth::user()->company->id)->first();
        $stock->update(['quantity' => $stock->initial_quantity]);
        $stock->increment('quantity', - $order->order_detail->amount);
    }

    public function discount(Request $request){
        $orders = OrderDetail::find($request->order_id);
        $orders->update([
            'discount' => $request->discount *  $orders->amount ,
            'total_order' => $orders->total_order - ($request->discount *  $orders->amount)
        ]);
    }
    public function deleteAll(){

        $orders = Order::where('terminated', false)
            ->where('company_id', Auth::user()->company->id)->get();
        foreach ($orders as $order){
            $this->delete($order->id);
        }     
    }

    public function validateOperation(){
        // no longer used
        $orders = Order::with('order_detail.product')
            ->where('terminated', false)
            ->where('company_id', Auth::user()->company->id);
        $total = 0;
        $discount = 0 ;
        foreach($orders->get() as $order){
           $total = $total + $order->order_detail->total_order;
           $discount = $discount + $order->order_detail->discount;
        }
        $operation = new Operation;
        $operation->company_id = Auth::user()->company->id;
        $operation->user_id = Auth::user()->id;
        $operation->total = $total;
        $operation->payment = $total;
        $operation->discount = $discount;
        $operation->save();

        $orders->update([
            'terminated' => true,
            'operation_id' => $operation->id
        ]);

        return response()->json('Validate', 200); 

    }

    public function updateQuantity(Request $request){

        $order = Order::with('order_detail.product')->where('id',$request->order_id)->where('company_id', Auth::user()->company->id)->first();
        $product = Product::where('company_id', Auth::user()->company->id)->where('id',$order->order_detail->product->id)->first();

        $stock = Stock::where('product_id',$order->order_detail->product->id)->where('initial_quantity','>=',DB::raw('quantity'))
                ->orderBy('created_at','asc')
                ->where('company_id', Auth::user()->company->id)->first();
        $sell_price = $product->selling_price;
        $cost = $product->cost;
        if ($request->update =='increment'){
            if($stock->quantity > 0)
                {
                    $stock->decrement('quantity');
                    $sell_price = $stock->selling_price;
                    $cost = $stock->cost;
                }
            $order->order_detail->increment('amount');
            $order->order_detail->update([ 
                'total_order' => $sell_price + $order->order_detail->total_order ,
                'cost' => ($cost + $order->order_detail->cost )
            ]);      
        }else if($request->update =='decrement'){
            if($stock){
                $stock->increment('quantity');
                $sell_price = $stock->selling_price;
                $cost = $stock->cost;
            }
            $order->order_detail->decrement('amount');
            $order->order_detail->update([ 
                'total_order' =>  $order->order_detail->total_order - $sell_price ,
                'cost' => ($order->order_detail->cost - $cost )
            ]);
        }
    }
}
