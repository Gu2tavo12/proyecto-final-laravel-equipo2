<?php

use App\Http\Controllers\EmpresaController;
use App\Http\Controllers\OfertaController;
use App\Http\Controllers\PanelController;
use App\Http\Controllers\PostulacionController;
use Illuminate\Support\Facades\Route;

Route::get('/', [PanelController::class, 'index'])->name('panel');

// Ofertas: CRUD completo (index, create, store, show, edit, update, destroy).
// [SEGURIDAD] destroy solo responde a DELETE (con @csrf y @method('DELETE')).
Route::resource('ofertas', OfertaController::class);

// Catalogo de empresas. parameters() fija el nombre {empresa} para el binding Empresa $empresa.
Route::resource('empresas', EmpresaController::class)
    ->parameters(['empresas' => 'empresa']);

// Postulaciones. parameters() fija {postulacion} (el singular en ingles seria "postulacione").
Route::resource('postulaciones', PostulacionController::class)
    ->parameters(['postulaciones' => 'postulacion'])
    ->only(['index', 'create', 'store', 'show', 'edit', 'update']);
