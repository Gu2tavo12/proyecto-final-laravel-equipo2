<?php

use App\Http\Controllers\OfertaController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::resource('ofertas', OfertaController::class)->only(['index', 'show']); // Fase 2: quitar only()
