<?php

namespace App\Http\Controllers;

use App\Models\Skill;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;


class SkillController extends Controller
{
    #新增技能
    public function addSkill(Request $request)
    {

        #驗證(失敗API回傳422)
        $validator = Validator::make($request->all(), [
            'sort' => 'required',
            'type' => 'required',
            'name' => 'required',
            'describe' => 'required',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:1024'
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        #取使用者資料
        $user = Auth::user();

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
                "last_upd_user" => $user->name,
            ]);

            Log::info('新增:' . $add->name);
            return response()->json(['type' => 0, 'message' => '新增成功', 'data' => $add]);
            #
        } catch (\Exception $e) {
            #sql異常,寫日誌->刪圖片->回傳500
            Log::error($e->getMessage());
            Storage::delete($storagePath);
            return response()->json(['type' => 1, 'message' => '資料庫異常'], 500);
        }
    }

    #看技能
    public function show(string $type)
    {
        $skillData = Skill::where('type', $type)->orderBy('sort', 'asc')->get();

        return response()->json(['type' => 0, 'data' => $skillData]);
    }

    public function edit(Request $request)
    {
        //
    }

    #修改技能
    public function updateSkill(Request $request)
    {
        #驗證(失敗API回傳422)
        $validator = Validator::make($request->all(), [
            'id' => 'required',
            'sort' => 'required',
            'type' => 'required',
            'name' => 'required',
            'describe' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        #取使用者資料
        $user = Auth::user();

        try {
            #存入資料庫&寫log
            Skill::where('id', '=', $request->id)->update([
                "sort" => $request->sort,
                "type" => $request->type,
                "name" => $request->name,
                "describe" => $request->describe,
                "last_upd_user" => $user->name,
            ]);

            Log::info('修改Skill id:' . $request->id);
            return response()->json(['type' => 0, 'message' => '修改成功']);
            #
        } catch (\Exception $e) {
            #sql異常,寫日誌->刪圖片->回傳500
            Log::error($e->getMessage());
            return response()->json(['type' => 1, 'message' => '資料庫異常'], 500);
        }
    }

    #修改技能圖片
    public function updateSkillImg(Request $request)
    {
        $checkImg = Skill::where('img_url', '=', $request->imageUrl);

        if (!$checkImg) {
        }
    }
}
