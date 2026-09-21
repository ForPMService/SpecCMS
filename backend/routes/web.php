<?php

use App\Models\Page;
use App\Models\Site;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/editor/sites/{site}/pages/{page}', function (Site $site, Page $page) {
    return view('editor.context', [
        'site' => $site,
        'page' => $page,
    ]);
})->scopeBindings()->name('editor.context');
