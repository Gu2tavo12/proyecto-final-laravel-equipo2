{{-- [CRUD-CREATE] Formulario de registro de nuevas ofertas de empleo y pasantias --}}
@extends('layouts.app')

@section('titulo', 'Nueva Oferta')

@section('contenido')
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <nav aria-label="breadcrumb" class="mb-3">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('panel') }}">Inicio</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('ofertas.index') }}">Ofertas</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Nueva Oferta</li>
                </ol>
            </nav>

            <div class="card shadow-sm border-0">
                <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                    <div>
                        <h1 class="h4 mb-0 fw-bold">Publicar Nueva Oferta</h1>
                        <p class="text-muted small mb-0">Complete la información requerida para registrar la vacante.</p>
                    </div>
                    <a href="{{ route('ofertas.index') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-arrow-left me-1" aria-hidden="true"></i>Volver al listado
                    </a>
                </div>

                <div class="card-body p-4">
                    <form action="{{ Route::has('ofertas.store') ? route('ofertas.store') : url('/ofertas') }}"
                          method="POST"
                          enctype="multipart/form-data"
                          novalidate>
                        @csrf

                        @include('ofertas._form')

                        <div class="d-flex flex-wrap justify-content-end gap-2 mt-4 pt-3 border-top">
                            <a href="{{ route('ofertas.index') }}" class="btn btn-outline-secondary">
                                <i class="bi bi-x-circle me-1" aria-hidden="true"></i>Cancelar
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-circle me-1" aria-hidden="true"></i>Guardar Oferta
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
