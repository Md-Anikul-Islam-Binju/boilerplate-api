<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;


class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validate = Validator::make($request->all(),[
            'name' => 'required',
            'phone' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6|confirmed'

        ]);
        if($validate->fails()){
            return response()->json([
                'errors' => $validate->errors()
            ],422);
        }
        $reqData = request()->only('name','phone','email','password');
        $reqData['password'] = Hash::make($request->password);
        $user = User::create($reqData);
        Auth::login($user);

        $data['token'] = $user->createToken('userToken')->accessToken;
        $data['user'] = $user;
        return response()->json($data,200);

    }
    public function login(Request $request)
    {
       $validate = Validator::make($request->all(),[
           'email' => 'required|email|exists:users',
           'password' => 'required'

       ]);
       if($validate->fails()){
           return response()->json([
               'errors' => $validate->errors()
           ],422);
       }

       $reqData = request()->only('email','password');
       if(Auth::attempt($reqData)){
           $user = Auth::user();
           $data['token'] = $user->createToken('userToken')->accessToken;
           $data['user'] = $user;
           return response()->json($data,200);
       }else{
           $data['loginField'] = 'Email Or Password Incorrect';
           return response()->json([
               'errors' => $data
           ],401);
       }
    }


    public function logout()
    {
        Auth::user()->token()->revoke();
        return response()->json([
            'message' => 'User Logout Successfully'
        ],200);
    }
}
