<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SkillController;
use App\Http\Controllers\AuthController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

//Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//    return $request->user();
//});

#看技能
Route::get('/show', [SkillController::class, 'show']);

#註冊
Route::post('/register', [AuthController::class, 'register']);
#登入
Route::post('/login', [AuthController::class, 'login']);

#需要驗證才能使用的路由
Route::group(['middleware' => 'auth:sanctum'], function () {
    #新增技能
    Route::post('/addSkill', [SkillController::class, 'addSkill']);
    #登出
    Route::post('/logout', [AuthController::class, 'logout']);
    #拿使用者資料
    Route::post('/userAuth', [AuthController::class, 'userAuth']);
});
