<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Key;

class LicenseController extends Controller
{
    public function gerenateKey(Request $request){
        $validateData = $request->validate([
            'plan_type' => 'required'
        ]);
        $hasKey = Key::where('company_id', Auth::user()->company_id)->where('plan_type', 1)->first();
        if(!$hasKey){
            $key = Key::create(
                [
                    'status' => true,
                    'lifetime' => 60 * 60 * 24 * 15,
                    'plan_type' => $request->plan_type,
                    'company_id' => Auth::user()->company->id,
                    'activated_at' => now()
                ]
            );
        }else{
            return response()->json('Not Acceptable', 406);
        }
    }
}
