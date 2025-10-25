<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProfileController;
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

// routes/api.php
Route::get('/technician/{email}/profile', [ProfileController::class, 'getTechnicianProfile']);


Route::get('/bookings', [BookingController::class, 'index']);
Route::get('/bookings/technician/{technician_id}/{status}', [BookingController::class, 'byTechnicianAndStatus']);
Route::get('/bookings/{id}', [BookingController::class, 'show']);


