<?php

use App\Http\Controllers\OfertaController;
use App\Http\Controllers\PanelController;
use Illuminate\Support\Facades\Route;

Route::get('/', [PanelController::class, 'index'])->name('panel');

Route::resource('ofertas', OfertaController::class)->only(['index', 'show']); // Fase 2: quitar only()
