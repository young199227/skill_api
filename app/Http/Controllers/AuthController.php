<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class AuthController extends Controller
{
    #註冊
    public function register(Request $request)
    {
        #驗證(失敗API回傳422)
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'password' => ['required', Password::min(6)],
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        #查詢是否有相同帳號
        $checkUserName = User::where('name', '=', $request->name);
        if ($checkUserName) {
            return response()->json(['type' => 1, 'message' => '已有相同帳號'], 422);
        }

        #新增使用者&異常處理
        try {
            User::create([
                'name' => $request->name,
                'password' => Hash::make($request->password),
            ]);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return response()->json(['type' => 500, 'message' => '資料庫異常'], 500);
        }


        return response()->json(['type' => 0, 'message' => $request->name . '註冊成功']);
    }

    #登入
    public function login(Request $request)
    {
        #驗證(失敗API回傳422)
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'password' => ['required', Password::min(6)],
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        #查詢相同帳號&輸入密碼驗證
        $user = User::where('name', '=', $request->name)->first();
        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['type' => 1, 'message' => '號帳或密碼錯誤'], 422);
        }

        #重複登入->砍掉原本的token重新發放
        $checkUserLogin = DB::table('personal_access_tokens')->where('tokenable_id', '=', $user->id)->first();
        if ($checkUserLogin) {
            $user->tokens()->where('id', $checkUserLogin->id)->delete();
        }

        #發送token(系統設定3小時會失效)
        $token = $user->createToken('token')->plainTextToken;

        #對登入者發送cookie(cookie存活 台灣時間3小時後)
        return response()->json(['type' => 0, 'message' => '登入成功'])->cookie('skill_token', $token, 660);
    }

    #登出
    public function logout(Request $request)
    {
        $user = Auth::user();
        $request->user()->currentAccessToken()->delete();

        return response()->json(['type' => 0, 'message' => $user->name . '已登出']);
    }

    #拿使用者資料
    public function userAuth(Request $request)
    {
        $user = Auth::user();

        return response()->json(['type' => 0, 'data' => $user]);
    }
}
