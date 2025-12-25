<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TodayTaskController;
use App\Http\Controllers\Api\BookingController;

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

// Authentication
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);
Route::post('/technician/profile', [ProfileController::class, 'store']);
Route::post('/technician/profile/avatar', [ProfileController::class, 'updateAvatar']);
Route::post('/technician/profile/update', [ProfileController::class, 'updateAccountInfo']);

// Today Tasks routes - no auth required for mobile app
Route::get('/today-tasks', [TodayTaskController::class, 'index']);
Route::post('/today-tasks', [TodayTaskController::class, 'store']);
Route::post('/today-tasks/{id}/update', [TodayTaskController::class, 'update']);

// routes/api.php
Route::get('/technician/{email}/profile', [ProfileController::class, 'getTechnicianProfile']);
Route::get('/technician/{id}/allprofile', [ProfileController::class, 'getTechnicianAllProfile']);
Route::get('/technician/allprofile', [ProfileController::class, 'getTechnicianAll']);


Route::get('/bookings', [BookingController::class, 'index']);
Route::get('/bookings/technician/{technician_id}/{status}', [BookingController::class, 'byTechnicianAndStatus']);
Route::get('/bookings/{id}', [BookingController::class, 'show']);
Route::post('/bookings/{id}/installation-proof', [BookingController::class, 'uploadInstallationProof']);

