<?php

namespace App\Http\Controllers;

use App\Models\Skill;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Str;


class SkillController extends Controller
{
    public function addSkill(Request $request)
    {

        #驗證(失敗API回傳422)
        $validator = Validator::make($request->all(), [
            'sort' => 'required',
            'type' => 'required',
            'name' => 'required',
            'describe' => 'required',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:1024',
            'last_upd_user' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        #宣告圖片路徑變數
        $storagePath = '';

        try {
            #儲存圖片&取圖片路徑
            $storagePath = Storage::put('/public/images/skill', $request->file('image'));
            $imageUrl = Storage::url($storagePath);

            #存入資料庫&寫log
            $add = Skill::create([
                "sort" => $request->sort,
                "type" => $request->type,
                "name" => $request->name,
                "describe" => $request->describe,
                "img_url" => $imageUrl,
                "last_upd_user" => $request->last_upd_user,
            ]);
        } catch (\Exception $e) {
            #sql異常,寫日誌->刪圖片->回傳500
            Log::error($e->getMessage());
            Storage::delete($storagePath);
            return response()->json(['type' => 1, 'message' => '資料庫異常'], 500);
        }

        Log::info('新增:' . $add->name);
        return response()->json(['type' => 0, 'message' => $add]);
    }

    public function show(Skill $order)
    {
        return Skill::all();
    }

    public function edit(Skill $order)
    {
        //
    }

    public function update(Request $request, Skill $order)
    {
        //
    }

    public function addCookie(Request $request)
    {

        #已經有cookie就不重複發放
        if ($request->hasCookie('skill_token')) {

            $cookieData = CookieSql::where('cookie', $request->cookie('skill_token'))->first();
            if ($cookieData) {
                return response()->json(['type' => 0, 'message' => '已有cookie']);
            }
        }

        #如果沒有發一個新cookie&&新增
        $randomToken = Str::random(64);
        #存在時間為(現在台灣時間+2小時)
        $cookie = CookieAdd::make('skill_token', $randomToken, 600);
        CookieSql::create([
            "cookie" => $randomToken,
            "ip" => $request->ip(),
        ]);

        #刪除超過2小時候的cookie
        $twoHoursAgo = Carbon::now()->copy()->subHours(2); // 获取两小时前的时间
        CookieSql::where('created_at', '<', $twoHoursAgo)->delete();

        return response()->json(['type' => 0, 'message' => $randomToken])->cookie($cookie);
    }

    public static function checkCookie(string $cookie)
    {

    }
}
