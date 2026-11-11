{{-- [CRUD-UPDATE] Formulario de modificacion de oferta de empleo o pasantia existente --}}
@extends('layouts.app')

@section('titulo', 'Editar: ' . $oferta->titulo)

@section('contenido')
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <nav aria-label="breadcrumb" class="mb-3">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('panel') }}">Inicio</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('ofertas.index') }}">Ofertas</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('ofertas.show', $oferta) }}">{{ Str::limit($oferta->titulo, 30) }}</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Editar</li>
                </ol>
            </nav>

            <div class="card shadow-sm border-0">
                <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                    <div>
                        <h1 class="h4 mb-0 fw-bold">Editar Oferta</h1>
                        <p class="text-muted small mb-0">Modificando la oferta: <strong>{{ $oferta->titulo }}</strong></p>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="{{ route('ofertas.show', $oferta) }}" class="btn btn-outline-secondary btn-sm">
                            <i class="bi bi-eye me-1" aria-hidden="true"></i>Ver ficha
                        </a>
                        <a href="{{ route('ofertas.index') }}" class="btn btn-outline-secondary btn-sm">
                            <i class="bi bi-arrow-left me-1" aria-hidden="true"></i>Volver al listado
                        </a>
                    </div>
                </div>

                <div class="card-body p-4">
                    <form action="{{ Route::has('ofertas.update') ? route('ofertas.update', $oferta) : url('/ofertas/' . $oferta->id) }}"
                          method="POST"
                          enctype="multipart/form-data"
                          novalidate>
                        @csrf
                        @method('PUT')

                        @include('ofertas._form')

                        <div class="d-flex flex-wrap justify-content-end gap-2 mt-4 pt-3 border-top">
                            <a href="{{ route('ofertas.show', $oferta) }}" class="btn btn-outline-secondary">
                                <i class="bi bi-x-circle me-1" aria-hidden="true"></i>Cancelar
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-circle me-1" aria-hidden="true"></i>Actualizar Oferta
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
