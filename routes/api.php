<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\OffreController;
use App\Http\Controllers\CandidatureController;

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

// Ancienne route Sanctum (peut être supprimée si vous n'utilisez plus Sanctum)
// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });

// Routes d'authentification (pas besoin de middleware)
Route::group(['prefix' => 'auth'], function () {
    Route::post('register', [UserController::class, 'register']);
    Route::post('login', [UserController::class, 'login']);
    
    // Ces routes nécessitent une authentification
    Route::group(['middleware' => 'auth:api'], function() {
        Route::post('logout', [UserController::class, 'logout']);
        Route::post('refresh', [UserController::class, 'refresh']);
        Route::get('me', [UserController::class, 'me']);
    });
});

// Routes protégées avec JWT
Route::middleware('auth:api')->group(function () {
    
    // Routes de profil
    Route::get('/profile', [ProfileController::class, 'show']);
    Route::put('/profile', [ProfileController::class, 'update']);
    
    // Routes de CV
    Route::post('/cvs', [ProfileController::class, 'uploadCV']);
    Route::get('/cvs', [ProfileController::class, 'getCVs']);
    Route::delete('/cvs/{id}', [ProfileController::class, 'deleteCV']);
    
    // Routes de candidature
    Route::get('/candidatures', [CandidatureController::class, 'index']);
    Route::post('/candidatures', [CandidatureController::class, 'store']);
    Route::get('/candidatures/{id}', [CandidatureController::class, 'show']);
    Route::post('/candidatures/bulk', [CandidatureController::class, 'storeBulk']);
    Route::put('/candidatures/{id}/cancel', [CandidatureController::class, 'cancel']); 
    
    // Routes d'administration
    Route::middleware('admin')->group(function () {
        Route::post('/offres', [OffreController::class, 'store']);
        Route::put('/offres/{id}', [OffreController::class, 'update']);
        Route::delete('/offres/{id}', [OffreController::class, 'destroy']);
        
        Route::put('/candidatures/{id}/status', [CandidatureController::class, 'updateStatus']);
    });
});

// Routes publiques
Route::get('/offres', [OffreController::class, 'index']);
Route::get('/offres/{id}', [OffreController::class, 'show']);