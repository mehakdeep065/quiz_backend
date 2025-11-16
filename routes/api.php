<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\QuestionController;
use App\Http\Controllers\AttemptController;
use Illuminate\Http\Request;


// Public routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Public question routes (questions available without authentication for quiz)
Route::prefix('questions')->group(function () {
    Route::get('/', [QuestionController::class, 'index']);
    Route::get('/random', [QuestionController::class, 'random']);
    Route::get('/{id}', [QuestionController::class, 'show']);
});

// Protected routes (require authentication)
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
   
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    
    // Question management routes (create, update, delete - typically admin only)
    Route::prefix('questions')->group(function () {
        Route::post('/', [QuestionController::class, 'store']);
        Route::put('/{id}', [QuestionController::class, 'update']);
        Route::delete('/{id}', [QuestionController::class, 'destroy']);
    });
    
    // Attempt routes (user's quiz attempts)
    Route::prefix('attempts')->group(function () {
        Route::get('/', [AttemptController::class, 'index']);
        Route::get('/statistics', [AttemptController::class, 'statistics']);
        Route::get('/question/{questionId}', [AttemptController::class, 'getByQuestion']);
        Route::get('/{id}', [AttemptController::class, 'show']);
        Route::post('/', [AttemptController::class, 'store']);
    });
});
Route::get('/leaderboard', [AttemptController::class, 'leaderboard']);

//test route 
Route::get('/test', function () {
    return response()->json(['message' => 'Laravel is connected']);
});

 Route::post('/check-answers', [QuestionController::class, 'checkAnswers']);




