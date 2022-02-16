<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use App\CompanyType;
use Illuminate\Support\Facades\Auth;

class CompanyTypeController extends Controller
{
    public function __construct(){
        $this->content = array();
    }

    public function get(Request $request){
        $company = CompanyType::get();

        if($this->content['data'] = $company){
          $this->content['status'] = 200;
          return response()->json($this->content, $this->content['status']);
        }

        $this->content['error'] = "Server Error";
        $this->content['status'] = 500;

        return response()->json($this->content, $this->content['status']);
    }

    public function find(Request $request){
        $company = CompanyType::find($request->id);

        if($this->content['data'] = $company){
          $this->content['status'] = 200;
          return response()->json($this->content, $this->content['status']);
        }

        $this->content['error'] = "Server Error";
        $this->content['status'] = 500;

        return response()->json($this->content, $this->content['status']);
    }

    public function store(Request $request){
      $validateData = $request->validate([
        'name' => 'required|min:5|unique:company_types',
    ]);

      CompanyType::create(['name' => $request->input('name')]);
      
  }
}
