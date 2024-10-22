<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Models\User;
use Auth;
use Validator;

class UserController extends Controller
{
    public $successStatus = 200;

    public function login(){
        if(Auth::attempt(['email' => request('email'), 'password' => request('password')])){ 
            $user = Auth::user();  
            
            $success['token'] =  'Bearer '.$user->createToken('MyApp')-> accessToken; 
            return response()->json(['success' => $success,'user'=>$user], $this-> successStatus); 
        } 
        else{ 
            return response()->json(['error'=>'Unauthorised'], 401); 
        } 
    }

    public function getUsers()
    {
        $usuarios = User::all();

        return response()->json($usuarios); 

    }

    public function addUser(Request $request){
        $user = new User();
        $user->name = $request->name;
        $user->email = $request->email;
        $user->password = bcrypt($request->password);
        if ($user->save()) {
            return response()->json(array(
                'mensaje' => 1
            )); 
        }
    }
}
