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
use App\Http\Controllers\API\ReplyController;
use App\Http\Controllers\API\LehrveranstaltungController;
use App\Http\Controllers\API\UploadController;
use App\Http\Controllers\API\MetaController;
use App\Http\Controllers\API\ActionController;
use App\Http\Controllers\API\EmailController;
use App\Http\Controllers\API\ArchiveController;
use App\Http\Controllers\API\GraduateController;

use App\Http\Controllers\API\TestingTheApi;
use Illuminate\Support\Facades\Storage;


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

Route::get('/auth/login', [CustomAuthController::class, 'shibLogin'])->middleware('log_action')->middleware('maintenance');
Route::get('/login', [CustomAuthController::class, 'shibLogin'])->middleware('log_action')->middleware('maintenance');
Route::get('/auth/logout', [CustomAuthController::class, 'logout'])->middleware('log_action');


Route::apiResource('/apps', AppController::class)->middleware('log_action')->middleware('maintenance')->middleware('authentication:1');
Route::apiResource('/tags', TagController::class)->middleware('log_action')->middleware('maintenance')->middleware('localhost')->middleware('authentication:36');
Route::apiResource('/permissions', GroupAppPErmissionsController::class)->middleware('log_action')->middleware('maintenance')->middleware('authentication:6');


Route::get('/users', [UserController::class, 'index'])->middleware('log_action')->middleware('maintenance')->middleware('authentication:39');
Route::get('/users/{id}', [UserController::class, 'show'])->middleware('log_action')->middleware('maintenance')->middleware('authentication:39');
Route::post('/users/{id}', [UserController::class, 'update'])->middleware('log_action')->middleware('maintenance')->middleware('authentication:39');

Route::apiResource('/groups', GroupController::class)->middleware('log_action')->middleware('maintenance')->middleware('authentication:40');

Route::post('/seen/{id}', [SubmissionController::class, 'seen'])->middleware('log_action')->middleware('maintenance')->middleware('authentication:37');




Route::post('/reply', [ReplyController::class, 'store'])->middleware('log_action')->middleware('maintenance')->middleware('authentication:41');
Route::post('/reply/{submission_id}', [ReplyController::class, 'replyToSubmission'])->middleware('log_action')->middleware('maintenance')->middleware('authentication:41');





Route::get('/forms', [FormController::class, 'index'])->middleware('log_action')->middleware('maintenance')->middleware('authentication:38');



Route::post('/email', [EmailController::class, 'store'])->middleware('log_action')->middleware('maintenance')->middleware('authentication:47');




// Route::get('/public/forms/{id}', [FormController::class, 'show_public']);
Route::get('/forms/copy/{id}', [FormController::class, 'copy'])->middleware('log_action')->middleware('maintenance')->middleware('authentication:38');
Route::get('/forms/{id}', [FormController::class, 'show'])->middleware('log_action')->middleware('maintenance')->middleware('localhost');
Route::post('/forms', [FormController::class, 'store'])->middleware('log_action')->middleware('maintenance')->middleware('authentication:38');
Route::post('/forms/{id}', [FormController::class, 'update'])->middleware('log_action')->middleware('maintenance')->middleware('authentication:38');
Route::delete('/forms/{id}', [FormController::class, 'destroy'])->middleware('log_action')->middleware('maintenance')->middleware('authentication:38');

Route::post('/forms/upload/{id}', [FormController::class, 'upload'])->middleware('log_action')->middleware('maintenance')->middleware('authentication:38');
Route::get('/submissions', [SubmissionController::class, 'index'])->middleware('log_action')->middleware('maintenance')->middleware('authentication:37');
Route::get('/submissions/bescheide', [SubmissionController::class, 'indexBescheide'])->middleware('log_action')->middleware('maintenance')->middleware('authentication:37');

Route::post('/submissions', [SubmissionController::class, 'store'])->middleware('log_action')->middleware('maintenance')->middleware('localhost');
Route::get('/submissions/confirm/{id}', [SubmissionController::class, 'confirm'])->middleware('log_action')->middleware('maintenance');
Route::post('/submissions/{id}', [SubmissionController::class, 'update'])->middleware('log_action')->middleware('maintenance')->middleware('authentication:37');
Route::delete('/submissions/{id}', [SubmissionController::class, 'destroy'])->middleware('log_action')->middleware('maintenance')->middleware('authentication:37');


