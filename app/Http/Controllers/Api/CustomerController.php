<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use App\Customer;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\CustomerImport;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use App\Exports\CustomerExport;

class CustomerController extends Controller
{
    public function __construct(){
        $this->content = array();
    }


    public function export(Request $request){

        return Excel::download(new CustomerExport($request), 'Clients.xlsx');

    }

    public function get(Request $request){
        $customer = Customer::where('company_id', Auth::user()->company->id);
        
        if($request->has('filter')){
            $params = (object) json_decode($request->filter, true);

            if(is_array($params->date_range)){
                $params->date_range = (object) $params->date_range;
            }
            if($params->name){
                $customer = $customer->where('full_name', 'like', '%' . $params->name . '%');
            }
            if($params->company){
                $customer = $customer->where('company_name', 'like', '%' . $params->company . '%');
            }
            if($params->email){
                $customer = $customer->where('email', 'like', '%' . $params->email . '%');
            }
            if($params->phone){
                $customer = $customer->where('phone', 'like', '%' . $params->phone . '%');
            }

            if(is_object($params->date_range) && ($params->date_range->from && $params->date_range->to)){
                $customer = $customer->whereBetween('created_at', [
                    Carbon::createFromFormat('Y-m-d', $params->date_range->from)->format('Y-m-d')." 00:00:00",
                    Carbon::createFromFormat('Y-m-d', $params->date_range->to)->format('Y-m-d')." 23:59:59"
                ]);
            }
        }

        if($this->content['data'] = $customer->paginate($request->perpage)){
          $this->content['status'] = 200;
          return response()->json($this->content, $this->content['status']);
        }

        $this->content['error'] = "Server Error";
        $this->content['status'] = 500;

        return response()->json($this->content, $this->content['status']);
    }

    public function find(Request $request){
        $customer = Customer::where('company_id', Auth::user()->company->id)->with(['operations' => function ($q) {
            $q->orderBy('created_at', 'desc')->take(10);
        }])->find($request->id);

        if($this->content['data'] = $customer){
          $this->content['status'] = 200;
          return response()->json($this->content, $this->content['status']);
        }

        $this->content['error'] = "Server Error";
        $this->content['status'] = 500;

        return response()->json($this->content, $this->content['status']);
    }

    public function addCustomer(Request $request){
        $request->validate([
            'import_file' => 'required|file|mimes:xls,xlsx',
          ]);
        $path = $request->file('import_file');
        $data = Excel::import(new CustomerImport, $path);
    }

    public function add(Request $request)
    {
        $validateData = $request->validate([
            'full_name' => 'required',
        ]); 
        $customer = Customer::create([
          'full_name' => $request->input('full_name'),
          'company_name' => $request->input('company_name'),
          'email' => $request->input('email'),
          'address' => $request->input('address'),
          'phone' => $request->input('phone'),
          'city' => $request->input('city'),
          'company_id' => Auth::user()->company->id
        ]);

        if($this->content['data'] = Customer::where('company_id', Auth::user()->company->id)->get()){
          $this->content['status'] = 200;
          return response()->json($this->content, $this->content['status']);
        }

        $this->content['error'] = "Server Error";
        $this->content['status'] = 500;

        return response()->json($this->content, $this->content['status']);
    }

    public function update(Request $request)
    {
        $validateData = $request->validate([
            'full_name' => 'required',
        ]);
        $customer = Customer::where('company_id', Auth::user()->company->id)->find($request->id);
        
        $customer->update([
          'full_name' => $request->input('full_name'),
          'company_name' => $request->input('company_name'),
          'email' => $request->input('email'),
          'address' => $request->input('address'),
          'phone' => $request->input('phone'),
          'city' => $request->input('city')
        ]);

        if($this->content['data'] = Customer::where('company_id', Auth::user()->company->id)->find($request->id)){
          $this->content['status'] = 200;
          return response()->json($this->content, $this->content['status']);
        }

        $this->content['error'] = "Server Error";
        $this->content['status'] = 500;

        return response()->json($this->content, $this->content['status']);
    }

    public function delete(Request $request)
    {
        $customer = Customer::where('company_id', Auth::user()->company->id)->find($request->id);
        
        $customer->delete();

        if(!$this->content['data'] = Customer::where('company_id', Auth::user()->company->id)->find($request->id)){
          $this->content['status'] = 200;
          return response()->json($this->content, $this->content['status']);
        }

        $this->content['error'] = "Server Error";
        $this->content['status'] = 500;

        return response()->json($this->content, $this->content['status']);
    }
}
