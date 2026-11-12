{{-- [CRUD-READ] ficha de la postulacion con su oferta y empresa --}}
@extends('layouts.app')

@section('titulo', 'Postulación de '.$postulacion->nombre_candidato)

@section('contenido')
    <div class="mb-3">
        <a href="{{ route('postulaciones.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1" aria-hidden="true"></i>Volver al listado
        </a>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card shadow-sm border-0">
                <div class="card-body p-4">
                    <div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-3">
                        <div>
                            <h1 class="h3 fw-bold mb-1">{{ $postulacion->nombre_candidato }}</h1>
                            @include('postulaciones._estado', ['estado' => $postulacion->estado])
                        </div>
                        <div class="d-flex gap-2">
                            <a href="{{ route('postulaciones.edit', $postulacion) }}" class="btn btn-outline-secondary btn-sm">
                                <i class="bi bi-pencil me-1" aria-hidden="true"></i>Editar
                            </a>
                            {{-- [CRUD-DELETE] [SEGURIDAD] DELETE con CSRF y confirmacion previa (modal comun del layout) --}}
                            <form action="{{ route('postulaciones.destroy', $postulacion) }}" method="POST"
                                  data-confirmar-eliminar="¿Eliminar la postulación de «{{ $postulacion->nombre_candidato }}» a «{{ $postulacion->oferta->titulo }}»? La oferta se conserva. Esta acción no se puede deshacer.">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-outline-danger btn-sm">
                                    <i class="bi bi-trash me-1" aria-hidden="true"></i>Eliminar
                                </button>
                            </form>
                        </div>
                    </div>

                    <dl class="row mb-0">
                        <dt class="col-sm-4 text-muted fw-normal">Correo</dt>
                        <dd class="col-sm-8">
                            <a href="mailto:{{ $postulacion->correo }}" class="text-decoration-none">{{ $postulacion->correo }}</a>
                        </dd>

                        <dt class="col-sm-4 text-muted fw-normal">Carrera</dt>
                        <dd class="col-sm-8">{{ $postulacion->carrera }}</dd>

                        <dt class="col-sm-4 text-muted fw-normal">Fecha de postulación</dt>
                        <dd class="col-sm-8">{{ $postulacion->fecha_postulacion->format('d/m/Y') }}</dd>

                        <dt class="col-sm-4 text-muted fw-normal">Currículum</dt>
                        <dd class="col-sm-8 mb-0">
                            @include('postulaciones._enlace-cv')
                            <span class="d-block text-muted small text-break mt-1">{{ $postulacion->enlace_cv }}</span>
                        </dd>
                    </dl>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white py-3">
                    <h2 class="h5 fw-bold mb-0">Oferta</h2>
                </div>
                <div class="card-body p-4">
                    <h3 class="h6 fw-bold mb-1">
                        <a href="{{ route('ofertas.show', $postulacion->oferta) }}" class="text-decoration-none">{{ $postulacion->oferta->titulo }}</a>
                    </h3>
                    <p class="text-muted small mb-3">
                        <i class="bi bi-building me-1" aria-hidden="true"></i>{{ $postulacion->oferta->empresa->nombre }}
                    </p>
                    <ul class="list-unstyled small mb-3">
                        <li>{{ \App\Models\Oferta::TIPOS[$postulacion->oferta->tipo] ?? $postulacion->oferta->tipo }}
                            · {{ \App\Models\Oferta::MODALIDADES[$postulacion->oferta->modalidad] ?? $postulacion->oferta->modalidad }}</li>
                        <li>Cierre: {{ $postulacion->oferta->fecha_cierre->format('d/m/Y') }}
                            @if ($postulacion->oferta->estaActiva())
                                <span class="badge bg-success ms-1">Activa</span>
                            @else
                                <span class="badge bg-secondary ms-1">Cerrada</span>
                            @endif
                        </li>
                    </ul>
                    <a href="{{ route('postulaciones.index', ['oferta_id' => $postulacion->oferta_id]) }}" class="btn btn-outline-primary btn-sm">
                        <i class="bi bi-people me-1" aria-hidden="true"></i>Postulaciones de esta oferta
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection
