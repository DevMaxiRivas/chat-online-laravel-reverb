<?php
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\ChatController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', [\App\Http\Controllers\ChatController::class,'index'])->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::post('/chat/send', [\App\Http\Controllers\ChatController::class, 'sendMessage'])->name('chat.send');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware('auth')->group(function () {
    Route::get('/chat', [ChatController::class, 'index'])->name('chat.index');
    Route::get('/chat/{room}', [ChatController::class, 'room'])->name('chat.room');
    Route::post('/chat/messages', [ChatController::class, 'store'])->name('chat.store');

    Route::get('/turnero', [ChatController::class, 'turnero'])->name('chat.rooms');
});

Route::get('/test-broadcast', function () {
    $message = \App\Models\Message::create([
        'user_id' => auth()->id(),
        'content' => 'Test message',
        'room' => 'general',
    ]);
    
    broadcast(new \App\Events\MessageSent($message));
    
    return 'Broadcast sent!';
});

require __DIR__.'/auth.php';