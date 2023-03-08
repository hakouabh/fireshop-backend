<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use App\Charge;
use App\ChargeType;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use DB;
use App\Exports\ChargeExport;
use Maatwebsite\Excel\Facades\Excel;


class ChargeController extends Controller
{
    public function __construct(){
        $this->content = array();
    }

    public function export(Request $request){

      return Excel::download(new ChargeExport($request), 'Prélèvements.xlsx');

  }

    public function get(Request $request){
        $charge = Charge::with('type')
                            ->where('company_id', Auth::user()->company->id);
                            
        if($request->has('filter')){
            $params = (object) json_decode($request->filter, true);
            
            if(is_array($params->type)){
                $params->type = (object) $params->type;
            }
            if(is_array($params->date_range)){
                $params->date_range = (object) $params->date_range;
            }
            if(is_object($params->type) && $params->type->id){
                $charge = $charge->where('type_id', $params->type->id);
            }
            if(is_array($params->user)){
                $params->user = (object) $params->user;
                $charge = $charge->where('user_id',$params->user->id);
            }
            if(is_object($params->date_range) && ($params->date_range->from && $params->date_range->to)){
                $charge = $charge->whereBetween('created_at', [
                    Carbon::createFromFormat('Y-m-d', $params->date_range->from)->format('Y-m-d')." 00:00:00",
                    Carbon::createFromFormat('Y-m-d', $params->date_range->to)->format('Y-m-d')." 23:59:59"
                ]);
            }
        }
          if($this->content['data'] = $charge->paginate($request->perpage)){
          $this->content['status'] = 200;
          $this->content['total_charges'] = $charge->sum('amount');
          return response()->json($this->content, $this->content['status'], [], JSON_NUMERIC_CHECK);
        }

        $this->content['error'] = "Server Error";
        $this->content['status'] = 500;

        return response()->json($this->content, $this->content['status'], [], JSON_NUMERIC_CHECK);
    }

    public function find(Request $request){
        $charge = Charge::with('type')
                            ->where('company_id', Auth::user()->company->id)
                            ->find($request->id);

        if($this->content['data'] = $charge){
          $this->content['status'] = 200;
          return response()->json($this->content, $this->content['status'], [], JSON_NUMERIC_CHECK);
        }

        $this->content['error'] = "Server Error";
        $this->content['status'] = 500;

        return response()->json($this->content, $this->content['status'], [], JSON_NUMERIC_CHECK);
    }

    public function add(Request $request)
    {   
      $validateData = $request->validate([
          'amount' => 'required',
          'type_id' => 'required'
      ]);
        $charge = Charge::create([
          'amount' => $request->input('amount'),
          'type_id' => $request->input('type_id'),
          'company_id' => Auth::user()->company->id,
          'user_id' => Auth::user()->id,
          'site_id' => Auth::user()->site->id,
          'description' => $request->input('description')
        ]);

        if($this->content['data'] = Charge::with('type')->where('company_id', Auth::user()->company->id)->get()){
          $this->content['status'] = 200;
          return response()->json($this->content, $this->content['status'], [], JSON_NUMERIC_CHECK);
        }

        $this->content['error'] = "Server Error";
        $this->content['status'] = 500;

        return response()->json($this->content, $this->content['status'], [], JSON_NUMERIC_CHECK);
    }

    public function update(Request $request)
    {
        $charge = Charge::with('type')
                            ->where('company_id', Auth::user()->company->id)
                            ->find($request->id);
        
        $charge->update([
          'amount' => $request->input('amount'),
          'type_id' => ($request->input('type.id')) ? $request->input('type.id') : ChargeType::create(['name' => $request->input('type')])->id,
          'description' => $request->input('description')
        ]);

        if($this->content['data'] = Charge::with('type')->where('company_id', Auth::user()->company->id)->find($request->id)){
          $this->content['status'] = 200;
          return response()->json($this->content, $this->content['status'], [], JSON_NUMERIC_CHECK);
        }

        $this->content['error'] = "Server Error";
        $this->content['status'] = 500;

        return response()->json($this->content, $this->content['status'], [], JSON_NUMERIC_CHECK);
    }

    public function delete(Request $request)
    {
        $charge = Charge::with('type')
                            ->where('company_id', Auth::user()->company->id)
                            ->find($request->id);
        
        $charge->delete();

        if(!$this->content['data'] = Charge::with('type')->where('company_id', Auth::user()->company->id)->find($request->id)){
          $this->content['status'] = 200;
          return response()->json($this->content, $this->content['status'], [], JSON_NUMERIC_CHECK);
        }

        $this->content['error'] = "Server Error";
        $this->content['status'] = 500;

        return response()->json($this->content, $this->content['status'], [], JSON_NUMERIC_CHECK);
    }
}
