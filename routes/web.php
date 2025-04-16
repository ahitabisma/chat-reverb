<?php

use App\Http\Controllers\ChatController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', [ChatController::class, 'index'])->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Chat
    Route::get('/chat', [ChatController::class, 'index'])->name('chat.index');
    Route::get('/chat/{chatId}', [ChatController::class, 'show'])->name('chat.show');
    Route::post('/chat/{chatId}/send', [ChatController::class, 'sendMessage'])->name('chat.send');
    Route::get('/chat/start/{userId}/{type}', [ChatController::class, 'startChat'])->name('chat.start');
    Route::get('/chat/{chatId}/messages', [ChatController::class, 'getMessages'])->name('chat.messages');
});

require __DIR__.'/auth.php';
