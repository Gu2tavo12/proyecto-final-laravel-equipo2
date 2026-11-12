<?php

use App\Http\Controllers\EmpresaController;
use App\Http\Controllers\OfertaController;
use App\Http\Controllers\PanelController;
use Illuminate\Support\Facades\Route;

Route::get('/', [PanelController::class, 'index'])->name('panel');

// Ofertas: consulta, registro y edicion. destroy se activa en el siguiente paso.
Route::resource('ofertas', OfertaController::class)->except(['destroy']);

// Catalogo de empresas. parameters() fija el nombre {empresa} para el binding Empresa $empresa.
Route::resource('empresas', EmpresaController::class)
    ->parameters(['empresas' => 'empresa']);
