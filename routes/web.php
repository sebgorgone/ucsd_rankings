<?php

use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\AdvanceBracketController;
use App\Http\Controllers\BracketController;
use App\Http\Controllers\BracketStatsController;
use App\Http\Controllers\CandidateController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ResetBracketController;
use App\Http\Controllers\TieResolutionController;
use App\Http\Controllers\ToggleBracketPauseController;
use App\Http\Controllers\VoteController;
use App\Models\Bracket;
use Illuminate\Support\Facades\Route;

Route::get('/', [BracketController::class, 'index'])->name('home');
Route::get('/brackets', [BracketController::class, 'index'])->name('brackets.index');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', function () {
        $brackets = Bracket::query()
            ->whereBelongsTo(request()->user())
            ->withCount('candidates')
            ->latest()
            ->get();

        return view('dashboard', compact('brackets'));
    })->name('dashboard');

    Route::get('/brackets/create', [BracketController::class, 'create'])->name('brackets.create');
    Route::post('/brackets', [BracketController::class, 'store'])->name('brackets.store');
    Route::get('/brackets/{bracket}/edit', [BracketController::class, 'edit'])->name('brackets.edit');
    Route::patch('/brackets/{bracket}', [BracketController::class, 'update'])->name('brackets.update');
    Route::get('/brackets/{bracket}/stats', BracketStatsController::class)->name('brackets.stats');
    Route::put('/brackets/{bracket}/advance', AdvanceBracketController::class)->name('brackets.advance');
    Route::put('/brackets/{bracket}/pause', ToggleBracketPauseController::class)->name('brackets.pause');
    Route::put('/brackets/{bracket}/reset', ResetBracketController::class)->name('brackets.reset');
    Route::post('/brackets/{bracket}/candidates', CandidateController::class)->name('brackets.candidates.store');
    Route::put('/matchups/{matchup}/vote', VoteController::class)->name('matchups.vote');
    Route::put('/matchups/{matchup}/resolve-tie', TieResolutionController::class)->name('matchups.resolve-tie');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/admin/users', [AdminUserController::class, 'index'])->name('admin.users.index');
    Route::get('/admin/users/{user}', [AdminUserController::class, 'show'])->name('admin.users.show');
});

Route::get('/brackets/{bracket}', [BracketController::class, 'show'])->name('brackets.show');

require __DIR__.'/auth.php';
