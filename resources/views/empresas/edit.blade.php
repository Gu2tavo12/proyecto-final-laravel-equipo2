{{-- [CRUD-UPDATE] formulario de edicion de empresa --}}
@extends('layouts.app')

@section('titulo', 'Editar empresa')

@section('contenido')
    <div class="mb-3">
        <a href="{{ route('empresas.show', $empresa) }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1" aria-hidden="true"></i>Volver a la ficha
        </a>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body p-4">
            <h1 class="h3 mb-4">Editar empresa</h1>

            <form action="{{ route('empresas.update', $empresa) }}" method="POST" novalidate>
                {{-- [SEGURIDAD] token CSRF y metodo PUT simulado --}}
                @csrf
                @method('PUT')
                @include('empresas._form', ['empresa' => $empresa])

                <div class="d-flex justify-content-end gap-2 mt-4">
                    <a href="{{ route('empresas.show', $empresa) }}" class="btn btn-outline-secondary">Cancelar</a>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-save me-1" aria-hidden="true"></i>Guardar cambios</button>
                </div>
            </form>
        </div>
    </div>
@endsection
