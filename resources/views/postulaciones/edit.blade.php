{{-- [CRUD-UPDATE] edicion de los datos y el estado de una postulacion --}}
@extends('layouts.app')

@section('titulo', 'Editar postulación')

@section('contenido')
    <div class="mb-3">
        <a href="{{ route('postulaciones.show', $postulacion) }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1" aria-hidden="true"></i>Volver a la ficha
        </a>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body p-4">
            <h1 class="h3 mb-3">Editar postulación</h1>

            {{-- La oferta y la fecha no se editan: se muestran solo como referencia --}}
            <div class="alert alert-light border mb-4">
                <span class="d-block small text-muted">Oferta</span>
                <span class="fw-semibold">{{ $postulacion->oferta->titulo }}</span>
                · {{ $postulacion->oferta->empresa->nombre }}
                @unless ($postulacion->oferta->estaActiva())
                    <span class="badge bg-secondary ms-1">Cerrada</span>
                @endunless
                <span class="d-block small text-muted mt-1">Postulación del {{ $postulacion->fecha_postulacion->format('d/m/Y') }}</span>
            </div>

            <form action="{{ route('postulaciones.update', $postulacion) }}" method="POST" novalidate>
                {{-- [SEGURIDAD] token CSRF y metodo PUT simulado --}}
                @csrf
                @method('PUT')

                <div class="mb-3">
                    <label for="estado" class="form-label">Estado <span class="required-indicator" aria-hidden="true">*</span></label>
                    <select name="estado" id="estado" required class="form-select @error('estado') is-invalid @enderror">
                        @foreach (\App\Models\Postulacion::ESTADOS as $valor => $etiqueta)
                            <option value="{{ $valor }}" @selected(old('estado', $postulacion->estado) === $valor)>{{ $etiqueta }}</option>
                        @endforeach
                    </select>
                    @error('estado')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                @include('postulaciones._form', ['postulacion' => $postulacion])

                <div class="d-flex justify-content-end gap-2 mt-4">
                    <a href="{{ route('postulaciones.show', $postulacion) }}" class="btn btn-outline-secondary">Cancelar</a>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-save me-1" aria-hidden="true"></i>Guardar cambios</button>
                </div>
            </form>
        </div>
    </div>
@endsection
