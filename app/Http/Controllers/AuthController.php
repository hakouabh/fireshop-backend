<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Support\Facades\Storage;
use App\Company;
use App\Autorisation;
use App\CompanyType;
use DB;
use Log;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
class AuthController extends Controller
{
    public function __construct(){
        $this->content = array();
    }

    public function login(Request $request){
        $validateData = $request->validate([
            'email' => 'required',
            'password' => 'required',
        ]);
        if(Auth::attempt([
          'email' => $request->input('email'),
          'password' => $request->input('password')
        ])){
            $id = Auth::user()->id;
            $user = User::where('id', $id)->with('company', 'autorisation')->first();
            $this->content['access_token'] =  $user->createToken($user->name . ' App')->accessToken;
            $this->content['token_type'] = 'Bearer';
            $this->content['expires_in'] =  31536000;
            $this->content['user'] = $user;
            $status = 200;
        }
        else{
            $this->content['error'] = "Unauthorized";
            $status = 401;
        }
        return response()->json($this->content, $status);
    }

    public function register(Request $request){

        $validateData = $request->validate([
            'email' => 'required|unique:users|max:255',
            'name' => 'required',
            'phone' => 'required',
            'password' => 'required|min:8|confirmed',
            'company' => 'required'
        ]);

        $company = Company::create([
            'name' => $request->input('company.name'),
            'expire_at' => Carbon::now()->addMonth(),
            'type_id' => ($request->input('company.type.id')) ? $request->input('company.type.id') : CompanyType::create(['name' => $request->input('company.type')])->id
        ]);

        $user = User::create([
            'name' => $request->input('name'),
            'email' => $request->input('email'),
            'phone' => $request->input('phone'),
            'company_id' => $company->id,
            'password' => bcrypt($request->input('password')),
        ]);
        $autorisation = Autorisation::create([
            'user_id' => $user->id,
        ]);

        if(Auth::attempt([
          'email' => $request->input('email'),
          'password' => $request->input('password')
        ])){
            $id = Auth::user()->id;
            $user = User::where('id', $id)->with('company', 'autorisation')->first();
            $this->content['access_token'] =  $user->createToken($user->name . ' App')->accessToken;
            $this->content['token_type'] = 'Bearer';
            $this->content['expires_in'] =  31536000;
            $this->content['user'] = $user;
            $status = 200;
        }
        else{
            $this->content['error'] = "Unauthorized";
            $status = 401;
        }
        return response()->json($this->content, $status);
    }

    /**
     * Log the user out of the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function logout(Request $request)
    {
        $user = Auth::user();
        if($user->oatoken()->delete()){
            $this->content['message'] = 'success';
            $status = 200;
        }else{
            $this->content['message'] = 'error';
            $status = 500;
        }

        return response()->json($this->content, $status);
    }

    public function user($id)
    {
        $user = User::with('autorisation')->leftJoin('operations', 'operations.user_id', '=', 'users.id')
            ->join('companies', 'users.company_id', '=', 'companies.id')
            ->where('users.id',$id)
            ->select('users.*',DB::raw('sum(operations.total) as revenue, sum(operations.total - operations.cost) as profit'))->first();
        return response()->json($user, 200);
    }

    public function addUser(Request $request){

        $validateData = $request->validate([
            'email' => 'required|unique:users|max:255',
            'name' => 'required',
            'role' => 'required',
            'password' => 'required|min:8|confirmed',
        ]);

        $image = null;
        if($request->hasFile('image')){
            if(!Storage::exists('public/users/'.Auth::user()->company->id.'/')) {
            
                Storage::makeDirectory('public/users/'.Auth::user()->company->id.'/'); //creates directory
            
            }
    
            $ext = $request->file('image')->getClientOriginalExtension();
            $filename = time().".".$ext;
        
            $path =Storage::putFileAs('public/users/'.Auth::user()->company->id.'/', $request->file('image'), $filename); 

            $image = env('APP_URL').Storage::disk('local')->url($path);
        }
        $user = User::create([
            'name' => $request->input('name'),
            'email' => $request->input('email'),
            'role' => $request->input('role'),
            'image' => $image,
            'company_id' => Auth::user()->company->id,
            'password' => bcrypt($request->input('password')),
        ]);

        $this->createAuth($request->input('role'), $user->id);

        return response()->json('success', 200);
    }

    public function createAuth($role, $user_id){
        $auth = new Autorisation;
        $auth->user_id = $user_id;
            switch ($role) {
                case 1:
                    $auth->product_update = false;
                    $auth->stock_update = false;
                    $auth->charge_update = false;
                    $auth->corbeille = false;
                    break;
                case 2:
                    $auth->charge_list = false;
                    $auth->charge_add = false;
                    $auth->charge_update = false;
                    $auth->dashboard = false;
                    $auth->corbeille = false;
                    break;
            }
            $auth->save();
    }

    public function users(){
        
        $users = User::where('company_id', Auth::user()->company->id)->get();

        return response()->json($users, 200);
    }

    public function updateAuth(Request $request){
        $autorisation = Autorisation::find($request->id);
        $autorisation->update($request->all());

    }
}
