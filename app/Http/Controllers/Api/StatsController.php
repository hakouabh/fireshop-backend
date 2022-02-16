<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use DB;
use App\Order;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class StatsController extends Controller
{
    public function __construct(){
        $this->content = array();
    }

    public function stats(Request $request){
        $weeks = [0,1,2,3,4,5,6];
        $dt = Carbon::now();
        $lt = Carbon::now()->subWeek();
        $products = DB::table('products')->where('company_id', Auth::user()->company->id)->whereNull('deleted_at')->count();
        $customers = DB::table('customers')->where('company_id', Auth::user()->company->id)->whereNull('deleted_at')->count();
        $orders = DB::table('orders')->where('company_id', Auth::user()->company->id)->whereNull('deleted_at')->count();

        $stat_amounts = DB::table('order_details')
                 ->join('products', 'order_details.product_id', '=', 'products.id')
                 ->join('orders', 'order_details.order_id', '=', 'orders.id')
                 ->where([
                   'products.company_id' => Auth::user()->company->id,
                   'orders.deleted_at' => null
                  ])
                  ->select(DB::raw('product_id, count(product_id) as unit, amount, discount,sum(total_order) as total_order ,sum(discount) as total, sum(amount) as sold, order_details.cost as total_cost'))
                  ->groupBy('product_id')
                  ->get();
        $stat_charges = DB::table('charges')
                 ->join('charge_types', 'charges.type_id', '=', 'charge_types.id')
                 ->where([
                   'charges.company_id' => Auth::user()->company->id,
                   'charges.deleted_at' => null
                  ])
                  ->select(DB::raw('type_id, sum(amount) as total_charge'))
                  ->groupBy('type_id')
                  ->get();
        
        $_pre = Order::with(['order_detail.product', 'customer'])
                ->where('company_id', Auth::user()->company->id);
        
        $currentWeekRevenue = $_pre
          ->whereBetween('created_at', [
            $dt->startOfWeek()->toDateTimeString(),
            $dt->endOfWeek()->toDateTimeString()
          ])
          ->get();

        $lastWeekRevenue = $_pre
          ->whereBetween('created_at', [
            $lt->startOfWeek()->toDateTimeString(),
            $lt->endOfWeek()->toDateTimeString()
          ])
          ->get();

        $mapToZero = function($v) {
          return 0;
        };
        
        $graph = (object) [
          'current' => (array) array_map($mapToZero, $weeks),
          'last' => (array) array_map($mapToZero, $weeks)
        ];
        
        $stats = (object) [
          'cost' => 0,
          'revenue' => 0,
          'profit' => 0,
          'charges' => 0
        ];

        foreach ($weeks as $index) {
          foreach ($currentWeekRevenue as $current) {
            $curdate = $dt->startOfWeek()->addDays($index + 1);
            $dbdate = Carbon::createFromFormat('Y-m-d H:i:s', $current->created_at)->format('Y-m-d');

            if($curdate->format('Y-m-d') === $dbdate) {
              $revenue = $current->order_detail->total_order;
              $graph->current[$curdate->dayOfWeek] += $revenue;
            }
          }
          
          foreach ($lastWeekRevenue as $last) {
            $curdate = $lt->startOfWeek()->addDays($index + 1);
            $dbdate = Carbon::createFromFormat('Y-m-d H:i:s', $last->created_at)->format('Y-m-d');

            if($curdate->format('Y-m-d') === $dbdate) {
              $revenue = $last->order_detail->total_order;
              $graph->last[$curdate->dayOfWeek] += $revenue;
            }
          }
        }

        foreach ($stat_charges as $index) {
          $charges = $index->total_charge;
          $stats->charges += $charges;
        }

        foreach ($stat_amounts as $index) {
          $pre = DB::table('products')
                     ->where('company_id', Auth::user()->company->id)
                     ->find($index->product_id);
          $cost = ($pre->cost * $pre->stock)+($index->sold *$index->total_cost);
          $revenue =$index->total_order;
          $stats->cost += $cost;
          $stats->revenue += $revenue;
          $stats->profit += $revenue - $cost;
        }

        if($this->content['data'] = [
          'products' => $products,
          'customers' => $customers,
          'orders' => $orders,
          'stats' => $stats,
          'graph' => $graph
        ]){
          $this->content['status'] = 200;
          return response()->json($this->content, $this->content['status'], [], JSON_NUMERIC_CHECK);
        }

        $this->content['error'] = "Server Error";
        $this->content['status'] = 500;

        return response()->json($this->content, $this->content['status'], [], JSON_NUMERIC_CHECK);
    }

    public function topProducts(Request $request){
        $top_products = DB::table('order_details')
                 ->join('products', 'order_details.product_id', '=', 'products.id')
                 ->join('orders', 'order_details.order_id', '=', 'orders.id')
                 ->where([
                   'products.company_id' => Auth::user()->company->id,
                   'orders.deleted_at' => null
                  ])
                 ->select(DB::raw('products.id, products.name, products.stock, count(product_id) as orders, sum(amount) as sold'))
                 ->orderBy('sold', 'desc')
                 ->groupBy('product_id')
                 ->take(5)
                 ->get();

        if($this->content['data'] = $top_products){
          $this->content['status'] = 200;
          return response()->json($this->content, $this->content['status'], [], JSON_NUMERIC_CHECK);
        }

        $this->content['error'] = "Server Error";
        $this->content['status'] = 500;

        return response()->json($this->content, $this->content['status'], [], JSON_NUMERIC_CHECK);
    }
}