Route::get('/testing-the-api', [TestingTheApi::class, 'test'])->middleware('log_action')->middleware('maintenance');
Route::post('/testing-the-api', [TestingTheApi::class, 'test_post'])->middleware('log_action')->middleware('maintenance');
Route::get('/auth/testing-the-api', [TestingTheApi::class, 'test'])->middleware('log_action')->middleware('maintenance');

Route::post('/lv/upload', [LehrveranstaltungController::class, 'upload'])->middleware('log_action')->middleware('maintenance');



Route::post('/bewerberportal/login', [BewerberController::class, 'login'])->middleware('log_action')->middleware('maintenance');
Route::post('/bewerberportal/register', [BewerberController::class, 'register'])->middleware('log_action')->middleware('maintenance');
Route::post('/bewerberportal/data_protection', [BewerberController::class, 'data_protection'])->middleware('log_action')->middleware('maintenance');


Route::post('/upload/table', [UploadController::class, 'table'])->middleware('log_action')->middleware('maintenance')->middleware('authentication:42');
Route::get('/upload/get_tables', [UploadController::class, 'getTables'])->middleware('log_action')->middleware('maintenance')->middleware('authentication:42');
Route::post('/upload/get_rows', [UploadController::class, 'getRows'])->middleware('log_action')->middleware('maintenance')->middleware('authentication:42');
Route::post('/upload/get_table', [UploadController::class, 'getTable'])->middleware('log_action')->middleware('maintenance')->middleware('authentication:42');
Route::post('/upload/get_columns', [UploadController::class, 'getColumns'])->middleware('log_action')->middleware('maintenance')->middleware('authentication:42');
Route::post('/upload/commit', [UploadController::class, 'commit'])->middleware('log_action')->middleware('maintenance')->middleware('authentication:42');

Route::get('/meta', [MetaController::class, 'index'])->middleware('log_action')->middleware('maintenance');
Route::post('/meta', [MetaController::class, 'update'])->middleware('log_action')->middleware('maintenance')->middleware('authentication:45');

Route::apiResource('/actions', ActionController::class)->middleware('log_action')->middleware('maintenance')->middleware('localhost')->middleware('authentication:46');
Route::get('/sessions', [ActionController::class, 'sessions'])->middleware('log_action')->middleware('maintenance')->middleware('localhost')->middleware('authentication:46');


Route::get('/_forms', [FormController::class, 'getForms'])->middleware('log_action')->middleware('maintenance')->middleware('localhost')->middleware('authentication:38');
Route::post('/_forms', [FormController::class, 'postForm'])->middleware('log_action')->middleware('maintenance')->middleware('localhost')->middleware('authentication:38');
Route::post('/_forms/wildcard', [FormController::class, 'wildcardToSubmissionSettings'])->middleware('log_action')->middleware('maintenance')->middleware('localhost')->middleware('authentication:38');
Route::get('/_forms/{form_id}', [FormController::class, 'getForm'])->middleware('log_action')->middleware('maintenance')->middleware('localhost');
Route::post('/_forms/{form_id}', [FormController::class, 'updateForm'])->middleware('log_action')->middleware('maintenance')->middleware('localhost')->middleware('authentication:38');
Route::delete('/_forms/{form_id}', [FormController::class, 'deleteForm'])->middleware('log_action')->middleware('maintenance')->middleware('localhost')->middleware('authentication:38');

