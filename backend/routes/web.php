<?php

use App\Http\Controllers\EditorContextController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/editor/sites/{site}/pages/{page}', EditorContextController::class)
    ->scopeBindings()
    ->name('editor.context');
