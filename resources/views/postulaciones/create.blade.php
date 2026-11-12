{{-- [CRUD-CREATE] registro de un candidato en una oferta activa --}}
@extends('layouts.app')

@section('titulo', 'Registrar postulación')

@section('contenido')
    <div class="mb-3">
        <a href="{{ route('postulaciones.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1" aria-hidden="true"></i>Volver al listado
        </a>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body p-4">
            <h1 class="h3 mb-4">Registrar postulación</h1>

            @if ($ofertas->isEmpty())
                <div class="alert alert-warning mb-0">
                    No hay ofertas activas en este momento. Las postulaciones solo se aceptan en ofertas abiertas.
                </div>
            @else
                <form action="{{ route('postulaciones.store') }}" method="POST" novalidate>
                    {{-- [SEGURIDAD] token CSRF --}}
                    @csrf

                    <div class="mb-3">
                        <label for="oferta_id" class="form-label">Oferta <span class="required-indicator" aria-hidden="true">*</span></label>
                        {{-- [REGLA-NEGOCIO] Solo se listan ofertas activas; el servidor vuelve a validarlo --}}
                        <select name="oferta_id" id="oferta_id" required class="form-select @error('oferta_id') is-invalid @enderror">
                            <option value="">Selecciona una oferta</option>
                            @foreach ($ofertas as $oferta)
                                <option value="{{ $oferta->id }}" @selected((string) old('oferta_id', $ofertaSeleccionada) === (string) $oferta->id)>
                                    {{ $oferta->titulo }} · {{ $oferta->empresa->nombre }} (cierra {{ $oferta->fecha_cierre->format('d/m/Y') }})
                                </option>
                            @endforeach
                        </select>
                        @error('oferta_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    @include('postulaciones._form')

                    <div class="d-flex justify-content-end gap-2 mt-4">
                        <a href="{{ route('postulaciones.index') }}" class="btn btn-outline-secondary">Cancelar</a>
                        <button type="submit" class="btn btn-primary"><i class="bi bi-send me-1" aria-hidden="true"></i>Registrar postulación</button>
                    </div>
                </form>
            @endif
        </div>
    </div>
@endsection
