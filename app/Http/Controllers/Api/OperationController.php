<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Operation;
use App\Order;
use App\Product;
use App\Stock;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use App\Exports\OperationExport;
use  Maatwebsite\Excel\Excel;
use Mike42\Escpos\PrintConnectors\FilePrintConnector;
use Mike42\Escpos\Printer;
use Log;

class OperationController extends Controller
{

    public function validateOperation(Request $request){
        // $this->printeReceipt();
        $customer = json_decode($request->customer,true);

        $orders = Order::with('order_detail.product')
            ->where('terminated', false)
            ->where('company_id', Auth::user()->company->id);

        if ($orders->get()->isEmpty()){
            return response()->json('not found', 424);
        }

        $total = 0;
        $discount = 0 ;
        $cost = 0;

        foreach($orders->get() as $order){
           $total = $total + $order->order_detail->total_order;
           $discount = $discount + $order->order_detail->discount;
           $cost = $cost + $order->order_detail->cost;

           $product = Product::where('company_id', Auth::user()->company->id)
                ->where('id',$order->order_detail->product->id)->first();
            $stocks = Stock::where('company_id', Auth::user()->company->id)
                ->where('product_id',$order->order_detail->product->id)->get();

            if($order->type == -1){
                $product->increment('stock',-$order->order_detail->amount);                
            }
            else if($order->type == 1){
                $product->decrement('stock',$order->order_detail->amount);
            }

            foreach ($stocks as $stock){

                $stock->update(['initial_quantity' => $stock->quantity]);
            }
        }
        $operation = new Operation;
        $operation->company_id = Auth::user()->company->id;
        $operation->user_id = Auth::user()->id;
        $operation->total = $total;
        $operation->cost = $cost;
        $operation->payment = $request->payement;
        $operation->rest = $request->payement - $total;
        $operation->discount = $discount;

        if(isset($customer['id'])){
            $operation->customer_id = $customer['id'];   
        }
        $operation->save();

        $orders->update([
            'terminated' => true,
            'operation_id' => $operation->id
        ]);

        return response()->json("Validate", 200); 

    }
    
    public function export(Request $request, Excel $excel){
        return $excel->download(new OperationExport($request), 'Operation.xlsx');
    }
    public function get(Request $request){

        $order = Operation::with(['order.order_detail','customer'])
            ->where('company_id', Auth::user()->company->id);
            
        if($request->has('filter')){
            $params = (object) json_decode($request->filter, true);

            if(is_array($params->date_range)){
                $params->date_range = (object) $params->date_range;
            }
            
            if($params->customer){
                $order = $order->whereHas('customer', function($q) use ($params) {
                  $q->where('full_name', 'like', '%' . $params->customer . '%');
                });
            }
            if($params->phone){
                $order = $order->whereHas('customer', function($q) use ($params) {
                  $q->where('phone', 'like', '%' . $params->phone . '%');
                });
            }
            if(is_array($params->user)){
                $params->user = (object) $params->user;
                $order = $order->where('user_id',$params->user->id);
            }

            if(is_object($params->date_range) && ($params->date_range->from && $params->date_range->to)){
                $order = $order->whereBetween('created_at', [
                    Carbon::createFromFormat('Y-m-d', $params->date_range->from)->format('Y-m-d')." 00:00:00",
                    Carbon::createFromFormat('Y-m-d', $params->date_range->to)->format('Y-m-d')." 23:59:59"
                ]);
            }
            $stats = (object) [
                'total' => $order->sum('total'),
                'benefit' => $order->sum('payment') - $order->sum('cost'),
                'discount' => $order->sum('discount'),
              ];

            if($this->content['data'] =[
                'operations' => $order->orderby('created_at','desc')->paginate($request->perpage),
                'stats' => $stats
                 ]){
                $this->content['status'] = 200;
                return response()->json($this->content, $this->content['status'], [], JSON_NUMERIC_CHECK);
              }
      
              $this->content['error'] = "Server Error";
              $this->content['status'] = 500;
      
              return response()->json($this->content, $this->content['status'], [], JSON_NUMERIC_CHECK);
        }
    }
    public function viewOperation($id){
        $order = Operation::with(['order.order_detail.product','customer'])
                ->where('company_id', Auth::user()->company->id)
                ->where('id', $id)->first();
        return response()->json($order, 200);
    }

    public function printeReceipt(){
        $connector = new FilePrintConnector("php://stdout");
        $printer = new Printer($connector);
        $printer -> text("Hello World!\n");
        $printer -> cut();
        $printer -> close(); 
    }
}
