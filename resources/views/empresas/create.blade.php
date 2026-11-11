{{-- [CRUD-CREATE] formulario de registro de empresa --}}
@extends('layouts.app')

@section('titulo', 'Registrar empresa')

@section('contenido')
    <div class="mb-3">
        <a href="{{ route('empresas.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1" aria-hidden="true"></i>Volver al catálogo
        </a>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body p-4">
            <h1 class="h3 mb-4">Registrar empresa</h1>

            <form action="{{ route('empresas.store') }}" method="POST" novalidate>
                {{-- [SEGURIDAD] token CSRF --}}
                @csrf
                @include('empresas._form')

                <div class="d-flex justify-content-end gap-2 mt-4">
                    <a href="{{ route('empresas.index') }}" class="btn btn-outline-secondary">Cancelar</a>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-save me-1" aria-hidden="true"></i>Guardar</button>
                </div>
            </form>
        </div>
    </div>
@endsection
