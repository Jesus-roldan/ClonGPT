<?php

use Illuminate\Routing\Console\MiddlewareMakeCommand;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\ConversationController;
use App\Http\Controllers\ChatInstructionController;

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware(['auth','verified'])->group(function(){

Route::get('/', function() {return redirect()->route('conversations.index');})->name('root');

Route::get('conversations/{conversation?}', [ConversationController::class, 'index'])->name('conversations.index');

Route::delete('conversations/{conversation?}', [ConversationController::class, 'destroy'])->name('conversations.destroy');

Route::post('conversations/{conversation}/send-message', [ConversationController::class, 'sendMessage'])->name('conversations.sendMessage');

Route::post('conversations/{conversation}/model', [ConversationController::class, 'updateModel'])->name('conversations.updateModel');

Route::resource('conversations', ConversationController::class)->except('index', 'destroy', 'updateModel');

Route::get('/chat/instructions', [ChatInstructionController::class, 'edit'])->name('chat.instructions.edit');
Route::post('/chat/instructions', [ChatInstructionController::class, 'store'])->name('chat.instructions.store');
});

require __DIR__.'/settings.php';
