<?php

use Illuminate\Support\Facades\Route;

Route::get('/docs-api', function () {
    return view()->exists('scribe.index') ?
        view('scribe.index') :
        view('scribe.404');
});