Route::get('/_forms/{form_id}/submissions', [FormController::class, 'getFormSubmissions'])->middleware('log_action')->middleware('maintenance')->middleware('localhost')->middleware('authentication:38');
Route::post('/_forms/{form_id}/submissions', [FormController::class, 'postFormSubmission'])->middleware('log_action')->middleware('maintenance')->middleware('localhost')->middleware('authentication:38');
Route::get('/_forms/{form_id}/submissions/{submission_id}', [FormController::class, 'getFormSubmission'])->middleware('log_action')->middleware('maintenance')->middleware('localhost')->middleware('authentication:38');
Route::post('/_forms/{form_id}/submissions/{submission_id}', [FormController::class, 'updateFormSubmission'])->middleware('log_action')->middleware('maintenance')->middleware('localhost')->middleware('authentication:38');
Route::delete('/_forms/{form_id}/submissions/{submission_id}', [FormController::class, 'deleteFormSubmission'])->middleware('log_action')->middleware('maintenance')->middleware('localhost')->middleware('authentication:38');


Route::post('/_forms/submissions/archive', [FormController::class, 'archiveSubmissions'])->middleware('log_action')->middleware('maintenance')->middleware('localhost')->middleware('authentication:38');
Route::post('/_forms/submissions/dearchive', [FormController::class, 'dearchiveSubmissions'])->middleware('log_action')->middleware('maintenance')->middleware('localhost')->middleware('authentication:38');
Route::get('/_forms/submissions/archive/{form_id}/{key?}', [FormController::class, 'getArchiveSubmissions'])->middleware('log_action')->middleware('maintenance')->middleware('localhost')->middleware('authentication:38');


Route::post('/_forms/anon/{form_id}/submissions', [FormController::class, 'postAnonFormSubmission'])->middleware('log_action')->middleware('maintenance')->middleware('localhost');

Route::get('/_forms/anon/confirm',[FormController::class, 'confirmAnonFormSubmission'])->name('anon_confirm')->middleware('log_action')->middleware('maintenance')->middleware('localhost');


Route::get('/archive',[ArchiveController::class, 'index'])->middleware('log_action')->middleware('authentication:51');
Route::post('/archive/directory',[ArchiveController::class, 'directory'])->middleware('log_action')->middleware('authentication:51');
Route::post('/archive/store',[ArchiveController::class, 'storeFile'])->middleware('log_action')->middleware('authentication:51');
Route::post('/archive/file/move',[ArchiveController::class, 'moveFile'])->middleware('log_action')->middleware('authentication:51');
Route::post('/archive/file/copy',[ArchiveController::class, 'copyFile'])->middleware('log_action')->middleware('authentication:51');
Route::post('/archive/file/rename',[ArchiveController::class, 'renameFile'])->middleware('log_action')->middleware('authentication:51');
Route::post('/archive/directory/rename',[ArchiveController::class, 'renameDir'])->middleware('log_action')->middleware('authentication:51');
Route::post('/archive/directory/create',[ArchiveController::class, 'createDir'])->middleware('log_action')->middleware('authentication:51');
Route::post('/archive',[ArchiveController::class, 'store'])->middleware('log_action')->middleware('authentication:51');
// Route::post('/archive/search',[ArchiveController::class, 'search']);
Route::post('/archive/search',[ArchiveController::class, 'search'])->middleware('log_action')->middleware('authentication:51');
Route::post('/archive/searchsuggestions',[ArchiveController::class, 'searchsuggestions'])->middleware('log_action')->middleware('authentication:51');


Route::post('/graduate/commit', [GraduateController::class, 'commit'])->middleware('log_action')->middleware('authentication:49');
Route::post('/graduate/get_by_matr', [GraduateController::class, 'getListByMatr'])->middleware('log_action')->middleware('authentication:49');
Route::post('/graduate/search', [GraduateController::class, 'search'])->middleware('log_action')->middleware('authentication:49');

Route::get('/archive/crawler', [ArchiveController::class, 'crawler'])->middleware('log_action')->middleware('authentication:51');


Route::get('/get_files', function(Request $request) {
  if(!$request->hasValidSignature()) {
    abort(401);
  }
  $root = Storage::disk($request->disk)->path('');
  $type=$request->type;
  if($type==="auto_bescheide") {
    $fragments = [$root, $type, $request->form, $request->file];
  } else {
    $fragments = [$root, $request->fragment];

  }
  $file = File::get(implode("\\",$fragments));
  $response = Response::make($file, 200);
  $tmp = explode("/",end($fragments));
  $response->header('Content-Type', getHeader(end($tmp)));
  $response->header('Content-Disposition', 'inline; filename="'.end($tmp).'"');
  return $response;
})->name('file_hosting');


