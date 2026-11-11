<?php

use App\Http\Controllers\EmpresaController;
use App\Http\Controllers\OfertaController;
use App\Http\Controllers\PanelController;
use Illuminate\Support\Facades\Route;

Route::get('/', [PanelController::class, 'index'])->name('panel');

Route::resource('ofertas', OfertaController::class)->only(['index', 'show']); // Fase 2: quitar only()

// Catalogo de empresas. parameters() fija el nombre {empresa} para el binding Empresa $empresa.
Route::resource('empresas', EmpresaController::class)
    ->parameters(['empresas' => 'empresa']);
