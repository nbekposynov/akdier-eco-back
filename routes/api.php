<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\ModeratorController;
use App\Http\Controllers\ProcessingController;
use App\Models\Moderator;
use App\Models\Processing;

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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);
Route::post('/register_moderator', [AuthController::class, 'registerModerator']);
Route::post('/register_company', [AuthController::class, 'registerCompany'])->middleware('auth:sanctum');

Route::post('/add_processing', [ProcessingController::class, 'create'])->middleware('auth:sanctum');
Route::get('/get_by_range', [ProcessingController::class, 'getByDateRange'])->middleware('auth:sanctum');
Route::get('/getByDateRangeAdmin', [ProcessingController::class, 'getByDateRangeAdmin'])->middleware('auth:sanctum');
Route::post('/getByDateAdmin', [ProcessingController::class, 'getByDateAdmin'])->middleware('auth:sanctum');
Route::get('/getByDateRangeCompany', [ProcessingController::class, 'getByDateRangeCompany'])->middleware('auth:sanctum');
Route::get('/export_by_month', [ProcessingController::class, 'exportByMonth'])->middleware('auth:sanctum');
Route::get('/exportByCompany', [ProcessingController::class, 'exportByMonthCompany'])->middleware('auth:sanctum');
Route::get('/exportByAdmin', [ProcessingController::class, 'exportByAdmin'])->middleware('auth:sanctum');
Route::post('/show_processing/{id}', [ProcessingController::class, 'getById'])->middleware('auth:sanctum');
Route::post('/show_processing', [ProcessingController::class, 'index'])->middleware('auth:sanctum');
Route::post('/updateById/{id}', [ProcessingController::class, 'updateById'])->middleware('auth:sanctum');


Route::post('/getByIdFinal/{id}', [ProcessingController::class, 'getByIdFinal'])->middleware('auth:sanctum');
Route::post('/updateByIdFinal/{id}', [ProcessingController::class, 'updateByIdFinal'])->middleware('auth:sanctum');
Route::post('/getByDateRangeAdminFinal', [ProcessingController::class, 'getFinalProcessingData'])->middleware('auth:sanctum');
Route::post('/ExportByDateRangeAdminFinal', [ProcessingController::class, 'exportFinalProcessingData'])->middleware('auth:sanctum');
Route::post('/getByDateAdminFinal', [ProcessingController::class, 'getByDateAdminFinal'])->middleware('auth:sanctum');


Route::post('/show_companies', [CompanyController::class, 'index'])->middleware('auth:sanctum');
Route::get('/getCompanyById', [CompanyController::class, 'getCompanyById'])->middleware('auth:sanctum');
Route::POST('/getByIdCompany/{id}', [CompanyController::class, 'getByIdCompany'])->middleware('auth:sanctum');
Route::post('/updateByIdCompany/{id}', [CompanyController::class, 'updateByIdCompany'])->middleware('auth:sanctum');

Route::post('/updateByIdModerator/{id}', [ModeratorController::class, 'updateByIdModerator'])->middleware('auth:sanctum');
Route::post('/getByIdModerator/{id}', [ModeratorController::class, 'getByIdModerator'])->middleware('auth:sanctum');
Route::get('/show_moderators', [ModeratorController::class, 'index'])->middleware('auth:sanctum');

Route::delete('/delete_by_range_final', [ProcessingController::class, 'DeleteByRangeFinal'])->middleware('auth:sanctum');
Route::delete('/delete_by_range', [ProcessingController::class, 'DeleteByRangeProcessing'])->middleware('auth:sanctum');