function getHeader($fragment) {
  $exp_fragment = explode(".", $fragment);
  $file_ending = end($exp_fragment);
  if($file_ending=='pdf') {
    return 'application/pdf';
  } else if($file_ending=='jpg') {
    return 'image/jpeg';
  } else if($file_ending=='png') {
    return 'image/png';
  } else if($file_ending=='gif') {
    return 'image/gif';
  } else if($file_ending=='doc' || $file_ending=='dot') {
    return 'application/msword';
  } else if($file_ending=='docx') {
    return 'application/vnd.openxmlformats-officedocument.wordprocessingml.document';
  } else if($file_ending=='dotx') {
    return 'application/vnd.openxmlformats-officedocument.wordprocessingml.template';
  } else if($file_ending=='docm') {
    return 'application/vnd.ms-word.document.macroEnabled.12';
  } else if($file_ending=='dotm') {
    return 'application/vnd.ms-word.template.macroEnabled.12';
  } else if($file_ending==='xls') {
    return 'application/vnd.ms-excel';
  } else if($file_ending==='xlt') {
    return 'application/vnd.ms-excel';
  } else if($file_ending==='xla') {
    return 'application/vnd.ms-excel';
  } else if($file_ending==='xlsx') {
    return 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';
  } else if($file_ending==='xltx') {
    return 'application/vnd.openxmlformats-officedocument.spreadsheetml.template';
  } else if($file_ending==='xlsm') {
    return 'application/vnd.ms-excel.sheet.macroEnabled.12';
  } else if($file_ending==='xltm') {
    return 'application/vnd.ms-excel.template.macroEnabled.12';
  } else if($file_ending==='xlam') {
    return 'application/vnd.ms-excel.addin.macroEnabled.12';
  } else if($file_ending==='xlsb') {
    return 'application/vnd.ms-excel.sheet.binary.macroEnabled.12';
  } else if($file_ending==='ppt') {
    return 'application/vnd.ms-powerpoint';
  } else if($file_ending==='pot') {
    return 'application/vnd.ms-powerpoint';
  } else if($file_ending==='pps') {
    return 'application/vnd.ms-powerpoint';
  } else if($file_ending==='ppa') {
    return 'application/vnd.ms-powerpoint';
  } else if($file_ending==='pptx') {
    return 'application/vnd.openxmlformats-officedocument.presentationml.presentation';
  } else if($file_ending==='potx') {
    return 'application/vnd.openxmlformats-officedocument.presentationml.template';
  } else if($file_ending==='ppsx') {
    return 'application/vnd.openxmlformats-officedocument.presentationml.slideshow';
  } else if($file_ending==='ppam') {
    return 'application/vnd.ms-powerpoint.addin.macroEnabled.12';
  } else if($file_ending==='pptm') {
    return 'application/vnd.ms-powerpoint.presentation.macroEnabled.12';
  } else if($file_ending==='potm') {
    return 'application/vnd.ms-powerpoint.template.macroEnabled.12';
  } else if($file_ending==='ppsm') {
    return 'application/vnd.ms-powerpoint.slideshow.macroEnabled.12';
  } else if($file_ending==='mdb') {
    return 'application/vnd.ms-access';
  } else {
    return 'text/plain';
  }
}



Route::apiResource('/entrance_exam', EntranceExamController::class)->middleware('maintenance')->middleware('rights');
// Route::apiResource('/bewerberportal', BewerberController::class)->middleware('rights');
// // Route::apiResource('/bescheid', BescheidController::class)->middleware('rights');
Route::apiResource('/bescheid', BescheidController::class)->middleware('maintenance')->middleware('authentication:48');
Route::post('/bescheid/bewerber', [BescheidController::class, 'makeBewerberBescheid'])->middleware('maintenance')->middleware('authentication:48');



// Route::post('/lv/upload', [LehrveranstaltungController::class, 'upload']);


// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });
