<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (auth()->check()) {
        $user = auth()->user();
        if ($user->isAdmin() || $user->isAgent()) {
            return redirect('/admin');
        } else {
            return redirect('/dashboard');
        }
    }

    return redirect('/admin/login');
});

Route::get('/dashboard', [\App\Http\Controllers\DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])->name('dashboard');

Route::get('/faq', [\App\Http\Controllers\FAQController::class, 'index'])->name('faq.index');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/search', [\App\Http\Controllers\SearchController::class, 'search'])
        ->middleware('rate.limit:search')
        ->name('search');

    // Saved Search routes
    Route::post('/search/save', [\App\Http\Controllers\SearchController::class, 'saveSearch'])->name('search.save');
    Route::delete('/search/saved/{savedSearch}', [\App\Http\Controllers\SearchController::class, 'deleteSavedSearch'])->name('search.saved.delete');
    Route::get('/api/search/saved', [\App\Http\Controllers\SearchController::class, 'getSavedSearches'])->name('api.search.saved');

    Route::get('/analytics', [\App\Http\Controllers\AnalyticsController::class, 'index'])->name('analytics.dashboard');

    // Canned Responses API routes
    Route::prefix('api/canned-responses')->group(function () {
        Route::get('/', [\App\Http\Controllers\CannedResponseController::class, 'index'])->name('api.canned-responses.index');
        Route::get('/{cannedResponse}', [\App\Http\Controllers\CannedResponseController::class, 'show'])->name('api.canned-responses.show');
        Route::post('/{cannedResponse}/preview', [\App\Http\Controllers\CannedResponseController::class, 'preview'])->name('api.canned-responses.preview');
        Route::post('/{cannedResponse}/use', [\App\Http\Controllers\CannedResponseController::class, 'use'])->name('api.canned-responses.use');
    });

    Route::get('/tickets', [\App\Http\Controllers\TicketController::class, 'index'])->name('tickets.index');
    Route::get('/tickets/create', [\App\Http\Controllers\TicketController::class, 'create'])->name('tickets.create');
    Route::post('/tickets', [\App\Http\Controllers\TicketController::class, 'store'])
        ->middleware('rate.limit:tickets')
        ->name('tickets.store');
    Route::get('/tickets/{ticket}', [\App\Http\Controllers\TicketController::class, 'show'])->name('tickets.show');
    Route::post('/tickets/{ticket}/reply', [\App\Http\Controllers\TicketController::class, 'reply'])
        ->middleware('rate.limit:tickets')
        ->name('tickets.reply');

    // Ticket merging routes
    Route::get('/tickets/{ticket}/merge', [\App\Http\Controllers\TicketMergeController::class, 'show'])->name('tickets.merge.show');
    Route::get('/tickets/{ticket}/merge/search', [\App\Http\Controllers\TicketMergeController::class, 'search'])->name('tickets.merge.search');
    Route::get('/tickets/{ticket}/merge/preview', [\App\Http\Controllers\TicketMergeController::class, 'preview'])->name('tickets.merge.preview');
    Route::post('/tickets/{ticket}/merge', [\App\Http\Controllers\TicketMergeController::class, 'merge'])->name('tickets.merge');
});

require __DIR__.'/auth.php';
