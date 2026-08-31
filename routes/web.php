<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view()->exists('scribe.index') ?
        view('scribe.index') :
        view('scribe.404');
});
