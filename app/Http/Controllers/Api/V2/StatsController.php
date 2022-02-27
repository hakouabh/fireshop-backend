<?php

namespace App\Http\Controllers\Api\V2;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Operation;
use App\Product;
use App\Customer;
use App\Order;
use App\Charge;
use DB;

class StatsController extends Controller
{
    public function stats(Request $request){
      $weeks = [0,1,2,3,4,5,6];
      $dt_from  = Carbon::now()->startOfWeek();
      $dt_to = Carbon::now()->endOfWeek();
      $lt_from  = Carbon::now()->subWeek()->startOfWeek();
      $lt_to = Carbon::now()->subWeek()->endOfWeek();

      $products = Product::where('company_id', Auth::user()->company->id)->whereNull('deleted_at')->count();
      $customers = Customer::where('company_id', Auth::user()->company->id)->whereNull('deleted_at')->count();
      $orders = Operation::where('company_id', Auth::user()->company->id)->count();

      $stat_amounts = Operation::where('company_id', Auth::user()->company->id)->get();

      $stat_charges = Charge::where('company_id', Auth::user()->company->id)->get();

      $stock_products = Product::where('company_id', Auth::user()->company->id)->where('stock','>',0)->whereNull('deleted_at')->get();

      $gross_salle = Operation::where('company_id', Auth::user()->company->id)->whereBetween('created_at', [
            $dt_from->toDateTimeString(),
            $dt_to->toDateTimeString()
      ])->max('total');

      $currentweeksRevenue = Operation::where('company_id', Auth::user()->company->id)
        ->whereBetween('created_at', [
          $dt_from->toDateTimeString(),
          $dt_to->toDateTimeString()
        ])
        ->select(DB::raw('DATE(created_at) as date'), DB::raw('sum(total) as revenue, count(id) as operation'))
        ->groupBy('date')
        ->orderBy('created_at')
        ->get();
      $lastweeksRevenue = Operation::where('company_id', Auth::user()->company->id)
            ->whereBetween('created_at', [
              $lt_from->toDateTimeString(),
              $lt_to->toDateTimeString()
            ])
              ->select(DB::raw('DATE(created_at) as date'), DB::raw('sum(total) as revenue, count(id) as operation'))
              ->groupBy('date')
              ->orderBy('created_at')
              ->get();

      $mapToZero = function($v) {
        return 0;
      };
      
      $graph = (object) [
        'current' => (array) array_map($mapToZero, $weeks),
        'last' => (array) array_map($mapToZero, $weeks)
      ];

      $graphoperation = (object) [
        'current' => (array) array_map($mapToZero, $weeks),
        'last' => (array) array_map($mapToZero, $weeks)
      ];

      $stats = (object) [
        'cost' => 0,
        'revenue' => 0,
        'profit' => 0,
        'charges' => 0,
        'discount' => 0,
        'cost_stock' => 0,
        'total_cost' => 0
      ];

      foreach($currentweeksRevenue as $current){
        $dbdate = Carbon::createFromFormat('Y-m-d', $current->date);
        $graph->current[$dbdate->dayOfWeek] = $current->revenue;
      }

      foreach($lastweeksRevenue as $last){
        $dbdate = Carbon::createFromFormat('Y-m-d', $last->date);
        $graph->last[$dbdate->dayOfWeek] = $last->revenue;
      }

      foreach($currentweeksRevenue as $current){
        $dbdate = Carbon::createFromFormat('Y-m-d', $current->date);
        $graphoperation->current[$dbdate->dayOfWeek] = $current->operation;
      }

      foreach($lastweeksRevenue as $last){
        $dbdate = Carbon::createFromFormat('Y-m-d', $last->date);
        $graphoperation->last[$dbdate->dayOfWeek] = $last->operation;
      }

      $cost_stock = $stock_products->sum(function ($row) {
        return $row->cost * $row->stock;
      });
      $costs = $stat_amounts->sum('cost');
      $revenues = $stat_amounts->sum('total');
      $payments = $stat_amounts->sum('payment');
      $discounts = $stat_amounts->sum('discount');
      $charges = $stat_charges->sum('amount');

      $stats->cost_stock = $cost_stock;
      $stats->charges = $charges;
      $stats->cost = $costs;
      $stats->revenue = $revenues;
      $stats->discount = $discounts;
      $stats->profit = $revenues - $costs;
      $stats->total_cost = $cost_stock + $costs;
      $stats->total_profit =$revenues -  ($cost_stock + $costs ) ;
        if($this->content['data'] = [
          'products' => $products,
          'gross_salle' => $gross_salle,
          'customers' => $customers,
          'orders' => $orders,
          'stats' => $stats,
          'graph' => $graph,
          'graphoperation' => $graphoperation
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
