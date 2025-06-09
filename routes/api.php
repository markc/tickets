<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\TicketController;
use App\Http\Controllers\KnowledgeBaseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

// Authentication routes (no auth required)
Route::prefix('auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
});

// Protected API routes
Route::middleware(['auth:sanctum'])->group(function () {
    // Authentication
    Route::prefix('auth')->group(function () {
        Route::get('/user', [AuthController::class, 'user']);
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::post('/logout-all', [AuthController::class, 'logoutAll']);
        Route::post('/refresh', [AuthController::class, 'refresh']);
    });

    // Tickets
    Route::prefix('tickets')->group(function () {
        Route::get('/', [TicketController::class, 'index']);
        Route::post('/', [TicketController::class, 'store']);
        Route::get('/stats', [TicketController::class, 'stats']);
        Route::get('/form-data', [TicketController::class, 'formData']);
        Route::get('/{uuid}', [TicketController::class, 'show']);
        Route::put('/{uuid}', [TicketController::class, 'update']);
        Route::delete('/{uuid}', [TicketController::class, 'destroy']);
    });

    // Knowledge Base (reuse existing controller)
    Route::prefix('knowledge-base')->group(function () {
        Route::get('/search', [KnowledgeBaseController::class, 'search']);
        Route::get('/trending', [KnowledgeBaseController::class, 'trending']);
        Route::get('/analytics', [KnowledgeBaseController::class, 'analytics']);
        Route::get('/tickets/{ticket}/suggestions', [KnowledgeBaseController::class, 'getSuggestions']);
        Route::get('/faqs/{faq}', [KnowledgeBaseController::class, 'show']);
        Route::post('/faqs/{faq}/format', [KnowledgeBaseController::class, 'format']);
        Route::post('/faqs/{faq}/track-usage', [KnowledgeBaseController::class, 'trackUsage']);
    });

    // User profile
    Route::get('/user/profile', function (Request $request) {
        return response()->json([
            'data' => $request->user()->load('offices'),
        ]);
    });

    // Health check
    Route::get('/health', function () {
        return response()->json([
            'status' => 'ok',
            'timestamp' => now()->toISOString(),
            'version' => config('app.version', '1.0.0'),
        ]);
    });
});

// Public API endpoints
Route::get('/status', function () {
    return response()->json([
        'api' => 'TIKM API',
        'version' => '1.0.0',
        'status' => 'active',
        'documentation' => url('/api/docs'),
    ]);
});
