<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use App\ChargeType;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;

class ChargeTypeController extends Controller
{
    public function __construct(){
        $this->content = array();
    }

    public function get(Request $request){
        $charge = ChargeType::where('company_id', Auth::user()->company->id)->get();

        if($this->content['data'] = $charge){
          $this->content['status'] = 200;
          return response()->json($this->content, $this->content['status']);
        }

        $this->content['error'] = "Server Error";
        $this->content['status'] = 500;

        return response()->json($this->content, $this->content['status']);
    }

    public function find(Request $request){
        $charge = ChargeType::where('company_id', Auth::user()->company->id)->find($request->id);

        if($this->content['data'] = $charge){
          $this->content['status'] = 200;
          return response()->json($this->content, $this->content['status']);
        }

        $this->content['error'] = "Server Error";
        $this->content['status'] = 500;

        return response()->json($this->content, $this->content['status']);
    }
    public function store(Request $request){
      $validateData = $request->validate([
        'name' => ['required',Rule::unique('charge_types')->where(function ($query) use ($request) {
          return $query->where('company_id', Auth::user()->company->id);
      })],
    ]);

      ChargeType::create([
        'name' => $request->input('name'),
        'company_id' => Auth::user()->company->id
    ]);
      
  }
}
