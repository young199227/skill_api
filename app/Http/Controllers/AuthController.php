<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{

    public function register(Request $request)
    {
        User::create([
            'name' => $request->name,
            'password' => Hash::make($request->password),
        ]);

        return response()->json(['type' => $request->name]);
    }

    public function login(Request $request)
    {
        $user = User::where('name', '=', $request->name)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['type' => '登入失敗']);
        }

        $token = $user->createToken('token')->plainTextToken;

        return response()->json(['type' => $token]);

    }

    public function logout(Request $request)
    {
//        $user = Auth::user();
////        return response()->json(['type' => $user]);
//
//        if(Auth::check()){
            return response()->json(['type' => '已登入']);
//        }
    }

}
