<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\Auth\AuthController2;
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


//JWT
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::middleware('auth:api')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/guardarArchivo', [AuthController::class, 'guardarArchivo']); //Subir archivo con JWT
    Route::get('/listarArchivos', [AuthController::class, 'listarArchivos']); //Listar archivos con JWT
    Route::get('/buscarArchivo', [AuthController::class, 'buscarArchivo']); //Buscar archivos con JWT
    Route::get('/eliminarArchivo', [AuthController::class, 'eliminarArchivo']); //Buscar archivos con JWT
});


/* Route::middleware('auth:api')->group(function () {
    Route::get('/users-jwt', [UserController::class, 'getUsersForJWT']);
}); */


//Sanctum
Route::post('register2', [AuthController2::class, 'register']);
Route::post('login2', [AuthController2::class, 'login']);
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me2', [AuthController2::class, 'me']);
    Route::post('/logout2', [AuthController2::class, 'logout']);
});

/* Route::middleware('auth:sanctum')->group(function () {
    Route::get('/users-sanctum', [UserController::class, 'getUsersForSanctum']);
}); */
//Route::get('users', [ProductController::class, 'index']);


Route::get('/login', function () {
    Log::error('Error en la autenticación');
    return response()->json(['error' => 'No autenticado'], 401);
})->name('login');


Route::middleware('jwt.verify')->group(function () {
    Route::get('users', [UserController::class, 'index']);
});
