{{-- [CRUD-READ] ficha de detalle --}}
@extends('layouts.app')

@section('titulo', $oferta->titulo)

@section('contenido')
    <div class="mb-3">
        <a href="{{ route('ofertas.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i>Volver al listado
        </a>
    </div>

    <div class="row g-4">
        {{-- Columna izquierda: Detalle de la Oferta y Postulaciones --}}
        <div class="col-lg-8">
            {{-- Tarjeta de la Oferta --}}
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-body p-4">
                    <div class="text-center mb-4">
                        <img src="{{ $oferta->imagen_url }}" alt="{{ $oferta->titulo }}" class="imagen-oferta rounded">
                    </div>

                    <div class="d-flex flex-wrap gap-2 mb-3">
                        @if ($oferta->tipo === 'pasantia')
                            <span class="badge bg-info text-dark">
                                {{ \App\Models\Oferta::TIPOS[$oferta->tipo] ?? $oferta->tipo }}
                            </span>
                        @else
                            <span class="badge bg-primary">
                                {{ \App\Models\Oferta::TIPOS[$oferta->tipo] ?? $oferta->tipo }}
                            </span>
                        @endif

                        <span class="badge bg-light text-dark border">
                            {{ \App\Models\Oferta::MODALIDADES[$oferta->modalidad] ?? $oferta->modalidad }}
                        </span>

                        @if ($oferta->estaActiva())
                            <span class="badge bg-success">Activa</span>
                        @else
                            <span class="badge bg-secondary">Cerrada</span>
                        @endif
                    </div>

                    <h1 class="h3 fw-bold mb-3">{{ $oferta->titulo }}</h1>

                    <div class="row g-3 py-3 border-top border-bottom my-3 bg-light rounded px-2">
                        <div class="col-sm-6 col-md-3">
                            <span class="d-block text-muted small">Rango Salarial</span>
                            <span class="fw-semibold text-dark">{{ $oferta->rango_salarial }}</span>
                        </div>
                        <div class="col-sm-6 col-md-3">
                            <span class="d-block text-muted small">Vacantes</span>
                            <span class="fw-semibold text-dark">{{ $oferta->vacantes }}</span>
                        </div>
                        <div class="col-sm-6 col-md-3">
                            <span class="d-block text-muted small">Fecha Publicación</span>
                            <span class="fw-semibold text-dark">{{ $oferta->fecha_publicacion ? $oferta->fecha_publicacion->format('d/m/Y') : 'N/A' }}</span>
                        </div>
                        <div class="col-sm-6 col-md-3">
                            <span class="d-block text-muted small">Fecha Cierre</span>
                            <span class="fw-semibold text-dark">{{ $oferta->fecha_cierre ? $oferta->fecha_cierre->format('d/m/Y') : 'N/A' }}</span>
                        </div>
                    </div>

                    <h2 class="h5 fw-bold mb-2">Descripción del puesto</h2>
                    <p class="text-secondary mb-0" style="white-space: pre-line;">{{ $oferta->descripcion }}</p>
                </div>
            </div>

            {{-- Sección Postulaciones --}}
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white py-3">
                    <h2 class="h5 fw-bold mb-0">Postulaciones ({{ $oferta->postulaciones->count() }})</h2>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th scope="col">Candidato</th>
                                    <th scope="col">Correo</th>
                                    <th scope="col">Carrera</th>
                                    <th scope="col">CV</th>
                                    <th scope="col">Fecha</th>
                                    <th scope="col">Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($oferta->postulaciones as $postulacion)
                                    <tr>
                                        <td class="fw-semibold">{{ $postulacion->nombre_candidato }}</td>
                                        <td>
                                            <a href="mailto:{{ $postulacion->correo }}" class="text-decoration-none">
                                                {{ $postulacion->correo }}
                                            </a>
                                        </td>
                                        <td>{{ $postulacion->carrera }}</td>
                                        <td>
                                            <a href="{{ $postulacion->enlace_cv }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-primary">
                                                <i class="bi bi-file-earmark-text me-1"></i>Ver CV
                                            </a>
                                        </td>
                                        <td>{{ $postulacion->fecha_postulacion ? $postulacion->fecha_postulacion->format('d/m/Y') : 'N/A' }}</td>
                                        <td>
                                            @php
                                                $badgeClass = match ($postulacion->estado) {
                                                    'aceptada' => 'bg-success',
                                                    'en_revision' => 'bg-warning text-dark',
                                                    'rechazada' => 'bg-danger',
                                                    default => 'bg-info text-dark',
                                                };
                                            @endphp
                                            <span class="badge {{ $badgeClass }}">
                                                {{ \App\Models\Postulacion::ESTADOS[$postulacion->estado] ?? $postulacion->estado }}
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-4 text-muted">
                                            No hay postulaciones registradas para esta oferta.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- Columna derecha: Tarjeta de la Empresa --}}
        <div class="col-lg-4">
            <div class="card shadow-sm border-0 sticky-top" style="top: 1.5rem;">
                <div class="card-header bg-white py-3">
                    <h2 class="h5 fw-bold mb-0">Información de la Empresa</h2>
                </div>
                <div class="card-body p-4">
                    <h3 class="h5 fw-bold text-primary mb-2">{{ $oferta->empresa->nombre }}</h3>
                    <p class="text-muted small mb-3">
                        <i class="bi bi-briefcase me-1"></i>{{ $oferta->empresa->rubro }}
                    </p>

                    <ul class="list-unstyled mb-0">
                        <li class="mb-2">
                            <span class="d-block text-muted small">Correo de contacto:</span>
                            <a href="mailto:{{ $oferta->empresa->correo_contacto }}" class="text-decoration-none">
                                <i class="bi bi-envelope me-1"></i>{{ $oferta->empresa->correo_contacto }}
                            </a>
                        </li>
                        <li class="mb-0">
                            <span class="d-block text-muted small">Teléfono:</span>
                            <span class="text-dark">
                                <i class="bi bi-telephone me-1"></i>{{ $oferta->empresa->telefono ?? 'No especificado' }}
                            </span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
@endsection
