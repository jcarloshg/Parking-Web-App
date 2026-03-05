<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ParkingSpaceController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ReportController;

Route::prefix('auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:api');
    Route::post('/refresh', [AuthController::class, 'refresh'])->middleware('auth:api');
    Route::get('/me', [AuthController::class, 'me'])->middleware('auth:api');
});

Route::get('parking-spaces', [ParkingSpaceController::class, 'index']);
Route::get('parking-spaces/{parkingSpace}', [ParkingSpaceController::class, 'show']);
Route::get('parking-spaces/available', [ParkingSpaceController::class, 'available']);
Route::get('parking-spaces/available-count', [ParkingSpaceController::class, 'availableCount']);

Route::get('tickets', [TicketController::class, 'index']);
Route::get('tickets/active', [TicketController::class, 'active']);
Route::get('tickets/search', [TicketController::class, 'search']);
Route::get('tickets/{ticket}', [TicketController::class, 'show']);
Route::get('tickets/{ticket}/calculate', [TicketController::class, 'calculate']);

Route::get('payments', [PaymentController::class, 'index']);
Route::get('payments/today', [PaymentController::class, 'today']);
Route::get('payments/calculate/{ticket}', [PaymentController::class, 'calculate']);
Route::get('payments/{payment}', [PaymentController::class, 'show']);

Route::middleware('auth:api')->group(function () {
    Route::apiResource('parking-spaces', ParkingSpaceController::class)->except(['index', 'show']);
    Route::apiResource('users', UserController::class);
    Route::post('tickets', [TicketController::class, 'store']);
    Route::post('tickets/{ticket}/checkout', [TicketController::class, 'checkout']);
    Route::post('payments', [PaymentController::class, 'store']);
    Route::get('reports/daily', [ReportController::class, 'daily']);
    Route::get('reports/monthly', [ReportController::class, 'monthly']);
});
