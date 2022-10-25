<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\CustomAuthController;
use App\Http\Controllers\API\AppController;
use App\Http\Controllers\API\PermissionController;
use App\Http\Controllers\API\GroupAppSettingsController;
use App\Http\Controllers\API\GroupAppPermissionsController;
use App\Http\Controllers\API\UserController;
use App\Http\Controllers\API\GroupController;
use App\Http\Controllers\API\FormController;
use App\Http\Controllers\API\FormControllerNoLogin;
use App\Http\Controllers\API\SubmissionControllerNoLogin;
use App\Http\Controllers\API\TagController;
use App\Http\Controllers\API\SubmissionController;
use App\Http\Controllers\API\StundenzettelController;
use App\Http\Controllers\API\ApplicantController;
use App\Http\Controllers\API\BewerberController;
use App\Http\Controllers\API\EntranceExamController;
use App\Http\Controllers\API\BescheidController;
use App\Http\Controllers\API\LehrveranstaltungController;

use App\Http\Controllers\API\TestingTheApi;


/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::get('/auth/login', [CustomAuthController::class, 'shibLogin']);
Route::get('/login', [CustomAuthController::class, 'shibLogin']);
Route::get('/auth/logout', [CustomAuthController::class, 'logout']);


Route::apiResource('/apps', AppController::class)->middleware('authentication:1');
Route::apiResource('/permissions', GroupAppPErmissionsController::class)->middleware('authentication:6');


Route::get('/users', [UserController::class, 'index'])->middleware('authentication:39');
Route::get('/users/{id}', [UserController::class, 'show'])->middleware('authentication:39');
Route::post('/users/{id}', [UserController::class, 'update'])->middleware('authentication:39');

Route::apiResource('/groups', GroupController::class)->middleware('authentication:40');


Route::apiResource('/forms', FormController::class)->middleware('authentication:38');

Route::get('/submissions', [SubmissionController::class, 'index'])->middleware('authentication:37');
Route::post('/submissions', [SubmissionController::class, 'store'])->middleware('authentication:37');
Route::post('/submissions/{id}', [SubmissionController::class, 'update'])->middleware('authentication:37');
Route::delete('/submissions/{id}', [SubmissionController::class, 'destroy'])->middleware('authentication:37');


Route::get('/testing-the-api', [TestingTheApi::class, 'test']);
Route::get('/auth/testing-the-api', [TestingTheApi::class, 'test']);


// Route::apiResource('/tags', TagController::class)->middleware('rights');
// Route::apiResource('/groupappsettings', GroupAppSettingsController::class)->middleware('rights');
// Route::apiResource('/forms_nologin', FormControllerNoLogin::class);
// Route::apiResource('/submit_nologin', SubmissionControllerNoLogin::class);
// Route::apiResource('/stundenzettel', StundenzettelController::class);
// Route::apiResource('/applicants', ApplicantController::class);









// Route::apiResource('/entrance_exam', EntranceExamController::class)->middleware('rights');
// Route::apiResource('/bewerberportal', BewerberController::class)->middleware('rights');
// // Route::apiResource('/bescheid', BescheidController::class)->middleware('rights');
// Route::apiResource('/bescheid', BescheidController::class);
// Route::post('/bewerberportal/login', [BewerberController::class, 'login']);
// Route::post('/bewerberportal/register', [BewerberController::class, 'register']);
// Route::post('/bewerberportal/data_protection', [BewerberController::class, 'data_protection']);


// Route::post('/lv/upload', [LehrveranstaltungController::class, 'upload']);


// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });
