<?php

use Illuminate\Routing\Console\MiddlewareMakeCommand;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\ConversationController;

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware(['auth','verified'])->group(function(){

Route::get('/', function() {
        return redirect()->route('conversations.index');
    })->name('root');

Route::get('conversations/{conversation?}', [ConversationController::class, 'index'])->name('conversations.index');

Route::post('conversations/{conversation}/send-message', [ConversationController::class, 'sendMessage'])->name('conversations.sendMessage');

});

Route::resource('conversations', ConversationController::class)->except('index');



require __DIR__.'/settings.php';
